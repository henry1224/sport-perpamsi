<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SportMasterSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = array_map(fn ($row) => [
            'code' => $row['code'],
            'name' => $row['name'],
            'type' => $row['type'],
            'default_format' => $row['default_format'],
            'score_template' => $row['score_template'],
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->csvRows(base_path('data/seed/sports.csv')));

        DB::table('sports')->upsert($rows, ['code'], ['name', 'type', 'default_format', 'score_template', 'updated_at']);
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
