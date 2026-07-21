<?php

namespace Tests\Feature;

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

        $this->actingAs($admin)->post(route('admin.events.publish', $event), [
            'registration_open_at' => now()->subMinute()->toDateTimeString(),
            'registration_close_at' => now()->addWeek()->toDateTimeString(),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $event->refresh();
        $this->assertSame('registration_open', $event->status);
        $this->assertSame($admin->id, $event->registration_published_by);
        $this->assertSame($event->category->min_members, $event->registration_rules['min_members']);
        $this->assertTrue($event->registrationIsOpen());

        $this->actingAs($admin)->post(route('admin.events.close', $event))->assertRedirect();
        $this->assertSame('registration_closed', $event->fresh()->status);
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

        $this->actingAs($admin)->post(route('admin.events.publish', $event), [
            'registration_open_at' => now()->toDateTimeString(),
            'registration_close_at' => now()->addWeek()->toDateTimeString(),
        ])->assertForbidden();
    }
}
