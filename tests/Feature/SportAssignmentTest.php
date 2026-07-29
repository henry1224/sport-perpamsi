<?php

namespace Tests\Feature;

use App\Models\EventAgenda;
use App\Models\SportAssignment;
use App\Models\TournamentMatch;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SportAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_staff_and_assign_venue(): void
    {
        $this->seed();

        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $match = TournamentMatch::query()->with('tournamentEvent')->firstOrFail();
        $venue = Venue::query()->where('is_active', true)->firstOrFail();
        $agenda = EventAgenda::query()->firstOrFail();
        $match->update(['event_agenda_id' => $agenda->id, 'venue_id' => $venue->id, 'scheduled_at' => now()]);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Panitia Venue',
            'email' => 'panitia@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'scorekeeper',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $staff = User::query()->where('email', 'panitia@example.test')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.assignments.store'), [
            'user_id' => $staff->id,
            'sport_id' => $match->tournamentEvent->sport_id,
            'venue_id' => $venue->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $assignment = SportAssignment::query()->firstOrFail();
        $this->assertTrue($assignment->is_active);
        $this->assertSame($admin->id, $assignment->assigned_by);
        $this->assertDatabaseHas('sport_assignment_audits', ['sport_assignment_id' => $assignment->id, 'action' => 'assigned']);
    }

    public function test_assignment_requires_scheduled_sport_venue_pair_and_active_staff(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $staff = User::factory()->create(['role' => 'scorekeeper', 'account_status' => 'verified']);
        $inactiveStaff = User::factory()->create(['role' => 'scorekeeper', 'account_status' => 'suspended']);
        $match = TournamentMatch::query()->with('tournamentEvent')->firstOrFail();
        $venue = Venue::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.assignments.store'), [
            'user_id' => $staff->id, 'sport_id' => $match->tournamentEvent->sport_id, 'venue_id' => $venue->id,
        ])->assertUnprocessable();

        $this->actingAs($admin)->post(route('admin.assignments.store'), [
            'user_id' => $inactiveStaff->id, 'sport_id' => $match->tournamentEvent->sport_id, 'venue_id' => $venue->id,
        ])->assertSessionHasErrors('user_id');

        $this->actingAs($admin)->get(route('admin.assignments.index'))->assertInertia(fn ($page) => $page
            ->where('staff', fn ($items) => ! collect($items)->contains('id', $inactiveStaff->id)));
    }

    public function test_pd_admin_cannot_manage_staff_assignments(): void
    {
        $this->seed();

        $pdAdmin = User::query()->where('role', 'pd_admin')->firstOrFail();

        $this->actingAs($pdAdmin)->get(route('admin.assignments.index'))->assertForbidden();
        $this->actingAs($pdAdmin)->get(route('admin.users.index'))->assertForbidden();
    }
}
