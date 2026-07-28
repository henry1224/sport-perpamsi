<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_filter_and_create_internal_users(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.users.index', ['status' => 'active']))
            ->assertInertia(fn ($page) => $page
                ->where('filters.status', 'active')
                ->where('filters.tab', 'internal')
                ->where('users.data', fn ($users) => collect($users)->every(fn ($user) => $user['role'] !== 'pd_admin' && $user['account_status'] === 'verified')));

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Panitia Baru',
            'email' => 'panitia-baru@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'sport_coordinator',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'panitia-baru@example.test', 'role' => 'sport_coordinator', 'account_status' => 'verified']);
    }

    public function test_pd_accounts_cannot_be_created_from_internal_user_menu(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'PD Bypass',
            'email' => 'pd-bypass@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'pd_admin',
        ])->assertSessionHasErrors('role');
    }

    public function test_internal_user_password_must_be_strong_and_confirmed(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $payload = ['name' => 'Panitia Aman', 'email' => 'panitia-aman@example.test', 'role' => 'scorekeeper'];

        $this->actingAs($admin)->post(route('admin.users.store'), $payload + [
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('password');

        $this->actingAs($admin)->post(route('admin.users.store'), $payload + [
            'password' => 'Password123!',
            'password_confirmation' => 'Berbeda123!',
        ])->assertSessionHasErrors('password');
    }

    public function test_admin_can_update_and_suspend_internal_user_but_not_pd_user(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $staff = User::factory()->create(['role' => 'scorekeeper', 'account_status' => 'verified']);
        $pdAdmin = User::query()->where('role', 'pd_admin')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.users.update', $staff), [
            'name' => 'Scorekeeper Diperbarui',
            'email' => $staff->email,
            'password' => '',
            'password_confirmation' => '',
            'role' => 'scorekeeper',
            'account_status' => 'suspended',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $staff->id, 'name' => 'Scorekeeper Diperbarui', 'account_status' => 'suspended']);

        $this->actingAs($admin)->put(route('admin.users.update', $pdAdmin), [
            'name' => $pdAdmin->name,
            'email' => $pdAdmin->email,
            'role' => 'scorekeeper',
            'account_status' => 'verified',
        ])->assertNotFound();
    }

    public function test_admin_cannot_suspend_or_demote_own_account(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'scorekeeper',
            'account_status' => 'suspended',
        ])->assertUnprocessable();
    }

    public function test_suspended_internal_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'nonaktif@example.test',
            'password' => 'password123',
            'role' => 'scorekeeper',
            'account_status' => 'suspended',
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_event_admin_can_view_users_and_only_update_own_account(): void
    {
        $eventAdmin = User::factory()->create(['role' => 'admin_event', 'account_status' => 'verified']);
        $other = User::factory()->create(['role' => 'scorekeeper', 'account_status' => 'verified']);

        $this->actingAs($eventAdmin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($eventAdmin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($eventAdmin)->post(route('admin.users.store'), [
            'name' => 'Tidak Boleh', 'email' => 'tidak-boleh@example.test', 'password' => 'Password123!',
            'password_confirmation' => 'Password123!', 'role' => 'scorekeeper',
        ])->assertForbidden();
        $this->actingAs($eventAdmin)->put(route('admin.users.update', $other), [
            'name' => $other->name, 'email' => $other->email, 'role' => $other->role, 'account_status' => 'verified',
        ])->assertForbidden();
        $this->actingAs($eventAdmin)->put(route('admin.users.update', $eventAdmin), [
            'name' => 'Admin Event Diperbarui', 'email' => $eventAdmin->email, 'role' => 'admin_event', 'account_status' => 'verified',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $eventAdmin->id, 'name' => 'Admin Event Diperbarui']);
    }

    public function test_only_super_admin_can_delete_other_internal_user(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'account_status' => 'verified']);
        $eventAdmin = User::factory()->create(['role' => 'admin_event', 'account_status' => 'verified']);
        $staff = User::factory()->create(['role' => 'scorekeeper', 'account_status' => 'verified']);

        $this->actingAs($eventAdmin)->delete(route('admin.users.destroy', $staff))->assertForbidden();
        $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $staff))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
        $this->actingAs($superAdmin)->delete(route('admin.users.destroy', $superAdmin))->assertUnprocessable();
    }

    public function test_pd_users_are_excluded_from_internal_user_list(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertInertia(fn ($page) => $page->where('users.data', fn ($users) => collect($users)->doesntContain(fn ($user) => in_array($user['role'], ['pd_admin', 'super_admin'], true))));

        $this->actingAs($admin)->get(route('admin.users.index', ['tab' => 'regional']))
            ->assertInertia(fn ($page) => $page
                ->where('filters.tab', 'regional')
                ->where('users.data', fn ($users) => collect($users)->every(fn ($user) => $user['role'] === 'pd_admin')));
    }

    public function test_only_super_admin_can_toggle_internal_user_status(): void
    {
        $this->seed();
        $superAdmin = User::query()->where('role', 'super_admin')->firstOrFail();
        $eventAdmin = User::factory()->create(['role' => 'admin_event', 'account_status' => 'verified']);
        $staff = User::factory()->create(['role' => 'scorekeeper', 'account_status' => 'verified']);
        $pdAdmin = User::query()->where('role', 'pd_admin')->firstOrFail();

        $this->actingAs($eventAdmin)->post(route('admin.users.toggle-status', $staff))->assertForbidden();
        $this->actingAs($superAdmin)->post(route('admin.users.toggle-status', $staff))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'account_status' => 'suspended']);
        $this->actingAs($superAdmin)->post(route('admin.users.toggle-status', $staff))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'account_status' => 'verified']);
        $this->actingAs($superAdmin)->post(route('admin.users.toggle-status', $superAdmin))->assertUnprocessable();
        $this->actingAs($superAdmin)->post(route('admin.users.toggle-status', $pdAdmin))->assertNotFound();
    }
}
