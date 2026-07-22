# Dokumentasi Sport PERPAMSI

Struktur ini mengadopsi pola rapi dari `digital-bookkeeping`: folder bernomor per domain, dokumen kecil per tanggung jawab, dan PRD tetap sebagai master produk.

## Struktur Dokumen

### 00 Project

- [Roadmap pengembangan terkendali](./00-project/development-roadmap.md): phase aktif, dependency, checklist, dan exit criteria.
- [Standar eksekusi phase](./00-project/phase-execution-standard.md): gate wajib sebelum task, aturan phase aktif, dan larangan melompat phase.
- [Peta aplikasi dan alur end-to-end](./00-project/application-flow.md): fitur saat ini, target v1, relasi data, dan gap pengembangan.
- [Piagam proyek](./00-project/charter.md): tujuan, scope, non-scope, metrik keberhasilan.
- [PRD utama](./00-project/prd.md): visi produk, modul, scope v1, acceptance criteria.
- [Delivery plan](./00-project/delivery-plan.md): fase pengembangan, prioritas, timeline awal September.
- [Glossary](./00-project/glossary.md): istilah resmi produk dan event.
- [Gap checklist](./00-project/gap-checklist.md): keputusan dan dokumen yang masih kurang sebelum development.
- [Review gap](./00-project/review-gap.md): review lead architect dan keputusan baru.
- [Audit alur Phase 1–5](./00-project/phase-1-5-flow-audit.md): bukti implementasi, gap yang ditutup, dan batas phase berikutnya.
- [Audit dokumen vs kode 2026-07-22](./00-project/audit-2026-07-22.md): drift terkonfirmasi antara standar dan implementasi, ranking prioritas P0–P3, dan aturan eksekusi lanjutannya.

### 01 Architecture

- [Architecture standard](./01-architecture/architecture-standard.md): arsitektur, performa, keamanan, operasional.
- [Tech stack](./01-architecture/tech-stack.md): keputusan Laravel, Inertia, Vue, SSR, PostgreSQL, dan ID.
- [Domain model](./01-architecture/domain-model.md): bounded context dan aturan lintas domain.
- [ADR-001 fondasi v1](./01-architecture/adr-001-keputusan-fondasi.md): keputusan teknis awal.

### 02 Data

- [Data standard](./02-data/data-standard.md): single source of truth, governance, import/export, audit.
- [Postgres standard](./02-data/postgres-standard.md): keputusan PostgreSQL, index, constraint, transaksi, backup.
- [ERD konseptual](./02-data/erd.md): relasi utama.
- [Data dictionary](./02-data/data-dictionary.md): field inti v1.
- [Migration plan](./02-data/migration-plan.md): urutan migration Laravel/PostgreSQL.
- [Database lifecycle standard](./02-data/database-lifecycle-standard.md): klasifikasi active/legacy/dead dan gate wajib sebelum drop atau perubahan seeder.
- [ERD v1 baseline](./02-data/erd-v1-baseline.md): ERD baseline development.
- [Import template standard](./02-data/import-template-standard.md): kolom CSV/Excel data awal.
- [Sample participant data](./02-data/sample-participant-data.md): contoh PD PERPAMSI, registrasi cabor, dan pemain untuk development/UAT.
- [Public identifier standard](./02-data/public-identifier-standard.md): aturan `slug`, `public_id`, dan larangan expose ID internal.
- [Region standard](./02-data/region-standard.md): master provinsi dan identitas PD PERPAMSI.
- [Delegation standard](./02-data/delegation-standard.md): pengajuan akun, registrasi cabor, pemain, dan identitas PD PERPAMSI.
- [Standar entry dan multi-team](./02-data/team-entry-standard.md): satu parent registrasi per PD/kompetisi, unit team peserta, verifikasi hybrid, kuota dinamis, seeding, dan medali.

### 03 Product

- [API contract v1](./03-product/api-contract-v1.md): endpoint public dan action admin/panitia baseline.
- [Agenda standard](./03-product/agenda-standard.md): agenda, venue, cabor, jam dari screenshot awal.
- [Seminar standard](./03-product/seminar-standard.md): konten NIWC, sesi, peserta, biaya, dan implementasi menu Seminar.
- [Standar informasi teknis cabor](./03-product/sport-technical-guide-standard.md): sumber data, kuota opsional, snapshot, dan tampilan publik.

### 04 Design

- [UI standard](./04-design/ui-standard.md): standar UI umum public, admin, dan panitia.
- [Public/Admin UI standard](./04-design/public-admin-ui-standard.md): token visual, spacing, component, dan checklist konsistensi tampilan.

