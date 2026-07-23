<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CleanupTournamentDemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_requires_force_and_preserves_master_and_registration_data(): void
    {
        $this->seed();
        $counts = collect(['sports', 'tournament_events', 'event_entries', 'entry_teams', 'entry_members'])
            ->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()]);
        $matches = DB::table('matches')->count();

        $this->assertGreaterThan(0, $matches);
        $this->assertSame(0, Artisan::call('demo:cleanup-tournament'));
        $this->assertSame($matches, DB::table('matches')->count());

        $this->assertSame(0, Artisan::call('demo:cleanup-tournament', ['--force' => true]));
        $this->assertDatabaseCount('matches', 0);
        $this->assertDatabaseCount('match_scores', 0);
        $this->assertDatabaseCount('score_audits', 0);
        $counts->each(fn ($count, $table) => $this->assertDatabaseCount($table, $count));
        $this->assertDatabaseMissing('tournament_events', ['status' => 'bracket_locked', 'registration_published_at' => null]);
    }
}
