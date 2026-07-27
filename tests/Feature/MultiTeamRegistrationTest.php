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
            ['members' => [$this->member('Pemain Satu', '3173010101900001', true)]],
            ['members' => [$this->member('Pemain Dua', '3173010101900002', true)]],
        ], 'intent' => 'submit'])->assertSessionHasNoErrors();

        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $user->regional_committee_id)->firstOrFail();
        $this->assertSame(2, $entry->teams()->count());
        $this->assertSame(['Pemain Satu', 'Pemain Dua'], $entry->teams()->with('members')->get()->flatMap->members->pluck('name')->all());
    }

    public function test_verified_registration_reopens_when_pd_adds_remaining_team(): void
    {
        Storage::fake('local');
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay(), 'registration_rules' => array_merge($event->registration_rules, ['max_teams_per_pd' => 2, 'min_members_per_team' => 1, 'max_members_per_team' => 1])]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $pd->regional_committee_id)->delete();

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => [$this->member('Tim Satu', '3173010101900301', true)]]]])->assertSessionHasNoErrors();
        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $pd->regional_committee_id)->with('teams.members')->firstOrFail();
        $firstTeam = $entry->teams->first();
        $firstTeam->members->each->update(['verification_status' => 'verified']);
        $this->actingAs($admin)->post(route('admin.entry-teams.override', $firstTeam), ['status' => 'verified', 'reason' => 'Tim pertama lengkap.'])->assertRedirect();
        $this->assertSame('verified', $entry->fresh()->verification_status);

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => [$this->member('Tim Dua', '3173010101900302', true)]]]])->assertSessionHasNoErrors();
        $entry->refresh();
        $secondTeam = $entry->teams()->where('team_no', 2)->with('members')->firstOrFail();
        $this->assertSame('pending', $entry->verification_status);
        $this->assertSame('verified', $firstTeam->fresh()->verification_status_override);
        $this->assertNull($secondTeam->verification_status_override);
        $this->actingAs($pd)->get(route('pd.dashboard', ['search' => $event->code]))->assertInertia(fn ($page) => $page
            ->where('events.data.0.code', $event->code)
            ->where('events.data.0.entries.pending', 1)
            ->where('events.data.0.teams.total', 2)
            ->where('events.data.0.teams.verified', 1)
            ->where('events.data.0.teams.pending', 1)
            ->where('events.data.0.players.total', 2)
            ->where('events.data.0.players.verified', 1));

        $secondTeam->members->each->update(['verification_status' => 'verified']);
        $this->actingAs($admin)->post(route('admin.entry-teams.override', $secondTeam), ['status' => 'verified', 'reason' => 'Tim kedua lengkap.'])->assertRedirect();
        $this->assertSame('verified', $entry->fresh()->verification_status);
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

        $this->actingAs($user)->post(route('pd.events.entries.store', $event), ['intent' => 'draft', 'teams' => [
            ['members' => [$this->member('Pemain Satu', '3173010101900401')]],
            ['members' => [$this->member('Pemain Dua', '3173010101900401')]],
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

        $this->actingAs($user)->post(route('pd.events.entries.store', $event), ['teams' => [['members' => [$this->member('Pengganti', '3173010101900099', true)]]], 'intent' => 'submit'])->assertStatus(422);
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
            ['members' => [$this->member('Tim Satu', '3173010101900101', true)]],
            ['members' => [$this->member('Tim Dua', '3173010101900102', true)]],
        ]])->assertSessionHasNoErrors();

        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $pd->regional_committee_id)->with('teams.members')->firstOrFail();
        [$firstTeam, $secondTeam] = $entry->teams;
        $this->actingAs($admin)->post(route('admin.entry-teams.override', $secondTeam), ['status' => 'revision_required', 'reason' => 'Perbaiki identitas pemain kedua.'])->assertRedirect();
        $this->assertSame('Perbaiki identitas pemain kedua.', $secondTeam->fresh()->verification_note);

        $secondMember = $secondTeam->members->first();
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [[
            'id' => $secondTeam->id,
            'members' => [[...$this->member('Tim Dua Diperbaiki', '3173010101900102'), 'id' => $secondMember->id]],
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

    public function test_admin_can_open_new_team_slot_without_unlocking_verified_team(): void
    {
        Storage::fake('local');
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay(), 'registration_rules' => array_merge($event->registration_rules, ['max_teams_per_pd' => 2, 'min_members_per_team' => 1, 'max_members_per_team' => 1])]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $pd->regional_committee_id)->delete();

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => [$this->member('Tim Satu', '3173010101900201', true)]]]])->assertSessionHasNoErrors();
        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $pd->regional_committee_id)->with('teams.members')->firstOrFail();
        $firstTeam = $entry->teams->first();
        $firstTeam->members->each->update(['verification_status' => 'verified']);
        $firstTeam->update(['verification_status_override' => 'verified', 'verified_by' => $admin->id, 'verified_at' => now()]);
        $event->update(['status' => 'registration_closed', 'registration_close_at' => now()->subMinute()]);

        $this->actingAs($admin)->post(route('admin.entries.team-addition', $entry), ['note' => 'Tambahkan tim kedua sesuai sisa kuota.'])->assertRedirect();
        $this->assertNotNull($entry->fresh()->team_addition_opened_at);
        $this->assertSame('pending', $entry->fresh()->verification_status);
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => [$this->member('Tim Dua', '3173010101900202', true)]]]])->assertSessionHasNoErrors();

        $entry->refresh();
        $this->assertNull($entry->team_addition_opened_at);
        $this->assertSame('verified', $firstTeam->fresh()->verification_status_override);
        $this->assertSame('Tim Satu', $firstTeam->members()->firstOrFail()->name);
        $this->assertDatabaseHas('entry_teams', ['event_entry_id' => $entry->id, 'team_no' => 2]);
        $this->assertDatabaseHas('entry_registration_audits', ['event_entry_id' => $entry->id, 'action' => 'team_addition_opened']);
        $this->assertDatabaseHas('entry_registration_audits', ['event_entry_id' => $entry->id, 'action' => 'team_added']);
    }

    public function test_pd_can_add_second_team_while_first_team_is_still_pending(): void
    {
        Storage::fake('local');
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay(), 'registration_rules' => array_merge($event->registration_rules, ['max_teams_per_pd' => 2, 'min_members_per_team' => 1, 'max_members_per_team' => 1])]);
        EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $pd->regional_committee_id)->delete();

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => [$this->member('Tim Satu Pending', '3173010101900301', true)]]]])->assertSessionHasNoErrors();
        $entry = EventEntry::query()->where('tournament_event_id', $event->id)->where('regional_committee_id', $pd->regional_committee_id)->with('teams.members')->firstOrFail();
        $firstTeam = $entry->teams->first();
        $this->assertSame('pending', $firstTeam->effectiveStatus());

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => [$this->member('Tim Dua Langsung', '3173010101900302', true)]]]])->assertSessionHasNoErrors();

        $this->assertSame('Tim Satu Pending', $firstTeam->members()->firstOrFail()->name);
        $this->assertSame('pending', $firstTeam->fresh()->effectiveStatus());
        $this->assertDatabaseHas('entry_teams', ['event_entry_id' => $entry->id, 'team_no' => 2]);
        $this->assertDatabaseHas('entry_registration_audits', ['event_entry_id' => $entry->id, 'action' => 'team_added']);
    }

    private function member(string $name, string $identityNumber, bool $withDocuments = false): array
    {
        $member = ['name' => $name, 'pdam_id' => Pdam::query()->value('id'), 'identity_type' => 'nik', 'identity_number' => $identityNumber];
        if ($withDocuments) foreach (['photo', 'registration_form', 'identity_card', 'pension_card', 'employee_decree'] as $key) $member['documents'][$key] = UploadedFile::fake()->create($key.'.pdf', 100, 'application/pdf');
        return $member;
    }
}
