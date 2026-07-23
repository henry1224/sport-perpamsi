<?php

namespace Tests\Feature;

use App\Models\EventEntry;
use App\Models\EntryTeam;
use App\Models\Pdam;
use App\Models\TournamentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiTeamRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pd_registers_multiple_teams_with_unique_rosters(): void
    {
        Storage::fake('local');
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
            ['members' => [$this->member('Pemain Satu', '3173000000000001', true)]],
            ['members' => [$this->member('Pemain Dua', '3173000000000002', true)]],
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
        Storage::fake('local');
        $this->seed();
        $user = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay()]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $user->regional_committee_id)->delete();
        $members = [['name' => 'Pemain 1']];
        $this->actingAs($user)->post(route('pd.events.entries.store', $event), ['members' => $members, 'intent' => 'draft'])->assertSessionHasNoErrors();
        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $user->regional_committee_id)->firstOrFail();
        $entry->update(['verification_status' => 'revision_required']);
        EntryTeam::query()->where('event_entry_id', $entry->id)->firstOrFail()->update(['verification_status_override' => 'verified']);

        $this->actingAs($user)->post(route('pd.events.entries.store', $event), ['teams' => [['members' => [$this->member('Pengganti', '3173000000000099', true)]]], 'intent' => 'submit'])->assertStatus(422);
    }

    public function test_bracket_eligibility_uses_effective_team_status(): void
    {
        $this->seed();
        $event = TournamentEvent::query()->whereHas('entries.teams')->firstOrFail();
        $entry = $event->entries()->with('teams')->firstOrFail();
        $team = $entry->teams->first();
        $entry->update(['verification_status' => 'pending']);
        $team->update(['verification_status_override' => null, 'cancelled_at' => null]);
        $this->assertSame(0, $event->eligibleTeams()->whereKey($team->id)->count());
        $this->assertGreaterThan(0, $event->bracketBlockers());

        $team->update(['verification_status_override' => 'verified']);
        $this->assertSame(1, $event->eligibleTeams()->whereKey($team->id)->count());
    }

    public function test_revision_preserves_team_identity_and_number(): void
    {
        $this->seed();
        $user = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay()]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $user->regional_committee_id)->delete();
        $members = collect(range(1, $event->registration_rules['min_members'] ?? 1))->map(fn ($number) => ['name' => 'Awal '.$number])->all();
        $this->actingAs($user)->post(route('pd.events.entries.store', $event), ['members' => $members, 'intent' => 'draft'])->assertSessionHasNoErrors();
        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $user->regional_committee_id)->firstOrFail();
        $team = $entry->teams()->firstOrFail();
        $replacement = collect($members)->map(fn ($member, $index) => ['name' => 'Revisi '.($index + 1)])->all();
        $this->actingAs($user)->post(route('pd.events.entries.store', $event), ['members' => $replacement, 'intent' => 'draft'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('entry_teams', ['id' => $team->id, 'event_entry_id' => $entry->id, 'team_no' => 1]);
    }

    private function member(string $name, string $identityNumber, bool $withDocuments = false): array
    {
        $member = ['name' => $name, 'pdam_id' => Pdam::query()->value('id'), 'identity_type' => 'nik', 'identity_number' => $identityNumber];
        if ($withDocuments) foreach (['photo', 'registration_form', 'identity_card', 'pension_card', 'employee_decree'] as $key) $member['documents'][$key] = UploadedFile::fake()->create($key.'.pdf', 100, 'application/pdf');
        return $member;
    }
}
