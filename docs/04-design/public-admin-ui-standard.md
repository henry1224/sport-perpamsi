# Public, Admin, dan PD UI Standard

## Design Direction

- Public: dark navy tournament, resmi, energetic, readable.
- Admin dan PD: portal terang bersama, utilitarian, rapi, dan padat.

Dilarang:

- Card putih polos default.
- Gradient ungu/pink generik.
- Glassmorphism berlebihan.
- Shadow acak antar halaman.
- Spacing rapat tanpa hierarchy.

## Token Visual

| Token | Nilai | Fungsi |
|---|---|---|
| Navy base | `#071126` | Background panel utama |
| Navy card | `#08142d` | Card, filter, table |
| Navy row | `#0B1B3F` | Row match/list |
| Cyan | `#36C2F0` | Active path, info, accent |
| Teal | `#20C6B7` | Winner, success |
| Yellow | `#F6C64A` | Active tab, CTA utama |
| Orange | `#F05A28` | Shadow/energy accent |

## Spacing

- Section top/bottom desktop: 40-72px.
- Card padding desktop: 18-24px.
- Card gap: 16-24px.
- Filter block gap: 8-12px.
- Table row height minimal: 44px.
- Match row height minimal: 43px.

## Scrollbar

- Public memakai track navy dengan thumb cyan dan hover kuning.
- Admin dan PD memakai track abu terang dengan thumb biru portal.
- Modal mengikuti tema pemanggil: dark untuk public, light untuk Admin dan PD.
- Semua area scroll vertikal dan horizontal memakai pola yang sama.

## Typography

- Heading utama harus lega: line-height minimal `.98` untuk multi-line besar.
- Uppercase boleh dipakai untuk label, bukan body panjang.
- Body public maksimal 2 kalimat pendek.
- Button label harus action oriented: `Lihat Hasil`, `Buka Bracket`, `Pilih Kategori`.

## Public Page Standard

### Header

- Active menu wajib jelas.
- Nav tetap ringkas.
- CTA kanan satu saja.

### Home

- Hero headline maksimal 3 baris.
- Mascot boleh overlap, tapi tidak menutup teks/card.
- Copy harus resmi, bukan “AI generic”.

### Agenda/Hasil

- Filter di atas list.
- Card/list punya hierarchy: cabor, peserta, venue, waktu, status.
- Empty state wajib ada.

### Cabor

- Kartu hanya menampilkan ringkasan agar grid tetap pendek dan mudah dipindai.
- Persyaratan, sistem pertandingan, official, biaya, dan informasi panjang dibuka melalui modal dark.
- Modal tidak menampilkan metadata internal versi regulasi atau slide sumber.

### Error

- Status `403`, `404`, `419`, `500`, dan `503` memakai template public yang sama dengan `STOP.png` dan teks sesuai status.
- Tindakan kembali wajib jelas dan dapat digunakan dengan keyboard.

### Bracket

- Jika cabor punya kategori, kategori tampil di bawah tab cabor.
- Main view default `Round 64`.
- Opsi: `Round 16`, `Round 32`, `Round 64`, `Round 128`, `Round Awal`.
- Round awal tampil list compact, bukan full bracket.
- Hover winner path line aktif halus dan tidak putus.
- Final tidak sticky terhadap scroll horizontal.

### Peserta PD PERPAMSI

- Server-side pagination.
- Search debounce 300 ms.
- Pagination atas dan bawah jika data panjang.
- Card/list memakai nama display ringkas, nama lengkap di detail/tooltip.

### Ranking

- Filter: event, cabor, kategori, provinsi, dan PD PERPAMSI.
- Medal count tidak boleh terlalu dekat dengan nama wilayah.
- Sorting harus jelas.

## Admin dan PD UI Standard

### Standar Halaman Data Admin

Urutan komponen wajib konsisten:

1. Judul halaman sesuai grup menu sidebar.
2. Overview card dengan konteks dan maksimal tiga statistik.
3. Header daftar dengan satu tombol tambah.
4. Filter tabel tepat setelah tombol tambah dan menyatu dengan header card.
5. Tabel, empty state, dan pagination.
6. Audit bila tersedia.

