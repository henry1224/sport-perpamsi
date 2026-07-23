# Sport Catalog v1

## Cabor Resmi Sementara

| Kode | Nama | Tipe | Format Default | Struktur Skor Default |
|---|---|---|---|
| volleyball | Voli | Sport | Grup lalu knockout | Set menang, poin per set |
| chess | Catur | Sport | Swiss | Match point dan tie-break Swiss |
| badminton | Bulu tangkis | Sport | Grup atau sistem gugur, mengikuti jumlah peserta | Rally point 21, best of 3 |
| table-tennis | Tenis meja | Sport | Grup lalu knockout | Game/set, poin per game |
| tennis | Tenis lapangan | Sport | Grup lalu knockout | Set, game, tie-break opsional |
| golf | Golf | Eksibisi | Score ranking | Stroke/score |
| padel | Padel | Eksibisi | Fun Games | Fun Games |
| vocal | Vokal | Eksibisi | Sekali tampil lalu ranking | Nilai juri |
| mini-football | Mini Football | Sport | Grup lalu knockout | Gol, durasi 2 x 25 menit |

## Kategori Resmi Panduan Teknis

| Cabor | Kategori | Unit | Anggota per team | Team per PD | Sistem pertandingan |
|---|---|---|---:|---|---|
| Bulu Tangkis | Tunggal Putra, Tunggal Putri | Individual | 1 | Ditetapkan technical meeting | Rally point; grup atau gugur; 21 poin best of 3 |
| Bulu Tangkis | Ganda Putra, Ganda Putri, Ganda Campuran, Ganda Veteran U45 | Pair | 2 | Ditetapkan technical meeting | Rally point; grup atau gugur; 21 poin best of 3 |
| Catur | Perorangan Cepat | Individual | 1 | Ditetapkan technical meeting | Swiss, 10 menit tanpa increment, 5-7 babak |
| Catur | Beregu Cepat | Team | 3 | Ditetapkan technical meeting | Swiss, 15 menit tanpa increment, 5 babak |
| Tenis Lapangan | Beregu Putra Bebas Usia, Beregu Putra Veteran KU 40 | Team | 6 | Ditetapkan technical meeting | Setengah kompetisi lalu sistem gugur |
| Tenis Meja | Tunggal Putra/Putri | Individual | 1 | Ditetapkan technical meeting | Setengah kompetisi lalu sistem gugur |
| Tenis Meja | Ganda Putra/Putri dan Ganda Campuran | Pair | 2 | Ditetapkan technical meeting | Setengah kompetisi lalu sistem gugur |
| Mini Football | Putra | Team | 15 | Ditetapkan technical meeting | Setengah kompetisi lalu sistem gugur; 2 x 25 menit |
| Voli | Putra | Team | 12 | Ditetapkan technical meeting | Setengah kompetisi lalu sistem gugur |
| Vokal | Solo Putra, Solo Putri | Individual | 1 | Ditetapkan technical meeting | Sekali tampil; tiga pemenang per kategori |
| Golf | Individual | Individual | 1 | Ditetapkan technical meeting | Score ranking |
| Padel | Eksibisi Eksekutif, Eksibisi Prestasi | Pair | 2 | Ditetapkan technical meeting | Fun Games |

Angka `Team per PD` tidak menjadi nilai global katalog. Admin wajib mengisinya pada setiap kompetisi berdasarkan hasil technical meeting; publish ditolak jika belum terisi.

Baseline Catur hasil keputusan operasional: Perorangan Cepat maksimal 2 team per PD dengan 1 atlet per team; Beregu Cepat maksimal 1 team per PD dengan 3 atlet per team.

Sumber baseline: Panduan PORPAMNAS IX slide 5-23, diakses 22 Juli 2026. Official tidak dihitung sebagai `entry_members`.

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
