<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TournamentBracketDemoSeeder extends Seeder
{
    public function run(): void
    {
        $participants = collect($this->csvRows(base_path('data/seed/pdams.csv')))
            ->map(fn ($row) => [
                'code' => $row['code'],
                'name' => $row['name'],
                'display_name' => $this->shortName($row['name']),
                'province_code' => $row['province_code'],
                'regency_code' => $row['regency_code'],
                'city' => $row['city'],
            ])
            ->groupBy('regency_code')
            ->flatMap(fn ($rows) => $rows->values()->map(fn ($row, $index) => [$index, $row]))
            ->sortBy(fn ($pair) => $pair[0].$pair[1]['province_code'].$pair[1]['regency_code'])
            ->pluck(1)
            ->values();

        $size = 2 ** (int) ceil(log(max(2, $participants->count()), 2));
        $slots = $participants->pad($size, null)->values();
        $matches = [];
        $matchNo = 1;
        $roundNo = 1;
        $roundSize = $size / 2;

        while ($roundSize >= 1) {
            $nextRoundFirstMatchNo = $matchNo + $roundSize;

            for ($slot = 1; $slot <= $roundSize; $slot++) {
                $firstRound = $roundNo === 1;
                $index = ($slot - 1) * 2;
                $leftHalf = $slot <= $roundSize / 2;
                $scoreA = $firstRound && $slot <= 8 ? (($slot % 3) + 1) : null;
                $scoreB = $firstRound && $slot <= 8 ? ($slot % 2) : null;

                $matches[] = [
                    'id' => 'M'.str_pad((string) $matchNo++, 4, '0', STR_PAD_LEFT),
                    'sport_code' => 'mini-football',
                    'round_no' => $roundNo,
                    'round_name' => $this->roundName($roundSize),
                    'side' => $roundSize === 1 ? 'final' : ($leftHalf ? 'left' : 'right'),
                    'slot_no' => $slot,
                    'team_a' => $firstRound ? $slots[$index] : null,
                    'team_b' => $firstRound ? $slots[$index + 1] : null,
                    'score_a' => $scoreA,
                    'score_b' => $scoreB,
                    'winner_code' => $scoreA === null || $scoreB === null ? null : (($scoreA >= $scoreB ? $slots[$index] : $slots[$index + 1])['code'] ?? null),
                    'next_match_id' => $roundSize === 1 ? null : 'M'.str_pad((string) ($nextRoundFirstMatchNo + intdiv($slot - 1, 2)), 4, '0', STR_PAD_LEFT),
                    'next_slot' => $slot % 2 ? 'a' : 'b',
                    'status' => $scoreA === null ? 'scheduled' : 'final',
                ];
            }
            $roundNo++;
            $roundSize /= 2;
        }

        Storage::disk('local')->put('porpamnas-bracket-demo.json', json_encode([
            'sport_code' => 'mini-football',
            'bracket_size' => $size,
            'participant_count' => $participants->count(),
            'note' => 'Demo bracket otomatis: peserta disebar per regency, kiri-kanan, BYE otomatis.',
            'matches' => $matches,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function shortName(string $name): string
    {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/\b(kabupaten|kota)\b/i', '', preg_replace('/^(perumda|perumdam|perusahaan umum daerah|pdam|pt)\s+(air\s+minum\s+)?/i', '', $name))));
    }

    private function roundName(int $matchCount): string
    {
        return match ($matchCount) {
            1 => 'Final',
            2 => 'Semi Final',
            4 => 'Perempat Final',
            default => 'Round of '.($matchCount * 2),
        };
    }

    private function csvRows(string $path): array
    {
        $file = fopen($path, 'r');
        $headers = fgetcsv($file, escape: '');
        $rows = [];

        while (($data = fgetcsv($file, escape: '')) !== false) {
            $rows[] = array_combine($headers, $data);
        }

        fclose($file);

        return $rows;
    }
}
