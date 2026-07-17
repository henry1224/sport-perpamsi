# Gap Checklist Menuju Development

## Keputusan yang Masih Kurang

- [x] Stack backend dan frontend final: Laravel + Inertia + Vue.js + SSR public.
- [x] Pilihan ID database: `bigint` internal + UUID public.
- [x] Struktur skor per cabor: skor utama + segment per set/babak/quarter/ronde.
- [x] Rumus ranking resmi: medali untuk PDAM, aturan cabor untuk klasemen grup.
- [x] Format kompetisi setiap cabor: knockout, grup, round robin, time/score ranking.
- [ ] Template import Excel/CSV untuk PDAM, tim, atlet, jadwal.
- [ ] PIC final sengketa skor.
- [ ] Domain production dan environment hosting.
- [ ] Target server sizing awal.
- [ ] Batas ukuran file dokumen peserta.

## Dokumen Teknis yang Masih Perlu Dibuat

- [x] API contract v1 baseline.
- [x] Migration plan Laravel/PostgreSQL baseline.
- [x] ERD v1 baseline setelah daftar cabor sementara masuk.
- [x] UI wireframe map public, admin, panitia.
- [x] Test case UAT panitia baseline.
- [x] Load test plan baseline.
- [x] Deployment runbook detail Laravel/Inertia/SSR/PostgreSQL baseline.

## Masih Dibutuhkan Setelah App Dibuat

- [ ] API contract detail per request/response final.
- [ ] Migration Laravel nyata.
- [ ] Wireframe high-fidelity.
- [ ] Load test script k6/Artillery.
- [ ] CI/CD pipeline.

## Risiko Jika Belum Diputuskan

- Skor dan ranking bisa berubah saat development sudah jalan.
- Import data bisa bolak-balik karena template belum baku.
- UI scorekeeper bisa tidak cocok dengan format cabor.
- Load public bisa berat jika endpoint live score tidak disiapkan sejak awal.
- UAT terlambat karena panitia belum punya akun dan assignment.
