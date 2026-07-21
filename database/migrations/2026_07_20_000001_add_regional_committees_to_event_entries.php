<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regional_committees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->unique()->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();
        $provinces = DB::table('provinces')->orderBy('id')->get();

        foreach ($provinces as $province) {
            DB::table('regional_committees')->insert([
                'province_id' => $province->id,
                'name' => 'PD PERPAMSI '.mb_strtoupper($province->name),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('event_entries', function (Blueprint $table) {
            $table->foreignId('regional_committee_id')->nullable()->after('pdam_id')->constrained()->nullOnDelete();
            $table->index(['tournament_event_id', 'regional_committee_id']);
        });

        foreach (DB::table('regional_committees')->get() as $committee) {
            DB::table('event_entries')
                ->where('province_id', $committee->province_id)
                ->update(['regional_committee_id' => $committee->id]);
        }
    }

    public function down(): void
    {
        Schema::table('event_entries', function (Blueprint $table) {
            $table->dropIndex(['tournament_event_id', 'regional_committee_id']);
            $table->dropConstrainedForeignId('regional_committee_id');
        });

        Schema::dropIfExists('regional_committees');
    }
};
