<?php

namespace Tests\Feature;

use App\Models\EventAgenda;
use App\Models\SportAssignment;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StaffMatchScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_only_sees_and_opens_matches_in_active_assignment(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $staff = User::factory()->create(['role' => 'scorekeeper', 'account_status' => 'verified']);
        $venue = Venue::query()->firstOrFail();
        $otherVenue = Venue::query()->whereKeyNot($venue->id)->firstOrFail();
        $assigned = TournamentMatch::query()->with('tournamentEvent')->firstOrFail();
        $blocked = TournamentMatch::query()->with('tournamentEvent')->whereKeyNot($assigned->id)->firstOrFail();
        $assigned->update(['venue_id' => $venue->id, 'scheduled_at' => now()]);
        $blocked->update(['venue_id' => $otherVenue->id, 'scheduled_at' => now()]);
        SportAssignment::query()->create(['user_id' => $staff->id, 'sport_id' => $assigned->tournamentEvent->sport_id, 'venue_id' => $venue->id, 'assignment_role' => 'scorekeeper', 'is_active' => true, 'assigned_by' => $admin->id, 'assigned_at' => now()]);

        $this->actingAs($staff)->get(route('staff.matches.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('matches', 1)->where('matches.0.id', $assigned->id));
        $this->actingAs($staff)->get(route('staff.matches.show', $assigned))->assertOk();
        $this->actingAs($staff)->get(route('staff.matches.show', $blocked))->assertForbidden();
    }

    public function test_staff_without_assignment_sees_no_matches(): void
    {
        $this->seed();
        $staff = User::factory()->create(['role' => 'scorekeeper', 'account_status' => 'verified']);
        $this->actingAs($staff)->get(route('staff.matches.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('matches', 0));
    }

    public function test_suspended_staff_cannot_open_staff_portal(): void
    {
        $staff = User::factory()->create(['role' => 'scorekeeper', 'account_status' => 'suspended']);

        $this->actingAs($staff)->get(route('staff.matches.index'))->assertForbidden();
    }

    public function test_phase_five_operational_flow_from_agenda_to_revoked_staff_access(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $staff = User::factory()->create(['role' => 'scorekeeper', 'account_status' => 'verified']);
        $match = TournamentMatch::query()->where('status', 'scheduled')->with('tournamentEvent')->firstOrFail();
        $venue = Venue::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.agendas.store'), [
            'date' => '2026-09-10', 'title' => 'UAT Operasional Phase 5', 'type' => 'sport',
            'sport_id' => $match->tournamentEvent->sport_id, 'tournament_event_id' => $match->tournament_event_id,
            'venue_id' => $venue->id, 'start_time' => '13:00', 'end_time' => '15:00',
        ])->assertSessionHasNoErrors();
        $agenda = EventAgenda::query()->where('title', 'UAT Operasional Phase 5')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.agendas.publish', $agenda))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.matches.schedule', $match), ['event_agenda_id' => $agenda->id])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.assignments.store'), ['user_id' => $staff->id, 'sport_id' => $match->tournamentEvent->sport_id, 'venue_id' => $venue->id])->assertSessionHasNoErrors();

        $assignment = SportAssignment::query()->where('user_id', $staff->id)->firstOrFail();
        $this->actingAs($staff)->get(route('staff.matches.index'))->assertInertia(fn (Assert $page) => $page->has('matches', 1)->where('matches.0.id', $match->id));
        $this->actingAs($staff)->get(route('staff.matches.show', $match))->assertOk();

        $this->actingAs($admin)->post(route('admin.assignments.revoke', $assignment))->assertSessionHasNoErrors();
        $this->actingAs($staff)->get(route('staff.matches.index'))->assertInertia(fn (Assert $page) => $page->has('matches', 0));
        $this->actingAs($staff)->get(route('staff.matches.show', $match))->assertForbidden();
    }
}
