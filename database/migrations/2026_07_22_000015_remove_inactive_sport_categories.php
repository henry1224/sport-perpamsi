<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $categoryIds = DB::table('sport_categories')->where('is_active', false)->pluck('id');
            $eventIds = DB::table('tournament_events')->whereIn('sport_category_id', $categoryIds)->pluck('id');

            DB::table('event_agendas')->whereIn('tournament_event_id', $eventIds)->delete();
            DB::table('tournament_events')->whereIn('id', $eventIds)->delete();
            DB::table('sport_categories')->whereIn('id', $categoryIds)->delete();
        });
    }

    public function down(): void {}
};
