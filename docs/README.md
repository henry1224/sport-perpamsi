# Dokumentasi Sport PERPAMSI

Struktur ini mengadopsi pola rapi dari `digital-bookkeeping`: folder bernomor per domain, dokumen kecil per tanggung jawab, dan PRD tetap sebagai master produk.

## Struktur Dokumen

### 00 Project

- [Piagam proyek](./00-project/charter.md): tujuan, scope, non-scope, metrik keberhasilan.
- [PRD utama](./00-project/prd.md): visi produk, modul, scope v1, acceptance criteria.
- [Delivery plan](./00-project/delivery-plan.md): fase pengembangan, prioritas, timeline awal September.
- [Glossary](./00-project/glossary.md): istilah resmi produk dan event.
- [Gap checklist](./00-project/gap-checklist.md): keputusan dan dokumen yang masih kurang sebelum development.
- [Review gap](./00-project/review-gap.md): review lead architect dan keputusan baru.

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
- [ERD v1 baseline](./02-data/erd-v1-baseline.md): ERD baseline development.
- [Import template standard](./02-data/import-template-standard.md): kolom CSV/Excel data awal.
- [Public identifier standard](./02-data/public-identifier-standard.md): aturan `slug`, `public_id`, dan larangan expose ID internal.
- [Region standard](./02-data/region-standard.md): master provinsi/kabupaten-kota Indonesia dan relasi PDAM.

### 03 Product

- [API contract v1](./03-product/api-contract-v1.md): endpoint public dan action admin/panitia baseline.
- [Agenda standard](./03-product/agenda-standard.md): agenda, venue, cabor, jam dari screenshot awal.

### 04 Design

- [UI standard](./04-design/ui-standard.md): public, admin, panitia, aksesibilitas, komponen utama.
- [Wireframe map](./04-design/wireframe-map.md): screen map public, admin, panitia.

### 05 Business Rules

- [Match dan score rules](./05-business-rules/match-score-rules.md): status match, input skor, finalisasi, ranking.
- [Competition format standard](./05-business-rules/competition-format-standard.md): format grup, knockout, round robin, ranking per jenis cabor.
- [Ranking rules](./05-business-rules/ranking-rules.md): rumus ranking PDAM dan klasemen cabor.
- [Score structure](./05-business-rules/score-structure.md): struktur skor utama dan detail segment per jenis cabor.
- [Sport catalog v1](./05-business-rules/sport-catalog-v1.md): cabor resmi sementara dan format default.

### 06 Security

- [RBAC matrix](./06-security/rbac-matrix.md): role, permission, matrix akses.
- [Threat model](./06-security/threat-model.md): risiko utama dan mitigasi minimum.

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

- Perubahan scope masuk ke PRD dan delivery plan.
- Perubahan data masuk ke data standard, ERD, atau data dictionary.
- Perubahan arsitektur masuk ke architecture standard atau ADR.
- Perubahan UI masuk ke UI standard.
- Perubahan akses masuk ke RBAC matrix.
- Perubahan operasional event masuk ke runbook.
- Perubahan workflow development masuk ke git workflow.
