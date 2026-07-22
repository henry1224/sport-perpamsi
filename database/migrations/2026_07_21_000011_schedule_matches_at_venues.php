<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('event_agenda_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('venue_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamp('scheduled_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_agenda_id');
            $table->dropConstrainedForeignId('venue_id');
            $table->dropColumn('scheduled_at');
        });
    }
};
