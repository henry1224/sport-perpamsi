# Test Strategy Sport PERPAMSI

Setiap kontrol kritis/tinggi pada [risk-register.md](../06-security/risk-register.md) wajib memiliki test atau bukti UAT.

## Backend Feature Tests

1. Satu provinsi hanya memiliki satu PD PERPAMSI.
2. Dua pengajuan aktif untuk provinsi sama ditolak, termasuk request bersamaan.
3. Akun menunggu, ditolak, nonaktif, atau ditangguhkan tidak dapat mengakses portal.
4. Pengguna tidak dapat mengganti provinsi atau PD melalui request manual.
5. Penolakan/perbaikan pengajuan wajib alasan dan audit.
6. Pengurus Daerah hanya mengelola registrasi milik PD sendiri.
7. Registrasi ditolak saat pendaftaran ditutup.
8. Pemain duplikat dan jumlah pemain di luar batas aturan ditolak.
9. Kategori/peraturan yang sudah dipakai tidak dapat diubah tanpa workflow versi.
10. Bracket tidak dapat dikunci jika verifikasi belum selesai.
11. Venue dan waktu agenda yang bertabrakan ditolak.
12. Panitia tidak dapat mengakses cabor atau match di luar assignment.
13. Scorekeeper tidak dapat mengubah hasil final langsung.
14. Revisi final wajib alasan, approval, dan audit.
15. Klasemen hanya memakai hasil final/terverifikasi.
16. Public tidak melihat draft, data pribadi, ID internal, atau audit.
17. Seeder dapat dijalankan ulang tanpa menimpa data operasional.

## Frontend/E2E

1. Pengguna memilih Masuk atau Daftar Pengurus Daerah.
2. Pengajuan baru menampilkan status Indonesia dan tidak langsung membuka portal.
3. Admin memverifikasi, meminta perbaikan, atau menolak pengajuan.
4. Pengurus Daerah memilih cabor dan memasukkan pemain sesuai kategori.
5. Admin mengelola cabor, kategori, peraturan, venue, agenda, dan assignment panitia.
6. Panitia hanya melihat cabor dan pertandingan tugasnya.
7. Public melihat nama `PD PERPAMSI {provinsi}` pada peserta, bracket, hasil, dan klasemen.
8. Tidak ada kode mentah seperti `registration_open` atau `bracket_locked` pada UI/export.

## Load dan Concurrency

- Submit pengajuan provinsi sama secara bersamaan.
- 1.000 pengguna publik membuka agenda/hasil.
- 100 panitia login.
- 50 scorekeeper memperbarui match berbeda.
- Double submit skor tidak membuat dua hasil.

## Exit Criteria

- Semua test P0/P1 lulus.
- Tidak ada risiko kritis terbuka.
- Risiko tinggi memiliki kontrol dan bukti test/UAT.
- Migration fresh, migration upgrade, seed ulang, dan restore test lulus.
