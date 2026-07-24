<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entry_members', function (Blueprint $table) {
            $table->string('verification_status')->default('pending')->after('documents');
            $table->text('verification_note')->nullable()->after('verification_status');
            $table->foreignId('verified_by')->nullable()->after('verification_note')->constrained('users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable()->after('verified_by');
            $table->index(['member_type', 'verification_status']);
        });

        Schema::create('entry_member_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_member_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->jsonb('before_json');
            $table->jsonb('after_json');
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestampsTz();
        });

        DB::table('event_entries')->where('verification_status', 'verified')->orderBy('id')->eachById(fn ($entry) => DB::table('entry_members')->where('event_entry_id', $entry->id)->update([
            'verification_status' => 'verified',
            'verified_at' => $entry->verified_at,
            'verified_by' => $entry->verified_by,
        ]));

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE entry_members ADD CONSTRAINT entry_members_verification_status_check CHECK (verification_status IN ('pending','revision_required','verified','rejected'))");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE entry_members DROP CONSTRAINT IF EXISTS entry_members_verification_status_check');
        }
        Schema::dropIfExists('entry_member_audits');
        Schema::table('entry_members', function (Blueprint $table) {
            $table->dropIndex(['member_type', 'verification_status']);
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['verification_status', 'verification_note', 'verified_at']);
        });
    }
};
