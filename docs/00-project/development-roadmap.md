# Roadmap Pengembangan Terkendali

Setiap phase memakai branch terpisah dan mengikuti `AGENTS.md` serta Git workflow.

## Phase 0 — Baseline dan Dokumen

- [x] Portal publik, seminar, login, dashboard dasar, registrasi legacy, verifikasi dasar, skor dasar.
- [x] Dokumen target PD PERPAMSI dan risk register.
- [x] Tandai PDAM sebagai legacy; registrasi baru tidak menerima atau menulis instansi asal.

Exit: dokumen, ERD, migration, RBAC, test, UAT, dan risiko konsisten.

## Phase 1 — Pengajuan Akun PD

- [x] `committee_applications` dan status akun.
- [x] Form Daftar Pengurus Daerah.
- [x] Unique pengajuan aktif per provinsi.
- [x] Verifikasi, perbaikan, penolakan, alasan, audit.
- [x] Middleware status akun.

Exit: akun belum terverifikasi tidak dapat masuk; race condition provinsi diuji.

## Phase 2 — Master Cabor, Kategori, dan Regulasi

- [x] Master cabor dan kategori dari baseline seed.
- [x] Batas jumlah pemain pada kategori.
- [ ] CRUD Admin cabor dan kategori.
- [ ] Regulasi berversi dan dokumen technical meeting.
- [ ] Restrict delete dan audit perubahan master.

Exit: Admin dapat menyiapkan master valid tanpa membuatnya otomatis terlihat PD.

## Phase 3 — Kompetisi dan Publikasi Registrasi

- [x] Kompetisi memiliki status draft/open/closed dan periode registrasi.
- [x] Snapshot regulasi disimpan saat publish.
- [x] Portal PD hanya menampilkan kompetisi terpublikasi.
- [x] Admin dapat publish dan menutup registrasi.
- [ ] Preview publikasi, tarik publikasi sebelum ada entry, dan audit event.

Exit: hanya paket kompetisi yang disahkan Admin dapat dipilih PD.

## Phase 4 — Registrasi Cabor dan Pemain

- [x] Registrasi baru `event_entries` memakai PD PERPAMSI tanpa PDAM.
- [x] Tabel `entry_members` dan backfill pemain legacy.
- [x] Validasi jumlah/duplikasi pemain dari master kategori.
- [x] Validasi memakai snapshot regulasi kompetisi.
- [-] Workflow submit, verifikasi, penolakan, dan pembatalan pending; draft/perbaikan roster belum tersedia.

Exit: PD dapat mendaftarkan pemain tanpa instansi asal; data legacy terverifikasi aman.

## Phase 5 — Venue, Agenda, dan Panitia

- [ ] Venue lengkap.
- [ ] Agenda/jadwal, publikasi, dan konflik waktu.
- [ ] Kamus label status Indonesia.
- [ ] Akun panitia dan assignment per cabor/match.
- [ ] Policy dan menu berbasis scope.

Exit: CRUD, seed ulang, audit, dan restrict delete lulus.

## Phase 6 — Kompetisi dan Skor

- [ ] Seeding/grup/bracket manager.
- [ ] Precondition bracket lock.
- [ ] Input skor idempotent.
- [ ] Finalisasi dan revisi beralasan.
- [ ] Klasemen cabor dan medali PD PERPAMSI.

Exit: seluruh hasil dapat ditelusuri ke entry, aturan, aktor, dan audit.

## Phase 7 — Operasional

- [ ] Import/export aman.
- [ ] Laporan dan audit.
- [ ] Cache invalidation.
- [ ] Load test.
- [ ] Backup/restore.
- [ ] UAT dan training.

Exit: tidak ada risiko kritis terbuka dan seluruh P0/P1 lulus.
