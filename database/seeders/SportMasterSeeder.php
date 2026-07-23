<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SportMasterSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $officials = ['badminton' => 2, 'chess' => 1, 'tennis' => 2, 'table-tennis' => 1, 'mini-football' => 4, 'volleyball' => 2];
        $rows = array_map(fn ($row) => [
            'code' => $row['code'],
            'name' => $row['name'],
            'type' => $row['type'],
            'default_format' => $row['default_format'],
            'score_template' => $row['score_template'],
            'default_max_officials_per_pd' => $officials[$row['code']] ?? 0,
            'official_roles' => json_encode($row['code'] === 'badminton' ? ['team_manager', 'coach'] : (isset($officials[$row['code']]) ? ['official'] : [])),
            'allow_member_cross_category' => $row['code'] === 'badminton',
            'max_categories_per_member' => null,
            'official_can_compete' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->csvRows(base_path('data/seed/sports.csv')));

        DB::table('sports')->upsert($rows, ['code'], ['name', 'type', 'default_format', 'score_template', 'default_max_officials_per_pd', 'official_roles', 'allow_member_cross_category', 'max_categories_per_member', 'official_can_compete', 'updated_at']);
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
