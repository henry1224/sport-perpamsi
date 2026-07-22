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
        $this->actingAs($pdAdmin)->get(route('admin.venues.index'))->assertForbidden();
        $this->actingAs($pdAdmin)->get(route('admin.agendas.index'))->assertForbidden();
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

    public function test_admin_can_search_paginate_create_and_delete_unused_venue(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.venues.store'), [
            'code' => 'venue-uji', 'name' => 'Venue Uji Koordinat', 'address' => 'Balikpapan', 'city' => 'Balikpapan',
            'latitude' => -1.237927, 'longitude' => 116.852852, 'is_active' => true,
        ])->assertSessionHasNoErrors();

        $venue = Venue::query()->where('code', 'venue-uji')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.venues.index', ['search' => 'Koordinat', 'per_page' => 10]))
            ->assertInertia(fn ($page) => $page->where('venues.total', 1)->where('venues.data.0.id', $venue->id));
        $this->actingAs($admin)->get(route('admin.venues.index', ['search' => 'Koordinat', 'status' => 'inactive']))
            ->assertInertia(fn ($page) => $page->where('venues.total', 0)->where('filters.status', 'inactive'));
        $this->get(route('venue'))->assertInertia(fn ($page) => $page->where('venues', fn ($venues) => collect($venues)->contains(fn ($item) => $item['code'] === 'venue-uji' && (float) $item['latitude'] === -1.237927)));

        $this->actingAs($admin)->delete(route('admin.venues.destroy', $venue))->assertSessionHas('error');
        $venue->update(['is_active' => false]);
        $this->actingAs($admin)->delete(route('admin.venues.destroy', $venue))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('venues', ['id' => $venue->id]);
    }

    public function test_used_venue_cannot_be_deleted(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $venue = Venue::query()->has('agendas')->firstOrFail();

        $this->actingAs($admin)->delete(route('admin.venues.destroy', $venue))->assertSessionHas('error');
        $this->assertDatabaseHas('venues', ['id' => $venue->id]);
    }

    public function test_public_cabor_uses_active_master_venue_data(): void
    {
        $this->seed();
        $agenda = EventAgenda::query()->whereNotNull('sport_id')->whereNotNull('published_at')->firstOrFail();
        $venue = Venue::query()->findOrFail($agenda->venue_id);
        $venue->update(['name' => 'Venue Master Terbaru', 'is_active' => true]);

        $this->get(route('cabor'))->assertInertia(fn ($page) => $page
            ->where('agenda', fn ($items) => collect($items)->contains(fn ($item) => $item['venue'] === 'Venue Master Terbaru')));

        $venue->update(['is_active' => false]);
        $this->get(route('cabor'))->assertInertia(fn ($page) => $page
            ->where('agenda', fn ($items) => ! collect($items)->contains(fn ($item) => $item['venue'] === 'Venue Master Terbaru')));
    }
}
