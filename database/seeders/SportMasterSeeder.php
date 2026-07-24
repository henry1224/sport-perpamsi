<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $sports = DB::table('sports')->get()->keyBy('code');
        foreach ($this->csvRows(base_path('data/seed/sport_categories.csv')) as $row) {
            $sport = $sports[$row['sport_code']] ?? null;
            if (! $sport) {
                continue;
            }

            DB::table('sport_categories')->upsert([[
                'public_id' => (string) Str::uuid(),
                'sport_id' => $sport->id,
                'code' => $row['code'],
                'name' => $row['name'],
                'competition_type' => $row['competition_type'],
                'min_members' => (int) $row['min_members'],
                'max_members' => $row['max_members'] === '' ? null : (int) $row['max_members'],
                'default_max_teams_per_pd' => ($row['sport_code'] === 'badminton' && in_array($row['code'], ['mens-double', 'womens-double', 'mixed-double', 'veteran-u45'], true)) || ($row['sport_code'] === 'chess' && $row['code'] === 'individual-fast') ? 2 : 1,
                'scoring_type' => $row['scoring_type'],
                'bracket_enabled' => (bool) $row['bracket_enabled'],
                'sort_order' => (int) $row['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]], ['sport_id', 'code'], ['name', 'competition_type', 'min_members', 'max_members', 'default_max_teams_per_pd', 'scoring_type', 'bracket_enabled', 'sort_order', 'is_active', 'updated_at']);
        }
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
