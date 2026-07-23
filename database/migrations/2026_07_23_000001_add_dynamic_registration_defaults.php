<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->unsignedSmallInteger('default_max_officials_per_pd')->default(0);
            $table->jsonb('official_roles')->nullable();
            $table->boolean('allow_member_cross_category')->default(false);
            $table->unsignedSmallInteger('max_categories_per_member')->nullable();
            $table->boolean('official_can_compete')->default(false);
        });
        Schema::table('sport_categories', fn (Blueprint $table) => $table->unsignedSmallInteger('default_max_teams_per_pd')->default(1));

        DB::table('sports')->where('code', 'badminton')->update([
            'default_max_officials_per_pd' => 2,
            'official_roles' => json_encode(['team_manager', 'coach']),
            'allow_member_cross_category' => true,
        ]);
        DB::table('sports')->where('code', 'chess')->update(['default_max_officials_per_pd' => 1, 'official_roles' => json_encode(['official'])]);
        foreach (['tennis' => 2, 'table-tennis' => 1, 'mini-football' => 4, 'volleyball' => 2] as $code => $officials) {
            DB::table('sports')->where('code', $code)->update(['default_max_officials_per_pd' => $officials, 'official_roles' => json_encode(['official'])]);
        }

        DB::table('sport_categories')->whereIn('code', ['mens-double', 'womens-double', 'mixed-double', 'veteran-u45'])
            ->where('sport_id', DB::table('sports')->where('code', 'badminton')->value('id'))->update(['default_max_teams_per_pd' => 2]);
        DB::table('sport_categories')->where('code', 'individual-fast')
            ->where('sport_id', DB::table('sports')->where('code', 'chess')->value('id'))->update(['default_max_teams_per_pd' => 2]);

        DB::table('tournament_events')->whereNull('registration_published_at')->orderBy('id')->eachById(function ($event) {
            $sport = DB::table('sports')->find($event->sport_id);
            $category = DB::table('sport_categories')->find($event->sport_category_id);
            if (! $sport || ! $category) return;

            $rules = json_decode($event->registration_rules ?: '{}', true) ?: [];
            DB::table('tournament_events')->where('id', $event->id)->update(['registration_rules' => json_encode($rules + [
                'max_teams_per_pd' => $category->default_max_teams_per_pd,
                'min_members_per_team' => $category->min_members,
                'max_members_per_team' => $category->max_members,
                'max_officials_per_pd' => $sport->default_max_officials_per_pd,
                'official_roles' => json_decode($sport->official_roles ?: '[]', true) ?: [],
                'allow_member_cross_category' => $sport->allow_member_cross_category,
                'max_categories_per_member' => $sport->max_categories_per_member,
                'official_can_compete' => $sport->official_can_compete,
            ])]);
        });
    }

    public function down(): void
    {
        Schema::table('sport_categories', fn (Blueprint $table) => $table->dropColumn('default_max_teams_per_pd'));
        Schema::table('sports', fn (Blueprint $table) => $table->dropColumn(['default_max_officials_per_pd', 'official_roles', 'allow_member_cross_category', 'max_categories_per_member', 'official_can_compete']));
    }
};
