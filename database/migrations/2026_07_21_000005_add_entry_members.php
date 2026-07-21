<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sport_categories', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_members')->default(1)->after('competition_type');
            $table->unsignedSmallInteger('max_members')->default(1)->after('min_members');
        });

        DB::table('sport_categories')->where('competition_type', 'doubles')->update(['min_members' => 2, 'max_members' => 2]);
        DB::table('sport_categories')->where('competition_type', 'team')->update(['min_members' => 5, 'max_members' => 20]);

        Schema::table('event_entries', function (Blueprint $table) {
            $table->foreignId('pdam_id')->nullable()->change();
            $table->string('registration_key')->nullable()->unique()->after('regional_committee_id');
            $table->timestamp('submitted_at')->nullable()->after('verification_note');
            $table->foreignId('verified_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });

        Schema::create('entry_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_entry_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('normalized_name', 120);
            $table->string('member_type')->default('player');
            $table->string('gender', 20)->nullable();
            $table->unsignedSmallInteger('shirt_number')->nullable();
            $table->string('position', 80)->nullable();
            $table->timestamps();

            $table->unique(['event_entry_id', 'normalized_name']);
        });

        DB::table('event_entries')
            ->orderBy('id')
            ->each(function ($entry) {
                foreach (array_filter([$entry->athlete_1, $entry->athlete_2]) as $name) {
                    DB::table('entry_members')->insert([
                        'event_entry_id' => $entry->id,
                        'name' => trim($name),
                        'normalized_name' => mb_strtolower(trim($name)),
                        'member_type' => 'player',
                        'created_at' => $entry->created_at,
                        'updated_at' => $entry->updated_at,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_members');

        Schema::table('event_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['registration_key', 'submitted_at', 'verified_at']);
        });

        DB::table('event_entries')->whereNull('pdam_id')->delete();

        Schema::table('event_entries', function (Blueprint $table) {
            $table->foreignId('pdam_id')->nullable(false)->change();
        });

        Schema::table('sport_categories', function (Blueprint $table) {
            $table->dropColumn(['min_members', 'max_members']);
        });
    }
};
