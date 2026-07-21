<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status')->default('verified')->after('role');
            $table->string('phone')->nullable()->after('email');
            $table->string('position')->nullable()->after('phone');
        });

        Schema::create('committee_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regional_committee_id')->constrained()->restrictOnDelete();
            $table->foreignId('active_committee_id')->nullable()->unique()->constrained('regional_committees')->restrictOnDelete();
            $table->foreignId('user_id')->unique()->constrained()->restrictOnDelete();
            $table->string('status')->default('pending');
            $table->string('review_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('committee_application_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        foreach (DB::table('regional_committees')->get() as $committee) {
            $province = DB::table('provinces')->find($committee->province_id);
            if (! $province) continue;

            $name = 'PD PERPAMSI '.$province->name;
            DB::table('regional_committees')->where('id', $committee->id)->update(['name' => $name]);
            DB::table('event_entries')->where('regional_committee_id', $committee->id)->update(['display_name' => $name]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('committee_application_audits');
        Schema::dropIfExists('committee_applications');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_status', 'phone', 'position']);
        });
    }
};
