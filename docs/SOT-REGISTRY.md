# Source-of-Truth Registry — Sport PERPAMSI

Registry ini menentukan dokumen mana yang menang untuk setiap menu dan alur. Tujuan: satu aturan ditulis sekali, lalu dokumen lain hanya merujuk.

## Aturan Prioritas

1. **SoT Utama** pada tabel di bawah menang bila dua dokumen membahas alur yang sama.
2. Schema **nyata** mengikuti migration aktif; target schema wajib ditandai `TARGET` di dokumentasi.
3. Vocabulary status hanya dari [`02-data/STATUS-VOCABULARY.md`](./02-data/STATUS-VOCABULARY.md).
4. Status phase terkini hanya dari [`00-project/development-roadmap.md`](./00-project/development-roadmap.md). Dokumen audit/reaudit adalah snapshot historis.
5. Perubahan satu menu wajib memeriksa semua kolom **Interkoneksi** pada baris menu tersebut; tidak boleh hanya mengubah satu layar.
6. Dokumen pendukung tidak boleh menyalin ulang aturan kanonik. Gunakan link menuju heading SoT.
7. Kode/DB yang belum sesuai SoT harus ditulis sebagai **Gap Target**, bukan diam-diam dianggap tersedia.

## Peta Arsitektur Data

```text
Province ── RegionalCommittee ── CommitteeApplication ── User
    │              │                                      │
    └── Pdam       └── EventEntry ── EntryTeam ── EntryMember
                              │          │
Sport ── SportCategory        │          └── TournamentMatch ── MatchScore
   │   └── SportRegulation    │                     │               └── ScoreAudit
   └── TournamentEvent ───────┘                     │
   │          └── EventAgenda ── Venue ─────────────┘
   └──────────── SportAssignment ── User/Staff
                        └────────── Venue
```

Rantai participant kanonik: `TournamentEvent → EventEntry → EntryTeam → EntryMember`. Unit pertandingan adalah `EntryTeam`, bukan parent `EventEntry`.

## Registry Menu

