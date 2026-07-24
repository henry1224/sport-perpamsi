<?php

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\SportCategory;
use App\Models\SportRegulation;
use App\Models\TournamentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TournamentEventPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_draft_event_without_creating_tournament_data(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $sport = Sport::query()->whereHas('categories')->whereHas('regulations', fn ($query) => $query->where('is_active', true))->firstOrFail();
        $category = SportCategory::query()->create(['public_id' => (string) Str::uuid(), 'sport_id' => $sport->id, 'code' => 'CRUD-TEST', 'name' => 'CRUD Test', 'competition_type' => 'individual', 'min_members' => 1, 'max_members' => 1, 'default_max_teams_per_pd' => 1, 'scoring_type' => 'points', 'bracket_enabled' => true, 'sort_order' => 99, 'is_active' => true]);
        $regulation = SportRegulation::query()->where('sport_id', $sport->id)->where('is_active', true)->firstOrFail();
        $matchCount = \DB::table('matches')->count();

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'sport_id' => $sport->id, 'sport_category_id' => $category->id, 'sport_regulation_id' => $regulation->id,
            'code' => 'event-uji-crud', 'name' => 'Event Uji CRUD', 'format' => $sport->default_format,
        ])->assertSessionHasNoErrors();

        $event = TournamentEvent::query()->where('code', Str::slug($sport->code.'-'.$category->code))->firstOrFail();
        $this->assertSame('registration_draft', $event->status);
        $this->assertSame($sport->name.' - '.$category->name, $event->name);
        $this->assertSame($category->default_max_teams_per_pd, $event->registration_rules['max_teams_per_pd']);
        $this->assertSame($sport->default_max_officials_per_pd, $event->registration_rules['max_officials_per_pd']);
        $this->assertSame($sport->official_roles ?? [], $event->registration_rules['official_roles']);
        $this->assertDatabaseCount('matches', $matchCount);
        $this->assertSame(0, $event->entries()->count());
        $this->assertSame(0, $event->matches()->count());

        $category->update(['name' => 'CRUD Test Diperbarui']);
        $this->actingAs($admin)->put(route('admin.events.update', $event), [
            'sport_id' => $sport->id, 'sport_category_id' => $category->id, 'sport_regulation_id' => $regulation->id,
            'code' => 'event-uji-crud', 'name' => 'Event Uji Diperbarui', 'format' => $sport->default_format,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tournament_events', ['id' => $event->id, 'name' => $sport->name.' - CRUD Test Diperbarui']);

        $this->actingAs($admin)->delete(route('admin.events.destroy', $event))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('tournament_events', ['id' => $event->id]);

        $used = TournamentEvent::query()->has('entries')->firstOrFail();
        $this->actingAs($admin)->delete(route('admin.events.destroy', $used))->assertStatus(422);
    }

    public function test_super_admin_can_publish_and_close_registration(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNull('registration_published_at')->whereNotNull('sport_category_id')->with('category')->firstOrFail();
        $event->entries()->delete();
        $regulation = SportRegulation::query()->create(['sport_id' => $event->sport_id, 'version' => 2, 'title' => 'Regulasi Uji', 'content' => 'Aturan uji publikasi.', 'is_active' => true, 'created_by' => $admin->id]);

        $this->actingAs($admin)->post(route('admin.events.publish', $event), [
            'registration_open_at' => now()->subMinute()->toDateTimeString(),
            'registration_close_at' => now()->addWeek()->toDateTimeString(),
            'sport_regulation_id' => $regulation->id,
            'max_teams_per_pd' => 3,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('registration_open', $event->status);
        $this->assertSame($admin->id, $event->registration_published_by);
        $this->assertSame($event->category->min_members, $event->registration_rules['min_members']);
        $this->assertSame(2, $event->registration_rules['regulation_version']);
        $this->assertSame($event->category->default_max_teams_per_pd, $event->registration_rules['max_teams_per_pd']);
        $this->assertSame($event->sport->default_max_officials_per_pd, $event->registration_rules['max_officials_per_pd']);
        $this->assertSame($event->sport->official_roles ?? [], $event->registration_rules['official_roles']);
        $this->assertTrue($event->registrationIsOpen());
        $this->assertDatabaseHas('event_publication_audits', ['tournament_event_id' => $event->id, 'action' => 'published']);

        $snapshot = $event->registration_rules;
        $event->sport->update(['default_max_officials_per_pd' => 20, 'official_roles' => ['changed']]);
        $this->assertSame($snapshot, $event->fresh()->registration_rules);

        $this->actingAs($admin)->post(route('admin.events.close', $event))->assertRedirect();
        $this->assertSame('registration_closed', $event->fresh()->status);
        $this->assertDatabaseHas('event_publication_audits', ['tournament_event_id' => $event->id, 'action' => 'closed']);

        $this->actingAs($admin)->post(route('admin.events.unpublish', $event))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($event->fresh()->registration_published_at);
        $this->assertDatabaseHas('event_publication_audits', ['tournament_event_id' => $event->id, 'action' => 'unpublished']);
    }

    public function test_admin_cannot_create_duplicate_event_for_same_category(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('sport_category_id')->with(['sport.regulations'])->firstOrFail();

        $this->actingAs($admin)->post(route('admin.events.store'), [
            'sport_id' => $event->sport_id,
            'sport_category_id' => $event->sport_category_id,
            'sport_regulation_id' => $event->sport->regulations->firstOrFail()->id,
            'code' => 'duplicate-event',
            'name' => 'Duplicate Event',
            'format' => $event->format,
        ])->assertSessionHasErrors('sport_category_id');

        $this->assertDatabaseMissing('tournament_events', ['code' => 'duplicate-event']);
    }

    public function test_registration_period_blocks_early_and_expired_submit(): void
    {
        $this->seed();

        $pdAdmin = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $event->entries()->where('regional_committee_id', $pdAdmin->regional_committee_id)->delete();
        $payload = ['intent' => 'draft', 'members' => collect(range(1, $event->registration_rules['min_members']))->map(fn ($number) => ['name' => 'Pemain Waktu '.$number])->all()];

        $event->update(['registration_open_at' => now()->addDay(), 'registration_close_at' => now()->addWeek()]);
        $this->actingAs($pdAdmin)->post(route('pd.events.entries.store', $event), $payload)->assertSessionHas('error');

        $event->update(['registration_open_at' => now()->subWeek(), 'registration_close_at' => now()->subDay()]);
        $this->actingAs($pdAdmin)->post(route('pd.events.entries.store', $event), $payload)->assertSessionHas('error');
    }

    public function test_inactive_admin_cannot_publish_registration(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $admin->update(['account_status' => 'inactive']);
        $event = TournamentEvent::query()->firstOrFail();
        $regulation = SportRegulation::query()->create(['sport_id' => $event->sport_id, 'version' => 2, 'title' => 'Regulasi Uji', 'content' => 'Aturan uji.', 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.events.publish', $event), [
            'registration_open_at' => now()->toDateTimeString(),
            'registration_close_at' => now()->addWeek()->toDateTimeString(),
            'sport_regulation_id' => $regulation->id,
        ])->assertForbidden();
    }

    public function test_publication_with_entries_cannot_be_withdrawn(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->has('entries')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.events.unpublish', $event))->assertStatus(422);
        $this->assertNotNull($event->fresh()->registration_published_at);
    }

    public function test_bracket_lock_requires_every_active_team_to_be_verified(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereHas('entries.teams')->with('entries.teams')->firstOrFail();
        $event->update(['status' => 'registration_closed', 'registration_published_at' => now()]);
        $event->entries()->update(['verification_status' => 'verified']);
        $event->entries->flatMap->teams->each->update(['verification_status_override' => null, 'cancelled_at' => null, 'seed_no' => null]);
        $pending = $event->entries->firstOrFail();
        $pending->update(['verification_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.events.bracket.lock', $event))->assertStatus(422);
        $this->assertSame('registration_closed', $event->fresh()->status);

        $pending->update(['verification_status' => 'verified']);
        $event->entries()->with('members')->get()->flatMap->members->each->update(['verification_status' => 'verified']);
        $this->actingAs($admin)->post(route('admin.events.bracket.lock', $event))->assertRedirect()->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('bracket_locked', $event->status);
        $this->assertNotNull($event->seed_locked_at);
        $this->assertSame($event->entries()->withCount('teams')->get()->sum('teams_count'), $event->entries()->with('teams')->get()->flatMap->teams->whereNotNull('seed_no')->count());
    }

    public function test_parent_entry_waits_until_every_player_is_verified(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $entry = TournamentEvent::query()->whereHas('entries.members')->firstOrFail()->entries()->with(['members', 'teams'])->firstOrFail();
        $entry->update(['verification_status' => 'pending']);
        $entry->teams->each->update(['verification_status_override' => null, 'cancelled_at' => null]);
        $entry->members->each->update(['verification_status' => 'verified']);
        $pendingPlayer = $entry->members->where('member_type', 'player')->firstOrFail();
        $pendingPlayer->update(['verification_status' => 'pending']);

        $this->actingAs($admin)->post(route('admin.entries.verify', $entry))->assertStatus(422);
        $this->actingAs($admin)->post(route('admin.entry-members.verify', $pendingPlayer))->assertRedirect()->assertSessionHasNoErrors();
        foreach ($entry->teams as $team) $this->actingAs($admin)->post(route('admin.entry-teams.override', $team), ['status' => 'verified', 'reason' => 'Seluruh pemain lengkap.'])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.entries.verify', $entry))->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('verified', $entry->fresh()->verification_status);
        $this->assertSame('verified', $pendingPlayer->fresh()->verification_status);
        $this->assertDatabaseHas('entry_member_audits', ['entry_member_id' => $pendingPlayer->id, 'action' => 'verified']);
    }
}