Form tambah/edit wajib memakai modal light dan kelompok field yang jelas. Section bernomor dipakai untuk form bertahap; form panjang seperti Regulasi boleh memakai kelompok berjudul. Urutan field mengikuti dependensi pengisian: identitas terlebih dahulu, konfigurasi utama berikutnya, lalu aturan atau informasi tambahan.

Field Kata Sandi memakai label Bahasa Indonesia, tombol lihat/sembunyikan berikon, dan satu baris penuh. Form pembuatan atau perubahan Kata Sandi menampilkan indikator kekuatan serta konfirmasi kecocokan secara langsung; form login hanya menampilkan tombol lihat/sembunyikan.

### Overview dan Count Data

- Semua halaman data Admin mengikuti pola visual `Master PDAM`: satu overview card navy setelah judul, konteks singkat di kiri, dan maksimal tiga count di kanan.
- Count harus membantu keputusan pengguna, bukan mengulang pagination. Utamakan status atau kondisi data, seperti `Cabor Aktif`, `Kategori Aktif`, `Regulasi`, `Menunggu Verifikasi`, `Terverifikasi`, dan `Ditolak`.
- Count memakai seluruh dataset sesuai kewenangan pengguna dan tidak berubah karena pagination atau pencarian tabel. Filter domain utama boleh memengaruhi count hanya bila scope aktif dijelaskan pada judul atau label.
- Label count wajib mengikuti vocabulary kanonik. Jangan membuat label baru seperti `Draft` pada domain yang tidak memiliki status `draft`.
- `Data Tampil` hanya boleh dipakai bila jumlah baris halaman memang penting untuk pekerjaan; default-nya tidak ditampilkan.
- Setelah overview, tampilkan header daftar berupa surface putih berisi judul, deskripsi, dan maksimal satu tombol aksi utama. Halaman read-only atau verifikasi boleh tanpa tombol tambah.

### Tab Halaman Admin

- Halaman dengan beberapa domain setara mengikuti pola tab `Master Data Lomba`: tab horizontal setelah overview dan sebelum header daftar.
- Setiap tab menampilkan label domain dan count kecil, seperti `Cabor`, `Kategori`, dan `Regulasi`; active state memakai surface putih dan count biru portal.
- Tab hanya dipakai untuk dataset atau pekerjaan setara dalam satu konteks. Filter biasa tidak boleh diubah menjadi tab.
- Pergantian tab mempertahankan konteks halaman, memperbarui URL/query, mereset pagination, dan menampilkan header daftar, filter, tabel, serta aksi yang sesuai tab aktif.
- Mobile memakai tab horizontal yang dapat digulir atau dibagi rata tanpa memotong label.
- Jangan membuat variasi visual tab baru. Pengecualian hanya untuk workflow khusus yang sudah ditetapkan pada dokumen SoT menu.

### Agenda dan Jadwal

- Urutan grup `Penyusunan Lomba` adalah `Data Lomba`, `Agenda & Jadwal`, lalu `Panitia & Akses` sesuai dependency operasional.
- Menu `Agenda & Jadwal` memakai dua tab setara: `Agenda` untuk blok kegiatan dan `Jadwal` untuk penempatan pertandingan.
- Tab aktif wajib memiliki nomor, judul, penjelasan singkat, dan indikator bawah biru; tab tidak memakai pill berlebihan.
- Tab `Agenda` hanya menampilkan daftar agenda dan tombol `Tambah Agenda`; form tambah/edit wajib memakai modal light.
- Tabel Agenda wajib langsung terlihat setelah header daftar dan memiliki filter status `Terpublikasi`/`Draft`, pencarian, serta per halaman.
- Tabel Agenda memakai tree table per tanggal: parent menampilkan jumlah agenda, venue, terpublikasi, dan draft; children menampilkan detail agenda pada tanggal tersebut.
- Agenda memakai status Draft, Aktif, dan Nonaktif. Agenda nonaktif tidak tampil pada publik atau pilihan penempatan pertandingan.
- Aksi child mengikuti standar ikon `Show`, `Edit`, `Publish`, dan `Delete`; delete hanya aktif untuk agenda draft tanpa pertandingan dan wajib memakai modal konfirmasi.
- Tab `Jadwal` hanya menampilkan form penempatan pertandingan dan tabel status pertandingan terjadwal/belum terjadwal.
- Pemilihan agenda pada jadwal wajib disaring berdasarkan kompetisi dan cabor pertandingan.
- Overview maksimal tiga statistik: total agenda, agenda terpublikasi, dan pertandingan terjadwal.
- Badge agenda dan jadwal memakai radius control, bukan bentuk kapsul penuh.

