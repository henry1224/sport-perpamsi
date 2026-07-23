<?php

namespace App\Support\Porpamnas;

use App\Actions\Matches\SubmitMatchScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicDataService
{
    public function pageProps(): array
    {
        $data = $this->masterData();

        return [
            ...$data,
            'results' => $this->results(),
            'provinceRankings' => $this->provinceRankings(),
            'sportTechnicalGuides' => $this->technicalGuides(),
            'assets' => $this->assets(),
        ];
    }

    private function technicalGuides()
    {
        if (Schema::hasColumn('sport_regulations', 'technical_guide')) {
            return DB::table('sport_regulations')->join('sports', 'sport_regulations.sport_id', '=', 'sports.id')
                ->where('sports.is_active', true)->where('sport_regulations.is_active', true)->whereNotNull('sport_regulations.technical_guide')
                ->orderByDesc('sport_regulations.version')->get(['sports.code as sport_code', 'sport_regulations.technical_guide'])
                ->unique('sport_code')->map(function ($row) {
                    $guide = json_decode($row->technical_guide, true) ?: [];

                    return ['sport_code' => $row->sport_code, ...$guide];
                })->values();
        }

        return collect(json_decode(file_get_contents(base_path('data/seed/sport_technical_guides.json')), true));
    }

    public function adminScoreRows(): array
    {
        if (Schema::hasTable('matches')) {
            return DB::table('matches')
                ->join('tournament_events', 'matches.tournament_event_id', '=', 'tournament_events.id')
                ->join('sports', 'tournament_events.sport_id', '=', 'sports.id')
                ->leftJoin('event_entries as a', 'matches.entry_a_id', '=', 'a.id')
                ->leftJoin('event_entries as b', 'matches.entry_b_id', '=', 'b.id')
                ->leftJoin('entry_teams as ta', 'matches.team_a_id', '=', 'ta.id')
                ->leftJoin('entry_teams as tb', 'matches.team_b_id', '=', 'tb.id')
                ->whereNotNull('matches.entry_a_id')
                ->whereNotNull('matches.entry_b_id')
                ->orderBy('matches.id')
                ->limit(24)
                ->select('matches.code as id', 'sports.name as sport', DB::raw('COALESCE(ta.label, a.display_name) as team_a'), DB::raw('COALESCE(tb.label, b.display_name) as team_b'), 'matches.score_summary as score', 'matches.status')
                ->get()
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'sport' => $row->sport,
                    'team_a' => $row->team_a,
                    'team_b' => $row->team_b,
                    'score' => $row->score ?: '0-0',
                    'status' => $row->status,
                    'venue' => null,
                    'time' => null,
                ])
                ->all();
        }

        return collect($this->results())->values()
            ->map(fn ($row, $i) => ['id' => 'R'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), ...$row])
            ->all();
    }

    public function audit(): array
    {
        if (Schema::hasTable('score_audits')) {
            return DB::table('score_audits')
                ->join('matches', 'score_audits.match_id', '=', 'matches.id')
                ->latest('score_audits.created_at')
                ->limit(24)
                ->select('matches.code as match_id', 'score_audits.before_json', 'score_audits.after_json', 'score_audits.created_at')
                ->get()
                ->map(fn ($row) => [
                    'match_id' => $row->match_id,
                    'before' => json_decode($row->before_json ?: 'null', true),
                    'after' => json_decode($row->after_json ?: '[]', true),
                    'at' => (string) $row->created_at,
                ])
                ->all();
        }

        $path = storage_path('app/porpamnas-score-audit.json');

        return file_exists($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
    }

    public function updateScore(array $data): array
    {
        if (Schema::hasTable('matches')) {
            app(SubmitMatchScore::class)->handle($data);

            return $this->adminScoreRows();
        }

        $rows = collect($this->adminScoreRows());
        $before = $rows->firstWhere('id', $data['id']);
        $updated = $rows
            ->map(fn ($row) => $row['id'] === $data['id'] ? $data : $row)
            ->map(fn ($row) => collect($row)->except('id')->all())
            ->values()
            ->all();

        file_put_contents(storage_path('app/porpamnas-results.json'), json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents(storage_path('app/porpamnas-score-audit.json'), json_encode(array_values([[
            'match_id' => $data['id'],
            'before' => $before,
            'after' => $data,
            'at' => now()->toDateTimeString(),
        ], ...$this->audit()]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $updated;
    }

    private function masterData(): array
    {
        if (Schema::hasTable('pdams') && Schema::hasTable('tournament_events')) {
            return $this->databaseData();
        }

        return $this->seedFileData();
    }

    private function databaseData(): array
    {
        return [
            'agenda' => DB::table('event_agendas')
                ->join('venues', 'event_agendas.venue_id', '=', 'venues.id')
                ->leftJoin('sports', 'event_agendas.sport_id', '=', 'sports.id')
                ->where('venues.is_active', true)
                ->where(fn ($query) => $query->whereNull('event_agendas.sport_id')->orWhere('sports.is_active', true))
                ->whereNotNull('event_agendas.published_at')
                ->orderBy('event_agendas.date')
                ->orderBy('event_agendas.start_time')
                ->select('event_agendas.date', 'event_agendas.day', 'event_agendas.title', 'event_agendas.type', 'event_agendas.start_time', 'event_agendas.end_time', 'event_agendas.time_note', 'sports.code as sport_code', 'sports.name as sport', 'venues.name as venue', 'venues.address as venue_address')
                ->get(),
            'sports' => DB::table('sports')->where('is_active', true)->orderBy('name')->get(),
            'venues' => DB::table('venues')->where('is_active', true)->orderBy('name')->get(),
            'pdams' => DB::table('pdams')
                ->leftJoin('provinces', 'pdams.province_id', '=', 'provinces.id')
                ->leftJoin('regencies', 'pdams.regency_id', '=', 'regencies.id')
                ->leftJoin('regional_committees', 'regional_committees.province_id', '=', 'pdams.province_id')
                ->orderBy('pdams.name')
                ->select('pdams.code', 'pdams.name', 'pdams.slug', 'pdams.city', 'pdams.logo_path', 'provinces.code as province_code', 'provinces.name as province', 'regencies.code as regency_code', 'regencies.name as regency', 'regional_committees.name as regional_committee_name')
                ->get(),
            'provinces' => DB::table('provinces')->orderBy('name')->get(),
            'regionalCommittees' => Schema::hasTable('regional_committees')
                ? DB::table('regional_committees')
                    ->leftJoin('provinces', 'regional_committees.province_id', '=', 'provinces.id')
                    ->orderBy('regional_committees.name')
                    ->select('regional_committees.id', 'regional_committees.name', 'provinces.code as province_code', 'provinces.name as province')
                    ->get()
                : collect(),
            'sportCategories' => DB::table('sport_categories')
                ->join('sports', 'sport_categories.sport_id', '=', 'sports.id')
                ->where('sports.is_active', true)
                ->where('sport_categories.is_active', true)
                ->orderBy('sport_categories.sort_order')
                ->select('sports.code as sport_code', 'sport_categories.code', 'sport_categories.name', 'sport_categories.competition_type', 'sport_categories.scoring_type', 'sport_categories.min_members', 'sport_categories.max_members', 'sport_categories.bracket_enabled')
                ->get(),
            'sportRegulations' => DB::table('sport_regulations')
                ->join('sports', 'sport_regulations.sport_id', '=', 'sports.id')
                ->where('sports.is_active', true)
                ->where('sport_regulations.is_active', true)
                ->orderByDesc('sport_regulations.version')
                ->get(['sports.code as sport_code', 'sport_regulations.version', 'sport_regulations.title', 'sport_regulations.content', 'sport_regulations.document_url'])
                ->unique('sport_code')->values(),
            'tournamentEvents' => DB::table('tournament_events')
                ->join('sports', 'tournament_events.sport_id', '=', 'sports.id')
                ->leftJoin('sport_categories', 'tournament_events.sport_category_id', '=', 'sport_categories.id')
                ->where('sports.is_active', true)
                ->where(fn ($query) => $query->whereNull('tournament_events.sport_category_id')->orWhere('sport_categories.is_active', true))
                ->orderBy('tournament_events.name')
                ->select('tournament_events.code', 'tournament_events.name', 'tournament_events.format', 'tournament_events.status', 'tournament_events.bracket_size', 'sports.code as sport_code', 'sport_categories.code as category_code')
                ->get(),
        ];
    }

    private function seedFileData(): array
    {
        $venues = $this->csv('data/seed/venues.csv')->keyBy('code');
        $sports = $this->csv('data/seed/sports.csv')->values();
        $sportsByCode = $sports->keyBy('code');
        $provinces = $this->csv('data/seed/indonesia_provinces.csv')->values();
        $provincesByCode = $provinces->keyBy('code');

        return [
            'agenda' => $this->csv('data/seed/event_agenda.csv')->map(fn ($item) => [
                ...$item,
                'sport' => $item['sport_code'] ? ($sportsByCode[$item['sport_code']]['name'] ?? null) : null,
                'venue' => $venues[$item['venue_code']]['name'] ?? $item['venue_code'],
                'venue_address' => $venues[$item['venue_code']]['address'] ?? '',
            ])->values(),
            'sports' => $sports,
            'venues' => $venues->values(),
            'pdams' => $this->csv('data/seed/pdams.csv')->map(fn ($row) => [
                ...$row,
                'province' => $provincesByCode[$row['province_code']]['name'] ?? $row['province_code'],
                'regional_committee_name' => $provincesByCode[$row['province_code']]['name'] ?? $row['province_code'],
            ])->values(),
            'provinces' => $provinces,
            'regionalCommittees' => $provinces->map(fn ($p) => [
                'id' => null,
                'name' => $p['name'],
                'province_code' => $p['code'],
                'province' => $p['name'],
            ])->values(),
            'sportCategories' => $this->csv('data/seed/sport_categories.csv'),
            'sportRegulations' => collect(),
            'tournamentEvents' => collect(),
        ];
    }

    private function csv(string $path): Collection
    {
        $file = fopen(base_path($path), 'r');
        $headers = fgetcsv($file, escape: '');
        $rows = [];

        while (($data = fgetcsv($file, escape: '')) !== false) {
            $rows[] = array_combine($headers, $data);
        }

        fclose($file);

        return collect($rows);
    }

    private function results(): array
    {
        $path = storage_path('app/porpamnas-results.json');

        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?: [];
        }

        return [
            ['sport' => 'Mini Football', 'team_a' => 'Kalimantan Timur', 'team_b' => 'Jawa Timur', 'score' => '3–1', 'status' => 'final', 'venue' => 'Borneo Anfield', 'time' => '07 Okt 16:00'],
            ['sport' => 'Voli Putra', 'team_a' => 'Jawa Barat', 'team_b' => 'Sulawesi Selatan', 'score' => '2–3', 'status' => 'final', 'venue' => 'GOR Balikpapan', 'time' => '07 Okt 19:00'],
            ['sport' => 'Bulu Tangkis', 'team_a' => 'Kalimantan Timur', 'team_b' => 'Sulawesi Selatan', 'score' => '1–2', 'status' => 'final', 'venue' => 'BTS', 'time' => '08 Okt 09:00'],
            ['sport' => 'Tenis Meja', 'team_a' => 'Jawa Timur', 'team_b' => 'Jawa Barat', 'score' => '3–0', 'status' => 'live', 'venue' => 'BSCC Dome', 'time' => 'Sekarang'],
            ['sport' => 'Catur', 'team_a' => 'DKI Jakarta', 'team_b' => 'Jawa Tengah', 'score' => '½–½', 'status' => 'scheduled', 'venue' => 'Hotel Platinum', 'time' => '09 Okt 10:00'],
            ['sport' => 'Golf', 'team_a' => 'Bali', 'team_b' => 'Sumatera Utara', 'score' => '—', 'status' => 'scheduled', 'venue' => 'Balikpapan Golf', 'time' => '10 Okt 06:30'],
        ];
    }

    private function provinceRankings(): array
    {
        if (Schema::hasTable('regional_committees') && Schema::hasTable('matches')) {
            $rankings = DB::table('regional_committees')
                ->select('regional_committees.id', 'regional_committees.name')
                ->orderBy('regional_committees.name')
                ->get()
                ->mapWithKeys(fn ($committee) => [$committee->id => [
                    'name' => $committee->name,
                    'gold' => 0,
                    'silver' => 0,
                    'bronze' => 0,
                ]]);

            DB::table('matches')
                ->join('event_entries as a', 'matches.entry_a_id', '=', 'a.id')
                ->join('event_entries as b', 'matches.entry_b_id', '=', 'b.id')
                ->where('matches.status', 'final')
                ->where('matches.round_name', 'Final')
                ->whereNotNull('matches.winner_entry_id')
                ->whereNotNull('a.regional_committee_id')
                ->whereNotNull('b.regional_committee_id')
                ->select('matches.winner_entry_id', 'a.id as entry_a_id', 'a.regional_committee_id as committee_a_id', 'b.id as entry_b_id', 'b.regional_committee_id as committee_b_id')
                ->get()
                ->each(function ($match) use ($rankings): void {
                    $winnerId = $match->winner_entry_id === $match->entry_a_id ? $match->committee_a_id : $match->committee_b_id;
                    $runnerUpId = $match->winner_entry_id === $match->entry_a_id ? $match->committee_b_id : $match->committee_a_id;

                    if ($winner = $rankings->get($winnerId)) {
                        $winner['gold']++;
                        $rankings->put($winnerId, $winner);
                    }
                    if ($runnerUp = $rankings->get($runnerUpId)) {
                        $runnerUp['silver']++;
                        $rankings->put($runnerUpId, $runnerUp);
                    }
                });

            // Bronze = kedua kalah semifinal (aturan PORPAMNAS, tanpa 3rd-place playoff).
            // ponytail: kalau nanti ada round_name='3rd Place', ganti ke query winner-based match tersebut.
            DB::table('matches')
                ->join('event_entries as a', 'matches.entry_a_id', '=', 'a.id')
                ->join('event_entries as b', 'matches.entry_b_id', '=', 'b.id')
                ->where('matches.status', 'final')
                ->where('matches.round_name', 'Semi Final')
                ->whereNotNull('matches.winner_entry_id')
                ->whereNotNull('a.regional_committee_id')
                ->whereNotNull('b.regional_committee_id')
                ->select('matches.winner_entry_id', 'a.id as entry_a_id', 'a.regional_committee_id as committee_a_id', 'b.regional_committee_id as committee_b_id')
                ->get()
                ->each(function ($match) use ($rankings): void {
                    $loserCommittee = $match->winner_entry_id === $match->entry_a_id ? $match->committee_b_id : $match->committee_a_id;
                    if ($loser = $rankings->get($loserCommittee)) {
                        $loser['bronze']++;
                        $rankings->put($loserCommittee, $loser);
                    }
                });

            return $rankings->sortBy([
                ['gold', 'desc'],
                ['silver', 'desc'],
                ['bronze', 'desc'],
                ['name', 'asc'],
            ])->values()->all();
        }

        return [
            ['name' => 'Kalimantan Timur', 'gold' => 8, 'silver' => 5, 'bronze' => 3],
            ['name' => 'Sulawesi Selatan', 'gold' => 5, 'silver' => 3, 'bronze' => 4],
            ['name' => 'Jawa Timur', 'gold' => 4, 'silver' => 4, 'bronze' => 2],
            ['name' => 'Jawa Barat', 'gold' => 3, 'silver' => 6, 'bronze' => 5],
            ['name' => 'DKI Jakarta', 'gold' => 3, 'silver' => 2, 'bronze' => 4],
            ['name' => 'Bali', 'gold' => 2, 'silver' => 3, 'bronze' => 3],
        ];
    }

    private function assets(): array
    {
        return [
            'porpamnas' => '/assets/brand/logos/porpamnas/porpamnas-ix.png',
            'ptmb' => '/assets/brand/logos/ptmb/logo-ptmb-landscape.png',
            'ptmbMark' => '/assets/brand/logos/ptmb/only-logo-ptmb.png',
            'pemkot' => '/assets/brand/logos/LOGO_PEMKOT.png',
            'perpamsi' => '/assets/brand/logos/LOGO_PERPAMSI.jpeg',
            'beru' => '/assets/brand/mascots/beru.png',
            'ganga' => '/assets/brand/mascots/ganga.png',
            'stop' => '/assets/brand/mascots/STOP.png',
            'mascots' => [
                'badminton' => '/assets/brand/mascots/bulu-tangkis.png',
                'chess' => '/assets/brand/mascots/catur.png',
                'mini-football' => '/assets/brand/mascots/mini-football.png',
                'table-tennis' => '/assets/brand/mascots/tenis-meja.png',
                'tennis' => '/assets/brand/mascots/tenis-lapangan.png',
                'golf' => '/assets/brand/mascots/Golf.png',
                'padel' => '/assets/brand/mascots/Padel.png',
                'vocal' => '/assets/brand/mascots/vokal.png',
                'volleyball' => '/assets/brand/mascots/voli.png',
            ],
        ];
    }
}
