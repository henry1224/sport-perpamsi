<?php

namespace Tests\Feature;

use App\Models\EventAgenda;
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

    public function test_admin_can_disable_venue_and_audit_agenda_changes(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $venue = Venue::query()->firstOrFail();
        $agenda = EventAgenda::query()->whereNotNull('end_time')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.venues.update', $venue), [
            'code' => $venue->code, 'name' => $venue->name, 'address' => $venue->address, 'city' => $venue->city,
            'facilities' => $venue->facilities, 'map_url' => $venue->map_url, 'contact_name' => $venue->contact_name,
            'contact_phone' => $venue->contact_phone, 'is_active' => false,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('venues', ['id' => $venue->id, 'is_active' => false]);

        $venue->update(['is_active' => true]);
        $this->actingAs($admin)->put(route('admin.agendas.update', $agenda), [
            'date' => $agenda->date->format('Y-m-d'), 'title' => $agenda->title.' Revisi', 'type' => $agenda->type,
            'sport_id' => $agenda->sport_id, 'tournament_event_id' => $agenda->tournament_event_id, 'venue_id' => $agenda->venue_id,
            'start_time' => substr($agenda->start_time, 0, 5), 'end_time' => substr($agenda->end_time, 0, 5),
            'time_note' => $agenda->time_note, 'description' => $agenda->description, 'change_note' => 'Penyesuaian operasional venue',
        ])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.agendas.publish', $agenda))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('event_agenda_audits', ['event_agenda_id' => $agenda->id, 'action' => 'updated', 'reason' => 'Penyesuaian operasional venue', 'user_id' => $admin->id]);
        $this->assertDatabaseHas('event_agenda_audits', ['event_agenda_id' => $agenda->id, 'action' => 'published', 'user_id' => $admin->id]);
    }
}
