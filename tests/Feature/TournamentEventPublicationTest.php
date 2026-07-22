<?php

namespace Tests\Feature;

use App\Models\SportRegulation;
use App\Models\TournamentEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentEventPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_publish_and_close_registration(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNull('registration_published_at')->whereNotNull('sport_category_id')->with('category')->firstOrFail();
        $event->entries()->delete();
        $regulation = SportRegulation::query()->create(['sport_id' => $event->sport_id, 'version' => 1, 'title' => 'Regulasi Uji', 'content' => 'Aturan uji publikasi.', 'is_active' => true, 'created_by' => $admin->id]);

        $this->actingAs($admin)->post(route('admin.events.publish', $event), [
            'registration_open_at' => now()->subMinute()->toDateTimeString(),
            'registration_close_at' => now()->addWeek()->toDateTimeString(),
            'sport_regulation_id' => $regulation->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('registration_open', $event->status);
        $this->assertSame($admin->id, $event->registration_published_by);
        $this->assertSame($event->category->min_members, $event->registration_rules['min_members']);
        $this->assertSame(1, $event->registration_rules['regulation_version']);
        $this->assertTrue($event->registrationIsOpen());
        $this->assertDatabaseHas('event_publication_audits', ['tournament_event_id' => $event->id, 'action' => 'published']);

        $this->actingAs($admin)->post(route('admin.events.close', $event))->assertRedirect();
        $this->assertSame('registration_closed', $event->fresh()->status);
        $this->assertDatabaseHas('event_publication_audits', ['tournament_event_id' => $event->id, 'action' => 'closed']);

        $this->actingAs($admin)->post(route('admin.events.unpublish', $event))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertNull($event->fresh()->registration_published_at);
        $this->assertDatabaseHas('event_publication_audits', ['tournament_event_id' => $event->id, 'action' => 'unpublished']);
    }

    public function test_registration_period_blocks_early_and_expired_submit(): void
    {
        $this->seed();

        $pdAdmin = User::query()->where('role', 'pd_admin')->firstOrFail();
        $event = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $event->entries()->where('regional_committee_id', $pdAdmin->regional_committee_id)->delete();
        $payload = ['members' => collect(range(1, $event->registration_rules['min_members']))->map(fn ($number) => ['name' => 'Pemain Waktu '.$number])->all()];

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
        $regulation = SportRegulation::query()->create(['sport_id' => $event->sport_id, 'version' => 1, 'title' => 'Regulasi Uji', 'content' => 'Aturan uji.', 'is_active' => true]);

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

    public function test_format_can_change_only_before_publication(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $draft = TournamentEvent::query()->whereNull('registration_published_at')->firstOrFail();
        $draft->entries()->delete();

        $this->actingAs($admin)->put(route('admin.events.format.update', $draft), ['format' => 'round_robin'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('round_robin', $draft->fresh()->format);

        $published = TournamentEvent::query()->whereNotNull('registration_published_at')->firstOrFail();
        $original = $published->format;
        $this->actingAs($admin)->put(route('admin.events.format.update', $published), ['format' => 'ranking'])->assertSessionHasErrors('format');
        $this->assertSame($original, $published->fresh()->format);
    }
}
