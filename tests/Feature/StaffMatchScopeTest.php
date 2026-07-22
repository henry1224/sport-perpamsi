<?php

namespace Tests\Feature;

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
}
