<?php

namespace App\Actions\Matches;

use App\Models\MatchScore;
use App\Models\ScoreAudit;
use App\Models\TournamentMatch;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubmitMatchScore
{
    public function handle(array $data): TournamentMatch
    {
        [$scoreA, $scoreB] = $this->parseScore($data['score']);

        return DB::transaction(function () use ($data, $scoreA, $scoreB) {
            $match = TournamentMatch::query()->with(['entryA', 'entryB'])->where('code', $data['id'])->firstOrFail();
            $before = [
                'score_summary' => $match->score_summary,
                'status' => $match->status,
                'winner_entry_id' => $match->winner_entry_id,
            ];
            $winnerId = $scoreA === $scoreB ? null : ($scoreA > $scoreB ? $match->entry_a_id : $match->entry_b_id);

            $match->update([
                'score_summary' => $scoreA.'-'.$scoreB,
                'status' => $data['status'],
                'winner_entry_id' => $winnerId,
            ]);

            $payload = ['score_a' => $scoreA, 'score_b' => $scoreB, 'winner_entry_id' => $winnerId];

            MatchScore::query()->updateOrCreate(
                ['match_id' => $match->id],
                ['score_payload' => $payload, 'calculated_winner_entry_id' => $winnerId, 'verified_at' => now()]
            );

            ScoreAudit::query()->create([
                'match_id' => $match->id,
                'before_json' => $before,
                'after_json' => ['score_summary' => $match->score_summary, 'status' => $match->status, 'winner_entry_id' => $winnerId],
                'reason' => 'admin score input',
            ]);

            return $match->refresh();
        });
    }

    private function parseScore(string $score): array
    {
        if (! preg_match('/^\s*(\d+)\s*[-–—]\s*(\d+)\s*$/u', $score, $matches)) {
            throw new InvalidArgumentException('Format skor harus seperti 3-1.');
        }

        return [(int) $matches[1], (int) $matches[2]];
    }
}