### Pengguna dan Akses Panitia

- Menu `Pengguna` berada pada grup `Sistem` dan dipakai untuk membuat akun login internal.
- Form tambah/edit pengguna memakai modal light dan menyediakan Admin, Scorekeeper, serta Koordinator Cabor. Role Super Admin tidak diekspos pada daftar, statistik, filter, atau form.
- Halaman menampilkan statistik akun internal, Super Admin, Admin, dan Panitia; tabel menampilkan identitas, role, assignment, status login, tanggal dibuat, serta aksi ikon Show/Edit/Delete.
- Akun internal dapat diedit dan dinonaktifkan melalui modal yang sama; perubahan password bersifat opsional saat edit.
- Modal Show menampilkan identitas, status login, hak akses, assignment aktif, dan tanggal dibuat tanpa membuka form edit otomatis.
- Delete memakai modal konfirmasi bahaya dan ditolak bila akun sudah memiliki histori aktivitas.
- Halaman memakai tab `Admin & Panitia` dan `Pengurus Daerah`; query, pencarian, status, dan pagination mengikuti tab aktif tanpa mencampur data.
- Kartu `Kendali Identitas`, tab, header tabel, dan tombol tambah mengikuti hierarchy visual `Agenda & Jadwal`; overview maksimal tiga statistik sesuai tab aktif.
- Tombol `Tambah Pengguna` berada pada header tabel tab Admin & Panitia, bukan pada kartu overview.
- Filter tabel kembali memakai status `Aktif` dan `Tidak Aktif`, bukan role.
- Tab Pengurus Daerah bersifat read-only dan menyediakan akses menuju menu Verifikasi Pengurus Daerah.
- Super Admin memiliki seluruh aksi. Admin hanya melihat detail akun lain dan mengedit akun sendiri.
- Aksi Aktif/Nonaktif memakai ikon daya pada tabel dan hanya tampil untuk Super Admin; akun sendiri dan akun PD tidak dapat diubah melalui aksi ini.
- Akun sendiri tidak dapat dinonaktifkan, diubah role, atau dihapus.
- Menu `Panitia & Akses` tidak membuat akun; menu ini hanya menetapkan atau mencabut assignment cabor dan venue.
- Akun Pengurus Daerah tetap berasal dari registrasi dan verifikasi daerah, bukan dari menu Pengguna.

### Layout

- Admin dan PD memakai token, card, tabel, form, modal, badge, pagination, dan focus state yang sama.
- Portal lebih utilitarian, bukan dekoratif.
- Sidebar/topbar konsisten.
- Background portal `#eef3f6`, surface putih, header card `#f7f9fa`.
- Radius card `14px`; radius control `8px`.
- Form area jelas: label, input, help text, error.
- Table admin lebih padat dari public, tapi tetap readable.

### Form

- Field wajib punya label.
- Error tampil dekat field.
- Submit disabled saat loading.
- Write action berisiko wajib confirmation.

### Status Registrasi PD

- Status registrasi terkunci memakai card informasi portal, bukan alert polos: badge status, judul, penjelasan singkat, dan langkah berikutnya tersusun dengan hierarchy jelas.
- Panel `pending` memakai token portal terang, border biru-abu, aksen status kuning lembut, radius `14px`, dan spacing `16-20px`; tidak memakai warna atau shadow baru.
- Informasi sisa kuota menjadi metadata pendukung, bukan CTA, selama parent masih terkunci.
- Tombol `Tambah Tim` diletakkan pada header kelompok team agar hubungan aksinya jelas.
- Jika kuota team `1`, tombol tidak ditampilkan setelah team pertama tersedia.
- Jika kuota team lebih dari `1`, tombol `Tambah Tim {n}` tetap tampil selama slot tersedia dan registrasi masih terbuka, termasuk saat parent pending atau verified. Form team baru dibuka setelah tombol dipilih; team dan official existing tetap read-only.
- Halaman Verifikasi Peserta tidak menampilkan tombol persetujuan parent. Setelah seluruh pemain valid, Admin menyetujui team; persetujuan team terakhir menyelesaikan pendaftaran otomatis dan menghapusnya dari antrian.
- Setelah registrasi ditutup, form team baru hanya tampil bila Admin membuka izin khusus penambahan team.
- Disabled hanya dipakai selama request diproses. Kondisi yang tidak dapat dilakukan karena aturan bisnis disembunyikan dan dijelaskan melalui teks kapasitas.
- Periode registrasi pada tabel Data Lomba memakai format ringkas `01 Jul, 2026` tanpa jam. Waktu audit tetap memakai tanggal dan jam lengkap.

