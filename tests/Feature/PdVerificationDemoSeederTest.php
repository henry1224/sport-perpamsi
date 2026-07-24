<?php

namespace Tests\Feature;

use Database\Seeders\PdVerificationDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdVerificationDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_creates_two_pd_entries_with_player_verification_examples(): void
    {
        Storage::fake('local');
        $this->seed();
        $this->seed(PdVerificationDemoSeeder::class);

        $eventId = DB::table('tournament_events')->where('code', 'badminton-mixed-double')->value('id');
        $entryIds = DB::table('event_entries')->where('tournament_event_id', $eventId)->pluck('id');

        $this->assertCount(2, $entryIds);
        $this->assertSame(4, DB::table('entry_members')->whereIn('event_entry_id', $entryIds)->where('member_type', 'player')->count());
        $this->assertSame(1, DB::table('entry_members')->whereIn('event_entry_id', $entryIds)->where('verification_status', 'verified')->count());
        $this->assertSame(3, DB::table('entry_members')->whereIn('event_entry_id', $entryIds)->where('verification_status', 'pending')->count());
        $documents = json_decode(DB::table('entry_members')->whereIn('event_entry_id', $entryIds)->value('documents'), true);
        foreach ($documents as $path) Storage::disk('local')->assertExists($path);
    }
}
