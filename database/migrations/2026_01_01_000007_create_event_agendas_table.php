<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_agendas', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('day');
            $table->string('title');
            $table->string('type');
            $table->foreignId('sport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('venue_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->string('time_note')->nullable();
            $table->timestamps();

            $table->unique(['date', 'title', 'venue_id', 'start_time']);
            $table->index(['date', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_agendas');
    }
};
