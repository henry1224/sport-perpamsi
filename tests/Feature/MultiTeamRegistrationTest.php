<?php

namespace Tests\Feature;

use App\Models\EventEntry;
use App\Models\EntryTeam;
use App\Models\TournamentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTeamRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pd_registers_multiple_teams_with_unique_rosters(): void
    {
        $this->seed();
        $user = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $rules = $event->registration_rules;
        $rules['max_teams_per_pd'] = 2;
        $rules['min_members_per_team'] = 1;
        $rules['max_members_per_team'] = 2;
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay(), 'registration_rules' => $rules]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $user->regional_committee_id)->delete();

        $this->actingAs($user)->post(route('pd.events.entries.store', $event), ['teams' => [
            ['members' => [['name' => 'Pemain Satu']]],
            ['members' => [['name' => 'Pemain Dua']]],
        ], 'intent' => 'submit'])->assertSessionHasNoErrors();

        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $user->regional_committee_id)->firstOrFail();
        $this->assertSame(2, $entry->teams()->count());
        $this->assertSame(['Pemain Satu', 'Pemain Dua'], $entry->teams()->with('members')->get()->flatMap->members->pluck('name')->all());
    }

    public function test_duplicate_player_across_teams_is_rejected(): void
    {
        $this->seed();
        $user = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $rules = $event->registration_rules;
        $rules['max_teams_per_pd'] = 2;
        $rules['min_members_per_team'] = 1;
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay(), 'registration_rules' => $rules]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $user->regional_committee_id)->delete();

        $this->actingAs($user)->post(route('pd.events.entries.store', $event), ['teams' => [
            ['members' => [['name' => 'Pemain Sama']]],
            ['members' => [['name' => ' pemain sama ']]],
        ]])->assertSessionHasErrors('teams');
    }

    public function test_verified_team_roster_cannot_be_replaced(): void
    {
        $this->seed();
        $user = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay()]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $user->regional_committee_id)->delete();
        $members = collect(range(1, $event->registration_rules['min_members'] ?? 1))->map(fn ($number) => ['name' => 'Pemain '.$number])->all();
        $this->actingAs($user)->post(route('pd.events.entries.store', $event), ['members' => $members, 'intent' => 'draft'])->assertSessionHasNoErrors();
        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $user->regional_committee_id)->firstOrFail();
        $entry->update(['verification_status' => 'revision_required']);
        EntryTeam::query()->where('event_entry_id', $entry->id)->firstOrFail()->update(['verification_status_override' => 'verified']);

        $this->actingAs($user)->post(route('pd.events.entries.store', $event), ['members' => [['name' => 'Pengganti']], 'intent' => 'submit'])->assertStatus(422);
    }
}
