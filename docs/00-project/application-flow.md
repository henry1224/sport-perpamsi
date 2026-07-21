# Peta Aplikasi dan Alur End-to-End

## Cara Membaca

- `Done`: sudah tersedia pada aplikasi saat ini.
- `Partial`: fondasi data atau tampilan tersedia, tetapi alur admin belum lengkap.
- `Planned`: baru menjadi target dalam dokumen v1.

## Gambaran Aplikasi

```text
MASTER DATA
Provinsi ── Kontingen Provinsi ── PDAM
                       └────── registrasi cabor ── tim/atlet
                                                    │
                                                    ▼
OPERASIONAL                                  seeding/bracket
                                                    │
                                             jadwal dan match
                                                    │
                                             input/finalisasi skor
                                                    │
                           ┌────────────────────────┴──────────────────────┐
                           ▼                                               ▼
PUBLIC               hasil/bracket provinsi                  klasemen medali provinsi
                                                                    │
                                      seluruh PDAM satu provinsi dijumlahkan
```

## Alur Utama

### 1. Persiapan Master

1. Admin menyiapkan provinsi dan kabupaten/kota.
2. Sistem menyediakan satu Kontingen Provinsi untuk setiap provinsi.
3. Admin menyiapkan PDAM dan wilayahnya.
4. Admin menyiapkan cabor, kategori, venue, agenda, dan kompetisi.

Status: `Partial` — tabel, migration, dan seeder tersedia; halaman admin master belum tersedia.

### 2. Registrasi Peserta

1. Admin memilih PDAM dan cabor/kategori.
2. Sistem menurunkan Kontingen Provinsi dari provinsi PDAM.
3. Sistem menyimpan `pdam_id`, `province_id`, dan `regional_committee_id` pada `event_entries`.
4. Tim/atlet dan dokumen melengkapi registrasi.
5. Verifikator menerima, menolak, atau meminta revisi.

Status: `Partial` — dashboard PD, form registrasi, kontingen otomatis, hapus entry, serta verifikasi/tolak admin tersedia. Upload dokumen dan riwayat revisi verifikasi belum tersedia.

### 3. Penyusunan Kompetisi

1. Registrasi terverifikasi menjadi peserta kompetisi.
2. Panitia menentukan seed dan mengunci bracket atau grup.
3. Sistem membuat match dan hubungan pemenang ke ronde berikutnya.

Status: `Partial` — data kompetisi, entry, bracket demo, dan match tersedia; pengelolaan admin belum lengkap.

### 4. Operasional Pertandingan

1. Scorekeeper membuka match.
2. Scorekeeper memasukkan skor.
3. Sistem menentukan pemenang dan menyimpan audit skor.
4. Hasil final menjadi sumber bracket dan klasemen.
5. Koreksi hasil final harus melalui revisi beralasan.

Status: `Partial` — login, pembatasan `pd_admin`/`super_admin`, halaman input skor, penyimpanan pemenang, dan audit dasar tersedia. Assignment scorekeeper dan workflow revisi formal belum tersedia.

### 5. Publikasi

1. Public melihat agenda, hasil, cabor, bracket, venue, dan peserta PDAM.
2. Match dan bracket menampilkan PDAM/tim/atlet yang bertanding.
3. Hasil babak final memberi emas dan perak kepada Kontingen Provinsi peserta.
4. Seluruh hasil PDAM dalam provinsi sama dijumlahkan pada satu klasemen Kontingen Provinsi.

Status: `Done` untuk halaman public dasar dan klasemen Kontingen Provinsi. Perunggu masih `Planned` sampai aturan penetapan juara ketiga tersedia.

## Contoh Relasi

```text
Kalimantan Timur
└── Kalimantan Timur
    ├── PDAM Balikpapan ── Futsal ── Emas
    └── PDAM Samarinda  ── Badminton ── Emas

Klasemen:
Kalimantan Timur = 2 emas
```

## Status Modul

