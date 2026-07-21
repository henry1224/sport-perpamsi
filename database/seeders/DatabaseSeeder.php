<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            IndonesiaRegionSeeder::class,
            RegionalCommitteeSeeder::class,
            SportMasterSeeder::class,
            VenueSeeder::class,
            EventAgendaSeeder::class,
            PdamSeeder::class,
            TournamentDomainSeeder::class,
            TournamentBracketDemoSeeder::class,
            MedalDemoSeeder::class,
            RegistrationDemoSeeder::class,
            UserSeeder::class,
            PendingEntriesSeeder::class,
        ]);
    }
}
