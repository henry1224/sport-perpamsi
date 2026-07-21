<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sport_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('sport_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('competition_type');
            $table->string('scoring_type');
            $table->boolean('bracket_enabled')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sport_id', 'code']);
            $table->index(['sport_id', 'is_active', 'sort_order']);
        });

        Schema::create('tournament_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('sport_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('sport_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('format');
            $table->string('status')->default('registration_draft');
            $table->unsignedSmallInteger('bracket_size')->nullable();
            $table->timestamp('seed_locked_at')->nullable();
            $table->timestamps();

            $table->index(['sport_id', 'sport_category_id', 'status']);
        });

        Schema::create('event_entries', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('tournament_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pdam_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('regency_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('seed_no')->nullable();
            $table->string('display_name');
            $table->string('athlete_1')->nullable();
            $table->string('athlete_2')->nullable();
            $table->string('team_name')->nullable();
            $table->string('verification_status')->default('verified');
            $table->timestamps();

            $table->unique(['tournament_event_id', 'pdam_id', 'seed_no']);
            $table->index(['tournament_event_id', 'seed_no']);
            $table->index(['tournament_event_id', 'pdam_id']);
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('tournament_event_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->unsignedSmallInteger('round_no');
            $table->string('round_name');
            $table->string('side')->default('left');
            $table->unsignedSmallInteger('slot_no');
            $table->foreignId('entry_a_id')->nullable()->constrained('event_entries')->nullOnDelete();
            $table->foreignId('entry_b_id')->nullable()->constrained('event_entries')->nullOnDelete();
            $table->foreignId('winner_entry_id')->nullable()->constrained('event_entries')->nullOnDelete();
            $table->foreignId('next_match_id')->nullable()->constrained('matches')->nullOnDelete();
            $table->string('next_slot', 1)->nullable();
            $table->string('score_summary')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();

            $table->unique(['tournament_event_id', 'code']);
            $table->index(['tournament_event_id', 'round_no', 'side', 'slot_no']);
            $table->index(['tournament_event_id', 'status']);
            $table->index('next_match_id');
        });

        Schema::create('match_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained()->cascadeOnDelete();
            $table->jsonb('score_payload');
            $table->foreignId('calculated_winner_entry_id')->nullable()->constrained('event_entries')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique('match_id');
        });

        Schema::create('score_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained()->cascadeOnDelete();
            $table->jsonb('before_json')->nullable();
            $table->jsonb('after_json');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['match_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_audits');
        Schema::dropIfExists('match_scores');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('event_entries');
        Schema::dropIfExists('tournament_events');
        Schema::dropIfExists('sport_categories');
    }
};
