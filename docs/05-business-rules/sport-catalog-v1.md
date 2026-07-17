# Sport Catalog v1

## Cabor Resmi Sementara

| Kode | Nama | Format Default | Struktur Skor Default |
|---|---|---|---|
| badminton | Bulu tangkis | Grup lalu knockout | Game/set, poin per game |
| futsal | Futsal | Grup lalu knockout | Gol, babak 1-2, penalti opsional |
| table-tennis | Tenis meja | Grup lalu knockout | Game/set, poin per game |
| tennis | Tenis lapangan | Knockout atau grup lalu knockout | Set, game, tie-break opsional |
| volleyball | Voli | Grup lalu knockout | Set menang, poin per set |
| chess | Catur | Round robin atau Swiss | Match point/game point, tie-break |

## Prinsip Penambahan Cabor

- Cabor baru ditambah lewat master data admin.
- Setiap cabor wajib punya format kompetisi default.
- Setiap cabor wajib punya struktur skor default.
- Aturan ranking cabor bisa memakai template umum atau konfigurasi manual.
- Jika aturan cabor terlalu kompleks, sistem menyimpan hasil final yang disahkan panitia.

## Format yang Harus Didukung

- Knockout/sistem gugur.
- Group stage lalu knockout.
- Round robin.
- Swiss sederhana untuk catur bila disahkan technical meeting.
- Double elimination/lower bracket bila regulasi event memakai lower bracket.

## Catatan v1

- Sistem tidak menghitung semua aturan federasi secara otomatis.
- Sistem menyediakan template, input hasil, bracket, ranking, dan audit.
- Keputusan final tetap mengikuti technical meeting dan panitia berwenang.
