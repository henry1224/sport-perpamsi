<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sports = DB::table('sports')->pluck('id', 'code');

        foreach (json_decode(file_get_contents(base_path('data/seed/sport_technical_guides.json')), true) as $guide) {
            $sportId = $sports[$guide['sport_code']] ?? null;
            $current = $sportId ? DB::table('sport_regulations')->where('sport_id', $sportId)->latest('version')->first() : null;
            if (! $current) continue;

            $content = collect([
                ...array_map(fn ($item) => 'Sistem: '.$item, $guide['system'] ?? []),
                ...array_map(fn ($item) => 'Syarat: '.$item, $guide['eligibility'] ?? []),
                isset($guide['official_note']) ? 'Kontingen: '.$guide['official_note'] : null,
                isset($guide['fee_note']) ? 'Biaya: '.$guide['fee_note'] : null,
                isset($guide['source_note']) ? 'Catatan: '.$guide['source_note'] : null,
                'Sumber: slide '.$guide['source_slides'],
            ])->filter()->implode("\n");

            if ($current->content === $content) continue;

            DB::table('sport_regulations')->where('sport_id', $sportId)->update(['is_active' => false, 'updated_at' => now()]);
            $id = DB::table('sport_regulations')->insertGetId([
                'sport_id' => $sportId,
                'version' => $current->version + 1,
                'title' => 'Panduan Teknis PORPAMNAS IX',
                'content' => $content,
                'document_url' => $current->document_url,
                'technical_guide' => json_encode($guide),
                'is_active' => true,
                'created_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('master_data_audits')->insert(['entity_type' => 'sport_regulation', 'entity_id' => $id, 'action' => 'created', 'before_json' => null, 'after_json' => json_encode(['sport_id' => $sportId, 'version' => $current->version + 1, 'source' => 'participant_requirements_sync']), 'user_id' => null, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // ponytail: versi regulasi bersifat append-only; rollback data memakai versi lama yang tetap tersimpan.
    }
};
