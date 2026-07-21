# Standar Kontingen Provinsi

## Tujuan

Menetapkan identitas peserta publik dan relasi data registrasi agar nama instansi asal tidak disalahgunakan sebagai nama kontingen.

## Istilah Kanonik

- Pengurus Daerah: pengguna yang mengelola registrasi satu provinsi.
- Kontingen Provinsi: identitas pertandingan dan klasemen yang memakai `provinces.name`.
- Instansi Asal: PDAM/Perumda Air Minum asal atlet atau tim; hanya metadata internal registrasi.

Contoh benar:

```text
Pengurus Daerah Aceh
└── Kontingen: Aceh
    ├── Atlet A — asal Perumda Air Minum Kabupaten Aceh
    └── Tim B  — asal PDAM lain dalam Provinsi Aceh

Nama di bracket/klasemen: Aceh
```

## Relasi Kanonik

```text
Province 1 ── 1 RegionalCommittee
Province 1 ── N Pdam
RegionalCommittee 1 ── N EventEntry
Pdam 1 ── N EventEntry
TournamentEvent 1 ── N EventEntry
EventEntry 1 ── N MatchParticipant/Match
Final Match ── MedalStanding Province
```

`regional_committees` tetap nama teknis tabel saat ini. Nilai `regional_committees.name` wajib sama dengan `provinces.name`.

## Aturan Data

1. Satu provinsi memiliki satu akun/ruang kerja Pengurus Daerah.
2. Nama kontingen selalu memakai nama resmi provinsi tanpa awalan `PD`, `PERPAMSI`, `PDAM`, atau `Perumda Air Minum`.
3. `event_entries.display_name` memakai nama provinsi.
4. `event_entries.pdam_id` menyimpan instansi asal untuk administrasi dan verifikasi internal.
5. `regional_committee_id` diturunkan otomatis dari provinsi yang dikelola pengguna.
6. Operator tidak boleh memilih kontingen provinsi lain.
7. Atlet, tim, dokumen, seed, match, dan hasil terhubung ke `event_entries`.
8. Bracket, hasil, peserta public, dan klasemen menampilkan nama provinsi.
9. Detail instansi asal hanya tampil pada portal internal bila dibutuhkan.
10. Medali seluruh entry dari provinsi sama diakumulasi ke provinsi tersebut.

## Alur Registrasi

1. Pengurus Daerah masuk ke portal provinsinya.
2. Pengurus Daerah memilih instansi asal atlet/tim.
3. Sistem menetapkan `province_id`, `regional_committee_id`, dan `display_name` dari provinsi akun.
4. Pengurus Daerah memilih event, cabor, kategori, tim/atlet, dan dokumen.
5. Verifikator memeriksa data internal tanpa mengubah nama kontingen.
6. Entry terverifikasi masuk ke seeding, bracket, atau klasemen cabor.
7. Hasil final masuk ke klasemen medali provinsi.

## Sumber Kebenaran

- Nama kontingen: `provinces.name`.
- Ruang kerja Pengurus Daerah: `regional_committees`.
- Instansi asal: `pdams`.
- Registrasi cabor: `event_entries`.
- Hasil pertandingan: `matches`, `match_scores`, `score_audits`.
- Rekap publik: agregasi hasil final berdasarkan provinsi.
