# Roadmap Pengembangan Terkendali

Setiap phase memakai branch terpisah dan mengikuti `AGENTS.md` serta Git workflow.

Eksekusi harian wajib mengikuti [phase-execution-standard.md](./phase-execution-standard.md).

## Kontrol Eksekusi

- **Phase aktif: Phase 5 — Venue, Agenda, dan Panitia.**
- Phase berikutnya tidak boleh diperluas sebelum exit criteria Phase 5 selesai.
- Assignment panitia cabor/venue yang sudah tersedia boleh dilanjutkan dan dihubungkan ke agenda serta policy akses.
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

Pengecualian Phase 5: koreksi CRUD cabor, Format Bawaan, penguncian Format Kompetisi, serta kategori resmi panduan slide 5-23; diperlukan agar master dan assignment panitia memakai data konsisten tanpa mengubah histori terpublikasi.

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

Exit baseline: PD dapat mendaftarkan pemain tanpa instansi asal; data legacy terverifikasi aman.

## Phase 4B — Multi-Team Registration (Dependency Wajib)

- [ ] `EventEntry` menjadi satu parent registrasi PD/kompetisi.
- [ ] `EntryTeam` menjadi unit peserta seed, match, hasil, standing, dan medali.
- [ ] Technical meeting menetapkan unit, `max_teams_per_pd`, dan batas anggota per team pada setiap kompetisi.
- [ ] Snapshot publikasi menyimpan kuota team/member serta `avoid_same_pd_in_round=true`.
- [ ] Label team dibentuk server dan nomor team immutable.
- [ ] Verifikasi hybrid parent default + override team tersedia dan diaudit.
- [ ] Perpindahan/substitusi pemain antar-team setelah verified dilarang total.
- [ ] Entry/member/match legacy dibackfill ke team `#1` tanpa rebuild hasil lama.
- [ ] Feature test, concurrency test, migration upgrade, risk control, dan UAT multi-team lulus.

Exit: model participant konsisten dari registrasi sampai medali; seluruh team aktif efektif verified sebelum seed; tidak ada risiko kritis multi-team terbuka.

Phase 4B adalah dependency minimum Phase 6. Phase 5 boleh menyelesaikan UAT venue/agenda, tetapi tidak boleh ditutup dan phase aktif tidak boleh maju sampai Phase 4B selesai atau pengecualian formal disetujui.

## Phase 5 — Venue, Agenda, dan Panitia

- [x] Venue memiliki detail, kontak, peta, fasilitas, status aktif, serta form tambah/edit/nonaktif.
- [x] Agenda memiliki kompetisi, publikasi, validasi konflik waktu, edit, dan audit revisi.
- [x] Kamus label status Indonesia terpusat untuk portal utama.
- [x] Akun panitia dan assignment per cabor/venue terhubung ke jadwal pertandingan.
- [x] Policy dan menu panitia berbasis scope cabor/venue dengan deny-default.

Exit: CRUD, seed ulang, audit, dan restrict delete lulus.

Audit implementasi: [phase-1-5-flow-audit.md](./phase-1-5-flow-audit.md). Phase 5 belum ditutup sampai UAT manual dan review commit selesai.

## Phase 6 — Kompetisi dan Skor

> Beku sampai seluruh exit criteria Phase 4B terpenuhi; seluruh participant operasi turnamen wajib `EntryTeam`.

- [ ] Seeding/grup/bracket manager affiliation-aware.
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
