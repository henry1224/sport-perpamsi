# UI Standard Sport PERPAMSI

Dokumen detail pendamping: `docs/04-design/public-admin-ui-standard.md`.

## Arah Visual

- Gaya esport modern, gelap, energik, tetap formal untuk stakeholder resmi.
- Dark theme menjadi identitas utama.
- Aksen warna kuat untuk live, menang, kalah, final, revisi.
- Informasi pertandingan harus cepat dibaca di mobile dan layar venue.

## Prinsip UI

- Mobile-first untuk public.
- Admin-first untuk data table, filter, dan aksi cepat.
- Scorekeeper-first untuk input hasil selesai pertandingan dan minim salah tekan.
- Status tidak boleh hanya memakai warna; wajib ada teks status.
- Tombol kritis wajib punya konfirmasi.

## Public Page

- Home menampilkan hasil terbaru, jadwal hari ini, highlight, ranking ringkas.
- Hasil terbaru menampilkan status, skor, peserta, cabor, kategori, venue, waktu update.
- Jadwal punya filter tanggal, cabor, venue, PDAM, status.
- Match detail menampilkan skor, status, venue, waktu, peserta, dan update terakhir.
- Bracket memakai horizontal scroll di mobile.
- Ranking menampilkan rumus perhitungan.
- Peserta, match, bracket, hasil, dan klasemen medali menampilkan nama provinsi.

## Admin Page

- Dashboard menampilkan match hari ini, match live, pending verifikasi, revisi skor, data belum lengkap.
- Semua data besar memakai table dengan search, filter, pagination, export.
- Form penting menampilkan status simpan dan waktu update terakhir.
- Aksi delete/archive/finalisasi/revisi memakai konfirmasi.

## Panitia / Scorekeeper Page

- Layar utama hanya menampilkan match selesai/terjadwal yang ditugaskan.
- Input skor memakai tombol dan field besar.
- Status match terlihat jelas: terjadwal, berlangsung, jeda, selesai, final, revisi.
- Setelah simpan skor, tampilkan waktu update dan aktor terakhir.
- Jika koneksi bermasalah, tampilkan instruksi simpan manual.

## Aksesibilitas Minimum

- Kontras aman untuk outdoor dan proyektor.
- Font public cukup besar untuk mobile.
- Tombol input skor cukup besar untuk tablet/laptop lapangan.
- Loading state dan error state wajib jelas.
- Public tidak perlu login.

## Komponen Utama

- Match card.
- Live score board.
- Schedule filter.
- Bracket view.
- Ranking table.
- PDAM profile card.
- Admin data table.
- Score input panel.
- Status badge.
- Confirmation dialog.

## Addendum v2: Public Pagination, Ranking Filter, dan Bracket Besar

### Bracket Public

- Default tampil `Round 64` untuk data besar.
- Sediakan toggle: `Round 16`, `Round 32`, `Round 64`, `Round 128`, `Round Awal`.
- `Round Awal` tampil sebagai list compact, bukan bracket besar.
- Main bracket memakai mirror layout: kiri dan kanan bertemu di final tengah.
- Connector line harus stabil dan tidak overlap card.
- Hover pada peserta menampilkan active path ke ronde berikutnya.
- Animasi line aktif harus halus, lambat, dan tidak membuat line hilang.

### Kategori Cabor

- Jika cabor punya kategori, tampilkan kategori chip tepat di bawah tab cabor.
- Jika cabor tidak punya kategori, jangan tampilkan area kosong.
- Kategori aktif harus terlihat jelas dan tetap senada dengan tema dark tournament.

### Pagination Tema

- Pagination tidak boleh memakai style browser/default polos.
- Gunakan button clipped-corner seperti nav/tab.
- State wajib: normal, hover, active, disabled, loading.
- Letakkan pagination di atas dan bawah list besar untuk PDAM/ranking bila data panjang.

### Ranking Public

- Klasemen medali wajib punya filter: event, cabor, kategori, provinsi, dan pencarian Kontingen Provinsi.
- Tampilkan medal table ringkas dengan jarak teks cukup.
- Jangan rapatkan jumlah medali dengan nama wilayah.
- Empty state harus informatif, bukan blank.

### Copywriting Public

- Hindari frasa generic seperti `Public board`.
- Pakai bahasa resmi, pendek, dan mudah dipahami.
- CTA harus jelas: `Lihat Hasil`, `Lihat Jadwal`, `Buka Bracket`.
