# Gap Checklist Menuju Development

## Keputusan yang Masih Kurang

- [ ] Stack backend dan frontend final.
- [ ] Pilihan ID database: UUID atau bigint.
- [ ] Struktur skor per cabor: skor sederhana, set, babak, ronde, atau poin detail.
- [ ] Rumus ranking resmi: medali, poin, kemenangan, atau kombinasi.
- [ ] Format kompetisi setiap cabor: knockout, grup, liga, atau campuran.
- [ ] Template import Excel/CSV untuk PDAM, tim, atlet, jadwal.
- [ ] PIC final sengketa skor.
- [ ] Domain production dan environment hosting.
- [ ] Target server sizing awal.
- [ ] Batas ukuran file dokumen peserta.

## Dokumen Teknis yang Masih Perlu Dibuat

- [ ] API contract v1.
- [ ] Migration plan.
- [ ] ERD final setelah stack dipilih.
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
