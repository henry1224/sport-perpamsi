<?php

namespace App\Support\Porpamnas;

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
            'assets' => $this->assets(),
        ];
    }

    public function adminScoreRows(): array
    {
        if (Schema::hasTable('matches')) {
            return DB::table('matches')
                ->join('tournament_events', 'matches.tournament_event_id', '=', 'tournament_events.id')
                ->join('sports', 'tournament_events.sport_id', '=', 'sports.id')
                ->leftJoin('event_entries as a', 'matches.entry_a_id', '=', 'a.id')
                ->leftJoin('event_entries as b', 'matches.entry_b_id', '=', 'b.id')
                ->whereNotNull('matches.entry_a_id')
                ->whereNotNull('matches.entry_b_id')
                ->orderBy('matches.id')
                ->limit(24)
                ->select('matches.code as id', 'sports.name as sport', 'a.display_name as team_a', 'b.display_name as team_b', 'matches.score_summary as score', 'matches.status')
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
            app(\App\Actions\Matches\SubmitMatchScore::class)->handle($data);

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
                ->orderBy('event_agendas.date')
                ->orderBy('event_agendas.start_time')
                ->select('event_agendas.date', 'event_agendas.day', 'event_agendas.title', 'event_agendas.type', 'event_agendas.start_time', 'event_agendas.end_time', 'event_agendas.time_note', 'sports.code as sport_code', 'sports.name as sport', 'venues.name as venue', 'venues.address as venue_address')
                ->get(),
            'sports' => DB::table('sports')->orderBy('name')->get(),
            'venues' => DB::table('venues')->orderBy('name')->get(),
            'pdams' => DB::table('pdams')
                ->leftJoin('provinces', 'pdams.province_id', '=', 'provinces.id')
                ->leftJoin('regencies', 'pdams.regency_id', '=', 'regencies.id')
                ->orderBy('pdams.name')
                ->select('pdams.code', 'pdams.name', 'pdams.slug', 'pdams.city', 'pdams.logo_path', 'provinces.code as province_code', 'provinces.name as province', 'regencies.code as regency_code', 'regencies.name as regency')
                ->get(),
            'provinces' => DB::table('provinces')->orderBy('name')->get(),
            'sportCategories' => DB::table('sport_categories')
                ->join('sports', 'sport_categories.sport_id', '=', 'sports.id')
                ->where('sport_categories.is_active', true)
                ->orderBy('sport_categories.sort_order')
                ->select('sports.code as sport_code', 'sport_categories.code', 'sport_categories.name', 'sport_categories.competition_type', 'sport_categories.scoring_type', 'sport_categories.bracket_enabled')
                ->get(),
            'tournamentEvents' => DB::table('tournament_events')
                ->join('sports', 'tournament_events.sport_id', '=', 'sports.id')
                ->leftJoin('sport_categories', 'tournament_events.sport_category_id', '=', 'sport_categories.id')
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
            ])->values(),
            'provinces' => $provinces,
            'sportCategories' => $this->csv('data/seed/sport_categories.csv'),
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
            ['sport' => 'Mini Football', 'team_a' => 'PT Manuntung Balikpapan', 'team_b' => 'PTK Samarinda', 'score' => '3–1', 'status' => 'final', 'venue' => 'Borneo Anfield', 'time' => '07 Okt 16:00'],
            ['sport' => 'Voli Putra', 'team_a' => 'PT Taman Bontang', 'team_b' => 'PT Mahakam Kukar', 'score' => '2–3', 'status' => 'final', 'venue' => 'GOR Balikpapan', 'time' => '07 Okt 19:00'],
            ['sport' => 'Bulu Tangkis', 'team_a' => 'Batiwakkal Berau', 'team_b' => 'PDAM Makassar', 'score' => '1–2', 'status' => 'final', 'venue' => 'BTS', 'time' => '08 Okt 09:00'],
            ['sport' => 'Tenis Meja', 'team_a' => 'PDAM Surabaya', 'team_b' => 'PDAM Bandung', 'score' => '3–0', 'status' => 'live', 'venue' => 'BSCC Dome', 'time' => 'Sekarang'],
            ['sport' => 'Catur', 'team_a' => 'PDAM Jakarta', 'team_b' => 'PDAM Semarang', 'score' => '½–½', 'status' => 'scheduled', 'venue' => 'Hotel Platinum', 'time' => '09 Okt 10:00'],
            ['sport' => 'Golf', 'team_a' => 'PDAM Denpasar', 'team_b' => 'PDAM Medan', 'score' => '—', 'status' => 'scheduled', 'venue' => 'Balikpapan Golf', 'time' => '10 Okt 06:30'],
        ];
    }

    private function provinceRankings(): array
    {
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
            'beru' => '/assets/brand/mascots/beru.png',
            'ganga' => '/assets/brand/mascots/ganga.png',
            'mascots' => [
                'badminton' => '/assets/brand/mascots/bulu-tangkis.png',
                'chess' => '/assets/brand/mascots/catur.png',
                'mini-football' => '/assets/brand/mascots/mini-football.png',
                'table-tennis' => '/assets/brand/mascots/tenis-meja.png',
                'tennis' => '/assets/brand/mascots/tenis-lapangan.png',
                'golf' => '/assets/brand/mascots/beru.png',
                'padel' => '/assets/brand/mascots/ganga.png',
                'vocal' => '/assets/brand/mascots/vokal.png',
                'volleyball' => '/assets/brand/mascots/voli.png',
            ],
        ];
    }
}
