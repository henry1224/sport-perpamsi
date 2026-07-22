<?php

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function test_seed_matches_technical_guide_categories_and_formats(): void
    {
        $this->seed();

        $this->assertDatabaseHas('sports', ['code' => 'badminton', 'default_format' => 'group_or_knockout', 'score_template' => 'rally_point_21_best_of_3']);
        $this->assertDatabaseHas('sports', ['code' => 'chess', 'default_format' => 'swiss']);
        $this->assertDatabaseHas('sports', ['code' => 'padel', 'default_format' => 'fun_games']);
        $this->assertDatabaseHas('sport_categories', ['code' => 'veteran-u45', 'name' => 'Ganda Veteran U45', 'min_members' => 2, 'max_members' => 2]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'individual-fast', 'name' => 'Perorangan Cepat', 'min_members' => 2, 'max_members' => 2]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'mens-team-veteran-40', 'min_members' => 6, 'max_members' => 6]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'open', 'name' => 'Putra', 'min_members' => 15, 'max_members' => 15]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'mens-team', 'name' => 'Putra', 'min_members' => 12, 'max_members' => 12]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'executive', 'name' => 'Eksibisi Eksekutif', 'min_members' => 4, 'max_members' => 4]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'individual', 'name' => 'Individual', 'min_members' => 1, 'max_members' => null]);
    }

    public function test_public_sport_page_receives_categories_and_technical_guides(): void
    {
        $this->seed();

        $this->get(route('cabor'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Cabor')
                ->has('sportCategories')
                ->has('sportTechnicalGuides')
                ->where('sportTechnicalGuides', fn ($guides) => collect($guides)->contains(fn ($guide) => $guide['sport_code'] === 'golf' && $guide['source_slides'] === '21–22')));
    }

    public function test_admin_can_delete_only_unused_sport(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $unused = $this->sport('DELETE-ME');

        $this->actingAs($admin)->delete(route('admin.master-data.sports.destroy', $unused))->assertRedirect();
        $this->assertDatabaseMissing('sports', ['id' => $unused->id]);
        $this->assertDatabaseHas('master_data_audits', ['entity_type' => 'sport', 'entity_id' => $unused->id, 'action' => 'deleted']);

        foreach (['category', 'regulation', 'event'] as $relation) {
            $sport = $this->sport('BLOCK-'.$relation);
            $this->attachRelation($sport, $relation, $admin);

            $this->actingAs($admin)->delete(route('admin.master-data.sports.destroy', $sport))->assertSessionHas('error');
            $this->assertDatabaseHas('sports', ['id' => $sport->id]);
        }
    }

    private function sport(string $code): Sport
    {
        return Sport::query()->create([
            'code' => $code,
            'name' => $code,
            'type' => 'sport',
            'default_format' => 'knockout',
            'is_active' => true,
        ]);
    }

    private function attachRelation(Sport $sport, string $relation, User $admin): void
    {
        $now = now();
        if ($relation === 'category') {
            DB::table('sport_categories')->insert(['public_id' => (string) Str::uuid(), 'sport_id' => $sport->id, 'code' => 'TEST', 'name' => 'Test', 'competition_type' => 'single', 'min_members' => 1, 'max_members' => 1, 'scoring_type' => 'points', 'bracket_enabled' => true, 'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        } elseif ($relation === 'regulation') {
            DB::table('sport_regulations')->insert(['sport_id' => $sport->id, 'version' => 1, 'title' => 'Test', 'content' => 'Test', 'is_active' => true, 'created_by' => $admin->id, 'created_at' => $now, 'updated_at' => $now]);
        } else {
            DB::table('tournament_events')->insert(['public_id' => (string) Str::uuid(), 'sport_id' => $sport->id, 'code' => $sport->code, 'name' => $sport->name, 'format' => 'knockout', 'status' => 'registration_draft', 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
