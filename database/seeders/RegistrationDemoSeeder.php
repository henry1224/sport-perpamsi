<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegistrationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $eventIds = DB::table('tournament_events')->orderByDesc('id')->limit(6)->pluck('id');

        DB::table('tournament_events')
            ->whereIn('id', $eventIds)
            ->whereNull('registration_published_at')
            ->update([
                'status' => 'registration_open',
                'registration_published_at' => now(),
                'registration_open_at' => now(),
                'registration_close_at' => now()->addMonth(),
                'seed_locked_at' => null,
                'updated_at' => now(),
            ]);
    }
}
