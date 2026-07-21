<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->text('facilities')->nullable();
            $table->string('map_url')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('is_active')->default(true)->index();
        });

        Schema::table('event_agendas', function (Blueprint $table) {
            $table->foreignId('tournament_event_id')->nullable()->after('sport_id')->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamp('published_at')->nullable()->index();
        });
        DB::table('event_agendas')->whereNull('published_at')->update(['published_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('event_agendas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tournament_event_id');
            $table->dropColumn(['description', 'published_at']);
        });
        Schema::table('venues', fn (Blueprint $table) => $table->dropColumn(['facilities', 'map_url', 'contact_name', 'contact_phone', 'is_active']));
    }
};
