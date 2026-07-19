<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TournamentDomainSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $sports = DB::table('sports')->get()->keyBy('code');
        $pdams = DB::table('pdams')->orderBy('code')->get();

        foreach ($this->csvRows(base_path('data/seed/sport_categories.csv')) as $row) {
            $sport = $sports[$row['sport_code']] ?? null;
            if (! $sport) continue;

            DB::table('sport_categories')->upsert([[
                'public_id' => (string) Str::uuid(),
                'sport_id' => $sport->id,
                'code' => $row['code'],
                'name' => $row['name'],
                'competition_type' => $row['competition_type'],
                'scoring_type' => $row['scoring_type'],
                'bracket_enabled' => (bool) $row['bracket_enabled'],
                'sort_order' => (int) $row['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]], ['sport_id', 'code'], ['name', 'competition_type', 'scoring_type', 'bracket_enabled', 'sort_order', 'is_active', 'updated_at']);
        }

        $categories = DB::table('sport_categories')->get()->keyBy(fn ($row) => $row->sport_id.'-'.$row->code);

        foreach ($sports as $sport) {
            $sportCategories = $categories->filter(fn ($category) => $category->sport_id === $sport->id)->values();
            if ($sportCategories->isEmpty()) {
                $this->seedTournamentEvent($sport, null, $pdams, $now);
                continue;
            }

            foreach ($sportCategories as $category) {
                $this->seedTournamentEvent($sport, $category, $pdams, $now);
            }
        }
    }

    private function seedTournamentEvent(object $sport, ?object $category, object $pdams, object $now): void
    {
        $eventCode = $category ? $sport->code.'-'.$category->code : $sport->code;
        $format = $category && ! $category->bracket_enabled ? 'ranking' : ($sport->default_format ?: 'knockout');
        $entriesCount = $category && $category->bracket_enabled ? min(64, $pdams->count()) : min(16, $pdams->count());
        $bracketSize = $category && $category->bracket_enabled ? 2 ** (int) ceil(log(max(2, $entriesCount), 2)) : null;

        DB::table('tournament_events')->upsert([[
            'public_id' => (string) Str::uuid(),
            'sport_id' => $sport->id,
            'sport_category_id' => $category?->id,
            'code' => $eventCode,
            'name' => trim($sport->name.($category ? ' - '.$category->name : '')),
            'format' => $format,
            'status' => 'bracket_locked',
            'bracket_size' => $bracketSize,
            'seed_locked_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]], ['code'], ['name', 'format', 'status', 'bracket_size', 'seed_locked_at', 'updated_at']);

        $event = DB::table('tournament_events')->where('code', $eventCode)->first();
        $entries = $pdams->take($entriesCount)->values()->map(fn ($pdam, $index) => [
            'public_id' => (string) Str::uuid(),
            'tournament_event_id' => $event->id,
            'pdam_id' => $pdam->id,
            'province_id' => $pdam->province_id,
            'regency_id' => $pdam->regency_id,
            'seed_no' => $index + 1,
            'display_name' => $this->shortName($pdam->name),
            'athlete_1' => $category && $category->competition_type !== 'team' ? 'Atlet '.($index + 1).'A' : null,
            'athlete_2' => $category && $category->competition_type === 'doubles' ? 'Atlet '.($index + 1).'B' : null,
            'team_name' => $category && $category->competition_type === 'team' ? $this->shortName($pdam->name).' Team' : null,
            'verification_status' => 'verified',
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('event_entries')->where('tournament_event_id', $event->id)->delete();
        foreach (array_chunk($entries, 200) as $chunk) {
            DB::table('event_entries')->insert($chunk);
        }

        if ($bracketSize) {
            $this->seedMatches($event->id, $bracketSize, $now);
        }
    }

    private function seedMatches(int $eventId, int $bracketSize, object $now): void
    {
        $entries = DB::table('event_entries')->where('tournament_event_id', $eventId)->orderBy('seed_no')->get()->values();
        $slots = $entries->pad($bracketSize, null)->values();
        $matchesByNumber = [];
        $matchRows = [];
        $scoreRows = [];
        $auditRows = [];
        $matchNo = 1;
        $roundNo = 1;
        $roundSize = $bracketSize / 2;
        $entrants = $slots;

        while ($roundSize >= 1) {
            $nextRoundFirstMatchNo = $matchNo + $roundSize;
            for ($slot = 1; $slot <= $roundSize; $slot++) {
                $a = $entrants[($slot - 1) * 2] ?? null;
                $b = $entrants[($slot - 1) * 2 + 1] ?? null;
                $scoreA = $roundNo === 1 && $a && $b ? (($slot % 3) + 1) : null;
                $scoreB = $roundNo === 1 && $a && $b ? ($slot % 2) : null;
                $winner = $scoreA === null || $scoreB === null ? ($b ? null : $a) : ($scoreA >= $scoreB ? $a : $b);
                $code = 'M'.str_pad((string) $matchNo, 4, '0', STR_PAD_LEFT);

                $matchRows[] = [
                    'public_id' => (string) Str::uuid(),
                    'tournament_event_id' => $eventId,
                    'code' => $code,
                    'round_no' => $roundNo,
                    'round_name' => $this->roundName($roundSize),
                    'side' => $roundSize === 1 ? 'final' : ($slot <= $roundSize / 2 ? 'left' : 'right'),
                    'slot_no' => $slot,
                    'entry_a_id' => $a?->id,
                    'entry_b_id' => $b?->id,
                    'winner_entry_id' => $winner?->id,
                    'next_match_id' => null,
                    'next_slot' => $roundSize === 1 ? null : ($slot % 2 ? 'a' : 'b'),
                    'score_summary' => $scoreA === null || $scoreB === null ? null : $scoreA.'-'.$scoreB,
                    'status' => $scoreA === null || $scoreB === null ? 'scheduled' : 'final',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $matchesByNumber[$matchNo] = ['next' => $roundSize === 1 ? null : $nextRoundFirstMatchNo + intdiv($slot - 1, 2), 'winner' => $winner];
                $matchNo++;
            }
            $roundNo++;
            $roundSize /= 2;
            $entrants = collect(array_values($matchesByNumber))->slice(-($roundSize * 2))->map(fn ($row) => $row['winner'])->values();
        }

        DB::table('matches')->where('tournament_event_id', $eventId)->delete();
        DB::table('matches')->insert($matchRows);
        $matches = DB::table('matches')->where('tournament_event_id', $eventId)->get()->keyBy('code');

        foreach ($matchesByNumber as $number => $meta) {
            if (! $meta['next']) continue;
            DB::table('matches')->where('tournament_event_id', $eventId)->where('code', 'M'.str_pad((string) $number, 4, '0', STR_PAD_LEFT))->update([
                'next_match_id' => $matches['M'.str_pad((string) $meta['next'], 4, '0', STR_PAD_LEFT)]->id ?? null,
            ]);
        }

        foreach ($matches as $match) {
            if ($match->score_summary === null) continue;
            [$a, $b] = array_map('intval', explode('-', $match->score_summary));
            $payload = ['score_a' => $a, 'score_b' => $b, 'winner_entry_id' => $match->winner_entry_id];
            $scoreRows[] = ['match_id' => $match->id, 'score_payload' => json_encode($payload), 'calculated_winner_entry_id' => $match->winner_entry_id, 'verified_at' => $now, 'created_at' => $now, 'updated_at' => $now];
            $auditRows[] = ['match_id' => $match->id, 'before_json' => null, 'after_json' => json_encode($payload), 'reason' => 'demo seed', 'created_at' => $now, 'updated_at' => $now];
        }

        if ($scoreRows) DB::table('match_scores')->insert($scoreRows);
        if ($auditRows) DB::table('score_audits')->insert($auditRows);
    }

    private function roundName(int $matchCount): string
    {
        return match ($matchCount) { 1 => 'Final', 2 => 'Semi Final', 4 => 'Perempat Final', default => 'Round of '.($matchCount * 2) };
    }

    private function shortName(string $name): string
    {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/\b(kabupaten|kota)\b/i', '', preg_replace('/^(perumda|perumdam|perusahaan umum daerah|pdam|pt)\s+(air\s+minum\s+)?/i', '', $name))));
    }

    private function csvRows(string $path): array
    {
        $file = fopen($path, 'r');
        $headers = fgetcsv($file, escape: '');
        $rows = [];
        while (($data = fgetcsv($file, escape: '')) !== false) $rows[] = array_combine($headers, $data);
        fclose($file);
        return $rows;
    }
}
