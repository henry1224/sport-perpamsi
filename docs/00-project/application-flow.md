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

### 3. Registrasi Cabor dan Pemain

1. Pengurus Daerah hanya melihat kompetisi/cabor/kategori yang dipublikasikan Admin.
2. Sistem membuat registrasi atas nama PD PERPAMSI.
3. Pengurus Daerah memasukkan pemain sesuai snapshot regulasi yang sudah fix saat publikasi.
4. Admin/verifikator memeriksa dan memberi status Indonesia.
5. Hanya registrasi terverifikasi masuk kompetisi.

Status: `Partial` — filter publikasi, snapshot regulasi, periode, dan kontrol publish/tutup Admin tersedia; preview, audit event, serta revisi roster belum lengkap.

### 4. Penugasan Panitia

1. Admin membuat akun panitia.
2. Admin memberi assignment cabor atau match.
3. Policy backend membatasi menu dan action berdasarkan assignment.
4. Penonaktifan akun langsung memblokir akses.

Status: `Planned`.

### 5. Agenda dan Pertandingan

1. Admin/Koordinator menyusun agenda dan match pada venue aktif.
2. Sistem menolak bentrok venue/waktu.
3. Bracket hanya dapat dikunci setelah verifikasi registrasi selesai.
4. Scorekeeper memasukkan skor match tugasnya.
5. Finalisasi dan revisi memakai permission, alasan, dan audit.

Status: `Partial` — input skor dasar tersedia; CRUD agenda, assignment, lock, dan revisi formal belum lengkap.

### 6. Publikasi

1. Public hanya membaca agenda terbit serta hasil final/terverifikasi.
2. Peserta, bracket, hasil, dan klasemen memakai `PD PERPAMSI {provinsi}`.
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
- Kategori dan Peraturan.
- Kompetisi.
- Venue.
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
