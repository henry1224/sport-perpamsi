<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            IndonesiaRegionSeeder::class,
            SportMasterSeeder::class,
            VenueSeeder::class,
            EventAgendaSeeder::class,
            PdamSeeder::class,
            TournamentBracketDemoSeeder::class,
        ]);
    }
}
