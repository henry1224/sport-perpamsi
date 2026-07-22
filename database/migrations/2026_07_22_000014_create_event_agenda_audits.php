<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_agenda_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_agenda_id')->constrained()->cascadeOnDelete();
            $table->string('action', 30);
            $table->string('reason')->nullable();
            $table->jsonb('before_json')->nullable();
            $table->jsonb('after_json');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_agenda_audits');
    }
};
