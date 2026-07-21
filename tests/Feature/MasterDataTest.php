<?php

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_master_and_publish_versioned_regulation(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.master-data.sports.store'), [
            'code' => 'TEST', 'name' => 'Cabor Test', 'type' => 'sport', 'default_format' => 'knockout', 'score_template' => 'points',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $sport = Sport::query()->where('code', 'TEST')->firstOrFail();
        $category = ['sport_id' => $sport->id, 'code' => 'PUTRA', 'name' => 'Putra', 'competition_type' => 'team', 'min_members' => 5, 'max_members' => 12, 'scoring_type' => 'goals', 'bracket_enabled' => true, 'sort_order' => 1, 'is_active' => true];
        $this->actingAs($admin)->post(route('admin.master-data.categories.store'), $category)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.master-data.regulations.store'), ['sport_id' => $sport->id, 'title' => 'Regulasi Test', 'content' => 'Aturan versi pertama.'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.master-data.regulations.store'), ['sport_id' => $sport->id, 'title' => 'Regulasi Test Revisi', 'content' => 'Aturan versi kedua.'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sport_categories', ['sport_id' => $sport->id, 'code' => 'PUTRA']);
        $this->assertDatabaseHas('sport_regulations', ['sport_id' => $sport->id, 'version' => 2]);
        $this->assertDatabaseCount('master_data_audits', 4);
    }

    public function test_pd_admin_cannot_manage_master_data(): void
    {
        $this->seed();
        $pdAdmin = User::query()->where('role', 'pd_admin')->firstOrFail();
        $this->actingAs($pdAdmin)->get(route('admin.master-data.index'))->assertForbidden();
    }
}
