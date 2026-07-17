<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IndonesiaRegionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedProvinces();
            $this->seedRegencies();
        });
    }

    private function seedProvinces(): void
    {
        $now = now();
        $rows = array_map(fn ($row) => [
            'code' => $row['code'],
            'name' => $row['name'],
            'slug' => Str::slug($row['name']),
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->csvRows(base_path('data/seed/indonesia_provinces.csv')));

        DB::table('provinces')->upsert($rows, ['code'], ['name', 'slug', 'updated_at']);
    }

    private function seedRegencies(): void
    {
        $now = now();
        $provinceIds = DB::table('provinces')->pluck('id', 'code');
        $rows = array_map(fn ($row) => [
            'code' => $row['code'],
            'province_id' => $provinceIds[$row['province_code']],
            'name' => $row['name'],
            'slug' => Str::slug($row['name']),
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->csvRows(base_path('data/seed/indonesia_regencies.csv')));

        DB::table('regencies')->upsert($rows, ['code'], ['province_id', 'name', 'slug', 'updated_at']);
    }

    private function csvRows(string $path): array
    {
        $file = fopen($path, 'r');
        $headers = fgetcsv($file);
        $rows = [];

        while (($data = fgetcsv($file)) !== false) {
            $rows[] = array_combine($headers, $data);
        }

        fclose($file);

        return $rows;
    }
}
