# Roadmap Pengembangan Terkendali

Setiap phase memakai branch terpisah dan mengikuti `AGENTS.md` serta Git workflow.

Eksekusi harian wajib mengikuti [phase-execution-standard.md](./phase-execution-standard.md).

## Kontrol Eksekusi

- **Phase aktif: Phase 4 — Registrasi Cabor dan Pemain.**
- Phase berikutnya tidak boleh diperluas sebelum exit criteria Phase 4 selesai.
- Pekerjaan awal Phase 5 berupa assignment panitia cabor/venue tetap dibekukan sampai Phase 5 aktif.
- Perubahan phase aktif wajib dilakukan setelah seluruh gate pada standar eksekusi phase lulus.

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
- [x] CRUD Admin cabor dan kategori.
- [x] Regulasi berversi dan referensi dokumen technical meeting.
- [x] Master tidak memiliki delete permanen dan setiap perubahan tercatat audit.

Exit: Admin dapat menyiapkan master valid tanpa membuatnya otomatis terlihat PD.

## Phase 3 — Kompetisi dan Publikasi Registrasi

- [x] Kompetisi memiliki status draft/open/closed dan periode registrasi.
- [x] Snapshot regulasi disimpan saat publish.
- [x] Portal PD hanya menampilkan kompetisi terpublikasi.
- [x] Admin dapat publish dan menutup registrasi.
- [x] Preview publikasi, penetapan versi regulasi, tarik publikasi sebelum ada entry, dan audit event.

Exit: hanya paket kompetisi yang disahkan Admin dapat dipilih PD.

## Phase 4 — Registrasi Cabor dan Pemain

- [x] Registrasi baru `event_entries` memakai PD PERPAMSI tanpa PDAM.
- [x] Tabel `entry_members` dan backfill pemain legacy.
- [x] Validasi jumlah/duplikasi pemain dari master kategori.
- [x] Validasi memakai snapshot regulasi kompetisi.
- [x] Workflow draft, submit, verifikasi, perbaikan, penolakan, kirim ulang, pembatalan, dan audit roster.

Exit: PD dapat mendaftarkan pemain tanpa instansi asal; data legacy terverifikasi aman.

## Phase 5 — Venue, Agenda, dan Panitia

- [ ] Venue lengkap.
- [ ] Agenda/jadwal, publikasi, dan konflik waktu.
- [ ] Kamus label status Indonesia.
- [-] Akun panitia dan assignment per cabor/venue selesai; pembatasan akses per match menunggu jadwal pertandingan terhubung ke venue.
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
