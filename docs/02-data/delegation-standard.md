# Standar Delegasi Pimpinan Daerah

## Tujuan

Menetapkan satu rantai data untuk master wilayah, pendaftaran peserta, pertandingan, dan klasemen medali.

## Relasi Kanonik

```text
Province 1 ── 1 RegionalCommittee
Province 1 ── N Pdam
RegionalCommittee 1 ── N EventEntry
Pdam 1 ── N EventEntry
TournamentEvent 1 ── N EventEntry
EventEntry 1 ── N MatchParticipant/Match
Final Match ── MedalStanding RegionalCommittee
```

## Aturan Data

1. Satu provinsi memiliki tepat satu Pimpinan Daerah (`regional_committees`).
2. Nama default memakai format `PD PERPAMSI {NAMA PROVINSI}` dan boleh dikoreksi pada master tanpa mengubah relasi.
3. PDAM tetap menjadi instansi asal peserta dan selalu terhubung ke provinsi.
4. Saat PDAM mendaftar cabor, `event_entries` menyimpan `pdam_id`, `province_id`, dan `regional_committee_id` sebagai snapshot registrasi.
5. `regional_committee_id` diturunkan otomatis dari provinsi PDAM; operator tidak boleh memilih PD lain.
6. Tim, atlet, dokumen, seed, match, dan hasil tetap terhubung ke `event_entries`.
7. Bracket dan hasil menampilkan nama PDAM/tim/atlet sebagai peserta pertandingan.
8. Klasemen medali mengakumulasi hasil seluruh `event_entries` ke Pimpinan Daerah.
9. Emas dan perak berasal dari hasil babak final. Perunggu membutuhkan data penetapan juara ketiga yang eksplisit sesuai regulasi cabor.
10. Perubahan provinsi PDAM setelah registrasi tidak mengubah delegasi historis; koreksi dilakukan lewat revisi registrasi yang diaudit.

## Alur Registrasi

1. Admin memilih PDAM.
2. Sistem membaca `province_id` PDAM.
3. Sistem menetapkan `regional_committee_id` provinsi tersebut.
4. Admin memilih event, cabor, kategori, tim/atlet, dan dokumen.
5. Verifikator memeriksa data lalu menetapkan status registrasi.
6. Registrasi terverifikasi masuk ke seeding, bracket, atau klasemen cabor.
7. Hasil final masuk ke klasemen medali Pimpinan Daerah.

## Sumber Kebenaran

- Master wilayah: `provinces`, `regencies`.
- Master delegasi: `regional_committees`.
- Master instansi: `pdams`.
- Registrasi cabor: `event_entries`.
- Hasil pertandingan: `matches`, `match_scores`, `score_audits`.
- Rekap publik: proyeksi klasemen dari hasil final, bukan input ulang nama wilayah.