### Table

- Server-side pagination.
- Filter sticky di atas table.
- Bulk action tidak muncul jika belum ada selection.
- Status memakai badge konsisten lintas Public, Admin, PD, dan Panitia sesuai `docs/02-data/STATUS-VOCABULARY.md`; satu kode memakai label dan tone sama pada semua layar.
- Teks progres atau penyebab seperti `Pemain belum lengkap` ditempatkan sebagai metadata terpisah, bukan mengganti label badge.
- Semua daftar data utama memakai pola visual `AdminDataTable`.
- Daftar utama PD memakai `AdminDataTable`; tabel detail roster mengikuti token tabel yang sama.
- Search memakai debounce 300 ms untuk request server.
- Pilihan per halaman konsisten: `10`, `25`, `50`, `100`.
- Footer menampilkan rentang data, total data, halaman aktif, dan total halaman.
- Navigasi pagination memakai tombol `Sebelumnya` dan `Berikutnya`.

### Filter dan Modal

- Toolbar filter memakai dasar putih atau abu muda `#f8fafb` dengan border `#d9e3e9`.
- Urutan kontrol: `Pencarian`, `Filter`, lalu `Per Halaman`.
- Semua modal memakai komponen `Modal.vue`; modal custom per halaman dilarang.
- Modal Public memakai tema dark navy yang senada dengan portal publik.
- Modal Admin dan PD memakai `theme="light"` dengan dasar putih, header `#f7f9fb`, dan backdrop navy transparan.
- Tombol primary memakai biru, secondary putih outline, dan danger merah lembut.

### Aksi Data

- Aksi `Lihat`, `Edit`, `Aktifkan/Nonaktifkan`, dan `Hapus` memakai tombol ikon konsisten dengan tooltip serta `aria-label`.
- Tombol status berada pada kolom aksi: data aktif menampilkan aksi `Nonaktifkan`, data nonaktif menampilkan aksi `Aktifkan`.
- Data aktif tidak dapat dihapus.
- Data nonaktif hanya dapat dihapus bila tidak memiliki relasi; UI menonaktifkan tombol dan server tetap wajib menolak request ilegal.

### Score Input

- Panitia hanya melihat match yang ditugaskan.
- Input skor setelah pertandingan selesai.
- Tombol submit punya loading state.
- Koreksi skor wajib alasan.
- Perubahan winner wajib preview downstream impact.

## Component Rules

- Buat component jika dipakai minimal 2 tempat.
- Jangan buat abstraction untuk satu pemakaian.
- Nama component harus domain-based: `MatchCard`, `StatusBadge`, `SectionTitle`.
- Component public dan admin boleh beda jika kebutuhan UX beda.

## UI Review Checklist

- [ ] Active state jelas.
- [ ] Empty/loading/error state ada.
- [ ] Spacing antar card lega.
- [ ] Text tidak tertutup mascot/card.
- [ ] Data panjang pakai ellipsis + title/detail.
- [ ] Mobile tidak rusak total.
- [ ] Tidak ada warna/shape baru tanpa alasan.
- [ ] Kuota null tampil sebagai `Tidak dibatasi` di Admin dan `Minimal {n} pemain` di publik/PD.
- [ ] Informasi teknis cabor mudah dipindai dan ikon hanya tampil bila aset tersedia.
- [ ] Master Venue memakai tabel, pencarian, per-page, pagination, dan modal CRUD.
- [ ] Detail venue publik menyediakan tautan Google Maps yang dapat dibuka pada perangkat peserta.
