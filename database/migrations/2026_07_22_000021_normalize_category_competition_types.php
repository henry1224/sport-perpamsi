<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sport_categories')->where('competition_type', 'single')->update(['competition_type' => 'individual']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE tournament_events SET registration_rules = jsonb_set(registration_rules::jsonb, '{competition_type}', '\"individual\"') WHERE registration_rules->>'competition_type' = 'single'");
            DB::statement("ALTER TABLE sport_categories ADD CONSTRAINT sport_categories_competition_type_check CHECK (competition_type IN ('individual', 'doubles', 'team'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sport_categories DROP CONSTRAINT IF EXISTS sport_categories_competition_type_check');
        }
    }
};
