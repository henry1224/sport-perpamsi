<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventAgendaSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $sportIds = DB::table('sports')->pluck('id', 'code');
        $venueIds = DB::table('venues')->pluck('id', 'code');
        $rows = array_map(fn ($row) => [
            'date' => $row['date'],
            'day' => $row['day'],
            'title' => $row['title'],
            'type' => $row['type'],
            'sport_id' => $row['sport_code'] ? $sportIds[$row['sport_code']] : null,
            'venue_id' => $venueIds[$row['venue_code']],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'] ?: null,
            'time_note' => $row['time_note'] ?: null,
            'created_at' => $now,
            'updated_at' => $now,
        ], $this->csvRows(base_path('data/seed/event_agenda.csv')));

        DB::table('event_agendas')->upsert(
            $rows,
            ['date', 'title', 'venue_id', 'start_time'],
            ['day', 'type', 'sport_id', 'end_time', 'time_note', 'updated_at']
        );
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
