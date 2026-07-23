<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entry_members', fn (Blueprint $table) => $table->foreignId('pdam_id')->nullable()->after('entry_team_id')->constrained()->restrictOnDelete());
    }

    public function down(): void
    {
        Schema::table('entry_members', fn (Blueprint $table) => $table->dropConstrainedForeignId('pdam_id'));
    }
};
