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
- [x] Pengurus Daerah hanya melihat kompetisi yang dipublikasikan Admin.
- [-] Pengurus Daerah dapat menambah dan mengajukan daftar pemain; edit draft belum tersedia.
- [x] Jumlah dan duplikasi pemain divalidasi sesuai snapshot regulasi kompetisi.
- [x] Status registrasi dan event pada portal PD tampil dalam Bahasa Indonesia.

## Master Admin

- [x] Admin dapat mengelola cabor dan kategori tanpa menghapus histori.
- [x] Admin dapat membuat versi peraturan dan menetapkannya ke kompetisi melalui preview publikasi.
- [x] Admin dapat mempublikasikan dan menutup registrasi kompetisi.
- [x] Admin dapat preview, menarik publikasi sebelum ada entry, dan melihat audit publikasi.
- [-] Admin dapat menambah venue beserta detail dan status aktif; edit/nonaktif UI masih dilanjutkan.
- [-] Admin dapat menambah dan mempublikasikan agenda tanpa bentrok venue/waktu; edit dan audit revisi masih dilanjutkan.
- [ ] Admin dapat mengelola kompetisi dan status pendaftaran.
- [x] Admin dapat melihat, menyetujui, atau menolak registrasi serta pemain per PD PERPAMSI.
- [x] PD dapat menyimpan draft, mengirim roster, memperbaiki sesuai catatan, mengirim ulang, dan membatalkan registrasi.
- [x] Admin dapat meminta perbaikan roster dengan catatan.

## Panitia

- [x] Admin dapat membuat akun dan assignment panitia per cabor dan venue.
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
- [x] Perubahan master kategori tidak mengubah snapshot kompetisi terpublikasi.
- [ ] `git diff`, test, build, migration upgrade, dan security review lulus sebelum merge.
