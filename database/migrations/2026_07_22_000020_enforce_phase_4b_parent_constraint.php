<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_entries', fn (Blueprint $table) => $table->unique(['tournament_event_id', 'regional_committee_id'], 'event_entries_event_committee_unique'));
    }

    public function down(): void
    {
        Schema::table('event_entries', fn (Blueprint $table) => $table->dropUnique('event_entries_event_committee_unique'));
    }
};
