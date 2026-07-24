<?php

namespace Tests\Feature;

use App\Models\EventEntry;
use App\Models\EntryMember;
use App\Models\Pdam;
use App\Models\TournamentEvent;
use App\Models\User;
use Database\Seeders\PdVerificationDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EntryRosterWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_pd_account_has_no_precreated_roster(): void
    {
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();

        $this->assertDatabaseMissing('event_entries', ['regional_committee_id' => $pd->regional_committee_id]);
    }

    public function test_pd_can_save_draft_submit_and_resubmit_revision(): void
    {
        Storage::fake('local');
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->with('category')->firstOrFail();
        $event->entries()->where('regional_committee_id', $pd->regional_committee_id)->delete();
        $members = $this->members($event, 'Pemain Draft');

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'draft', 'members' => [['name' => 'Pemain Draft 1']]])->assertSessionHasNoErrors();
        $entry = EventEntry::query()->where('registration_key', $event->id.':'.$pd->regional_committee_id)->firstOrFail();
        $this->assertSame('draft', $entry->verification_status);

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => $this->withDocuments($members)] ]])->assertSessionHasNoErrors();
        $this->assertSame('pending', $entry->fresh()->verification_status);

        $this->actingAs($admin)->post(route('admin.entries.revision', $entry), ['note' => 'Perbaiki nama pemain.'])->assertSessionHasNoErrors();
        $this->assertSame('revision_required', $entry->fresh()->verification_status);

        $savedMembers = $entry->members()->where('member_type', 'player')->orderBy('id')->get();
        foreach ($members as $index => &$member) $member['id'] = $savedMembers[$index]->id;
        $members[0]['name'] = 'Pemain Diperbaiki';
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => $this->withDocuments($members)] ]])->assertSessionHasNoErrors();
        $this->assertSame('pending', $entry->fresh()->verification_status);
        $this->assertSame($savedMembers[0]->id, $entry->members()->where('name', 'Pemain Diperbaiki')->value('id'));
        $this->assertDatabaseHas('entry_members', ['event_entry_id' => $entry->id, 'name' => 'Pemain Diperbaiki']);
        $this->assertDatabaseHas('entry_registration_audits', ['event_entry_id' => $entry->id, 'action' => 'resubmitted']);
    }

    public function test_private_member_document_is_visible_only_to_admin_and_owner_pd(): void
    {
        Storage::fake('local');
        $this->seed();
        $this->seed(PdVerificationDemoSeeder::class);
        $owner = User::query()->where('email', 'aceh@gmail.com')->firstOrFail();
        $otherPd = User::query()->where('role', 'pd_admin')->whereKeyNot($owner->id)->firstOrFail();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->where('regional_committee_id', $owner->regional_committee_id)->firstOrFail();
        $path = 'registrations/test/foto.pdf';
        Storage::disk('local')->put($path, 'dokumen');
        $member = EntryMember::query()->where('event_entry_id', $entry->id)->firstOrFail();
        $member->update(['documents' => ['photo' => $path]]);

        $url = route('entry-members.documents.show', [$member, 'photo']);
        $this->actingAs($owner)->get($url)->assertOk();
        $this->actingAs($admin)->get($url)->assertOk();
        $this->actingAs($otherPd)->get($url)->assertForbidden();
        $this->actingAs($owner)->get(route('entry-members.documents.show', [$member, 'unknown']))->assertNotFound();
    }

    public function test_player_revision_automatically_opens_its_team_for_pd(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->whereHas('teams.members')->with('teams.members')->firstOrFail();
        $entry->update(['verification_status' => 'pending']);
        $team = $entry->teams->first();
        $team->update(['verification_status_override' => null, 'verification_note' => null]);
        $member = $team->members->first();
        $member->update(['verification_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.entry-members.revision', $member), ['note' => 'Perbaiki foto pemain.'])->assertRedirect();

        $this->assertSame('revision_required', $member->fresh()->verification_status);
        $this->assertSame('revision_required', $team->fresh()->verification_status_override);
        $this->assertSame('Perbaiki foto pemain.', $team->fresh()->verification_note);
        $this->assertDatabaseHas('entry_team_audits', ['entry_team_id' => $team->id, 'action' => 'member_revision_opened', 'reason' => 'Perbaiki foto pemain.']);
    }

    public function test_rejected_entry_can_be_reopened_for_revision(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->with('teams')->firstOrFail();
        $entry->update(['verification_status' => 'pending']);
        $team = $entry->teams->first();
        $team?->update(['verification_status_override' => 'verified']);

        $this->actingAs($admin)->post(route('admin.entries.reject', $entry), ['note' => 'Dokumen tidak sah.'])->assertRedirect();
        $this->assertSame('rejected', $entry->fresh()->verification_status);
        if ($team) {
            $this->assertNull($team->fresh()->verification_status_override);
            $this->assertDatabaseHas('entry_team_audits', ['entry_team_id' => $team->id, 'action' => 'parent_rejected', 'reason' => 'Dokumen tidak sah.']);
        }
        $this->actingAs($admin)->post(route('admin.entries.revision', $entry), ['note' => 'Silakan unggah dokumen pengganti.'])->assertRedirect();

        $this->assertSame('revision_required', $entry->fresh()->verification_status);
        $this->assertSame('Silakan unggah dokumen pengganti.', $entry->fresh()->verification_note);
    }

    public function test_pd_can_cancel_own_pending_entry_without_deleting_history(): void
    {
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $entry = EventEntry::query()->firstOrFail();
        $entry->update(['regional_committee_id' => $pd->regional_committee_id, 'registration_key' => $entry->tournament_event_id.':'.$pd->regional_committee_id, 'verification_status' => 'pending']);

        $this->actingAs($pd)->delete(route('pd.entries.destroy', $entry))->assertRedirect();

        $this->assertSame('cancelled', $entry->fresh()->verification_status);
        $this->assertDatabaseHas('entry_registration_audits', ['event_entry_id' => $entry->id, 'action' => 'cancelled']);
    }

    public function test_parent_approval_requires_every_active_team_approved(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->whereHas('teams.members')->with('teams.members')->firstOrFail();
        $entry->update(['verification_status' => 'pending']);
        $team = $entry->teams->first();
        $team->update(['verification_status_override' => null, 'cancelled_at' => null]);
        $team->members->each->update(['verification_status' => 'verified']);

        $this->actingAs($admin)->post(route('admin.entries.verify', $entry))->assertUnprocessable();
        $this->actingAs($admin)->post(route('admin.entry-teams.override', $team), ['status' => 'verified', 'reason' => 'Tim lengkap.'])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.entries.verify', $entry))->assertRedirect();

        $this->assertSame('verified', $entry->fresh()->verification_status);
    }

    public function test_cancelled_team_and_its_players_do_not_block_parent_approval(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->whereHas('teams.members')->with('teams.members')->firstOrFail();
        $entry->update(['verification_status' => 'pending']);
        $activeTeam = $entry->teams->first();
        $activeTeam->update(['verification_status_override' => 'verified', 'cancelled_at' => null]);
        $activeTeam->members->each->update(['verification_status' => 'verified']);
        $cancelledTeam = $entry->teams()->create(['public_id' => (string) \Illuminate\Support\Str::uuid(), 'team_no' => 99, 'label' => 'Tim Batal', 'cancelled_at' => now(), 'verification_status_override' => 'cancelled']);
        $cancelledTeam->members()->create(['event_entry_id' => $entry->id, 'name' => 'Pemain Batal', 'normalized_name' => 'pemain batal', 'member_type' => 'player', 'verification_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.entries.verify', $entry))->assertRedirect();

        $this->assertSame('verified', $entry->fresh()->verification_status);
    }

    public function test_player_cannot_be_verified_while_team_waits_for_revision(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->whereHas('teams.members')->with('teams.members')->firstOrFail();
        $entry->update(['verification_status' => 'pending']);
        $team = $entry->teams->first();
        $team->update(['verification_status_override' => 'revision_required']);
        $member = $team->members->first();
        $member->update(['verification_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.entry-members.verify', $member))->assertUnprocessable();

        $this->assertSame('pending', $member->fresh()->verification_status);
    }

    public function test_full_revision_cannot_open_when_team_is_already_verified(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->whereHas('teams')->with('teams')->firstOrFail();
        $entry->update(['verification_status' => 'pending']);
        $entry->teams->first()->update(['verification_status_override' => 'verified']);

        $this->actingAs($admin)->post(route('admin.entries.revision', $entry), ['note' => 'Perbaiki roster.'])->assertUnprocessable();

        $this->assertSame('pending', $entry->fresh()->verification_status);
    }

    public function test_pd_cannot_cancel_another_committee_entry(): void
    {
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $entry = EventEntry::query()->firstOrFail();
        $entry->update(['verification_status' => 'pending']);

        $this->actingAs($pd)->delete(route('pd.entries.destroy', $entry))->assertForbidden();
        $this->assertSame('pending', $entry->fresh()->verification_status);
    }

    public function test_official_is_blocked_when_already_registered_as_player_and_rule_forbids_competing(): void
    {
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        [$playerEvent, $officialEvent] = TournamentEvent::query()->whereNotNull('registration_published_at')->with('category')->limit(2)->get();
        $this->openForRegistration($playerEvent, ['official_can_compete' => false]);
        $this->openForRegistration($officialEvent, ['max_officials_per_pd' => 2, 'official_roles' => ['coach'], 'official_can_compete' => false]);
        EventEntry::query()->where('regional_committee_id', $pd->regional_committee_id)->whereIn('tournament_event_id', [$playerEvent->id, $officialEvent->id])->delete();

        $this->actingAs($pd)->post(route('pd.events.entries.store', $playerEvent), ['intent' => 'draft', 'teams' => [['members' => $this->members($playerEvent, 'Pemain', 'Budi Rangkap')]]])->assertSessionHasNoErrors();
        $this->actingAs($pd)->post(route('pd.events.entries.store', $officialEvent), ['intent' => 'draft', 'teams' => [['members' => $this->members($officialEvent, 'Atlet')]], 'officials' => [['name' => 'Nama Berbeda', 'identity_type' => 'nik', 'identity_number' => '3173000000000001', 'role' => 'coach']]])->assertSessionHasErrors('officials');
        $this->assertDatabaseMissing('entry_members', ['event_entry_id' => EventEntry::query()->where('tournament_event_id', $officialEvent->id)->value('id'), 'name' => 'Budi Rangkap', 'member_type' => 'official']);
    }

    public function test_allowed_official_lists_sports_where_they_also_play(): void
    {
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        [$playerEvent, $officialEvent] = TournamentEvent::query()->whereNotNull('registration_published_at')->with(['category', 'sport'])->limit(2)->get();
        $this->openForRegistration($playerEvent);
        $this->openForRegistration($officialEvent, ['max_officials_per_pd' => 2, 'official_roles' => ['coach'], 'official_can_compete' => true]);
        EventEntry::query()->where('regional_committee_id', $pd->regional_committee_id)->whereIn('tournament_event_id', [$playerEvent->id, $officialEvent->id])->delete();

        $this->actingAs($pd)->post(route('pd.events.entries.store', $playerEvent), ['intent' => 'draft', 'teams' => [['members' => $this->members($playerEvent, 'Pemain', 'Sari Rangkap')]]])->assertSessionHasNoErrors();
        $this->actingAs($pd)->post(route('pd.events.entries.store', $officialEvent), ['intent' => 'draft', 'teams' => [['members' => $this->members($officialEvent, 'Atlet')]], 'officials' => [['name' => 'Nama Official', 'identity_type' => 'nik', 'identity_number' => '3173000000000001', 'role' => 'coach']]])->assertRedirect()->assertSessionHasNoErrors()->assertSessionMissing('error');

        $official = DB::table('entry_members')->where('name', 'Nama Official')->first();
        $this->assertNotNull($official);
        $this->assertSame('official', $official->member_type);
        $this->assertSame('coach', $official->position);
        $this->assertSame('3173000000000001', $official->identity_number);
        $this->actingAs($pd)->get(route('pd.events.show', $officialEvent))->assertInertia(fn ($page) => $page
            ->where('category.official_can_compete', true)
            ->where('entries', fn ($entries) => collect($entries)->flatMap(fn ($entry) => $entry['officials'])->contains(fn ($official) => $official['name'] === 'Nama Official' && in_array($playerEvent->sport->name, $official['playing_sports'], true))));
    }

    public function test_submit_requires_player_documents_and_stores_private_files(): void
    {
        Storage::fake('local');
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->with('category')->firstOrFail();
        $this->openForRegistration($event);
        EventEntry::query()->where('regional_committee_id', $pd->regional_committee_id)->where('tournament_event_id', $event->id)->delete();
        $members = $this->members($event, 'Pemain');

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => $members]]])->assertSessionHasErrors('teams.0.members.0.documents.photo');

        foreach ($members as &$member) foreach (['photo', 'registration_form', 'identity_card', 'pension_card', 'employee_decree'] as $key) $member['documents'][$key] = UploadedFile::fake()->create($key.'.pdf', 100, 'application/pdf');
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => $members]]])->assertSessionHasNoErrors()->assertSessionMissing('error');

        $saved = DB::table('entry_members')->where('member_type', 'player')->whereNotNull('documents')->first();
        $this->assertNotNull($saved);
        $this->assertCount(5, json_decode($saved->documents, true));
    }

    private function openForRegistration(TournamentEvent $event, array $rules = []): void
    {
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay(), 'registration_rules' => array_merge($event->registration_rules ?? [], $rules)]);
    }

    private function members(TournamentEvent $event, string $prefix, ?string $firstName = null): array
    {
        return collect(range(1, $event->registration_rules['min_members_per_team'] ?? $event->registration_rules['min_members'] ?? 1))
            ->map(fn ($number) => ['name' => $number === 1 && $firstName ? $firstName : $prefix.' '.$number, 'pdam_id' => Pdam::query()->value('id'), 'identity_type' => 'nik', 'identity_number' => '3173'.str_pad((string) $number, 12, '0', STR_PAD_LEFT)])->all();
    }

    private function withDocuments(array $members): array
    {
        foreach ($members as &$member) foreach (['photo', 'registration_form', 'identity_card', 'pension_card', 'employee_decree'] as $key) $member['documents'][$key] = UploadedFile::fake()->create($key.'.pdf', 100, 'application/pdf');
        return $members;
    }
}
