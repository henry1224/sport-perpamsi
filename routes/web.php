<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

if (! function_exists('csvRows')) {
function csvRows(string $path): array {
    $file = fopen(base_path($path), 'r');
    $headers = fgetcsv($file);
    $rows = [];
    while (($data = fgetcsv($file)) !== false) {
        $rows[] = array_combine($headers, $data);
    }
    fclose($file);
    return $rows;
}
}

if (! function_exists('porpamnasData')) {
function porpamnasData(): array {
    $venues = collect(csvRows('data/seed/venues.csv'))->keyBy('code');
    $sports = collect(csvRows('data/seed/sports.csv'))->keyBy('code');
    $provincesSeed = collect(csvRows('data/seed/indonesia_provinces.csv'))->keyBy('code');
    $pdams = collect(csvRows('data/seed/pdams.csv'))->map(fn ($row) => [
        ...$row,
        'province' => $provincesSeed[$row['province_code']]['name'] ?? $row['province_code'],
    ])->values();
    $agenda = collect(csvRows('data/seed/event_agenda.csv'))->map(fn ($item) => [
        ...$item,
        'sport' => $item['sport_code'] ? ($sports[$item['sport_code']]['name'] ?? null) : null,
        'venue' => $venues[$item['venue_code']]['name'] ?? $item['venue_code'],
        'venue_address' => $venues[$item['venue_code']]['address'] ?? '',
    ])->values();

    $results = [
        ['sport' => 'Mini Football', 'team_a' => 'PT Manuntung Balikpapan', 'team_b' => 'PTK Samarinda', 'score' => '3–1', 'status' => 'final', 'venue' => 'Borneo Anfield', 'time' => '07 Okt 16:00'],
        ['sport' => 'Voli Putra', 'team_a' => 'PT Taman Bontang', 'team_b' => 'PT Mahakam Kukar', 'score' => '2–3', 'status' => 'final', 'venue' => 'GOR Balikpapan', 'time' => '07 Okt 19:00'],
        ['sport' => 'Bulu Tangkis', 'team_a' => 'Batiwakkal Berau', 'team_b' => 'PDAM Makassar', 'score' => '1–2', 'status' => 'final', 'venue' => 'BTS', 'time' => '08 Okt 09:00'],
        ['sport' => 'Tenis Meja', 'team_a' => 'PDAM Surabaya', 'team_b' => 'PDAM Bandung', 'score' => '3–0', 'status' => 'live', 'venue' => 'BSCC Dome', 'time' => 'Sekarang'],
        ['sport' => 'Catur', 'team_a' => 'PDAM Jakarta', 'team_b' => 'PDAM Semarang', 'score' => '½–½', 'status' => 'scheduled', 'venue' => 'Hotel Platinum', 'time' => '09 Okt 10:00'],
        ['sport' => 'Golf', 'team_a' => 'PDAM Denpasar', 'team_b' => 'PDAM Medan', 'score' => '—', 'status' => 'scheduled', 'venue' => 'Balikpapan Golf', 'time' => '10 Okt 06:30'],
    ];

    $provinces = [
        ['name' => 'Kalimantan Timur', 'gold' => 8, 'silver' => 5, 'bronze' => 3],
        ['name' => 'Sulawesi Selatan', 'gold' => 5, 'silver' => 3, 'bronze' => 4],
        ['name' => 'Jawa Timur', 'gold' => 4, 'silver' => 4, 'bronze' => 2],
        ['name' => 'Jawa Barat', 'gold' => 3, 'silver' => 6, 'bronze' => 5],
        ['name' => 'DKI Jakarta', 'gold' => 3, 'silver' => 2, 'bronze' => 4],
        ['name' => 'Bali', 'gold' => 2, 'silver' => 3, 'bronze' => 3],
    ];

    $storedResults = storage_path('app/porpamnas-results.json');
    if (file_exists($storedResults)) {
        $results = json_decode(file_get_contents($storedResults), true) ?: $results;
    }

    return [
        'agenda' => $agenda,
        'sports' => $sports->values(),
        'venues' => $venues->values(),
        'pdams' => $pdams,
        'provinces' => $provincesSeed->values(),
        'results' => $results,
        'provinceRankings' => $provinces,
        'assets' => [
            'porpamnas' => '/assets/brand/logos/porpamnas/porpamnas-ix.png',
            'ptmb' => '/assets/brand/logos/ptmb/logo-ptmb-landscape.png',
            'beru' => '/assets/brand/mascots/beru.png',
            'ganga' => '/assets/brand/mascots/ganga.png',
            'mascots' => [
                'badminton' => '/assets/brand/mascots/bulu-tangkis.png',
                'chess' => '/assets/brand/mascots/catur.png',
                'mini-football' => '/assets/brand/mascots/mini-football.png',
                'table-tennis' => '/assets/brand/mascots/tenis-meja.png',
                'tennis' => '/assets/brand/mascots/tenis-lapangan.png',
                'golf' => '/assets/brand/mascots/beru.png',
                'padel' => '/assets/brand/mascots/ganga.png',
                'vocal' => '/assets/brand/mascots/vokal.png',
                'volleyball' => '/assets/brand/mascots/voli.png',
            ],
        ],
    ];
}
}

if (! function_exists('porpamnasAudit')) {
function porpamnasAudit(): array {
    $path = storage_path('app/porpamnas-score-audit.json');
    return file_exists($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
}
}

Route::get('/', fn () => Inertia::render('Home', porpamnasData()));
Route::get('/agenda', fn () => Inertia::render('Agenda', porpamnasData()));
Route::get('/hasil', fn () => Inertia::render('Hasil', porpamnasData()));
Route::get('/cabor', fn () => Inertia::render('Cabor', porpamnasData()));
Route::get('/bracket', fn () => Inertia::render('Bracket', porpamnasData()));
Route::get('/ranking', fn () => Inertia::render('Ranking', porpamnasData()));
Route::get('/venue', fn () => Inertia::render('Venue', porpamnasData()));
Route::get('/peserta', fn () => Inertia::render('Peserta', porpamnasData()));
Route::get('/admin/skor', fn () => Inertia::render('AdminScores', [
    'matches' => collect(porpamnasData()['results'])->values()->map(fn ($row, $i) => ['id' => 'R'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), ...$row]),
    'audit' => porpamnasAudit(),
]));
Route::post('/admin/skor', function (Request $request) {
    $data = $request->validate([
        'id' => ['required', 'string'],
        'sport' => ['required', 'string'],
        'team_a' => ['required', 'string'],
        'team_b' => ['required', 'string'],
        'score' => ['required', 'string', 'max:20'],
        'status' => ['required', 'in:scheduled,live,final,verified,disputed'],
        'venue' => ['nullable', 'string'],
        'time' => ['nullable', 'string'],
    ]);

    $results = collect(porpamnasData()['results'])->values()->map(fn ($row, $i) => ['id' => 'R'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), ...$row]);
    $before = $results->firstWhere('id', $data['id']);
    $updated = $results->map(fn ($row) => $row['id'] === $data['id'] ? $data : $row)->map(fn ($row) => collect($row)->except('id')->all())->values()->all();

    file_put_contents(storage_path('app/porpamnas-results.json'), json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    file_put_contents(storage_path('app/porpamnas-score-audit.json'), json_encode(array_values([['match_id' => $data['id'], 'before' => $before, 'after' => $data, 'at' => now()->toDateTimeString()], ...porpamnasAudit()]), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return back();
});
