# Sport Catalog v1

## Cabor Resmi Sementara

| Kode | Nama | Tipe | Format Default | Struktur Skor Default |
|---|---|---|---|
| volleyball | Voli | Sport | Grup lalu knockout | Set menang, poin per set |
| chess | Catur | Sport | Round robin atau Swiss | Match point/game point, tie-break |
| badminton | Bulu tangkis | Sport | Grup lalu knockout | Game/set, poin per game |
| table-tennis | Tenis meja | Sport | Grup lalu knockout | Game/set, poin per game |
| tennis | Tenis lapangan | Sport | Knockout atau grup lalu knockout | Set, game, tie-break opsional |
| golf | Golf | Eksibisi | Score ranking | Stroke/score |
| padel | Padel | Eksibisi | Knockout atau eksibisi | Set, game, tie-break opsional |
| vocal | Vokal | Eksibisi | Score ranking | Nilai juri |
| mini-football | Mini Football | Sport | Grup lalu knockout | Gol, babak 1-2, penalti opsional |

## Prinsip Penambahan Cabor

- Cabor baru ditambah lewat master data admin.
- Master cabor/kategori tidak otomatis menjadi pilihan registrasi; Admin harus membuat dan mempublikasikan kompetisi registrasi.
- Tipe aktivitas bisa `sport`, `exhibition`, atau `official` untuk agenda non-pertandingan.
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
