<?php

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\SportRegulation;
use App\Models\TournamentEvent;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\SportRegulationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_chess_categories_separate_team_limit_from_members_per_team(): void
    {
        $this->seed();

        $individual = TournamentEvent::query()->where('code', 'chess-individual-fast')->with('category')->firstOrFail();
        $team = TournamentEvent::query()->where('code', 'chess-team-fast')->with('category')->firstOrFail();

        $this->assertSame(1, $individual->category->min_members);
        $this->assertSame(1, $individual->category->max_members);
        $this->assertSame(2, $individual->registration_rules['max_teams_per_pd']);
        $this->assertSame(1, $individual->registration_rules['min_members_per_team']);
        $this->assertSame(1, $individual->registration_rules['max_members_per_team']);
        $this->assertSame(1, $team->registration_rules['max_teams_per_pd']);
        $this->assertSame(3, $team->registration_rules['min_members_per_team']);
        $this->assertSame(3, $team->registration_rules['max_members_per_team']);
    }

    public function test_badminton_registration_defaults_follow_agreed_rules(): void
    {
        $this->seed();

        $sport = Sport::query()->where('code', 'badminton')->with('categories')->firstOrFail();
        $this->assertSame(2, $sport->default_max_officials_per_pd);
        $this->assertSame(['team_manager', 'coach'], $sport->official_roles);
        $this->assertTrue($sport->allow_member_cross_category);
        $this->assertNull($sport->max_categories_per_member);
        $this->assertFalse($sport->official_can_compete);
        $this->assertSame(1, $sport->categories->firstWhere('code', 'mens-single')->default_max_teams_per_pd);
        $this->assertSame(2, $sport->categories->firstWhere('code', 'mens-double')->default_max_teams_per_pd);
    }

    public function test_admin_can_manage_master_and_publish_versioned_regulation(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.master-data.sports.store'), [
            'code' => 'TEST', 'name' => 'Cabor Test', 'type' => 'sport', 'default_format' => 'knockout', 'score_template' => 'points',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $sport = Sport::query()->where('code', 'TEST')->firstOrFail();
        $this->assertFalse($sport->allow_member_cross_category);
        $this->assertSame(0, $sport->max_categories_per_member);
        $venue = Venue::query()->where('is_active', true)->firstOrFail();
        $category = ['sport_id' => $sport->id, 'code' => 'PUTRA', 'name' => 'Putra', 'competition_type' => 'team', 'min_members' => 5, 'max_members' => 12, 'scoring_type' => 'goals', 'bracket_enabled' => true, 'sort_order' => 1, 'is_active' => true];
        $this->actingAs($admin)->post(route('admin.master-data.categories.store'), $category)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.master-data.regulations.store'), ['sport_id' => $sport->id, 'title' => 'Regulasi Test', 'content' => 'Aturan versi pertama.', 'venue_id' => $venue->id])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.master-data.regulations.store'), ['sport_id' => $sport->id, 'title' => 'Regulasi Test Revisi', 'content' => 'Aturan versi kedua.'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sport_categories', ['sport_id' => $sport->id, 'code' => 'PUTRA', 'scoring_type' => 'points']);
        $this->assertDatabaseHas('sport_regulations', ['sport_id' => $sport->id, 'version' => 2]);
        $this->assertSame($venue->id, SportRegulation::query()->where('sport_id', $sport->id)->where('version', 1)->firstOrFail()->technical_guide['venue_id']);
        $this->assertDatabaseCount('master_data_audits', 4);
    }

    public function test_pd_admin_cannot_manage_master_data(): void
    {
        $this->seed();
        $pdAdmin = User::query()->where('role', 'pd_admin')->firstOrFail();
        $this->actingAs($pdAdmin)->get(route('admin.master-data.index'))->assertForbidden();
    }

    public function test_category_menu_receives_sports_and_category_relations_from_database(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $sport = Sport::query()->whereHas('categories')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.master-data.index', ['tab' => 'categories']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/MasterData')
                ->where('initialTab', 'categories')
                ->where('sports', fn ($sports) => collect($sports)->contains('id', $sport->id))
                ->where('categories', fn ($categories) => collect($categories)->contains(fn ($category) => $category['sport_id'] === $sport->id && $category['sport']['id'] === $sport->id)));
    }

    public function test_category_type_uses_individual_database_value(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $sport = $this->sport('INDIVIDUAL-TYPE');
        $category = ['sport_id' => $sport->id, 'code' => 'INDIVIDUAL', 'name' => 'Individual', 'competition_type' => 'individual', 'min_members' => 1, 'max_members' => 1, 'bracket_enabled' => true, 'sort_order' => 0, 'is_active' => true];

        $this->actingAs($admin)->post(route('admin.master-data.categories.store'), $category)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sport_categories', ['sport_id' => $sport->id, 'code' => 'INDIVIDUAL', 'competition_type' => 'individual']);
        $this->actingAs($admin)->post(route('admin.master-data.categories.store'), array_merge($category, ['code' => 'LEGACY', 'competition_type' => 'single']))->assertSessionHasErrors('competition_type');
    }

    public function test_seed_matches_technical_guide_categories_and_formats(): void
    {
        $this->seed();

        foreach ([
            'volleyball' => 'group_then_knockout', 'chess' => 'swiss', 'badminton' => 'group_or_knockout',
            'table-tennis' => 'group_then_knockout', 'tennis' => 'group_then_knockout', 'golf' => 'score_ranking',
            'padel' => 'fun_games', 'vocal' => 'single_performance_ranking', 'mini-football' => 'group_then_knockout',
        ] as $code => $format) {
            $this->assertDatabaseHas('sports', ['code' => $code, 'default_format' => $format]);
        }
        $this->assertDatabaseHas('sports', ['code' => 'padel', 'type' => 'exhibition']);
        $this->assertDatabaseHas('sports', ['code' => 'golf', 'type' => 'exhibition']);
        $this->assertDatabaseHas('sports', ['code' => 'vocal', 'type' => 'exhibition']);
        $this->assertDatabaseHas('sport_categories', ['code' => 'veteran-u45', 'name' => 'Ganda Veteran U45', 'min_members' => 2, 'max_members' => 2]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'individual-fast', 'name' => 'Perorangan Cepat', 'min_members' => 1, 'max_members' => 1]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'mens-team-veteran-40', 'min_members' => 6, 'max_members' => 6]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'open', 'name' => 'Putra', 'min_members' => 15, 'max_members' => 15]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'mens-team', 'name' => 'Putra', 'min_members' => 12, 'max_members' => 12]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'executive', 'name' => 'Eksibisi Eksekutif', 'min_members' => 4, 'max_members' => 4]);
        $this->assertDatabaseHas('sport_categories', ['code' => 'individual', 'name' => 'Individual', 'min_members' => 1, 'max_members' => null]);
        $this->assertSame(0, DB::table('sport_categories')->where('is_active', false)->count());
        $this->assertDatabaseHas('sport_regulations', ['version' => 1, 'title' => 'Panduan Teknis PORPAMNAS IX']);
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
                ->has('sportRegulations')
                ->where('assets.mascots.golf', '/assets/brand/mascots/Golf.png')
                ->where('assets.mascots.padel', '/assets/brand/mascots/Padel.png')
                ->where('assets.stop', '/assets/brand/mascots/STOP.png')
                ->where('sportTechnicalGuides', fn ($guides) => collect($guides)->contains(fn ($guide) => $guide['sport_code'] === 'golf' && $guide['source_slides'] === '21–22')
                    && collect($guides)->contains(fn ($guide) => $guide['sport_code'] === 'badminton'
                        && in_array('Karyawan tetap BUMD Air Minum anggota PERPAMSI dengan masa kerja minimal 1 tahun', $guide['eligibility'], true)
                        && isset($guide['source_note']))));
    }

    public function test_technical_guide_seed_does_not_overwrite_existing_regulation(): void
    {
        $this->seed();
        $regulation = DB::table('sport_regulations')->first();
        DB::table('sport_regulations')->where('id', $regulation->id)->update(['content' => 'Revisi Admin']);

        $this->seed(SportRegulationSeeder::class);

        $this->assertSame('Revisi Admin', DB::table('sport_regulations')->where('id', $regulation->id)->value('content'));
        $this->assertSame(9, DB::table('sport_regulations')->count());
    }

    public function test_admin_can_delete_only_unused_sport(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $unused = $this->sport('DELETE-ME');

        $this->actingAs($admin)->delete(route('admin.master-data.sports.destroy', $unused))->assertSessionHas('error');
        $this->assertDatabaseHas('sports', ['id' => $unused->id]);
        $unused->update(['is_active' => false]);
        $this->actingAs($admin)->delete(route('admin.master-data.sports.destroy', $unused))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('sports', ['id' => $unused->id]);
        $this->assertDatabaseHas('master_data_audits', ['entity_type' => 'sport', 'entity_id' => $unused->id, 'action' => 'deleted']);

        foreach (['category', 'regulation', 'event'] as $relation) {
            $sport = $this->sport('BLOCK-'.$relation);
            $this->attachRelation($sport, $relation, $admin);

            $this->actingAs($admin)->delete(route('admin.master-data.sports.destroy', $sport))->assertSessionHas('error');
            $this->assertDatabaseHas('sports', ['id' => $sport->id]);
        }
    }

    public function test_admin_can_delete_only_inactive_unused_category(): void
    {
        $this->seed();
        $admin = User::query()->where('role', 'super_admin')->firstOrFail();
        $sport = $this->sport('CATEGORY-DELETE');
        $categoryId = DB::table('sport_categories')->insertGetId(['public_id' => (string) Str::uuid(), 'sport_id' => $sport->id, 'code' => 'DELETE', 'name' => 'Delete', 'competition_type' => 'individual', 'min_members' => 1, 'max_members' => 1, 'scoring_type' => 'points', 'bracket_enabled' => true, 'sort_order' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($admin)->delete(route('admin.master-data.categories.destroy', $categoryId))->assertSessionHas('error');
        DB::table('sport_categories')->where('id', $categoryId)->update(['is_active' => false]);
        $this->actingAs($admin)->delete(route('admin.master-data.categories.destroy', $categoryId))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('sport_categories', ['id' => $categoryId]);
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
            DB::table('sport_categories')->insert(['public_id' => (string) Str::uuid(), 'sport_id' => $sport->id, 'code' => 'TEST', 'name' => 'Test', 'competition_type' => 'individual', 'min_members' => 1, 'max_members' => 1, 'scoring_type' => 'points', 'bracket_enabled' => true, 'sort_order' => 0, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        } elseif ($relation === 'regulation') {
            DB::table('sport_regulations')->insert(['sport_id' => $sport->id, 'version' => 1, 'title' => 'Test', 'content' => 'Test', 'is_active' => true, 'created_by' => $admin->id, 'created_at' => $now, 'updated_at' => $now]);
        } else {
            DB::table('tournament_events')->insert(['public_id' => (string) Str::uuid(), 'sport_id' => $sport->id, 'code' => $sport->code, 'name' => $sport->name, 'format' => 'knockout', 'status' => 'registration_draft', 'created_at' => $now, 'updated_at' => $now]);
        }
    }
}
