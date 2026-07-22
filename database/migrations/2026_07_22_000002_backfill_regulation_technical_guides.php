<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sports = DB::table('sports')->pluck('id', 'code');
        foreach (json_decode(file_get_contents(base_path('data/seed/sport_technical_guides.json')), true) as $guide) {
            if ($sportId = $sports[$guide['sport_code']] ?? null) {
                DB::table('sport_regulations')->where('sport_id', $sportId)->whereNull('technical_guide')->update(['technical_guide' => json_encode($guide)]);
            }
        }
    }

    public function down(): void {}
};
