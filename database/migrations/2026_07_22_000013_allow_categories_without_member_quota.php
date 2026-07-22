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
            DB::statement('ALTER TABLE sport_categories DROP CONSTRAINT IF EXISTS sport_categories_member_limits_check');
        }

        Schema::table('sport_categories', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_members')->nullable()->change();
        });

        DB::table('sport_categories')
            ->where('code', 'individual')
            ->whereIn('sport_id', DB::table('sports')->where('code', 'golf')->select('id'))
            ->update(['max_members' => null, 'updated_at' => now()]);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sport_categories ADD CONSTRAINT sport_categories_member_limits_check CHECK (min_members >= 1 AND (max_members IS NULL OR max_members >= min_members))');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sport_categories DROP CONSTRAINT IF EXISTS sport_categories_member_limits_check');
        }

        DB::table('sport_categories')->whereNull('max_members')->update(['max_members' => DB::raw('min_members')]);

        Schema::table('sport_categories', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_members')->nullable(false)->change();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sport_categories ADD CONSTRAINT sport_categories_member_limits_check CHECK (min_members >= 1 AND max_members >= min_members)');
        }
    }
};
