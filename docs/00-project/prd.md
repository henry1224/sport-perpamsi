# PRD Sport PERPAMSI

## Visi

Platform resmi PORPAMNAS untuk pendaftaran PD PERPAMSI, pemain, master lomba, panitia, agenda, pertandingan, hasil, dan informasi publik dalam satu sumber data.

## Pengguna

- Public.
- Pengurus Daerah.
- Super Admin/Admin Event.
- Verifikator Peserta.
- Koordinator Cabor.
- Scorekeeper.
- Auditor/Content Officer.

## Identitas Peserta

- Satu provinsi memiliki satu `PD PERPAMSI {Nama Provinsi}`.
- Tidak ada PDAM atau instansi asal dalam alur registrasi peserta.
- Nama PD dipakai pada peserta publik, bracket, hasil, klasemen, dan laporan.
- Registrasi publik membuat pengajuan akses PD, bukan membuat PD duplikat.

## Modul

### Public

- Home, agenda, seminar, cabor/peraturan, venue, peserta PD, bracket, hasil, dan klasemen.
- Hanya data terbit/final; tanpa data pribadi atau kode status mentah.

### Daftar dan Masuk

- Pilihan Masuk dan Daftar Pengurus Daerah.
- Pengajuan provinsi, penanggung jawab, jabatan, kontak, password, dan mandat opsional.
- Status: Menunggu Verifikasi, Perlu Perbaikan, Terverifikasi, Ditolak.
- Hanya akun terverifikasi/aktif dapat masuk portal.

### Portal Pengurus Daerah

- Dashboard PD.
- Pilih paket kompetisi/cabor/kategori yang sudah ditetapkan dan dipublikasikan Admin.
- Buat registrasi dan daftar pemain dinamis.
- Lihat status dan catatan verifikasi.
- Kelola pengguna tambahan melalui persetujuan/undangan.

### Admin

- Dashboard.
- Verifikasi Pengurus Daerah dan peserta.
- Master cabor, kategori, versi peraturan, kompetisi, venue, agenda/jadwal.
- Panitia dan assignment.
- Pertandingan, skor, finalisasi, revisi, laporan, dan audit.

### Panitia

- Dashboard sesuai assignment.
- Kelola cabor/jadwal/match sesuai permission.
- Input skor, finalisasi, atau view sesuai peran.

## Aturan Produk

1. Satu pengajuan aktif per provinsi.
2. Scope PD/panitia ditentukan server.
3. Jumlah pemain mengikuti versi peraturan kompetisi.
4. Master kategori tidak tampil langsung di portal PD; hanya kompetisi terpublikasi yang tampil.
5. Publikasi mengunci snapshot format, kategori, tipe skor, dan batas pemain.
6. Master yang sudah dipakai tidak boleh dihapus.
7. Venue tidak boleh bentrok waktu.
8. Bracket tidak dapat dikunci sebelum verifikasi selesai.
9. Revisi hasil final wajib alasan, approval, dan audit.
10. Klasemen hanya memakai hasil final/terverifikasi.
11. UI/export memakai label Indonesia; kode database tetap stabil.
12. Seeder tidak menimpa data operasional.

## Non-Functional

- Backend authorization pada setiap write.
- PostgreSQL constraint untuk integritas kritis.
- Pagination, index, cache, throttle, dan debounce untuk publik.
- Backup sebelum migration dan restore test sebelum go-live.
- Audit append-only untuk tindakan penting.
- Responsive dan aksesibel.

## Acceptance Criteria

- Alur daftar PD sampai verifikasi berjalan end-to-end.
- PD dapat mendaftarkan cabor dan pemain tanpa memilih instansi asal.
- PD hanya melihat dan memakai kompetisi yang telah dipublikasikan Admin.
- Perubahan master kategori tidak mengubah regulasi kompetisi terpublikasi.
- Admin dapat mengelola seluruh master dan assignment.
- Panitia tidak dapat keluar dari scope tugas.
- Agenda menolak bentrok venue.
- Tidak ada kode status mentah pada UI.
- Semua risiko kritis/tinggi memiliki kontrol dan bukti test/UAT.
- Kategori tanpa batas maksimum menerima roster di atas minimum, sedangkan kategori berbatas tetap menolak kelebihan pemain.
- Halaman Cabor publik menampilkan informasi teknis panduan slide 5–23 dan tidak menghitung official sebagai pemain.

## Dependency Dokumen

- Data: `docs/02-data/delegation-standard.md`.
- Alur: `docs/00-project/application-flow.md`.
- Publikasi registrasi: `docs/03-product/registration-publication-standard.md`.
- Informasi teknis cabor: `docs/03-product/sport-technical-guide-standard.md`.
- Security: `docs/06-security/rbac-matrix.md` dan `risk-register.md`.
- Test: `docs/08-testing/test-strategy.md` dan `uat-checklist.md`.
