<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionalCommitteeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach (DB::table('provinces')->orderBy('id')->get() as $province) {
            DB::table('regional_committees')->upsert([[
                'province_id' => $province->id,
                'name' => 'PD PERPAMSI '.mb_strtoupper($province->name),
                'created_at' => $now,
                'updated_at' => $now,
            ]], ['province_id'], ['name', 'updated_at']);
        }
    }
}
