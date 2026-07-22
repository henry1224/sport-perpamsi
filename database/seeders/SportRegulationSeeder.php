<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SportRegulationSeeder extends Seeder
{
    public function run(): void
    {
        $sports = DB::table('sports')->pluck('id', 'code');

        foreach (json_decode(file_get_contents(base_path('data/seed/sport_technical_guides.json')), true) as $guide) {
            $sportId = $sports[$guide['sport_code']] ?? null;
            if (! $sportId || DB::table('sport_regulations')->where('sport_id', $sportId)->exists()) {
                continue;
            }

            DB::table('sport_regulations')->insert([
                'sport_id' => $sportId,
                'version' => 1,
                'title' => 'Panduan Teknis PORPAMNAS IX',
                'content' => $this->content($guide),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function content(array $guide): string
    {
        return collect([
            ...array_map(fn ($item) => 'Sistem: '.$item, $guide['system'] ?? []),
            ...array_map(fn ($item) => 'Syarat: '.$item, $guide['eligibility'] ?? []),
            isset($guide['official_note']) ? 'Kontingen: '.$guide['official_note'] : null,
            isset($guide['fee_note']) ? 'Biaya: '.$guide['fee_note'] : null,
            'Sumber: slide '.$guide['source_slides'],
        ])->filter()->implode("\n");
    }
}
