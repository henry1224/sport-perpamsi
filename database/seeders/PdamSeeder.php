<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PdamSeeder extends Seeder
{
    public function run(): void
    {
        $regencies = DB::table('regencies')->pluck('id', 'code');
        $provinces = DB::table('provinces')->pluck('id', 'code');
        $now = now();

        $rows = array_map(function ($row) use ($regencies, $provinces, $now) {
            return [
                'public_id' => (string) Str::uuid(),
                'code' => $row['code'],
                'name' => $row['name'],
                'slug' => $row['slug'],
                'province_id' => $provinces[$row['province_code']] ?? null,
                'regency_id' => $regencies[$row['regency_code']] ?? null,
                'city' => $row['city'] ?: null,
                'address' => $row['address'] ?: null,
                'website' => $row['website'] ?: null,
                'logo_path' => $row['logo_path'] ?: null,
                'contact_name' => $row['contact_name'] ?: null,
                'contact_phone' => $row['contact_phone'] ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $this->csvRows(base_path('data/seed/pdams.csv')));

        // Chunk to keep memory + SQL packet size sane (514 rows total).
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('pdams')->upsert(
                $chunk,
                ['code'],
                ['name', 'slug', 'province_id', 'regency_id', 'city', 'address', 'website', 'logo_path', 'contact_name', 'contact_phone', 'updated_at']
            );
        }
    }

    private function csvRows(string $path): array
    {
        $file = fopen($path, 'r');
        $headers = fgetcsv($file, escape: '');
        $rows = [];
        while (($data = fgetcsv($file, escape: '')) !== false) {
            $rows[] = array_combine($headers, $data);
        }
        fclose($file);
        return $rows;
    }
}
