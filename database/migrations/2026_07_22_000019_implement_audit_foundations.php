<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_teams', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('event_entry_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('team_no');
            $table->string('label');
            $table->string('verification_status_override')->nullable();
            $table->text('verification_note')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->timestampTz('cancelled_at')->nullable();
            $table->timestampsTz();

            $table->unique(['event_entry_id', 'team_no']);
            $table->index(['event_entry_id', 'verification_status_override']);
        });

        Schema::create('entry_team_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_team_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->jsonb('before_json')->nullable();
            $table->jsonb('after_json');
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestampsTz();
        });

        Schema::table('entry_members', function (Blueprint $table) {
            $table->foreignId('entry_team_id')->nullable()->after('event_entry_id')->constrained('entry_teams')->cascadeOnDelete();
            $table->string('identity_hash', 64)->nullable()->after('normalized_name');
            $table->index(['entry_team_id', 'member_type']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('team_a_id')->nullable()->after('entry_a_id')->constrained('entry_teams')->nullOnDelete();
            $table->foreignId('team_b_id')->nullable()->after('entry_b_id')->constrained('entry_teams')->nullOnDelete();
            $table->foreignId('winner_team_id')->nullable()->after('winner_entry_id')->constrained('entry_teams')->nullOnDelete();
            $table->index(['tournament_event_id', 'scheduled_at']);
        });

        Schema::table('event_agendas', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('type');
            $table->uuid('public_id')->nullable()->unique()->after('id');
        });

        foreach (['regional_committees', 'sports', 'venues'] as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->uuid('public_id')->nullable()->unique()->after('id'));
        }

        DB::table('event_entries')->orderBy('id')->each(function ($entry): void {
            $teamId = DB::table('entry_teams')->insertGetId([
                'public_id' => (string) Str::uuid(),
                'event_entry_id' => $entry->id,
                'team_no' => 1,
                'label' => $entry->display_name.' #1',
                'created_at' => $entry->created_at,
                'updated_at' => $entry->updated_at,
            ]);
            DB::table('entry_members')->where('event_entry_id', $entry->id)->update(['entry_team_id' => $teamId]);
            DB::table('matches')->where('entry_a_id', $entry->id)->update(['team_a_id' => $teamId]);
            DB::table('matches')->where('entry_b_id', $entry->id)->update(['team_b_id' => $teamId]);
            DB::table('matches')->where('winner_entry_id', $entry->id)->update(['winner_team_id' => $teamId]);
        });

        DB::table('entry_members')->whereNull('identity_hash')->orderBy('id')->each(fn ($member) => DB::table('entry_members')->where('id', $member->id)->update([
            'identity_hash' => hash('sha256', mb_strtolower(trim($member->name))),
        ]));

        foreach (['regional_committees', 'sports', 'venues', 'event_agendas'] as $table) {
            DB::table($table)->whereNull('public_id')->orderBy('id')->each(fn ($row) => DB::table($table)->where('id', $row->id)->update(['public_id' => (string) Str::uuid()]));
        }

        DB::table('event_agendas')->update(['status' => DB::raw("CASE WHEN published_at IS NULL THEN 'draft' ELSE 'published' END")]);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE entry_teams ADD CONSTRAINT entry_teams_team_no_check CHECK (team_no > 0)');
            DB::statement("ALTER TABLE entry_teams ADD CONSTRAINT entry_teams_override_check CHECK (verification_status_override IS NULL OR verification_status_override IN ('draft','pending','revision_required','verified','rejected','cancelled'))");
            DB::statement("ALTER TABLE matches ADD CONSTRAINT matches_status_check CHECK (status IN ('scheduled','live','paused','postponed','walkover','final','verified','disputed','cancelled'))");
            DB::statement("ALTER TABLE event_agendas ADD CONSTRAINT event_agendas_status_check CHECK (status IN ('draft','published','cancelled'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE event_agendas DROP CONSTRAINT IF EXISTS event_agendas_status_check');
            DB::statement('ALTER TABLE matches DROP CONSTRAINT IF EXISTS matches_status_check');
        }
        Schema::table('event_agendas', fn (Blueprint $table) => $table->dropColumn(['status', 'public_id']));
        foreach (['regional_committees', 'sports', 'venues'] as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn('public_id'));
        }
        Schema::table('matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_a_id');
            $table->dropConstrainedForeignId('team_b_id');
            $table->dropConstrainedForeignId('winner_team_id');
            $table->dropIndex(['tournament_event_id', 'scheduled_at']);
        });
        Schema::table('entry_members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entry_team_id');
            $table->dropColumn('identity_hash');
        });
        Schema::dropIfExists('entry_team_audits');
        Schema::dropIfExists('entry_teams');
    }
};
