<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = array_map(fn ($row) => [
            'code' => $row['code'],
            'name' => $row['name'],
            'address' => $row['address'],
            'city' => $row['city'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->csvRows(base_path('data/seed/venues.csv')));

        DB::table('venues')->upsert($rows, ['code'], ['name', 'address', 'city', 'updated_at']);
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
