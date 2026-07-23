# Peta Aplikasi dan Alur End-to-End

## Sumber Kebenaran

- Identitas dan registrasi: [delegation-standard.md](../02-data/delegation-standard.md).
- Risiko: [risk-register.md](../06-security/risk-register.md).
- Status implementasi: `Done`, `Partial`, `Planned`.

## Gambaran

```text
MASTER
Provinsi ── PD PERPAMSI ── Pengurus Daerah terverifikasi
             │
             └── Registrasi Cabor ── Pemain
                         │
                         ├── Verifikasi
                         ├── Seed/Bracket/Grup
                         ├── Agenda/Venue/Match
                         └── Skor/Finalisasi/Audit
                                      │
                                      ▼
PUBLIC: agenda, peserta PD PERPAMSI, bracket, hasil, klasemen
```

## Alur Utama

### 1. Master Admin

1. Sistem menyediakan master provinsi dan satu PD PERPAMSI per provinsi.
2. Admin mengelola cabor, kategori, versi peraturan, venue, agenda, dan kompetisi berstatus draft.
3. Admin mempublikasikan hanya paket kompetisi yang sudah fix; publikasi menyimpan snapshot regulasi.
4. Master yang sudah dipakai hanya dapat dinonaktifkan/diarsipkan.
5. Seeder baseline tidak menimpa data operasional; seeder demo hanya berjalan pada local/testing.

Status: `Partial` — tabel dan seeder dasar tersedia; CRUD Admin belum lengkap.

### 2. Daftar dan Verifikasi Pengurus Daerah

1. Pengguna memilih `Masuk` atau `Daftar Pengurus Daerah`.
2. Pendaftaran membuat pengajuan akses ke PD PERPAMSI provinsi terpilih.
3. Sistem menolak pengajuan aktif duplikat untuk provinsi sama.
4. Admin memverifikasi, meminta perbaikan, atau menolak dengan alasan.
5. Hanya akun terverifikasi yang dapat masuk portal PD.

Status: `Done` — daftar, status pengajuan, verifikasi/perbaikan/penolakan Admin, audit, unique pengajuan aktif, dan blokir akun belum terverifikasi tersedia.

### 3. Registrasi Cabor, Team, dan Pemain

1. Pengurus Daerah hanya melihat kompetisi/cabor/kategori yang dipublikasikan Admin.
2. Technical meeting menetapkan unit peserta, batas team per PD, dan anggota per team sebelum publish.
3. Sistem membuat satu parent `EventEntry` atas nama PD PERPAMSI.
4. PD membuat `EntryTeam` sampai batas snapshot dan memasukkan pemain per team.
5. Label team dibentuk server sebagai `PD PERPAMSI {provinsi} #{team_no}`.
6. Admin memverifikasi default parent dan dapat memberi override team tertentu.
7. Hanya team dengan effective status verified masuk seed, grup, bracket, match, dan klasemen.
8. Perpindahan pemain antar-team setelah verified dilarang total.

Status: `Code Complete, UAT Pending` — parent/team, verifikasi hybrid, kuota snapshot, identity hash, dan roster immutable tersedia; UAT manual serta review commit masih wajib. Sumber: `docs/02-data/team-entry-standard.md`.

### 4. Penugasan Panitia

1. Admin membuat akun panitia.
2. Admin memberi assignment cabor atau match.
3. Policy backend membatasi menu dan action berdasarkan assignment.
4. Penonaktifan akun langsung memblokir akses.

Status: `Partial` — assignment cabor/venue + policy scope panitia tersedia dan diuji (`SportAssignmentTest`, `StaffMatchScopeTest`); menu panitia dan penonaktifan session eksplisit belum lengkap. Detail drift pada `docs/00-project/audit-2026-07-22.md` D19.

### 5. Agenda dan Pertandingan

1. Admin/Koordinator menyusun agenda dan match pada venue aktif.
2. Sistem menolak bentrok venue/waktu.
3. Bracket hanya dapat dikunci setelah verifikasi registrasi selesai.
4. Scorekeeper memasukkan skor match tugasnya.
5. Finalisasi dan revisi memakai permission, alasan, dan audit.

Status: `Partial` — CRUD agenda tersedia, tetapi data operasional belum terhubung: match belum memiliki agenda/venue/jadwal, assignment panitia kosong, serta bracket lock dan revisi skor formal belum lengkap.

### 6. Publikasi

1. Public hanya membaca agenda terbit serta hasil final/terverifikasi.
2. Peserta, bracket, dan hasil kategori memakai `PD PERPAMSI {provinsi} #{team_no}`; klasemen agregat memakai nama PD tanpa nomor.
3. Draft, data pribadi, ID internal, dan audit tidak tampil publik.
4. Cache diinvalidasi saat publikasi/finalisasi/revisi.

Status: `Partial` — halaman publik tersedia dan identitas kompetisi memakai PD PERPAMSI; publikasi operasional masih perlu menghapus fallback data legacy.

## Menu Target

### Pengurus Daerah

- Ringkasan.
- Registrasi Cabor.
- Daftar Pemain.
- Status Verifikasi.
- Profil dan Pengguna PD.

### Admin

- Dashboard.
- Verifikasi Pengurus Daerah.
- Verifikasi Peserta.
- Master Cabor.
- Kategori.
- Regulasi.
- Master Venue.
- Data Lomba.
- Agenda dan Jadwal.
- Panitia dan Penugasan.
- Pertandingan dan Skor.
- Laporan dan Audit.

### Panitia

- Dashboard tugas.
- Cabor yang ditugaskan.
- Jadwal/match tugas.
- Input skor.
- Riwayat dan audit sesuai scope.

## Dependency Implementasi

1. Dokumen dan constraint.
2. Pengajuan/verifikasi akun PD.
3. Lengkapi CRUD Admin, preview, audit publish, dan label status Indonesia.
4. Assignment panitia dan policy.
5. Agenda, bracket lock, skor, audit, laporan.
6. UAT, load test, backup/restore, go-live.

## Alur Teknis Cabor Terpadu

Admin menetapkan master cabor, kategori, kuota, dan peraturan. Publikasi kompetisi membuat snapshot aturan; PD hanya mengirim pemain ke kompetisi terpublikasi; Admin memverifikasi; panitia bekerja sesuai assignment cabor/venue; hasil final mengalir ke publik. Informasi teknis publik mengikuti `docs/03-product/sport-technical-guide-standard.md`.
