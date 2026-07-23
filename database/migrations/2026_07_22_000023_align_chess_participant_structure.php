<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $sport = DB::table('sports')->where('code', 'chess')->first();
        if (! $sport) return;

        $individual = DB::table('sport_categories')->where('sport_id', $sport->id)->where('code', 'individual-fast')->first();
        $team = DB::table('sport_categories')->where('sport_id', $sport->id)->where('code', 'team-fast')->first();
        $regulationId = DB::table('sport_regulations')->where('sport_id', $sport->id)->where('is_active', true)->latest('version')->value('id');
        if (! $individual || ! $team || ! $regulationId) return;

        DB::transaction(function () use ($sport, $individual, $team, $regulationId): void {
            DB::table('sport_categories')->where('id', $individual->id)->update(['min_members' => 1, 'max_members' => 1, 'updated_at' => now()]);

            DB::table('tournament_events')->where('code', 'chess')->update([
                'code' => 'chess-individual-fast',
                'name' => 'Catur - Perorangan Cepat',
                'sport_category_id' => $individual->id,
                'sport_regulation_id' => $regulationId,
                'format' => 'swiss',
                'status' => 'registration_draft',
                'registration_rules' => json_encode($this->rules($individual, $regulationId, 2)),
                'bracket_size' => null,
                'seed_locked_at' => null,
                'updated_at' => now(),
            ]);

            if (! DB::table('tournament_events')->where('code', 'chess-team-fast')->exists()) {
                DB::table('tournament_events')->insert([
                    'public_id' => (string) Str::uuid(),
                    'sport_id' => $sport->id,
                    'sport_category_id' => $team->id,
                    'sport_regulation_id' => $regulationId,
                    'code' => 'chess-team-fast',
                    'name' => 'Catur - Beregu Cepat',
                    'format' => 'swiss',
                    'status' => 'registration_draft',
                    'registration_rules' => json_encode($this->rules($team, $regulationId, 1)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    public function down(): void
    {
        $teamEvent = DB::table('tournament_events')->where('code', 'chess-team-fast')->first();
        if ($teamEvent && ! DB::table('event_entries')->where('tournament_event_id', $teamEvent->id)->exists() && ! DB::table('matches')->where('tournament_event_id', $teamEvent->id)->exists()) {
            DB::table('tournament_events')->where('id', $teamEvent->id)->delete();
        }

        $event = DB::table('tournament_events')->where('code', 'chess-individual-fast')->first();
        if ($event && ! $event->registration_published_at) {
            DB::table('tournament_events')->where('id', $event->id)->update(['code' => 'chess', 'name' => 'Catur', 'sport_category_id' => null, 'updated_at' => now()]);
        }
    }

    private function rules(object $category, int $regulationId, int $maxTeams): array
    {
        return [
            'category_name' => $category->name,
            'competition_type' => $category->competition_type,
            'scoring_type' => $category->scoring_type,
            'format' => 'swiss',
            'min_members' => $category->code === 'individual-fast' ? 1 : 3,
            'max_members' => $category->code === 'individual-fast' ? 1 : 3,
            'max_teams_per_pd' => $maxTeams,
            'min_members_per_team' => $category->code === 'individual-fast' ? 1 : 3,
            'max_members_per_team' => $category->code === 'individual-fast' ? 1 : 3,
            'avoid_same_pd_in_round' => true,
            'regulation_id' => $regulationId,
        ];
    }
};
