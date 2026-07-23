<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entry_members', function (Blueprint $table) {
            $table->string('identity_type', 20)->nullable()->after('identity_hash');
            $table->string('identity_number', 50)->nullable()->after('identity_type');
            $table->jsonb('documents')->nullable()->after('position');
            $table->index(['identity_type', 'identity_number']);
        });
    }

    public function down(): void
    {
        Schema::table('entry_members', function (Blueprint $table) {
            $table->dropIndex(['identity_type', 'identity_number']);
            $table->dropColumn(['identity_type', 'identity_number', 'documents']);
        });
    }
};
