<?php

namespace Tests\Feature;

use App\Models\EventEntry;
use App\Models\EntryMember;
use App\Models\Pdam;
use App\Models\TournamentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function test_nik_and_kta_formats_are_validated(): void
    {
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $this->openForRegistration($event);
        $event->entries()->where('regional_committee_id', $pd->regional_committee_id)->delete();
        $members = $this->members($event, 'Pemain');

        $members[0]['identity_number'] = '3173ABC000000001';
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'draft', 'teams' => [['members' => $members]]])->assertSessionHasErrors('teams.0.members.0.identity_number');

        $members[0]['identity_number'] = '123456789012345';
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'draft', 'teams' => [['members' => $members]]])->assertSessionHasErrors('teams.0.members.0.identity_number');

        $members[0]['identity_number'] = '31730101019000010';
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'draft', 'teams' => [['members' => $members]]])->assertSessionHasNoErrors();

        $members[0]['identity_type'] = 'kta';
        $members[0]['identity_number'] = 'A1';
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'draft', 'teams' => [['members' => $members]]])->assertSessionHasErrors('teams.0.members.0.identity_number');

        $members[0]['identity_number'] = 'A-1';
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'draft', 'teams' => [['members' => $members]]])->assertSessionHasNoErrors();
    }

    public function test_same_name_is_allowed_but_same_identity_is_rejected(): void
    {
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $this->openForRegistration($event, ['min_members_per_team' => 2, 'max_members_per_team' => 2]);
        $event->entries()->where('regional_committee_id', $pd->regional_committee_id)->delete();
        $members = [
            ['name' => 'Muhammad Rehan', 'pdam_id' => Pdam::query()->value('id'), 'identity_type' => 'nik', 'identity_number' => '3173010101900001'],
            ['name' => 'Muhammad Rehan', 'pdam_id' => Pdam::query()->value('id'), 'identity_type' => 'nik', 'identity_number' => '3173010101900002'],
        ];

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'draft', 'teams' => [['members' => $members]]])->assertSessionHasNoErrors();

        $members[1]['identity_number'] = $members[0]['identity_number'];
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'draft', 'teams' => [['members' => $members]]])->assertSessionHasErrors('teams');
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

        $savedMembers = $entry->members()->where('member_type', 'player')->orderBy('id')->get();
        $team = $entry->teams()->firstOrFail();
        $this->actingAs($admin)->post(route('admin.entry-members.revision', $savedMembers->first()), ['note' => 'Perbaiki nama pemain.'])->assertSessionHasNoErrors();
        $this->assertSame('revision_required', $team->fresh()->verification_status_override);

        foreach ($members as $index => &$member) $member['id'] = $savedMembers[$index]->id;
        $members[0]['name'] = 'Pemain Diperbaiki';
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['id' => $team->id, 'members' => $this->withDocuments($members)] ]])->assertSessionHasNoErrors();
        $this->assertSame('pending', $entry->fresh()->verification_status);
        $this->assertNull($team->fresh()->verification_status_override);
        $this->assertSame($savedMembers[0]->id, $entry->members()->where('name', 'Pemain Diperbaiki')->value('id'));
        $this->assertDatabaseHas('entry_members', ['event_entry_id' => $entry->id, 'name' => 'Pemain Diperbaiki']);
        $this->assertDatabaseHas('entry_registration_audits', ['event_entry_id' => $entry->id, 'action' => 'team_resubmitted']);
    }

    public function test_private_member_document_is_visible_only_to_admin_and_owner_pd(): void
    {
        Storage::fake('local');
        $this->seed();
        $owner = User::query()->where('role', 'pd_admin')->firstOrFail();
        $otherPd = User::factory()->create(['role' => 'pd_admin', 'account_status' => 'verified', 'regional_committee_id' => DB::table('regional_committees')->where('id', '!=', $owner->regional_committee_id)->value('id')]);
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->whereHas('members')->firstOrFail();
        $entry->update(['regional_committee_id' => $owner->regional_committee_id]);
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

    public function test_rejected_player_automatically_opens_its_team_for_correction(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->whereHas('teams.members')->with('teams.members')->firstOrFail();
        $entry->update(['verification_status' => 'pending']);
        $team = $entry->teams->first();
        $team->update(['verification_status_override' => null, 'verification_note' => null]);
        $member = $team->members->first();
        $member->update(['verification_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.entry-members.reject', $member), ['note' => 'Identitas pemain tidak memenuhi syarat.'])->assertRedirect();

        $this->assertSame('rejected', $member->fresh()->verification_status);
        $this->assertSame('revision_required', $team->fresh()->verification_status_override);
        $this->assertSame('Identitas pemain tidak memenuhi syarat.', $team->fresh()->verification_note);
        $this->assertDatabaseHas('entry_member_audits', ['entry_member_id' => $member->id, 'action' => 'rejected', 'reason' => 'Identitas pemain tidak memenuhi syarat.']);
        $this->assertDatabaseHas('entry_team_audits', ['entry_team_id' => $team->id, 'action' => 'member_rejection_opened', 'reason' => 'Identitas pemain tidak memenuhi syarat.']);
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

    public function test_last_active_team_approval_finishes_registration_automatically(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->whereHas('teams.members')->with('teams.members')->firstOrFail();
        $entry->update(['verification_status' => 'pending']);
        $team = $entry->teams->first();
        $team->update(['verification_status_override' => null, 'cancelled_at' => null]);
        $team->members->each->update(['verification_status' => 'verified']);

        $this->actingAs($admin)->post(route('admin.entry-teams.override', $team), ['status' => 'verified', 'reason' => 'Tim lengkap.'])->assertRedirect();

        $this->assertSame('verified', $entry->fresh()->verification_status);
        $this->assertDatabaseHas('entry_registration_audits', ['event_entry_id' => $entry->id, 'action' => 'verified_automatically', 'user_id' => $admin->id]);
    }

    public function test_cancelled_team_and_its_players_do_not_block_automatic_completion(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = EventEntry::query()->whereHas('teams.members')->with('teams.members')->firstOrFail();
        $entry->update(['verification_status' => 'pending']);
        $activeTeam = $entry->teams->first();
        $activeTeam->update(['verification_status_override' => null, 'cancelled_at' => null]);
        $activeTeam->members->each->update(['verification_status' => 'verified']);
        $cancelledTeam = $entry->teams()->create(['public_id' => (string) \Illuminate\Support\Str::uuid(), 'team_no' => 99, 'label' => 'Tim Batal', 'cancelled_at' => now(), 'verification_status_override' => 'cancelled']);
        $cancelledTeam->members()->create(['event_entry_id' => $entry->id, 'name' => 'Pemain Batal', 'normalized_name' => 'pemain batal', 'member_type' => 'player', 'verification_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.entry-teams.override', $activeTeam), ['status' => 'verified', 'reason' => 'Tim aktif lengkap.'])->assertRedirect();

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
        $this->actingAs($pd)->post(route('pd.events.entries.store', $officialEvent), ['intent' => 'draft', 'teams' => [['members' => $this->members($officialEvent, 'Atlet')]], 'officials' => [['name' => 'Nama Berbeda', 'identity_type' => 'nik', 'identity_number' => '3173010101900001', 'role' => 'coach']]])->assertSessionHasErrors('officials');
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
        $this->actingAs($pd)->post(route('pd.events.entries.store', $officialEvent), ['intent' => 'draft', 'teams' => [['members' => $this->members($officialEvent, 'Atlet')]], 'officials' => [['name' => 'Nama Official', 'identity_type' => 'nik', 'identity_number' => '3173010101900001', 'role' => 'coach']]])->assertRedirect()->assertSessionHasNoErrors()->assertSessionMissing('error');

        $official = DB::table('entry_members')->where('name', 'Nama Official')->first();
        $this->assertNotNull($official);
        $this->assertSame('official', $official->member_type);
        $this->assertSame('coach', $official->position);
        $this->assertSame('3173010101900001', $official->identity_number);
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
        $documents = json_decode($saved->documents, true);
        $this->assertCount(5, $documents);
        foreach ($documents as $key => $path) {
            $this->assertStringStartsWith("registrations/pd-{$pd->regional_committee_id}/{$event->code}/", $path);
            $this->assertStringContainsString('/player/', $path);
            $this->assertStringContainsString("/{$key}-", $path);
            Storage::disk('local')->assertExists($path);
        }
        $this->actingAs($pd)->get(route('pd.events.show', $event))->assertInertia(fn ($page) => $page
            ->where('entries', fn ($entries) => collect($entries)->flatMap(fn ($entry) => $entry['teams'])->flatMap(fn ($team) => $team['members'])->contains(fn ($member) => count($member['document_links']) === 5 && collect($member['document_links'])->every(fn ($document) => $document['is_image'] === false))));
    }

    public function test_registration_document_larger_than_one_megabyte_is_rejected(): void
    {
        Storage::fake('local');
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->with('category')->firstOrFail();
        $this->openForRegistration($event);
        EventEntry::query()->where('regional_committee_id', $pd->regional_committee_id)->where('tournament_event_id', $event->id)->delete();
        $members = $this->withDocuments($this->members($event, 'Pemain'));
        $members[0]['documents']['photo'] = UploadedFile::fake()->create('foto-besar.pdf', 1025, 'application/pdf');

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'teams' => [['members' => $members]]])->assertSessionHasErrors('teams.0.members.0.documents.photo');
    }

    public function test_admin_event_filter_shows_pending_aceh_registration(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $pd->committee()->update(['name' => 'PD PERPAMSI Aceh']);
        $event = TournamentEvent::query()->where('code', 'badminton-mixed-double')->firstOrFail();
        $pdamId = Pdam::query()->value('id');
        $entry = EventEntry::query()->create([
            'public_id' => (string) Str::uuid(),
            'registration_key' => $event->id.':'.$pd->regional_committee_id,
            'tournament_event_id' => $event->id,
            'regional_committee_id' => $pd->regional_committee_id,
            'pdam_id' => $pdamId,
            'display_name' => 'PD PERPAMSI Aceh',
            'verification_status' => 'pending',
            'submitted_at' => now(),
        ]);
        $team = $entry->teams()->create(['public_id' => (string) Str::uuid(), 'team_no' => 1, 'label' => 'Tim 1']);
        foreach (['Andre', 'Arhan'] as $index => $name) {
            $team->members()->create(['event_entry_id' => $entry->id, 'pdam_id' => $pdamId, 'name' => $name, 'normalized_name' => mb_strtolower($name), 'member_type' => 'player', 'verification_status' => 'pending', 'documents' => ['photo' => $index ? 'registrations/test/photo.jpg' : 'registrations/test/photo.pdf']]);
        }

        $this->actingAs($admin)->get(route('admin.entries.index', ['event' => 'badminton-mixed-double']))->assertInertia(fn ($page) => $page
            ->where('filters.event', 'badminton-mixed-double')
            ->where('entries.data', fn ($entries) => collect($entries)->contains(fn ($item) => $item['committee'] === 'PD PERPAMSI Aceh'
                && $item['players_count'] === 2
                && collect($item['teams'][0]['members'])->pluck('name')->all() === ['Andre', 'Arhan']
                && $item['teams'][0]['members'][0]['documents'][0]['is_image'] === false
                && $item['teams'][0]['members'][1]['documents'][0]['is_image'] === true)));
    }

    private function openForRegistration(TournamentEvent $event, array $rules = []): void
    {
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay(), 'registration_rules' => array_merge($event->registration_rules ?? [], $rules)]);
    }

    private function members(TournamentEvent $event, string $prefix, ?string $firstName = null): array
    {
        return collect(range(1, $event->registration_rules['min_members_per_team'] ?? $event->registration_rules['min_members'] ?? 1))
            ->map(fn ($number) => ['name' => $number === 1 && $firstName ? $firstName : $prefix.' '.$number, 'pdam_id' => Pdam::query()->value('id'), 'identity_type' => 'nik', 'identity_number' => '317301010190'.str_pad((string) $number, 4, '0', STR_PAD_LEFT)])->all();
    }

    private function withDocuments(array $members): array
    {
        foreach ($members as &$member) foreach (['photo', 'registration_form', 'identity_card', 'pension_card', 'employee_decree'] as $key) $member['documents'][$key] = UploadedFile::fake()->create($key.'.pdf', 100, 'application/pdf');
        return $members;
    }
}
