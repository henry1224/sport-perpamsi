<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sports', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('score_template');
        });

        Schema::create('sport_regulations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sport_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('version');
            $table->string('title');
            $table->text('content');
            $table->string('document_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['sport_id', 'version']);
        });

        Schema::create('master_data_audits', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('action');
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_data_audits');
        Schema::dropIfExists('sport_regulations');
        Schema::table('sports', fn (Blueprint $table) => $table->dropColumn('is_active'));
    }
};
