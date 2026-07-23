<?php

namespace Tests\Feature;

use App\Models\Pdam;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdamMasterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_create_and_update_pdam_master(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $province = Province::query()->firstOrFail();

        $this->actingAs($admin)->get(route('admin.pdams.index', ['search' => 'PAM']))->assertOk();
        $this->actingAs($admin)->post(route('admin.pdams.store'), ['code' => 'PDAM-TEST', 'name' => 'Perumda Air Minum Test', 'province_id' => $province->id, 'city' => 'Kota Test'])->assertRedirect();
        $pdam = Pdam::query()->where('code', 'PDAM-TEST')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.pdams.update', $pdam), ['code' => 'PDAM-TEST', 'name' => 'Perumda Air Minum Diperbarui', 'province_id' => $province->id, 'city' => 'Kota Test'])->assertRedirect();

        $this->assertSame('Perumda Air Minum Diperbarui', $pdam->fresh()->name);
    }
}
