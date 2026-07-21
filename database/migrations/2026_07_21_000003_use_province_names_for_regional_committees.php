<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('regional_committees')->get() as $committee) {
            $province = DB::table('provinces')->find($committee->province_id);

            if ($province) {
                DB::table('regional_committees')->where('id', $committee->id)->update(['name' => $province->name]);
                DB::table('event_entries')->where('regional_committee_id', $committee->id)->update(['display_name' => $province->name]);
            }
        }
    }

    public function down(): void
    {
        // Nama provinsi adalah identitas kanonik dan tidak dikembalikan ke format lama.
    }
};