### 09 Development

- [Git workflow](./09-development/git-workflow.md): branch, commit, merge, dan push.
- [Coding standard](./09-development/coding-standard.md): struktur backend/frontend, route, controller, service, action, dan review checklist.

### 04 Design

- [UI standard](./04-design/ui-standard.md): public, admin, panitia, aksesibilitas, komponen utama.
- [Wireframe map](./04-design/wireframe-map.md): screen map public, admin, panitia.
- [Brand asset standard](./04-design/brand-asset-standard.md): penggunaan logo, maskot, warna, dan layout public.
- [Public home concept](./04-design/public-home-concept.html): contoh layout HTML memakai logo dan maskot.

### 05 Business Rules

- [Match dan score rules](./05-business-rules/match-score-rules.md): status match, input skor, finalisasi, ranking.
- [Competition format standard](./05-business-rules/competition-format-standard.md): format grup, knockout, round robin, ranking per jenis cabor.
- [Ranking rules](./05-business-rules/ranking-rules.md): rumus klasemen medali PD PERPAMSI dan klasemen cabor.
- [Score structure](./05-business-rules/score-structure.md): struktur skor utama dan detail segment per jenis cabor.
- [Sport catalog v1](./05-business-rules/sport-catalog-v1.md): cabor resmi sementara dan format default.
- [Regulation reference](./05-business-rules/regulation-reference.md): referensi regulasi resmi per cabor.

### 06 Security

- [RBAC matrix](./06-security/rbac-matrix.md): role, permission, matrix akses.
- [Threat model](./06-security/threat-model.md): risiko utama dan mitigasi minimum.
- [Risk register](./06-security/risk-register.md): risiko rinci, kontrol wajib, dan verifikasi per modul.

### 07 Operations

- [Team plan](./07-operations/team-plan.md): struktur tim, ownership, support event.
- [Runbook](./07-operations/runbook.md): prosedur sebelum, saat, dan setelah event.
- [Deployment standard](./07-operations/deployment.md): environment dan checklist release.
- [Laravel deployment runbook](./07-operations/laravel-deployment-runbook.md): deploy Laravel/Inertia/SSR/PostgreSQL.
- [Environment config](./07-operations/env-config.md): daftar env production.
- [Observability checklist](./07-operations/observability-checklist.md): health check, logs, backup, alert.

### 08 Testing

- [Test strategy](./08-testing/test-strategy.md): backend, E2E, UAT, load test.
- [UAT checklist](./08-testing/uat-checklist.md): checklist UAT public, admin, panitia.
- [Load test plan](./08-testing/load-test-plan.md): skenario load 1.000 public dan 100 panitia.

### 09 Development

- [Git workflow](./09-development/git-workflow.md): standar branch, commit, push, dan merge.

## Aturan Pakai

- Sumber kebenaran publikasi cabor untuk portal PD: `03-product/registration-publication-standard.md`.

- Perubahan scope masuk ke PRD dan delivery plan.
- Perubahan data masuk ke data standard, ERD, atau data dictionary.
- Perubahan arsitektur masuk ke architecture standard atau ADR.
- Perubahan UI masuk ke UI standard.
- Perubahan akses masuk ke RBAC matrix.
- Perubahan operasional event masuk ke runbook.
- Perubahan workflow development masuk ke git workflow.

## Addendum v2: Peta Dokumen Terintegrasi

- PRD utama: `docs/00-project/prd.md`.
- Fase kerja: `docs/00-project/delivery-plan.md`.
- Relasi data: `docs/02-data/erd.md`.
- Standar data besar, pagination, filter: `docs/02-data/data-standard.md`.
- Kamus data kategori cabor: `docs/02-data/data-dictionary.md`.
- API public/admin: `docs/03-product/api-contract-v1.md`.
- Standar UI public: `docs/04-design/ui-standard.md`.
- Aturan skor dan format kompetisi: `docs/05-business-rules/`.
- Security dan throttle: `docs/06-security/threat-model.md`.
- Load test: `docs/08-testing/load-test-plan.md`.

Setiap perubahan fitur bracket, kategori cabor, ranking, atau public list besar wajib memperbarui minimal PRD, ERD/data standard, API contract, UI standard, security, dan load test.

Setiap pekerjaan wajib dimulai dengan membaca `AGENTS.md` dan [Git workflow](./09-development/git-workflow.md). Perubahan data/alur juga wajib memperbarui risk register, test strategy, dan UAT.
