<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedalDemoSeeder extends Seeder
{
    /**
     * Isi klasemen demo dari empat finalis per event: dua kalah semifinal mendapat
     * perunggu, kalah final mendapat perak, dan pemenang final mendapat emas.
     */
    public function run(): void
    {
        $now = now();

        foreach (DB::table('tournament_events')->orderBy('id')->pluck('id') as $eventIndex => $eventId) {
            $pool = DB::table('event_entries')
                ->where('tournament_event_id', $eventId)
                ->whereNotNull('regional_committee_id')
                ->orderBy('seed_no')
                ->get()
                ->unique('regional_committee_id')
                ->values();
            $offset = ($eventIndex * 4) % max(1, $pool->count());
            $entries = $pool->slice($offset)->concat($pool->slice(0, $offset))->take(4)->values();

            $semifinals = DB::table('matches')
                ->where('tournament_event_id', $eventId)
                ->where('round_name', 'Semi Final')
                ->orderBy('slot_no')
                ->limit(2)
                ->get();
            $final = DB::table('matches')
                ->where('tournament_event_id', $eventId)
                ->where('round_name', 'Final')
                ->first();

            if ($entries->count() < 4 || $semifinals->count() < 2 || ! $final) continue;

            $this->finalize($semifinals[0], $entries[0]->id, $entries[1]->id, $entries[0]->id, '3-1', $now);
            $this->finalize($semifinals[1], $entries[2]->id, $entries[3]->id, $entries[2]->id, '2-0', $now);
            $this->finalize($final, $entries[0]->id, $entries[2]->id, $entries[0]->id, '3-2', $now);
        }
    }

    private function finalize(object $match, int $entryA, int $entryB, int $winnerId, string $score, $now): void
    {
        [$scoreA, $scoreB] = array_map('intval', explode('-', $score));

        DB::table('matches')->where('id', $match->id)->update([
            'entry_a_id' => $entryA,
            'entry_b_id' => $entryB,
            'winner_entry_id' => $winnerId,
            'score_summary' => $score,
            'status' => 'final',
            'updated_at' => $now,
        ]);

        DB::table('match_scores')->updateOrInsert(
            ['match_id' => $match->id],
            [
                'score_payload' => json_encode(['score_a' => $scoreA, 'score_b' => $scoreB, 'winner_entry_id' => $winnerId]),
                'calculated_winner_entry_id' => $winnerId,
                'verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
