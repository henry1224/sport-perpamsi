# Agenda Standard PORPAMNAS IX Kaltim v1

Agenda khusus seminar NIWC mengikuti [seminar-standard.md](./seminar-standard.md) dan memiliki halaman public terpisah di `/seminar`.

## Sumber Awal

- Screenshot agenda: `docs/image/agenda.png`.
- Seed agenda: `data/seed/event_agenda.csv`.
- Seed venue: `data/seed/venues.csv`.
- Seed cabor/aktivitas: `data/seed/sports.csv`.
- Seeder Laravel:
  - `database/seeders/SportMasterSeeder.php`
  - `database/seeders/VenueSeeder.php`
  - `database/seeders/EventAgendaSeeder.php`

## Fungsi Produk

- Public bisa melihat agenda per tanggal.
- Public bisa filter agenda berdasarkan cabor/aktivitas, venue, dan tanggal.
- Admin bisa mengelola agenda pertandingan, eksibisi, dan acara resmi.
- Agenda menjadi sumber awal untuk membuat jadwal/match detail bila panitia sudah punya regulasi pertandingan.

## Tipe Agenda

- `sport`: pertandingan cabor resmi.
- `exhibition`: eksibisi seperti golf, padel, vokal.
- `official`: acara resmi seperti technical meeting, opening ceremony, welcome dinner, city tour, ladies program, farewell party.

## Cabor/Aktivitas dari Agenda

- Voli.
- Catur.
- Bulu tangkis.
- Tenis meja.
- Tenis lapangan.
- Golf.
- Padel.
- Vokal.
- Mini Football.

## Venue dari Agenda

- Hotel Platinum.
- Rujab Walikota.
- Royal Mahligai Golf and Country Club.
- Borneo Anfield Stadium.
- Balikpapan Tennis Stadium.
- Gedung KNPI Balikpapan.
- BSCC Dome.
- Gedung Kesenian Balikpapan.
- HOLA! Padel & Tennis Club.
- Gedung MFH Kemenko 2.
- Gor Banua Patra.
- Ibu Kota Negara (IKN).
- Pasar Tumpah Pringgodani.
- De Boekit Riverside.

## Struktur Data Agenda

- date.
- day.
- title.
- type: sport, exhibition, official.
- sport_code: nullable untuk acara official.
- venue_code.
- start_time.
- end_time: nullable bila sampai selesai.
- time_note: contoh `Selesai`, `Sesi 1`, `Sesi 2`.

## Catatan Implementasi

- Agenda bukan selalu match; satu agenda bisa menjadi blok waktu untuk banyak match.
- Match detail dibuat dari agenda setelah peserta dan regulasi tiap cabor siap.
- Mini Football memakai dua sesi per hari.
- Ladies Program memakai dua venue pada jam sama.
- Jika venue/jam berubah, agenda disimpan sebagai revisi dan public menampilkan data terbaru.
