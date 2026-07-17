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

- [ ] API contract v1.
- [ ] Migration plan Laravel/PostgreSQL.
- [ ] ERD final setelah daftar cabor resmi masuk.
- [ ] UI wireframe public, admin, panitia.
- [ ] Test case UAT panitia.
- [ ] Load test script.
- [ ] Deployment runbook detail sesuai hosting.

## Risiko Jika Belum Diputuskan

- Skor dan ranking bisa berubah saat development sudah jalan.
- Import data bisa bolak-balik karena template belum baku.
- UI scorekeeper bisa tidak cocok dengan format cabor.
- Load public bisa berat jika endpoint live score tidak disiapkan sejak awal.
- UAT terlambat karena panitia belum punya akun dan assignment.
