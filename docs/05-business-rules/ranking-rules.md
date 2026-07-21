# Ranking Rules v1

## Ranking Dipakai Untuk Apa

- Menentukan peringkat medali Kontingen Provinsi di public page.
- Menentukan klasemen grup cabor.
- Menentukan peserta yang lolos ke knockout.
- Menentukan juara umum event bila panitia memakai sistem medali.
- Mengurangi sengketa karena rumus tampil jelas di public.

## Klasemen Kontingen Provinsi / Juara Umum

Rekomendasi v1 memakai metode nasional/olahraga multi-event:

1. Medali emas terbanyak.
2. Jika sama, medali perak terbanyak.
3. Jika sama, medali perunggu terbanyak.
4. Jika masih sama, total medali.
5. Jika masih sama, urutan nama Kontingen Provinsi atau keputusan panitia.

Catatan: metode ini adalah lexicographic medal ranking, umum terlihat pada Olimpiade, SEA Games, PON, dan laporan klasemen kontingen.

## Klasemen Medali Kontingen Provinsi

Pengurus Daerah mendaftarkan atlet/tim untuk kontingen provinsinya. Instansi asal tetap disimpan internal, tetapi nama pada pertandingan dan klasemen memakai nama provinsi. Contoh: hasil peserta dari beberapa Perumda di Kalimantan Timur sama-sama tercatat untuk `Kalimantan Timur`.

Pendaftaran menyimpan `regional_committee_id` pada setiap `event_entries`. Nilainya diturunkan dari provinsi PDAM saat pendaftaran diverifikasi; panitia tidak memilih Kontingen Provinsi lain secara manual.

Urutan:

1. Total emas seluruh peserta dalam Kontingen Provinsi.
2. Jika sama, total perak.
3. Jika sama, total perunggu.
4. Jika sama, total medali.
5. Jika masih sama, nama Kontingen Provinsi.

## Ranking Grup Sepak Bola/Futsal

1. Poin: menang 3, seri 1, kalah 0.
2. Selisih gol.
3. Gol memasukkan.
4. Head-to-head antar tim terkait.
5. Fair play atau keputusan panitia.
6. Undian bila masih sama dan disetujui technical meeting.

## Ranking Grup Basket

1. Menang-kalah atau classification points.
2. Head-to-head antar tim terkait.
3. Selisih poin.
4. Poin memasukkan.
5. Ranking awal/undian bila masih sama.

## Ranking Grup Voli

1. Match point.
2. Jumlah kemenangan.
3. Set ratio.
4. Point ratio.
5. Head-to-head.

Match point voli umum:

- Menang 3-0 atau 3-1: 3 poin.
- Menang 3-2: 2 poin.
- Kalah 2-3: 1 poin.
- Kalah 0-3 atau 1-3: 0 poin.

## Ranking Bulutangkis/Tenis Meja

1. Jumlah kemenangan match.
2. Jika dua peserta sama: head-to-head.
3. Jika tiga atau lebih sama: selisih game/set.
4. Jika masih sama: selisih poin.
5. Jika masih sama: undian atau keputusan panitia.

## Ranking Catur

- Gunakan tie-break yang disahkan technical meeting.
- Default v1: match point/game point, direct encounter, Sonneborn-Berger/Buchholz bila diperlukan.
- Sistem hanya menyimpan nilai tie-break, bukan menghitung semua varian FIDE otomatis di v1.

## Referensi

- Setkab RI menampilkan klasemen Olimpiade berbasis emas, perak, perunggu: https://setkab.go.id/klasemen-akhir-olimpiade-paris-2024-amerika-serikat-posisi-pertama-indonesia-peringkat-ke-39/
- FIFA tie-break grup: https://www.fifa.com/en/articles/groups-how-teams-qualify-tie-breakers
- FIBA official rules/classification: https://about.fiba.basketball/en/our-sport/official-basketball-rules
- FIVB official volleyball rules: https://www.fivb.com/volleyball/the-game/official-volleyball-rules/
- O2SN 2026 ranking bulutangkis setengah kompetisi: https://repositori.kemendikdasmen.go.id/36365/1/Panduan_O2SN_2026_update.pdf
- FIDE tie-break regulations: https://handbook.fide.com/chapter/TieBreakRegulations032026
