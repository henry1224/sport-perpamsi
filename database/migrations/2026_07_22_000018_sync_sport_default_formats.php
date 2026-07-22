<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'volleyball' => 'group_then_knockout',
            'chess' => 'swiss',
            'badminton' => 'group_or_knockout',
            'table-tennis' => 'group_then_knockout',
            'tennis' => 'group_then_knockout',
            'golf' => 'score_ranking',
            'padel' => 'fun_games',
            'vocal' => 'single_performance_ranking',
            'mini-football' => 'group_then_knockout',
        ] as $code => $format) {
            DB::table('sports')->where('code', $code)->update(['default_format' => $format]);
        }
    }

    public function down(): void
    {
        // ponytail: koreksi data master resmi bersifat forward-only; restore backup jika rollback data diperlukan.
    }
};
