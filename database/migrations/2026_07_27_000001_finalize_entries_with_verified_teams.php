<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('event_entries')
            ->where('verification_status', 'pending')
            ->whereNull('team_addition_opened_at')
            ->whereExists(fn ($query) => $query->selectRaw('1')->from('entry_teams')->whereColumn('entry_teams.event_entry_id', 'event_entries.id')->whereNull('cancelled_at'))
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('entry_teams')->whereColumn('entry_teams.event_entry_id', 'event_entries.id')->whereNull('cancelled_at')->where(fn ($query) => $query->whereNull('verification_status_override')->orWhere('verification_status_override', '!=', 'verified')))
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('entry_members')->join('entry_teams', 'entry_members.entry_team_id', '=', 'entry_teams.id')->whereColumn('entry_members.event_entry_id', 'event_entries.id')->whereNull('entry_teams.cancelled_at')->where('entry_members.member_type', 'player')->where('entry_members.verification_status', '!=', 'verified'))
            ->orderBy('id')
            ->eachById(function ($entry) {
                $team = DB::table('entry_teams')->where('event_entry_id', $entry->id)->whereNull('cancelled_at')->where('verification_status_override', 'verified')->orderByDesc('verified_at')->first(['verified_by', 'verified_at']);
                $verifiedAt = $team?->verified_at ?? now();
                DB::table('event_entries')->where('id', $entry->id)->update(['verification_status' => 'verified', 'verification_note' => null, 'verified_by' => $team?->verified_by, 'verified_at' => $verifiedAt, 'updated_at' => now()]);
                DB::table('entry_registration_audits')->insert([
                    'event_entry_id' => $entry->id,
                    'action' => 'verified_automatically_backfill',
                    'before_json' => json_encode(['status' => 'pending']),
                    'after_json' => json_encode(['status' => 'verified']),
                    'user_id' => $team?->verified_by,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // ponytail: status bisnis tidak dibalik karena verifikasi team tetap sah; koreksi memakai forward migration bila aturan berubah.
    }
};
