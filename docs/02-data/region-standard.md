# Region Standard

## Master Wilayah

- `provinces` adalah sumber nama provinsi resmi.
- `regencies` tetap tersedia untuk kebutuhan alamat/venue, bukan identitas peserta.
- Satu `regional_committee` wajib terhubung unik ke satu provinsi.

## Nama PD PERPAMSI

- Dibentuk server: `PD PERPAMSI {provinces.name}`.
- Tidak dapat diedit bebas oleh Pengurus Daerah.
- Dipakai pada portal, peserta, bracket, hasil, klasemen, dan laporan.

## Pengajuan

- Pengguna memilih provinsi dari master aktif.
- Sistem mengarahkan pengajuan ke PD yang sudah ada.
- Unique pengajuan aktif mencegah klaim provinsi ganda.
- Perubahan master wilayah dilakukan Admin dan diaudit.

## Data Legacy

Tabel PDAM dan relasi kabupaten/kota yang sudah ada diperlakukan sebagai legacy selama transisi. Keduanya tidak menjadi bagian alur registrasi target dan tidak dihapus sebelum audit migrasi selesai.
