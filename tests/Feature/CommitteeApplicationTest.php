<?php

namespace Tests\Feature;

use App\Models\CommitteeApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommitteeApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_pending_account_and_blocks_pd_portal(): void
    {
        $this->seed();
        $committee = DB::table('regional_committees')
            ->whereNotIn('id', DB::table('users')->whereNotNull('regional_committee_id')->pluck('regional_committee_id'))
            ->first();

        $response = $this->post('/register', [
            'regional_committee_id' => $committee->id,
            'name' => 'Pengurus Uji',
            'position' => 'Sekretaris',
            'phone' => '081234567890',
            'email' => 'pengurus-uji@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('registration.status'));
        $user = User::query()->where('email', 'pengurus-uji@example.test')->firstOrFail();

        $this->assertSame('pending', $user->account_status);
        $this->assertDatabaseHas('committee_applications', [
            'regional_committee_id' => $committee->id,
            'active_committee_id' => $committee->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $this->actingAs($user)->get(route('pd.dashboard'))->assertForbidden();
    }

    public function test_one_committee_cannot_have_two_applications(): void
    {
        $this->seed();
        $committee = DB::table('regional_committees')->whereNotIn('id', DB::table('users')->whereNotNull('regional_committee_id')->pluck('regional_committee_id'))->first();

        $payload = [
            'regional_committee_id' => $committee->id,
            'name' => 'Pengurus Pertama',
            'position' => 'Ketua',
            'phone' => '081111111111',
            'email' => 'pertama@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->post('/register', $payload)->assertRedirect(route('registration.status'));
        $this->post('/logout');
        $this->post('/register', [...$payload, 'email' => 'kedua@example.test'])->assertSessionHasErrors('regional_committee_id');
        $this->assertSame(1, CommitteeApplication::query()->where('regional_committee_id', $committee->id)->count());
    }

    public function test_admin_verification_activates_account_and_records_audit(): void
    {
        $this->seed();
        $committee = DB::table('regional_committees')->whereNotIn('id', DB::table('users')->whereNotNull('regional_committee_id')->pluck('regional_committee_id'))->first();
        $user = User::query()->create([
            'name' => 'Pengurus Verifikasi',
            'email' => 'verifikasi@example.test',
            'phone' => '082222222222',
            'position' => 'Ketua',
            'password' => 'password123',
            'role' => 'pd_admin',
            'account_status' => 'pending',
            'regional_committee_id' => $committee->id,
        ]);
        $application = CommitteeApplication::query()->create([
            'regional_committee_id' => $committee->id,
            'active_committee_id' => $committee->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.committee-applications.verify', $application))
            ->assertRedirect();

        $this->assertSame('verified', $user->fresh()->account_status);
        $this->assertDatabaseHas('committee_application_audits', [
            'committee_application_id' => $application->id,
            'actor_id' => $admin->id,
            'from_status' => 'pending',
            'to_status' => 'verified',
        ]);
        $this->actingAs($user->fresh())->get(route('pd.dashboard'))->assertOk();
    }

    public function test_revision_can_be_resubmitted_and_rejection_releases_committee(): void
    {
        $this->seed();
        $committee = DB::table('regional_committees')->whereNotIn('id', DB::table('users')->whereNotNull('regional_committee_id')->pluck('regional_committee_id'))->first();
        $user = User::query()->create([
            'name' => 'Pengurus Revisi', 'email' => 'revisi@example.test', 'phone' => '083333333333',
            'position' => 'Sekretaris', 'password' => 'password123', 'role' => 'pd_admin',
            'account_status' => 'revision_required', 'regional_committee_id' => $committee->id,
        ]);
        $application = CommitteeApplication::query()->create([
            'regional_committee_id' => $committee->id,
            'active_committee_id' => $committee->id,
            'user_id' => $user->id,
            'status' => 'revision_required',
            'review_note' => 'Perbaiki jabatan.',
        ]);

        $this->actingAs($user)->put(route('registration.update'), [
            'name' => 'Pengurus Revisi',
            'position' => 'Ketua',
            'phone' => '083333333333',
        ])->assertRedirect();
        $this->assertSame('pending', $application->fresh()->status);

        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.committee-applications.reject', $application), ['note' => 'Mandat tidak sesuai'])->assertRedirect();
        $this->assertNull($application->fresh()->active_committee_id);
        $this->post('/logout');
        $this->get(route('register'))->assertOk();
    }
}
