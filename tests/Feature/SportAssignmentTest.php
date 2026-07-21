<?php

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\SportAssignment;
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
        $sport = Sport::query()->firstOrFail();
        $venue = Venue::query()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.assignments.users.store'), [
            'name' => 'Panitia Venue',
            'email' => 'panitia@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'scorekeeper',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $staff = User::query()->where('email', 'panitia@example.test')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.assignments.store'), [
            'user_id' => $staff->id,
            'sport_id' => $sport->id,
            'venue_id' => $venue->id,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $assignment = SportAssignment::query()->firstOrFail();
        $this->assertTrue($assignment->is_active);
        $this->assertSame($admin->id, $assignment->assigned_by);
        $this->assertDatabaseHas('sport_assignment_audits', ['sport_assignment_id' => $assignment->id, 'action' => 'assigned']);
    }

    public function test_pd_admin_cannot_manage_staff_assignments(): void
    {
        $this->seed();

        $pdAdmin = User::query()->where('role', 'pd_admin')->firstOrFail();

        $this->actingAs($pdAdmin)->get(route('admin.assignments.index'))->assertForbidden();
    }
}
