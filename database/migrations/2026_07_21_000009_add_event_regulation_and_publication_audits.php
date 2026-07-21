<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournament_events', function (Blueprint $table) {
            $table->foreignId('sport_regulation_id')->nullable()->after('sport_category_id')->constrained()->restrictOnDelete();
        });

        Schema::create('event_publication_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_event_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['tournament_event_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_publication_audits');
        Schema::table('tournament_events', fn (Blueprint $table) => $table->dropConstrainedForeignId('sport_regulation_id'));
    }
};