| Modul | Status | Sumber Utama |
|---|---|---|
| Public home, agenda, seminar, hasil, cabor, bracket, venue, peserta | Done | `routes/web.php`, `resources/js/Pages` |
| Klasemen Kontingen Provinsi | Done | `PublicDataService`, `Ranking.vue` |
| Master wilayah, PDAM, cabor, venue | Partial | migration dan seeder |
| Registrasi cabor dan kontingen otomatis | Done | dashboard PD, `PdEntryController`, `event_entries` |
| Verifikasi/tolak registrasi | Done | `AdminEntryVerificationController`, `Admin/Entries.vue` |
| Input dan audit skor dasar | Done | `ScoreController`, `SubmitMatchScore` |
| Login dan role dasar | Done | `AuthController`, middleware `pd.admin`/`super.admin` |
| RBAC granular | Planned | RBAC matrix |
| Dokumen peserta | Planned | PRD dan delegation standard |
| Assignment scorekeeper | Planned | domain model dan RBAC matrix |
| Revisi skor final formal | Planned | match score rules |
| Import/export operasional | Planned | import template standard |
| Penetapan medali perunggu | Planned | ranking rules per cabor |
| Monitoring, backup, deployment production | Planned | operations docs |

## Status Role dan Menu

| Role | Status | Menu/Akses Saat Ini | Kekurangan |
|---|---|---|---|
| Public | Done | home, agenda, hasil, cabor, bracket, ranking, venue, peserta | Konten masih memiliki fallback demo |
| Admin PD (`pd_admin`) | Done | login, dashboard PD, daftar cabor, registrasi/hapus peserta milik PD | Belum ada dokumen peserta dan revisi registrasi |
| Super Admin (`super_admin`) | Partial | dashboard admin, verifikasi/tolak registrasi, input skor, dan menu pusat kendali | Master data, jadwal, bracket, panitia, laporan, dan audit masih ditandai `Segera` |
| Admin Event | Planned | belum ada role/route khusus | Perlu pengelolaan event dan master data |
| Koordinator Cabor | Planned | belum ada role/route khusus | Perlu scope cabor, jadwal, finalisasi, dan revisi |
| Verifikator Peserta | Planned | fungsi masih digabung ke Super Admin | Perlu role dan permission khusus verifikasi |
| Scorekeeper | Planned | fungsi input skor masih digabung ke Super Admin | Perlu assignment match dan akses terbatas |
| Content Officer | Planned | belum ada role/route khusus | Perlu pengelolaan banner, info, dan livestream |
| Auditor/Read Only | Planned | belum ada role/route khusus | Perlu audit log dan export read-only |

Implementasi saat ini memakai pemeriksaan role langsung pada middleware, belum permission per action seperti target [RBAC matrix](../06-security/rbac-matrix.md).

## Hubungan Dokumen

```text
Charter/PRD
  └── Application Flow
       ├── Domain Model
       ├── Delegation Standard
       ├── ERD + Data Dictionary
       ├── API Contract
       ├── UI/Wireframe
       ├── Business Rules
       ├── RBAC/Threat Model
       ├── Operations Runbook
       └── Test Strategy/UAT
```

## Sumber Kebenaran per Perubahan

| Perubahan | Dokumen wajib diperbarui |
|---|---|
| Scope/modul | PRD, delivery plan, application flow |
| Relasi data | delegation standard, ERD, data dictionary, migration plan |
| Registrasi/verifikasi | delegation standard, API contract, UI standard, RBAC, UAT |
| Skor/finalisasi | match score rules, API contract, RBAC, test strategy |
| Klasemen medali | ranking rules, API contract, UI standard, UAT |
| Deployment/backup | deployment, runbook, observability checklist |

## Gap Prioritas Pengembangan

1. Bangun form master Kontingen Provinsi, PDAM, cabor, kategori, dan venue.
2. Tambah upload dokumen dan riwayat revisi registrasi.
3. Bangun assignment scorekeeper dan role granular.
4. Bangun revisi hasil final beralasan.
5. Tetapkan model juara ketiga/perunggu per cabor.
6. Hubungkan seluruh halaman public ke data operasional tanpa fallback demo.
