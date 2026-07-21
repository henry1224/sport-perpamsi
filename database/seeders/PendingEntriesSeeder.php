<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PendingEntriesSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil beberapa entry verified terakhir dari beberapa event, flip ke pending sebagai demo panel approval.
        // Hindari entry yang sudah dipakai di matches (biar bracket seeder tetap valid) — pilih seed_no tinggi.
        $eventIds = DB::table('tournament_events')->orderBy('id')->limit(6)->pluck('id');

        foreach ($eventIds as $eventId) {
            $ids = DB::table('event_entries')
                ->where('tournament_event_id', $eventId)
                ->where('verification_status', 'verified')
                ->orderByDesc('seed_no')
                ->limit(2)
                ->pluck('id');

            if ($ids->isEmpty()) continue;

            DB::table('event_entries')
                ->whereIn('id', $ids)
                ->update([
                    'verification_status' => 'pending',
                    'updated_at' => now(),
                ]);
        }
    }
}
