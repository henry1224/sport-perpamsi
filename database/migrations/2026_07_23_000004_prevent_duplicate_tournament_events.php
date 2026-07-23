<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_events', fn (Blueprint $table) => $table->unique(['sport_id', 'sport_category_id'], 'tournament_events_sport_category_unique'));
    }

    public function down(): void
    {
        Schema::table('tournament_events', fn (Blueprint $table) => $table->dropUnique('tournament_events_sport_category_unique'));
    }
};
