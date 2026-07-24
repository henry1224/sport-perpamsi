<?php

namespace Tests\Feature;

use Database\Seeders\SportMasterSeeder;
use Database\Seeders\SportRegulationSeeder;
use Database\Seeders\TournamentEventSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TournamentEventSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_events_only_use_badminton_master_categories_as_empty_drafts(): void
    {
        $this->seed([SportMasterSeeder::class, SportRegulationSeeder::class, TournamentEventSeeder::class]);

        $events = DB::table('tournament_events')->join('sports', 'tournament_events.sport_id', '=', 'sports.id')->get(['sports.code as sport_code', 'tournament_events.status']);

        $this->assertCount(6, $events);
        $this->assertSame(['badminton'], $events->pluck('sport_code')->unique()->values()->all());
        $this->assertSame(['registration_draft'], $events->pluck('status')->unique()->values()->all());
        $this->assertDatabaseCount('event_entries', 0);
    }
}
