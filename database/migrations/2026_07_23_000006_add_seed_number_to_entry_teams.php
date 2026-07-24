<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entry_teams', function (Blueprint $table) {
            $table->unsignedSmallInteger('seed_no')->nullable()->after('label');
            $table->index('seed_no');
        });
    }

    public function down(): void
    {
        Schema::table('entry_teams', function (Blueprint $table) {
            $table->dropIndex(['seed_no']);
            $table->dropColumn('seed_no');
        });
    }
};
