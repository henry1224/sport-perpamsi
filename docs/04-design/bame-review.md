# Review Bame Theme untuk Sport PERPAMSI

## Status Akses

- Preview ThemeForest memakai wrapper/anti-bot, jadi pemeriksaan live terbatas.
- Review memakai struktur demo Bame yang umum terlihat: esport homepage, tournament, match, gamer/team, streaming, shop/blog, inner pages.
- Keputusan desain: ambil pola UX dan hierarki, bukan clone asset, warna, template, atau kode.

## Ringkasan Cocok untuk Project

Bame cocok sebagai arah visual karena PORPAMNAS butuh public board yang terasa seperti turnamen besar: cepat dibaca, padat informasi, penuh energi, dan tetap resmi. Struktur Bame bagus untuk mengangkat agenda, hasil, cabor, bracket, ranking, peserta, venue, sponsor, dan berita singkat dalam satu portal.

## Review Per Menu Bame

### Home

- Hero besar dengan judul event, karakter/visual utama, CTA, dan statistik.
- Cocok untuk landing PORPAMNAS: logo full-color, maskot Beru/Ganga, quick stats agenda/cabor/venue.
- Implementasi: `resources/js/Pages/Home.vue` memakai hero arena, mascot anchor, CTA hasil dan agenda.

### Tournament

- Biasanya berisi daftar turnamen, format, jadwal, prize/status, dan detail kompetisi.
- Adaptasi PORPAMNAS: daftar cabor, regulasi, fase grup/knockout/lower bracket, jadwal per venue.
- Kebutuhan data: `sports`, `competition_formats`, `brackets`, `matches`, `match_results`.

### Match / Schedule

- Pola terbaik Bame: kartu pertandingan dengan tim kiri, skor/status tengah, tim kanan.
- Adaptasi PORPAMNAS: hasil selesai pertandingan, bukan live point-by-point.
- Status yang perlu ada: `scheduled`, `finished`, `walkover`, `postponed`, `verified`, `disputed`.

### Team / Gamer

- Bame menampilkan team/player profile.
- Adaptasi PORPAMNAS: PD PERPAMSI sebagai kontingen, pemain sebagai peserta, official sebagai pendamping.
- Public cukup tampilkan nama provinsi, cabang, hasil pertandingan, dan raihan medali.

### Ranking / Standing

- Bame kuat di leaderboard visual.
- Adaptasi PORPAMNAS: nama `PD PERPAMSI {provinsi}` tampil sebagai kontingen pertandingan dan klasemen.
- Urutan resmi: emas, perak, perunggu, total, lalu aturan tie-breaker dokumen regulasi.

### Streaming / Media

- Bisa dipakai untuk highlight, galeri, dokumentasi, dan link live bila tersedia.
- Untuk fase awal cukup siapkan blok informasi dan link eksternal; jangan bangun CMS video dulu.

### Shop

- Tidak relevan untuk fase September.
- Bisa dihapus dari scope awal.

### Blog / News

- Berguna untuk pengumuman singkat, perubahan jadwal, dan keputusan panitia.
- Fase awal cukup `announcements` sederhana: judul, isi, status, pin, tanggal publish.

### Contact / About

- Dipakai untuk informasi panitia, venue, aturan umum, dan kontak bantuan.
- Perlu halaman info resmi agar public tidak mencari lewat chat panitia.

## Struktur Public yang Direkomendasikan

- Home: hero, quick stats, hasil terbaru, agenda hari ini.
- Agenda: filter tanggal, cabor, venue, kontingen.
- Hasil: daftar hasil selesai, status verifikasi, link detail pertandingan.
- Cabor: format kompetisi, regulasi ringkas, venue, jadwal.
- Bracket: knockout, lower bracket jika cabor memakai double elimination.
- Ranking: medali per PD PERPAMSI.
- Venue: lokasi, alamat, cabor, jadwal kegiatan.
- Info: pengumuman, kontak, aturan umum.

## Standar UI yang Diambil

- Dark tournament portal dengan accent biru, oranye, kuning.
- Header sticky seperti arena dashboard.
- Hero besar dengan maskot keluar dari frame agar terasa hidup.
- Match card pendek agar hasil cepat dibaca.
- Bracket preview horizontal seperti jalur kompetisi.
- Grid cabor padat dengan maskot tiap cabang bila tersedia.
- Ranking panel selalu terlihat dekat agenda/hasil.

## Catatan Implementasi Hero

- Maskot tidak boleh terkunci dalam card penuh; frame hanya menjadi background glow.
- Text hero boleh overlap area visual, tapi z-index text harus tetap terbaca.
- Stats board tidak boleh menutup CTA utama.
- Accent kuning-oranye dipakai sebagai active/highlight state, bukan tombol palsu di belakang agenda.
- Mobile: satu maskot cukup bila ruang sempit.

## Anti-AI UI Direction

- Hindari glassmorphism seragam di semua card.
- Pakai gaya poster/ticket event: clip-path miring, shadow keras, dashed divider, dan panel bersudut.
- Hero harus terasa seperti key visual turnamen, bukan dashboard template.
- Card data tetap padat, tapi bentuk dan hierarchy beda per kebutuhan: jadwal, ranking, cabor, hasil.
- Mascot menjadi karakter utama yang keluar dari frame, bukan dekorasi kecil di dalam card.

## Standar UI yang Tidak Diambil

- Animasi berat.
- Ecommerce/shop.
- Blog panjang di landing.
- Clone asset/kode/theme.
- Terlalu banyak efek yang mengganggu akses mobile.

## Kebutuhan Lanjutan

- Pecah public page: `/agenda`, `/hasil`, `/cabor`, `/ranking`, `/venue`, `/bracket`.
- Tambah admin: input hasil, verifikasi hasil, upload bukti, koreksi sengketa.
- Tambah data kontingen: PD PERPAMSI, provinsi, logo, dan cabor yang diikuti.
- Tambah format cabor: round robin, knockout, double elimination, swiss, stroke play.
- Tambah audit trail: siapa update skor, kapan, sebelum/sesudah.
