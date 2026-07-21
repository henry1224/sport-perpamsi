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
            ->update([
                'status' => 'registration_open',
                'seed_locked_at' => null,
                'updated_at' => now(),
            ]);
    }
}
