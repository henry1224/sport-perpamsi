# Import Template Standard v1

## Prinsip

- Import hanya untuk data awal dan koreksi massal yang disetujui admin.
- Import wajib preview sebelum commit.
- Baris invalid tidak boleh masuk diam-diam.
- Template dikunci sebelum data dikumpulkan dari PDAM/panitia.

## Template PDAM

| Kolom | Wajib | Catatan |
|---|---|---|
| pdam_name | Ya | Nama resmi PDAM |
| region | Tidak | Wilayah |
| contact_name | Tidak | PIC |
| contact_phone | Tidak | Kontak PIC |

`province_code` wajib pada import operasional agar sistem dapat menetapkan Kontingen Provinsi. `regional_committee_id` tidak diisi dari file; sistem menurunkannya dari provinsi PDAM.

## Template Registrasi Cabor

| Kolom | Wajib | Catatan |
|---|---|---|
| pdam_name | Ya | Harus cocok dengan master PDAM |
| sport_code | Ya | Cabor tujuan |
| category_name | Tidak | Wajib bila cabor punya kategori |
| display_name | Ya | Nama provinsi pada bracket |
| athlete_1 | Tidak | Peserta pertama |
| athlete_2 | Tidak | Peserta kedua untuk ganda |
| team_name | Tidak | Wajib untuk kompetisi beregu |

Preview import wajib menampilkan Kontingen Provinsi hasil resolusi provinsi sebelum commit.

Contoh data: `data/seed/sample_pdams.csv`.

## Template Tim

| Kolom | Wajib | Catatan |
|---|---|---|
| pdam_name | Ya | Harus cocok dengan master PDAM |
| sport_code | Ya | Contoh `mini-football` |
| category_name | Ya | Putra/Putri/Beregu |
| team_name | Ya | Nama tim |

Contoh data: `data/seed/sample_teams.csv`.

## Template Atlet

| Kolom | Wajib | Catatan |
|---|---|---|
| pdam_name | Ya | Harus cocok dengan master PDAM |
| team_name | Tidak | Wajib bila atlet masuk tim |
| athlete_name | Ya | Nama atlet |
| identity_number | Tidak | Nomor internal event bila dipakai |

Contoh data: `data/seed/sample_athletes.csv`.

## Template Jadwal

| Kolom | Wajib | Catatan |
|---|---|---|
| sport_code | Ya | Cabor |
| category_name | Ya | Kategori |
| venue_name | Ya | Venue |
| participant_a | Ya | Tim/PDAM/peserta A |
| participant_b | Ya | Tim/PDAM/peserta B |
| scheduled_at | Ya | Format ISO atau template tanggal resmi |
| format | Ya | knockout/group/round_robin/double_elimination |
| bracket_round | Tidak | Jika knockout |
| group_name | Tidak | Jika group stage |

## Template Agenda

| Kolom | Wajib | Catatan |
|---|---|---|
| date | Ya | Tanggal agenda |
| day | Ya | Nama hari |
| title | Ya | Nama kegiatan/cabor |
| type | Ya | sport/exhibition/official |
| sport_code | Tidak | Wajib untuk sport/exhibition |
| venue_code | Ya | Kode venue |
| start_time | Ya | Jam mulai |
| end_time | Tidak | Kosong bila sampai selesai |
| time_note | Tidak | Sesi/Selesai/catatan jam |
