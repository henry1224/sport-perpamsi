<?php

namespace Tests\Feature;

use App\Models\EventEntry;
use App\Models\TournamentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $this->seed();
        $pd = User::query()->where('role', 'pd_admin')->firstOrFail();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->with('category')->firstOrFail();
        $event->entries()->where('regional_committee_id', $pd->regional_committee_id)->delete();
        $members = collect(range(1, $event->registration_rules['min_members']))->map(fn ($number) => ['name' => 'Pemain Draft '.$number])->all();

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'draft', 'members' => [['name' => 'Pemain Draft 1']]])->assertSessionHasNoErrors();
        $entry = EventEntry::query()->where('registration_key', $event->id.':'.$pd->regional_committee_id)->firstOrFail();
        $this->assertSame('draft', $entry->verification_status);

        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'members' => $members])->assertSessionHasNoErrors();
        $this->assertSame('pending', $entry->fresh()->verification_status);

        $this->actingAs($admin)->post(route('admin.entries.revision', $entry), ['note' => 'Perbaiki nama pemain.'])->assertSessionHasNoErrors();
        $this->assertSame('revision_required', $entry->fresh()->verification_status);

        $members[0]['name'] = 'Pemain Diperbaiki';
        $this->actingAs($pd)->post(route('pd.events.entries.store', $event), ['intent' => 'submit', 'members' => $members])->assertSessionHasNoErrors();
        $this->assertSame('pending', $entry->fresh()->verification_status);
        $this->assertDatabaseHas('entry_members', ['event_entry_id' => $entry->id, 'name' => 'Pemain Diperbaiki']);
        $this->assertDatabaseHas('entry_registration_audits', ['event_entry_id' => $entry->id, 'action' => 'resubmitted']);
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

        $this->actingAs($pd)->post(route('pd.events.entries.store', $playerEvent), ['intent' => 'draft', 'members' => $this->members($playerEvent, 'Pemain', 'Budi Rangkap')])->assertSessionHasNoErrors();
        $this->actingAs($pd)->post(route('pd.events.entries.store', $officialEvent), ['intent' => 'draft', 'members' => $this->members($officialEvent, 'Atlet'), 'officials' => [['name' => 'Budi Rangkap', 'role' => 'coach']]])->assertSessionHasErrors('officials');
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

        $this->actingAs($pd)->post(route('pd.events.entries.store', $playerEvent), ['intent' => 'draft', 'members' => $this->members($playerEvent, 'Pemain', 'Sari Rangkap')])->assertSessionHasNoErrors();
        $this->actingAs($pd)->post(route('pd.events.entries.store', $officialEvent), ['intent' => 'draft', 'members' => $this->members($officialEvent, 'Atlet'), 'officials' => [['name' => 'Sari Rangkap', 'role' => 'coach']]])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('entry_members', ['name' => 'Sari Rangkap', 'member_type' => 'official', 'position' => 'coach']);
        $this->actingAs($pd)->get(route('pd.events.show', $officialEvent))->assertInertia(fn ($page) => $page
            ->where('category.official_can_compete', true)
            ->where('entries', fn ($entries) => collect($entries)->flatMap(fn ($entry) => $entry['officials'])->contains(fn ($official) => $official['name'] === 'Sari Rangkap' && in_array($playerEvent->sport->name, $official['playing_sports'], true))));
    }

    private function openForRegistration(TournamentEvent $event, array $rules = []): void
    {
        $event->update(['status' => 'registration_open', 'registration_open_at' => now()->subMinute(), 'registration_close_at' => now()->addDay(), 'registration_rules' => array_merge($event->registration_rules ?? [], $rules)]);
    }

    private function members(TournamentEvent $event, string $prefix, ?string $firstName = null): array
    {
        return collect(range(1, $event->registration_rules['min_members_per_team'] ?? $event->registration_rules['min_members'] ?? 1))
            ->map(fn ($number) => ['name' => $number === 1 && $firstName ? $firstName : $prefix.' '.$number])->all();
    }
}
