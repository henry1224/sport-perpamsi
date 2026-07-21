# UAT Checklist

## Registrasi Pengurus Daerah

- [ ] Pengguna dapat memilih Masuk atau Daftar.
- [ ] Pengguna dapat mengajukan akses untuk satu provinsi.
- [ ] Pengajuan provinsi yang sedang diproses tidak dapat diduplikasi.
- [ ] Akun belum terverifikasi tidak dapat masuk portal.
- [ ] Admin dapat verifikasi, minta perbaikan, atau menolak dengan alasan.
- [ ] Aktivitas verifikasi tercatat pada audit.

## Portal Pengurus Daerah

- [ ] Nama portal memakai `PD PERPAMSI {provinsi}`.
- [ ] Pengurus Daerah hanya melihat data PD sendiri.
- [ ] Pengurus Daerah dapat memilih cabor/kategori yang dibuka.
- [ ] Pengurus Daerah dapat menambah, mengubah, dan mengajukan daftar pemain sebelum batas waktu.
- [ ] Jumlah dan duplikasi pemain divalidasi sesuai peraturan.
- [ ] Status tampil dalam Bahasa Indonesia.

## Master Admin

- [ ] Admin dapat mengelola cabor dan kategori tanpa menghapus histori.
- [ ] Admin dapat membuat versi peraturan dan menetapkannya ke kompetisi.
- [ ] Admin dapat mengelola venue beserta detail dan status aktif.
- [ ] Admin dapat mengelola agenda tanpa bentrok venue/waktu.
- [ ] Admin dapat mengelola kompetisi dan status pendaftaran.
- [ ] Admin dapat melihat registrasi serta pemain per PD PERPAMSI.

## Panitia

- [ ] Admin dapat membuat akun dan assignment panitia per cabor.
- [ ] Panitia hanya melihat cabor/match tugasnya.
- [ ] Scorekeeper dapat input skor match tugasnya.
- [ ] Koordinator dapat finalisasi sesuai permission.
- [ ] Akun dinonaktifkan langsung kehilangan akses.

## Public dan Hasil

- [ ] Public melihat agenda terbit, venue, cabor, bracket, hasil, dan klasemen.
- [ ] Nama peserta publik memakai `PD PERPAMSI {provinsi}`.
- [ ] Draft dan data pribadi tidak tampil publik.
- [ ] Klasemen hanya memakai hasil final/terverifikasi.
- [ ] Revisi hasil memperbarui public dan tetap memiliki audit.

## Data dan Operasional

- [ ] Seeder ulang tidak menimpa perubahan Admin.
- [ ] Import memiliki preview dan rollback saat invalid.
- [ ] Backup dan restore sudah diuji.
- [ ] Tidak ada status kode mentah pada UI/export.
- [ ] `git diff`, test, build, migration upgrade, dan security review lulus sebelum merge.
