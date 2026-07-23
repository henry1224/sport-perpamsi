<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:cleanup-tournament {--force : Hapus data demo setelah backup}', function () {
    if (app()->environment('production')) {
        return $this->error('Command cleanup demo tidak boleh dijalankan di production.');
    }

    $matches = DB::table('matches')
        ->whereNull('event_agenda_id')
        ->whereNull('venue_id')
        ->whereNull('scheduled_at');
    $matchIds = (clone $matches)->pluck('id');
    $eventIds = (clone $matches)->distinct()->pluck('tournament_event_id')
        ->merge(DB::table('tournament_events')->where('status', 'bracket_locked')->whereNull('registration_published_at')->pluck('id'))
        ->unique();
    $counts = [
        'matches' => $matchIds->count(),
        'match_scores' => DB::table('match_scores')->whereIn('match_id', $matchIds)->count(),
        'score_audits' => DB::table('score_audits')->whereIn('match_id', $matchIds)->count(),
    ];

    $this->table(['Data', 'Jumlah'], collect($counts)->map(fn ($count, $name) => [$name, $count]));

    if (! $this->option('force')) {
        return $this->warn('Dry-run saja. Jalankan kembali dengan --force setelah backup database.');
    }

    DB::transaction(function () use ($matchIds, $eventIds) {
        DB::table('matches')->whereIn('id', $matchIds)->delete();
        DB::table('tournament_events')->whereIn('id', $eventIds)
            ->where('status', 'bracket_locked')->whereNull('registration_published_at')
            ->update(['status' => 'registration_draft', 'bracket_size' => null, 'seed_locked_at' => null, 'updated_at' => now()]);
    });

    $this->info('Data pertandingan demo berhasil dibersihkan.');
})->purpose('Preview atau hapus match demo tanpa menyentuh master dan registrasi');
