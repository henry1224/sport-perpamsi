<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $csvRows = function (string $path): array {
        $file = fopen(base_path($path), 'r');
        $headers = fgetcsv($file);
        $rows = [];

        while (($data = fgetcsv($file)) !== false) {
            $rows[] = array_combine($headers, $data);
        }

        fclose($file);

        return $rows;
    };

    $venues = collect($csvRows('data/seed/venues.csv'))->keyBy('code');
    $sports = collect($csvRows('data/seed/sports.csv'))->keyBy('code');
    $agenda = collect($csvRows('data/seed/event_agenda.csv'))->map(fn ($item) => [
        ...$item,
        'sport' => $item['sport_code'] ? $sports[$item['sport_code']]['name'] : null,
        'venue' => $venues[$item['venue_code']]['name'],
        'venue_address' => $venues[$item['venue_code']]['address'],
    ])->values();

    return Inertia::render('Home', [
        'agenda' => $agenda,
        'sports' => $sports->values(),
        'results' => [
            ['sport' => 'Mini Football', 'team_a' => 'Perumda Tirta Manuntung Balikpapan', 'team_b' => 'Perumdam Tirta Kencana Samarinda', 'score' => '3–1', 'status' => 'Final'],
            ['sport' => 'Voli Putra', 'team_a' => 'Perumda Tirta Taman Bontang', 'team_b' => 'Perumda Tirta Mahakam Kutai Kartanegara', 'score' => '2–3', 'status' => 'Final'],
            ['sport' => 'Bulu Tangkis', 'team_a' => 'Perumda Batiwakkal Berau', 'team_b' => 'Perumda Air Minum Kota Makassar', 'score' => '1–2', 'status' => 'Final'],
        ],
        'provinceRankings' => [
            ['name' => 'Kalimantan Timur', 'gold' => 8, 'silver' => 5, 'bronze' => 3],
            ['name' => 'Sulawesi Selatan', 'gold' => 5, 'silver' => 3, 'bronze' => 4],
            ['name' => 'Jawa Timur', 'gold' => 4, 'silver' => 4, 'bronze' => 2],
        ],
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
    ]);
});
