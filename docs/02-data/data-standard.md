# Data Standard Sport PERPAMSI

## Single Source of Truth

- Aplikasi menjadi sumber resmi untuk jadwal, peserta, skor, hasil final, bracket, klasemen, dan ranking.
- Data public hanya berasal dari data published atau final.
- Data draft tidak muncul di public.
- Hasil final hanya dikunci oleh role berwenang.

## Entitas Utama

- Event.
- PDAM.
- Cabang olahraga.
- Kategori.
- Venue.
- Tim.
- Atlet.
- Dokumen.
- Match.
- Score.
- Bracket.
- Klasemen.
- Ranking.
- User.
- Role.
- Assignment panitia.
- Audit log.

## Relasi Inti

- Event memiliki banyak cabor, venue, jadwal, tim, match, dokumen, dan konten.
- PDAM memiliki banyak tim, atlet, dokumen, dan hasil pertandingan.
- Provinsi memiliki banyak kabupaten/kota dan PDAM.
- Kabupaten/kota memiliki banyak PDAM.
- Cabor memiliki banyak kategori, tim, match, bracket, dan klasemen.
- Tim dimiliki satu PDAM dan mengikuti satu cabor/kategori pada event.
- Atlet dimiliki satu PDAM dan dapat terhubung ke satu atau lebih tim sesuai aturan event.
- Match terhubung ke event, cabor, kategori, venue, dua peserta, skor, status, dan pemenang.
- Bracket terhubung ke match sebelumnya dan match berikutnya.
- Klasemen dihitung dari hasil match final.
- Audit log terhubung ke aktor, aksi, dan entitas yang berubah.

## Status Data

- Peserta: draft, diajukan, diverifikasi, ditolak, revisi.
- Match: draft, terjadwal, berlangsung, jeda, selesai, final, revisi.
- Konten: draft, published, archived.
- Dokumen: pending, valid, rejected, revision_required.

## Import dan Export

- Data awal PDAM, tim, atlet, dan jadwal dapat diimpor dari CSV atau Excel.
- Import wajib punya preview sebelum simpan.
- Baris invalid ditolak dengan alasan error.
- Export tersedia untuk peserta, jadwal, hasil, ranking.
- Format template import harus dikunci sebelum pengumpulan data.

## Audit

- Audit wajib untuk skor, jadwal, verifikasi, assignment panitia, finalisasi, revisi.
- Audit mencatat aktor, aksi, waktu, entitas, nilai sebelum, nilai sesudah.
- Audit log append-only.
- Public hanya melihat status final, bukan audit internal.

## Kualitas Data

- Nama PDAM harus konsisten.
- Cabor dan kategori harus dikunci sebelum jadwal dibuat.
- Jadwal tidak boleh bentrok venue dan waktu.
- Tim tidak boleh tampil di public sebelum diverifikasi.
- Ranking hanya dihitung dari match final.
- Ranking wilayah dihitung dari akumulasi medali/hasil PDAM yang terhubung ke provinsi dan kabupaten/kota.

## Addendum v2: Data Public Besar, Pagination, Filter, dan SSR

### Pagination Public

- Semua list besar wajib server-side pagination.
- Halaman public PDAM default: 24 atau 36 item per page.
- Ranking default: 50 row per page.
- Round awal bracket default: 24 match per round, dengan pagination/filter.
- Query parameter standar:

```text
?page=1&per_page=24&search=&sport=&category=&province=&regency=&sort=
```

### Search dan Filter

- Frontend wajib debounce minimal 400 ms.
- Backend wajib throttle public search.
- Search tidak boleh request per keypress tanpa debounce.
- Filter harus mempertahankan URL agar SSR dan share link tetap benar.
- Empty query tampilkan data default cacheable.

### Cache

- Public home, cabor, venue, dan ranking cache pendek: 30-120 detik.
- Bracket cache per `tournament_event_id` dan invalidasi saat skor final/verified berubah.
- Admin tidak memakai cache public untuk write flow.

### Naming Public

- Public list tampilkan `display_name` ringkas.
- Nama lengkap tetap tersedia di detail/tooltip.
- Prefix legal seperti `PDAM`, `Perumda`, `Perumdam`, `PT` boleh disembunyikan di display publik bila membuat card terlalu panjang.

### Data Lock

- `registration_open`: data peserta bisa berubah.
- `registration_closed`: data diverifikasi.
- `bracket_locked`: seed dan bracket tidak berubah tanpa role super admin.
- `running`: hanya skor dan status match yang berubah.
- `completed`: semua perubahan wajib lewat koreksi audit.
