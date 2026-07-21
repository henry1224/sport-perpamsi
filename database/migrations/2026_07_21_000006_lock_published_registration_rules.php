<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tournament_events ALTER COLUMN status SET DEFAULT 'registration_draft'");
        }

        Schema::table('tournament_events', function (Blueprint $table) {
            $table->jsonb('registration_rules')->nullable()->after('status');
            $table->timestamp('registration_published_at')->nullable()->after('registration_rules');
            $table->foreignId('registration_published_by')->nullable()->after('registration_published_at')->constrained('users')->nullOnDelete();
            $table->timestamp('registration_open_at')->nullable()->after('registration_published_at');
            $table->timestamp('registration_close_at')->nullable()->after('registration_open_at');
            $table->index(['registration_published_at', 'status']);
        });

        DB::table('tournament_events')
            ->leftJoin('sport_categories', 'sport_categories.id', '=', 'tournament_events.sport_category_id')
            ->select(
                'tournament_events.id',
                'tournament_events.format',
                'sport_categories.name as category_name',
                'sport_categories.competition_type',
                'sport_categories.scoring_type',
                'sport_categories.min_members',
                'sport_categories.max_members',
            )
            ->orderBy('tournament_events.id')
            ->each(function ($event) {
                DB::table('tournament_events')->where('id', $event->id)->update([
                    'registration_rules' => json_encode([
                        'category_name' => $event->category_name,
                        'competition_type' => $event->competition_type,
                        'scoring_type' => $event->scoring_type,
                        'format' => $event->format,
                        'min_members' => $event->min_members ?? 1,
                        'max_members' => $event->max_members ?? 1,
                    ]),
                ]);
            });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE sport_categories ADD CONSTRAINT sport_categories_member_limits_check CHECK (min_members >= 1 AND max_members >= min_members)");
            DB::statement("ALTER TABLE tournament_events ADD CONSTRAINT tournament_events_registration_period_check CHECK (registration_close_at IS NULL OR registration_open_at IS NULL OR registration_close_at > registration_open_at)");
            DB::statement("ALTER TABLE tournament_events ADD CONSTRAINT tournament_events_published_rules_check CHECK (registration_published_at IS NULL OR registration_rules IS NOT NULL)");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE tournament_events DROP CONSTRAINT IF EXISTS tournament_events_published_rules_check');
            DB::statement('ALTER TABLE tournament_events DROP CONSTRAINT IF EXISTS tournament_events_registration_period_check');
            DB::statement('ALTER TABLE sport_categories DROP CONSTRAINT IF EXISTS sport_categories_member_limits_check');
        }

        Schema::table('tournament_events', function (Blueprint $table) {
            $table->dropIndex(['registration_published_at', 'status']);
            $table->dropConstrainedForeignId('registration_published_by');
            $table->dropColumn(['registration_rules', 'registration_published_at', 'registration_open_at', 'registration_close_at']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE tournament_events ALTER COLUMN status SET DEFAULT 'registration_open'");
        }
    }
};