| Menu / alur | Route utama | Controller / service | SoT Utama | Schema | Rule / status | RBAC | Design | Interkoneksi wajib |
|---|---|---|---|---|---|---|---|---|
| Public — Home | `/` | `PublicPageController`, `PublicDataService` | `00-project/prd.md#public` | `02-data/data-dictionary.md` | `00-project/application-flow.md#6-publikasi` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | Agenda, cabor, peserta, hasil, ranking; hanya published/final dan tanpa data pribadi. |
| Public — Agenda | `/agenda` | `PublicPageController`, `PublicDataService` | `03-product/agenda-standard.md` | `02-data/data-dictionary.md#eventagenda` | `02-data/STATUS-VOCABULARY.md#vocabulary-kanonik` | `06-security/rbac-matrix.md` | `04-design/ui-standard.md` | `EventAgenda → TournamentEvent/Sport/Venue`; public gate memakai publikasi agenda. |
| Public — Seminar | `/seminar` | `PublicPageController` | `03-product/seminar-standard.md` | Belum ada schema operasional kanonik | `03-product/seminar-standard.md` | `06-security/rbac-matrix.md` | `04-design/ui-standard.md` | Konten/agenda/venue; tandai static/target secara eksplisit. |
| Public — Cabor | `/cabor` | `PublicPageController`, `PublicDataService` | `03-product/sport-technical-guide-standard.md` | `02-data/data-dictionary.md#sport-dan-category` | `05-business-rules/sport-catalog-v1.md` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | `Sport → SportCategory/SportRegulation/TournamentEvent`; public hanya informasi teknis aktif/terbit. |
| Public — Venue | `/venue` | `PublicPageController`, `PublicDataService` | `03-product/agenda-standard.md` | `02-data/data-dictionary.md#venue` | `02-data/STATUS-VOCABULARY.md` | `06-security/rbac-matrix.md` | `04-design/ui-standard.md` | `Venue → EventAgenda/TournamentMatch/SportAssignment`. |
| Public — Peserta | `/peserta` | `PublicPageController`, `PublicDataService` | `02-data/team-entry-standard.md#match-hasil-dan-medali` | `02-data/data-dictionary.md#entryteam` | `02-data/team-entry-standard.md#verifikasi-hybrid` | `06-security/rbac-matrix.md` | `04-design/ui-standard.md` | Hanya `EntryTeam` efektif verified; label `PD PERPAMSI {provinsi} #{team_no}`; jangan expose NIK/KTA/dokumen. |
| Admin — Pengguna | `/admin/users` | `AdminUserController` | `06-security/rbac-matrix.md` | `02-data/data-dictionary.md` | `02-data/STATUS-VOCABULARY.md#vocabulary-kanonik` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | Kelola akun internal sesuai hak Super Admin/Admin; akun PD dipisahkan dan tetap melalui registrasi serta verifikasi Pengurus Daerah. |
| Public — Bracket | `/bracket` | `PublicPageController`, `PublicDataService` | `05-business-rules/competition-format-standard.md` | `02-data/erd.md` | `02-data/team-entry-standard.md#seeding-sesama-pd` | `06-security/rbac-matrix.md` | `04-design/ui-standard.md` | `EntryTeam → TournamentMatch`; bracket lock, seed, format, roster snapshot. |
| Public — Hasil | `/hasil` | `PublicPageController`, `PublicDataService` | `05-business-rules/match-score-rules.md` | `02-data/data-dictionary.md#match-score-audit` | `02-data/STATUS-VOCABULARY.md` | `06-security/rbac-matrix.md` | `04-design/ui-standard.md` | `TournamentMatch → MatchScore/ScoreAudit`; hanya hasil resmi; keputusan `final` vs `verified` wajib tunggal. |
| Public — Ranking | `/ranking` | `PublicPageController`, `PublicDataService` | `05-business-rules/ranking-rules.md` | `02-data/erd.md` | `05-business-rules/ranking-rules.md` | `06-security/rbac-matrix.md` | `04-design/ui-standard.md` | Agregasi `EntryTeam → EventEntry → RegionalCommittee`; hanya match/hasil berstatus resmi. |
| Auth — Masuk | `/login`, `/logout` | `AuthController` | `00-project/prd.md#daftar-dan-masuk` | `02-data/data-dictionary.md#user` | `02-data/STATUS-VOCABULARY.md` | `06-security/rbac-matrix.md` | `04-design/ui-standard.md` | `User.role/account_status → middleware → redirect Admin/PD/Staff`. |
| Auth — Pengajuan PD & Status | `/register`, `/registration-status` | `CommitteeRegistrationController`, `CommitteeApplicationController` | `02-data/delegation-standard.md#pengajuan-pengurus-daerah` | `02-data/data-dictionary.md#committeeapplication` | `02-data/STATUS-VOCABULARY.md` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | `Province → RegionalCommittee → CommitteeApplication → User`; application status dan account status harus sinkron. |
| PD — Dashboard | `/pd/dashboard` | `PdDashboardController` | `00-project/application-flow.md#3-registrasi-cabor-team-dan-pemain` | `02-data/data-dictionary.md#tournamentevent` | `02-data/STATUS-VOCABULARY.md#hubungan-status-registrasi` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | Agregasi parent/team efektif/player; semua status harus terhitung, jangan gabungkan label status dengan alasan progres. |
| PD — Registrasi Cabor/Team/Pemain | `/pd/events/{event:code}`, `/pd/events/{event:code}/entries` | `PdEntryController`, `RegisterEventEntry`, `StoreEventEntryRequest` | `02-data/team-entry-standard.md` | `02-data/data-dictionary.md#evententry`, `#entryteam`, `#entrymember` | `02-data/STATUS-VOCABULARY.md#hubungan-status-registrasi` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | Snapshot Data Lomba, kuota team/member/official, PDAM pemain, dokumen privat, submit/resubmit/cancel, verifikasi hybrid. |
| PD — Dokumen Peserta | `/entry-members/{member}/documents/{document}` | `EntryMemberDocumentController` | `02-data/team-entry-standard.md#snapshot-registrasi` | `02-data/data-dictionary.md#entrymember` | Kelengkapan dokumen bukan status verifikasi | `06-security/risk-register.md` | `04-design/public-admin-ui-standard.md` | `EntryMember.documents`; hanya Admin atau PD pemilik; path storage tidak pernah dari client. |
| Staff — Pertandingan Tugas | `/panitia/pertandingan` | `AssignedMatchController` | `05-business-rules/match-score-rules.md` | `02-data/data-dictionary.md#match-score-audit` | `02-data/STATUS-VOCABULARY.md#vocabulary-kanonik` | `06-security/rbac-matrix.md` | `04-design/ui-standard.md` | Scope = active `SportAssignment` pada sport + venue; tampilkan label status, bukan kode mentah. |
| Admin — Dashboard | `/admin/dashboard` | `AdminDashboardController` | `00-project/application-flow.md#menu-target` | `02-data/erd.md` | `02-data/STATUS-VOCABULARY.md` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | Ringkasan Committee/Events/Entries/Matches; role target harus ditandai target, bukan aktif. |
| Admin — Verifikasi Pengurus Daerah | `/admin/committee-applications` | `CommitteeApplicationController` | `02-data/delegation-standard.md#pengajuan-pengurus-daerah` | `02-data/data-dictionary.md#committeeapplication` | `02-data/STATUS-VOCABULARY.md` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | Application status ↔ User account status ↔ audit ↔ middleware login. |
| Admin — Verifikasi Peserta | `/admin/entries` | `AdminEntryVerificationController` | `02-data/team-entry-standard.md#verifikasi-hybrid` | `02-data/data-dictionary.md#evententry`, `#entryteam`, `#entrymember` | `02-data/STATUS-VOCABULARY.md#hubungan-status-registrasi` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | Member decision → team override → parent auto-finalization → bracket eligibility; alasan/progres bukan badge status. |
| Admin — Master Cabor/Kategori/Regulasi | `/admin/master-data` | `MasterDataController` | `03-product/registration-publication-standard.md#hierarki-aturan-dinamis` | `02-data/data-dictionary.md#sport-dan-category` | `05-business-rules/sport-catalog-v1.md` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | Master mengalir ke Data Lomba draft; published snapshot tidak ikut berubah. |
| Admin — Data Lomba | `/admin/events` | `TournamentEventController` | `03-product/registration-publication-standard.md` | `02-data/data-dictionary.md#tournamentevent` | `02-data/STATUS-VOCABULARY.md#vocabulary-kanonik` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | Master → draft → snapshot publish → PD entry → verifikasi → bracket lock; cek progress team dan pemain. |
| Admin — Master PDAM | `/admin/pdams` | `PdamController` | `02-data/delegation-standard.md#registrasi-cabor-dan-pemain` | `02-data/data-dictionary.md#entrymember` | Aktif/nonaktif perlu rule kanonik bila ditambahkan | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | `Province/Regency → Pdam → EntryMember`; PDAM asal pemain, bukan identitas kontingen. |
| Admin — Venue & Agenda | `/admin/venues`, `/admin/agenda` | `VenueAgendaController` | `03-product/agenda-standard.md` | `02-data/data-dictionary.md#venue`, `#eventagenda` | `02-data/STATUS-VOCABULARY.md` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | Venue ↔ agenda ↔ event ↔ match ↔ assignment; status agenda dan `published_at` harus satu arti. |
| Admin — Panitia & Penugasan | `/admin/assignments` | `SportAssignmentController` | `06-security/rbac-matrix.md` | `02-data/data-dictionary.md#sportassignment` | `02-data/STATUS-VOCABULARY.md` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | User role ↔ assignment role ↔ sport ↔ venue ↔ match visibility; revoke harus langsung menutup scope. |
| Admin — Pertandingan & Skor | `/admin/skor`, `/admin/matches/{match}/schedule` | `ScoreController`, `VenueAgendaController`, `PublicDataService` | `05-business-rules/match-score-rules.md` | `02-data/data-dictionary.md#match-score-audit` | `02-data/STATUS-VOCABULARY.md#vocabulary-kanonik` | `06-security/rbac-matrix.md` | `04-design/public-admin-ui-standard.md` | EntryTeam participant, agenda/venue/jadwal, score payload, finalisasi, audit, hasil public, ranking. |

