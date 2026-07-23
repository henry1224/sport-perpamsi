# Audit Alur Phase 1–5

Tanggal audit: 22 Juli 2026. Scope hanya Phase 1–5; Phase 6–7 tetap ditunda sesuai standar eksekusi phase.

## Hasil

| Phase | Alur | Bukti Utama | Status |
|---|---|---|---|
| 1 | Daftar PD → status pending → revisi/verifikasi/penolakan → aktivasi akun | `committee_applications`, audit, middleware, `CommitteeApplicationTest` | Lengkap |
| 2 | Master cabor → kategori/kuota → regulasi berversi → audit | Master Admin, constraint, seeder, `MasterDataTest` | Lengkap |
| 3 | Kompetisi draft → CRUD → preview → snapshot → publish/close/unpublish | `registration_rules`, audit publikasi, `TournamentEventPublicationTest` | Lengkap secara kode; UAT manual terbuka |
| 4/4B | Portal PD → parent entry → multi-team → submit → verifikasi parent/override → roster lock | `EntryTeam`, snapshot kuota, audit override, backfill/seed tim `#1`, `MultiTeamRegistrationTest` | Lengkap secara kode; UAT manual terbuka |
| 5 | Venue → agenda → konflik → publish → jadwal match → scope panitia | policy deny-default, audit agenda, `VenueAgendaManagementTest`, `StaffMatchScopeTest` | Partial: data operasional belum terhubung |

## Perbaikan Audit

- Form Venue & Agenda sekarang mendukung tambah dan edit; venue dapat dinonaktifkan tanpa dihapus.
- Update dan publikasi agenda mencatat before/after, aktor, alasan perubahan, dan waktu pada `event_agenda_audits`.
- Label status Indonesia dipusatkan pada `resources/js/lib/status.js`.
- Validasi agenda tetap menolak venue nonaktif, jam selesai sebelum mulai, dan irisan waktu.
- Registrasi multi-team memakai satu parent per PD/kompetisi, label server, nomor stabil, dan identity hash pemain.
- Match, skor, panitia, dan data publik membaca `EntryTeam` dengan fallback foreign key legacy selama masa transisi.
- Query `eligibleTeams()` menjadi sumber tunggal precondition participant verified untuk bracket/seed Phase 6.

## Alur End-to-End

```text
Daftar Pengurus Daerah
→ Verifikasi Admin
→ Login Portal PD
→ Admin siapkan master dan kompetisi
→ Admin publish snapshot registrasi
→ Phase 4B: PD membuat parent entry, team, dan anggota per team
→ Admin verifikasi default parent atau override team
→ Hanya team efektif verified masuk kompetisi
→ Admin siapkan venue dan agenda
→ Admin assign panitia ke cabor/venue
→ Match dijadwalkan ke agenda
→ Panitia hanya melihat match dalam scope
→ Phase 6: skor, finalisasi, bracket, klasemen
```

## Batas Audit

- UAT manual belum dianggap selesai hanya karena automated test lulus.
- Phase 4B lulus migration dan automated test; UAT manual tetap wajib sebelum Phase 5 ditutup.
- Phase 6 tetap beku sampai UAT Phase 4B dan Phase 5 serta review commit selesai.
- Input skor panitia, finalisasi, revisi hasil, bracket manager, dan klasemen adalah Phase 6.
- Import/export, load test, backup/restore, dan operasional produksi adalah Phase 7.

## Reaudit Data Operasional — 22 Juli 2026

- Database memuat 15 kompetisi; seluruhnya memiliki kategori dan `sport_regulation_id` yang valid.
- Seeder demo menghasilkan 475 registrasi, 475 team, 993 pemain, 756 match, 264 skor, dan 229 audit skor.
- Sebanyak 756 match belum memiliki agenda, venue, atau jadwal; 34 agenda belum terhubung ke kompetisi.
- Belum ada `sport_assignments` dan belum ada akun panitia operasional.
- Tidak ada kompetisi `bracket_locked` tanpa `registration_published_at` setelah cleanup dan normalisasi.
- Tidak ditemukan orphan member, team tanpa member, roster verified kosong, atau duplikasi `identity_hash` dalam kompetisi sama.

Status Phase 5 tetap `Partial` sampai wiring data operasional, assignment panitia, dan UAT selesai.
