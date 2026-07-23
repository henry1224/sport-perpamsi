<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tournament_events')->whereNull('registration_published_at')->orderBy('id')->eachById(function ($event) {
            $sport = DB::table('sports')->find($event->sport_id);
            $category = DB::table('sport_categories')->find($event->sport_category_id);
            $regulation = DB::table('sport_regulations')->where('sport_id', $event->sport_id)->where('is_active', true)->orderByDesc('version')->first();

            if (! $sport || ! $category || ! $regulation) {
                return;
            }

            DB::table('tournament_events')->where('id', $event->id)->update([
                'code' => Str::slug($sport->code.'-'.$category->code),
                'name' => $sport->name.' - '.$category->name,
                'format' => $sport->default_format,
                'sport_regulation_id' => $regulation->id,
                'registration_rules' => json_encode([
                    'max_teams_per_pd' => $category->default_max_teams_per_pd,
                    'min_members_per_team' => $category->min_members,
                    'max_members_per_team' => $category->max_members,
                    'max_officials_per_pd' => $sport->default_max_officials_per_pd,
                    'official_roles' => json_decode($sport->official_roles ?: '[]', true),
                    'allow_member_cross_category' => (bool) $sport->allow_member_cross_category,
                    'max_categories_per_member' => $sport->max_categories_per_member,
                    'official_can_compete' => (bool) $sport->official_can_compete,
                ]),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void {}
};
