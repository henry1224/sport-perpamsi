# Test Strategy Sport PERPAMSI

Setiap kontrol kritis/tinggi pada [risk-register.md](../06-security/risk-register.md) wajib memiliki test atau bukti UAT.

## Phase 5

- Feature test memastikan hanya Admin mengelola venue/agenda.
- Feature test memastikan rentang waktu bertabrakan pada venue dan tanggal sama ditolak.
- Migration PostgreSQL dan build frontend wajib lulus sebelum merge.
- Feature test memastikan panitia tanpa assignment melihat daftar kosong dan URL di luar scope menghasilkan `403`.

## Backend Feature Tests

1. Satu provinsi hanya memiliki satu PD PERPAMSI.
2. Dua pengajuan aktif untuk provinsi sama ditolak, termasuk request bersamaan.
3. Akun menunggu, ditolak, nonaktif, atau ditangguhkan tidak dapat mengakses portal.
4. Pengguna tidak dapat mengganti provinsi atau PD melalui request manual.
5. Penolakan/perbaikan pengajuan wajib alasan dan audit.
6. Pengurus Daerah hanya mengelola registrasi milik PD sendiri.
7. Registrasi ditolak saat pendaftaran ditutup.
8. Pemain duplikat dan jumlah pemain di luar batas aturan ditolak.
9. Registrasi baru menyimpan `pdam_id` null, key PD/event unik, dan roster secara transaksional.
10. Portal PD hanya memuat kompetisi yang dipublikasikan Admin.
11. Route dan submit kompetisi unpublished ditolak.
12. Validasi roster memakai snapshot walau master kategori berubah.
13. Publish ditolak untuk kategori tidak aktif/tidak cocok dan periode invalid.
14. Kategori/peraturan yang sudah dipakai tidak dapat diubah tanpa workflow versi.
15. Format Kompetisi dapat diubah saat draft dan ditolak setelah publikasi.
16. Seed kategori, roster, Format Bawaan, dan sistem skor sesuai panduan teknis slide 5-23.
17. Bracket tidak dapat dikunci jika verifikasi belum selesai.
18. Venue dan waktu agenda yang bertabrakan ditolak.
19. Panitia tidak dapat mengakses cabor atau match di luar assignment.
20. Scorekeeper tidak dapat mengubah hasil final langsung.
21. Revisi final wajib alasan, approval, dan audit.
22. Klasemen hanya memakai hasil final/terverifikasi.
23. Public tidak melihat draft, data pribadi, ID internal, atau audit.
24. Seeder dapat dijalankan ulang tanpa menimpa data operasional.
25. Hanya Admin terverifikasi dapat membuat akun panitia serta menetapkan atau mencabut assignment cabor dan venue.
26. Admin dapat mengelola cabor/kategori, regulasi selalu membuat versi baru, dan seluruh perubahan master tercatat audit.
27. Publish wajib memakai regulasi dari cabor yang sama, snapshot menyimpan versinya, dan unpublish ditolak setelah entry tersedia.
28. PD dapat menyimpan draft, submit, memperbaiki, mengirim ulang, dan membatalkan roster sendiri tanpa menghapus histori.
29. PD tidak dapat mengubah atau membatalkan roster milik PD lain maupun roster pending/verified.

## Frontend/E2E

1. Pengguna memilih Masuk atau Daftar Pengurus Daerah.
2. Pengajuan baru menampilkan status Indonesia dan tidak langsung membuka portal.
3. Admin memverifikasi, meminta perbaikan, atau menolak pengajuan.
4. Admin preview dan mempublikasikan paket kompetisi yang sudah fix.
5. Pengurus Daerah hanya memilih kompetisi terpublikasi dan memasukkan pemain sesuai snapshot.
6. Admin mengelola cabor, kategori, peraturan, venue, agenda, dan assignment panitia.
7. Panitia hanya melihat cabor dan pertandingan tugasnya.
8. Public melihat nama `PD PERPAMSI {provinsi}` pada peserta, bracket, hasil, dan klasemen.
9. Tidak ada kode mentah seperti `registration_open` atau `bracket_locked` pada UI/export.

## Load dan Concurrency

- Submit pengajuan provinsi sama secara bersamaan.
- 1.000 pengguna publik membuka agenda/hasil.
- 100 panitia login.
- 50 scorekeeper memperbarui match berbeda.
- Double submit skor tidak membuat dua hasil.

## Exit Criteria

### Bukti Phase 5

- Venue dapat dinonaktifkan dan tidak tersedia untuk agenda baru.
- Update agenda terpublikasi membutuhkan alasan dan menghasilkan audit before/after dengan aktor.
- Publikasi agenda menghasilkan audit append-only.
- Upgrade migration menyisakan nol kategori nonaktif dan tidak menghapus kategori aktif.

- Semua test P0/P1 lulus.
- Tidak ada risiko kritis terbuka.
- Risiko tinggi memiliki kontrol dan bukti test/UAT.
- Migration fresh, migration upgrade, seed ulang, dan restore test lulus.
## Koreksi Tipe Master Cabor

- Feature test memastikan Padel, Golf, dan Vokal hasil seed bertipe `exhibition`.
- Feature test membandingkan format bawaan seluruh cabor dengan katalog resmi.
