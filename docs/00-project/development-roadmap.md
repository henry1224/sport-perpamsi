# Roadmap Pengembangan Terkendali

Setiap phase memakai branch terpisah dan mengikuti `AGENTS.md` serta Git workflow.

## Phase 0 — Baseline dan Dokumen

- [x] Portal publik, seminar, login, dashboard dasar, registrasi legacy, verifikasi dasar, skor dasar.
- [x] Dokumen target PD PERPAMSI dan risk register.
- [ ] Tandai implementasi PDAM saat ini sebagai legacy sampai migration selesai.

Exit: dokumen, ERD, migration, RBAC, test, UAT, dan risiko konsisten.

## Phase 1 — Pengajuan Akun PD

- [x] `committee_applications` dan status akun.
- [x] Form Daftar Pengurus Daerah.
- [x] Unique pengajuan aktif per provinsi.
- [x] Verifikasi, perbaikan, penolakan, alasan, audit.
- [x] Middleware status akun.

Exit: akun belum terverifikasi tidak dapat masuk; race condition provinsi diuji.

## Phase 2 — Registrasi Cabor dan Pemain

- [ ] Migrasi `event_entries` dari PDAM ke PD PERPAMSI.
- [ ] Tabel `entry_members`.
- [ ] Validasi jumlah/duplikasi pemain dari peraturan.
- [ ] Workflow draft, submit, perbaikan, verifikasi, pembatalan.

Exit: PD dapat mendaftarkan pemain tanpa instansi asal; data legacy terverifikasi aman.

## Phase 3 — Master Admin

- [ ] Master cabor dan kategori.
- [ ] Peraturan berversi.
- [ ] Kompetisi dan status pendaftaran.
- [ ] Venue lengkap.
- [ ] Agenda/jadwal, publikasi, dan konflik waktu.
- [ ] Kamus label status Indonesia.

Exit: CRUD, seed ulang, audit, dan restrict delete lulus.

## Phase 4 — Panitia dan Assignment

- [ ] Akun panitia.
- [ ] Assignment per cabor/match.
- [ ] Policy dan menu berbasis scope.
- [ ] Penonaktifan akses dan audit role.

Exit: horizontal access test lulus.

## Phase 5 — Kompetisi dan Skor

- [ ] Seeding/grup/bracket manager.
- [ ] Precondition bracket lock.
- [ ] Input skor idempotent.
- [ ] Finalisasi dan revisi beralasan.
- [ ] Klasemen cabor dan medali PD PERPAMSI.

Exit: seluruh hasil dapat ditelusuri ke entry, aturan, aktor, dan audit.

## Phase 6 — Operasional

- [ ] Import/export aman.
- [ ] Laporan dan audit.
- [ ] Cache invalidation.
- [ ] Load test.
- [ ] Backup/restore.
- [ ] UAT dan training.

Exit: tidak ada risiko kritis terbuka dan seluruh P0/P1 lulus.
