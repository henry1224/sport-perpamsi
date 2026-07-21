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
}
