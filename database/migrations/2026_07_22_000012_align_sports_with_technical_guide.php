<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $sports = DB::table('sports')->pluck('id', 'code');
        $now = now();

        foreach ($this->sports() as $code => $data) {
            DB::table('sports')->where('code', $code)->update($data + ['updated_at' => $now]);
        }

        foreach ($this->categories() as $row) {
            $sportId = $sports[$row['sport_code']] ?? null;
            if (! $sportId) {
                continue;
            }

            DB::table('sport_categories')->upsert([[
                'public_id' => (string) Str::uuid(),
                'sport_id' => $sportId,
                'code' => $row['code'],
                'name' => $row['name'],
                'competition_type' => $row['competition_type'],
                'scoring_type' => $row['scoring_type'],
                'min_members' => $row['min_members'],
                'max_members' => $row['max_members'],
                'bracket_enabled' => $row['bracket_enabled'],
                'sort_order' => $row['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]], ['sport_id', 'code'], ['name', 'competition_type', 'scoring_type', 'min_members', 'max_members', 'bracket_enabled', 'sort_order', 'is_active', 'updated_at']);
        }

        foreach ($this->officialCodes() as $sportCode => $codes) {
            if (! isset($sports[$sportCode])) {
                continue;
            }

            DB::table('sport_categories')->where('sport_id', $sports[$sportCode])->whereNotIn('code', $codes)->update(['is_active' => false, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        // ponytail: koreksi master resmi memakai forward-fix; tambah snapshot rollback jika rollback data menjadi kebutuhan operasional.
    }

    private function sports(): array
    {
        return [
            'badminton' => ['default_format' => 'group_or_knockout', 'score_template' => 'rally_point_21_best_of_3'],
            'chess' => ['default_format' => 'swiss', 'score_template' => 'swiss_match_points'],
            'tennis' => ['default_format' => 'group_then_knockout', 'score_template' => 'sets_games'],
            'table-tennis' => ['default_format' => 'group_then_knockout', 'score_template' => 'games_points'],
            'mini-football' => ['default_format' => 'group_then_knockout', 'score_template' => 'goals_2x25_minutes'],
            'volleyball' => ['default_format' => 'group_then_knockout', 'score_template' => 'set_points'],
            'vocal' => ['default_format' => 'single_performance_ranking', 'score_template' => 'judge_score'],
            'golf' => ['default_format' => 'score_ranking', 'score_template' => 'strokes'],
            'padel' => ['default_format' => 'fun_games', 'score_template' => 'fun_games'],
        ];
    }

    private function categories(): array
    {
        $csv = <<<'CSV'
sport_code,code,name,competition_type,scoring_type,min_members,max_members,bracket_enabled,sort_order
badminton,mens-single,Tunggal Putra,individual,rally_point_21_best_of_3,1,1,1,10
badminton,womens-single,Tunggal Putri,individual,rally_point_21_best_of_3,1,1,1,20
badminton,mens-double,Ganda Putra,doubles,rally_point_21_best_of_3,2,2,1,30
badminton,womens-double,Ganda Putri,doubles,rally_point_21_best_of_3,2,2,1,40
badminton,mixed-double,Ganda Campuran,doubles,rally_point_21_best_of_3,2,2,1,50
badminton,veteran-u45,Ganda Veteran U45,doubles,rally_point_21_best_of_3,2,2,1,60
chess,individual-fast,Perorangan Cepat,individual,swiss_rapid_10_minutes,2,2,0,10
chess,team-fast,Beregu Cepat,team,swiss_rapid_15_minutes,3,3,0,20
tennis,mens-team-open,Beregu Putra Bebas Usia,team,sets_games,6,6,1,10
tennis,mens-team-veteran-40,Beregu Putra Veteran KU 40 Tahun,team,sets_games,6,6,1,20
table-tennis,mens-single,Tunggal Putra,individual,games_points,1,1,1,10
table-tennis,womens-single,Tunggal Putri,individual,games_points,1,1,1,20
table-tennis,mens-double,Ganda Putra,doubles,games_points,2,2,1,30
table-tennis,womens-double,Ganda Putri,doubles,games_points,2,2,1,40
table-tennis,mixed-double,Ganda Campuran,doubles,games_points,2,2,1,50
mini-football,open,Putra,team,goals_2x25_minutes,15,15,1,10
volleyball,mens-team,Putra,team,set_points,12,12,1,10
vocal,mens-solo,Solo Putra,individual,judge_score,1,1,0,10
vocal,womens-solo,Solo Putri,individual,judge_score,1,1,0,20
golf,individual,Individual,individual,strokes,1,,0,10
padel,executive,Eksibisi Eksekutif,team,fun_games,4,4,0,10
padel,achievement,Eksibisi Prestasi,team,fun_games,4,4,0,20
CSV;
        $file = fopen('php://memory', 'r+');
        fwrite($file, $csv);
        rewind($file);
        $headers = fgetcsv($file, escape: '');
        $rows = [];

        while (($data = fgetcsv($file, escape: '')) !== false) {
            $row = array_combine($headers, $data);
            $row['min_members'] = (int) $row['min_members'];
            $row['max_members'] = $row['max_members'] === '' ? null : (int) $row['max_members'];
            $row['bracket_enabled'] = (bool) $row['bracket_enabled'];
            $row['sort_order'] = (int) $row['sort_order'];
            $rows[] = $row;
        }

        fclose($file);

        return $rows;
    }

    private function officialCodes(): array
    {
        return collect($this->categories())->groupBy('sport_code')->map(fn ($rows) => $rows->pluck('code')->all())->all();
    }
};
