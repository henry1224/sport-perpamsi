# Competition Format Standard v1

> Format non-knockout (group stage, round robin, Swiss, time/distance/score ranking) belum memiliki struktur data pendukung. Drift dan aksi lihat `docs/00-project/audit-2026-07-22.md` (D18).

Status implementasi saat ini:

- Knockout: data demo dapat dibentuk oleh seeder, tetapi generator operasional dan aksi lock belum tersedia.
- Swiss: belum tersedia; target Phase 6 untuk Catur.
- Score ranking: belum tersedia; target Phase 6 untuk Golf dan lomba berbasis nilai/waktu.
- Pembuatan Data Lomba tidak boleh otomatis membuat bracket atau match.

## Prinsip

- Participant seluruh format adalah `EntryTeam`, termasuk kategori individual yang memiliki satu anggota.
- Sistem kompetisi harus dikunci per cabor sebelum jadwal dibuat.
- v1 mendukung format sederhana yang umum dipakai event nasional: grup/setengah kompetisi dan knockout.
- Detail teknis cabor mengikuti technical meeting dan regulasi federasi/panitia.
- Sistem tidak menjadi rule engine semua cabor; sistem menyimpan format dan hasil yang disahkan panitia.

## Format yang Didukung v1

### Knockout

- Peserta kalah langsung gugur.
- Cocok untuk peserta banyak dan waktu singkat.
- Sistem menyimpan ronde, slot peserta, pemenang, match berikutnya.
- Third place match opsional.

### Group Stage / Setengah Kompetisi

- Semua peserta dalam grup saling bertanding satu kali.
- Peringkat grup dihitung dari aturan ranking cabor.
- Juara grup atau peringkat tertentu masuk knockout.
- Cocok untuk mini football, basket, voli, bulutangkis beregu/perorangan, tenis meja.

### Round Robin Penuh

- Semua peserta saling bertanding.
- Cocok bila peserta sedikit.
- Ranking akhir langsung dari klasemen.

### Time / Distance / Score Ranking

- Peserta diranking dari waktu tercepat, jarak terbaik, nilai tertinggi, atau nilai terendah sesuai cabor.
- Cocok untuk lari, renang, panahan, catur, atau lomba berbasis nilai.

## Template Format per Jenis Cabor

| Jenis Cabor | Format Disarankan v1 | Ranking Grup |
|---|---|---|
| Mini football | Grup lalu knockout | Poin, selisih gol, gol memasukkan, head-to-head |
| Basket | Grup lalu knockout | Menang-kalah/poin klasifikasi, head-to-head, selisih poin, poin memasukkan |
| Voli | Grup lalu knockout | Match point, jumlah kemenangan, set ratio, point ratio |
| Bulutangkis/tenis meja | Grup lalu knockout | Jumlah menang, head-to-head, selisih game/set, selisih poin |
| Catur | Swiss/round robin | Match point/game point, tie-break sesuai aturan turnamen |
| Lari/renang | Heat lalu final | Waktu terbaik |
| Panahan/menembak | Kualifikasi nilai lalu knockout/final | Nilai tertinggi |

Format bawaan berasal dari master cabor dan menjadi nilai kompetisi draft. Setelah registrasi dipublikasikan atau peserta masuk, format kompetisi terkunci. Kuota team dan anggota per team merupakan snapshot terpisah dan tidak diturunkan dari nama format.

## Pairing Multi-Team Satu PD

- `avoid_same_pd_in_round` disimpan pada snapshot registrasi dan seeding; default `true`.
- Generator best-effort memisahkan team dari PD sama pada penempatan ronde awal bila alternatif valid tersedia.
- Bila mustahil, relaksasi dilakukan deterministik dengan meminimalkan pertemuan paling awal.
- Seeding snapshot mencatat versi algoritme, entrant team, konflik yang tak terhindarkan, dan urutan relaksasi.
- Aturan ini tidak menjamin team satu PD tidak bertemu pada ronde lanjutan karena hasil pertandingan belum diketahui.
- Detail mengikuti [standar multi-team](../02-data/team-entry-standard.md).

## Referensi

- O2SN 2026 memakai sistem kompetisi/round-robin dan knockout pada cabor tertentu: https://repositori.kemendikdasmen.go.id/36365/1/Panduan_O2SN_2026_update.pdf
- FIFA World Cup menjelaskan grup lalu knockout dan tie-break grup: https://www.fifa.com/en/articles/groups-how-teams-qualify-tie-breakers
- FIBA menjelaskan competition system grup dan final stage: https://www.fiba.basketball/en/events/fiba-basketball-world-cup-2027/competition-system
- FIVB menyediakan official volleyball rules: https://www.fivb.com/volleyball/the-game/official-volleyball-rules/
- FIDE menyediakan tie-break regulations untuk catur: https://handbook.fide.com/chapter/TieBreakRegulations032026