## Dependency Data Paralel

Perubahan menu berikut wajib dikerjakan sebagai satu graph, bukan satu layar:

### Master → Data Lomba → Registrasi

```text
Master Cabor/Kategori/Regulasi
→ sinkronisasi Data Lomba draft
→ snapshot publish
→ pilihan portal PD
→ validasi team/member/official
→ verifikasi Admin
→ eligibility bracket
```

### Verifikasi peserta

```text
EntryMember status
→ EntryTeam override/effective status
→ EventEntry parent status
→ progress dashboard PD/Admin
→ bracket blocker
→ peserta public
```

### Agenda/assignment/match

```text
Venue + EventAgenda
→ TournamentMatch jadwal
→ SportAssignment scope
→ Staff match visibility
→ status skor/finalisasi
→ Hasil + Ranking public
```

### Pengajuan akun PD

```text
Province + RegionalCommittee
→ CommitteeApplication status
→ User account_status
→ middleware login
→ akses portal PD
```

## Gate Perubahan Menu

Sebelum perubahan dianggap selesai:

- [ ] SoT Utama pada baris menu sudah dibaca dan tidak disalin ulang ke dokumen lain.
- [ ] Seluruh entity pada kolom Interkoneksi diperiksa.
- [ ] Schema nyata dan target tidak tercampur.
- [ ] Vocabulary, label, tone badge, filter, form option, dan agregasi memakai `STATUS-VOCABULARY.md`.
- [ ] RBAC dan scope backend diperiksa, bukan hanya visibilitas menu.
- [ ] Data demo/seeder menunjukkan alur terkoneksi, bukan hanya satu halaman.
- [ ] Test/UAT mencakup state sebelum, transition, state sesudah, dan layar terkait.
- [ ] Status phase hanya diperbarui di `development-roadmap.md`.
