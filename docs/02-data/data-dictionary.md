# Data Dictionary v1

## Event

- id: ID event.
- public_id: UUID untuk link publik bila diperlukan.
- slug: URL readable event.
- name: nama event.
- start_date: tanggal mulai.
- end_date: tanggal selesai.
- location: lokasi utama.
- status: draft, published, archived.

## PDAM

- id: ID PDAM.
- public_id: UUID untuk link publik bila diperlukan.
- slug: URL readable PDAM.
- name: nama PDAM.
- region: wilayah.
- province_id: relasi ke provinsi.
- regency_id: relasi ke kabupaten/kota.
- logo_url: logo.
- contact_name: nama perwakilan.
- contact_phone: kontak perwakilan.

## RegionalCommittee

- id: ID Kontingen Provinsi.
- province_id: relasi unik ke provinsi.
- name: nama resmi, default `{NAMA PROVINSI}`.

## EventEntry

- id: ID registrasi cabor.
- tournament_event_id: kompetisi/cabor yang diikuti.
- pdam_id: instansi asal peserta.
- regional_committee_id: Kontingen Provinsi saat registrasi.
- province_id: snapshot provinsi PDAM saat registrasi.
- regency_id: snapshot kabupaten/kota PDAM saat registrasi.
- display_name: nama peserta yang tampil pada match/bracket.
- verification_status: status verifikasi registrasi.

## Team

- id: ID tim.
- event_id: event.
- pdam_id: pemilik tim.
- sport_id: cabor.
- category_id: kategori.
- name: nama tim.
- verification_status: draft, diajukan, diverifikasi, ditolak, revisi.

## Athlete

- id: ID atlet.
- pdam_id: PDAM.
- name: nama atlet.
- identity_number: nomor identitas internal event bila dipakai.
- verification_status: status verifikasi.

## Match

- id: ID match.
- public_id: UUID untuk URL detail match public.
- event_id: event.
- sport_id: cabor.
- category_id: kategori.
- venue_id: venue.
- participant_a_id: peserta A.
- participant_b_id: peserta B.
- scheduled_at: waktu pertandingan.
- status: draft, terjadwal, berlangsung, jeda, selesai, final, revisi.
- winner_id: pemenang.
- finalized_at: waktu finalisasi.

## Score

- id: ID skor.
- match_id: match.
- participant_a_score: skor peserta A.
- participant_b_score: skor peserta B.
- period_label: set/babak/ronde bila perlu.
- updated_by: user terakhir.
- updated_at: waktu update.

## AuditLog

- id: ID audit.
- actor_id: user.
- action: aksi.
- entity_type: tipe entitas.
- entity_id: ID entitas.
- before_value: nilai sebelum.
- after_value: nilai sesudah.
- created_at: waktu aksi.

## RankingSnapshot

- id: ID snapshot ranking.
- event_id: event.
- scope: `regional_committee` untuk klasemen medali; scope cabor mengikuti format kompetisi.
- scope_id: ID Kontingen Provinsi.
- gold_count: jumlah emas.
- silver_count: jumlah perak.
- bronze_count: jumlah perunggu.
- total_medals: total medali.
- rank: peringkat.
- calculated_at: waktu kalkulasi.

## Addendum v2: Seed Kategori Cabor

File referensi: `data/seed/sport_categories.csv`.

Kolom:

| Kolom | Fungsi |
|---|---|
| `sport_code` | Relasi ke `sports.code` |
| `code` | Kode kategori unik per cabor |
| `name` | Nama kategori public |
| `competition_type` | `individual`, `doubles`, `team`, `ranking` |
| `scoring_type` | Tipe skor untuk kalkulasi winner |
| `bracket_enabled` | `1` bila memakai bracket, `0` bila ranking/penilaian |
| `sort_order` | Urutan tampil kategori |

Catatan:

- Cabor tanpa kategori aktif boleh langsung memakai `tournament_event` tanpa `sport_category_id`.
- Kategori adalah master data, bukan hasil pertandingan.
- Bracket tetap dibuat dari `tournament_events` agar tiap kategori punya bracket sendiri.
