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

    public function test_team_revision_is_scoped_and_requires_verified_players_before_approval(): void
    {
        Storage::fake('local');
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $rules = array_merge($event->registration_rules, ['max_teams_per_pd' => 2, 'min_members_per_team' => 1, 'max_members_per_team' => 1]);
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay(), 'registration_rules' => $rules]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $pd->regional_committee_id)->delete();

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [
            ['members' => [$this->member('Tim Satu', '3173000000000101', true)]],
            ['members' => [$this->member('Tim Dua', '3173000000000102', true)]],
        ]])->assertSessionHasNoErrors();

        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $pd->regional_committee_id)->with('teams.members')->firstOrFail();
        [$firstTeam, $secondTeam] = $entry->teams;
        $this->actingAs($admin)->post(route('admin.entry-teams.override', $secondTeam), ['status' => 'revision_required', 'reason' => 'Perbaiki identitas pemain kedua.'])->assertRedirect();
        $this->assertSame('Perbaiki identitas pemain kedua.', $secondTeam->fresh()->verification_note);

        $secondMember = $secondTeam->members->first();
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [[
            'id' => $secondTeam->id,
            'members' => [[...$this->member('Tim Dua Diperbaiki', '3173000000000102'), 'id' => $secondMember->id]],
        ]]])->assertSessionHasNoErrors();

        $this->assertSame('Tim Satu', $firstTeam->members()->firstOrFail()->name);
        $this->assertSame('Tim Dua Diperbaiki', $secondMember->fresh()->name);
        $this->assertNull($secondTeam->fresh()->verification_status_override);
        $this->assertSame('pending', $secondMember->fresh()->verification_status);
        $this->assertDatabaseHas('entry_registration_audits', ['event_entry_id' => $entry->id, 'action' => 'team_resubmitted']);
        $this->assertDatabaseHas('entry_team_audits', ['entry_team_id' => $secondTeam->id, 'action' => 'resubmitted', 'reason' => 'Perbaikan tim dikirim ulang oleh PD.']);

        $this->actingAs($admin)->post(route('admin.entry-teams.override', $secondTeam), ['status' => 'verified', 'reason' => 'Setujui tim.'])->assertRedirect()->assertSessionHas('error');
        $this->actingAs($admin)->post(route('admin.entry-members.verify', $secondMember))->assertRedirect();
        $this->actingAs($admin)->post(route('admin.entry-teams.override', $secondTeam), ['status' => 'verified', 'reason' => 'Dokumen lengkap.'])->assertRedirect();
        $this->actingAs($admin)->delete(route('admin.entry-teams.override.reset', $secondTeam), ['reason' => 'Kembali mengikuti status pendaftaran.'])->assertRedirect();
        $this->assertNull($secondTeam->fresh()->verification_status_override);
        $this->assertDatabaseHas('entry_team_audits', ['entry_team_id' => $secondTeam->id, 'action' => 'override_reset', 'reason' => 'Kembali mengikuti status pendaftaran.']);
    }

    private function member(string $name, string $identityNumber, bool $withDocuments = false): array
    {
        $member = ['name' => $name, 'pdam_id' => Pdam::query()->value('id'), 'identity_type' => 'nik', 'identity_number' => $identityNumber];
        if ($withDocuments) foreach (['photo', 'registration_form', 'identity_card', 'pension_card', 'employee_decree'] as $key) $member['documents'][$key] = UploadedFile::fake()->create($key.'.pdf', 100, 'application/pdf');
        return $member;
    }
}
