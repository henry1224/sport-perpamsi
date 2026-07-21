<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_registration_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_entry_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['event_entry_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_registration_audits');
    }
};
