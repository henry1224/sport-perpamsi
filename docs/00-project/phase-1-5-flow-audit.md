# Audit Alur Phase 1–5

Tanggal audit: 22 Juli 2026. Scope hanya Phase 1–5; Phase 6–7 tetap ditunda sesuai standar eksekusi phase.

## Hasil

| Phase | Alur | Bukti Utama | Status |
|---|---|---|---|
| 1 | Daftar PD → status pending → revisi/verifikasi/penolakan → aktivasi akun | `committee_applications`, audit, middleware, `CommitteeApplicationTest` | Lengkap |
| 2 | Master cabor → kategori/kuota → regulasi berversi → audit | Master Admin, constraint, seeder, `MasterDataTest` | Lengkap |
| 3 | Kompetisi draft → preview → snapshot → publish/close/unpublish | `registration_rules`, audit publikasi, `TournamentEventPublicationTest` | Lengkap |
| 4 | Portal PD → draft roster → submit → verifikasi/revisi/tolak/batal | `entry_members`, audit roster, `EntryRosterWorkflowTest` | Lengkap |
| 5 | Venue → agenda → konflik → publish → jadwal match → scope panitia | assignment, policy deny-default, audit agenda, `VenueAgendaManagementTest`, `StaffMatchScopeTest` | Lengkap secara kode |

## Perbaikan Audit

- Form Venue & Agenda sekarang mendukung tambah dan edit; venue dapat dinonaktifkan tanpa dihapus.
- Update dan publikasi agenda mencatat before/after, aktor, alasan perubahan, dan waktu pada `event_agenda_audits`.
- Label status Indonesia dipusatkan pada `resources/js/lib/status.js`.
- Validasi agenda tetap menolak venue nonaktif, jam selesai sebelum mulai, dan irisan waktu.

## Alur End-to-End

```text
Daftar Pengurus Daerah
→ Verifikasi Admin
→ Login Portal PD
→ Admin siapkan master dan kompetisi
→ Admin publish snapshot registrasi
→ PD isi dan ajukan roster
→ Admin verifikasi roster
→ Admin siapkan venue dan agenda
→ Admin assign panitia ke cabor/venue
→ Match dijadwalkan ke agenda
→ Panitia hanya melihat match dalam scope
→ Phase 6: skor, finalisasi, bracket, klasemen
```

## Batas Audit

- UAT manual belum dianggap selesai hanya karena automated test lulus.
- Input skor panitia, finalisasi, revisi hasil, bracket manager, dan klasemen adalah Phase 6.
- Import/export, load test, backup/restore, dan operasional produksi adalah Phase 7.
