<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueAgendaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_agenda_and_conflicting_time_is_rejected(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $venue = Venue::query()->firstOrFail();
        $agendaCount = \DB::table('event_agendas')->count();
        $payload = ['date' => '2026-09-02', 'title' => 'Pertandingan Pagi', 'type' => 'sport', 'venue_id' => $venue->id, 'start_time' => '08:00', 'end_time' => '10:00'];

        $this->actingAs($admin)->post(route('admin.agendas.store'), $payload)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.agendas.store'), $payload + ['title' => 'Pertandingan Bentrok', 'start_time' => '09:00', 'end_time' => '11:00'])
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('event_agendas', $agendaCount + 1);
    }

    public function test_pd_admin_cannot_manage_venue_and_agenda(): void
    {
        $this->seed();
        $pdAdmin = User::query()->where('role', 'pd_admin')->firstOrFail();
        $this->actingAs($pdAdmin)->get(route('admin.venue-agenda.index'))->assertForbidden();
    }
}
