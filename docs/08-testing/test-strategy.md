# Test Strategy Sport PERPAMSI

## Backend Tests

Required feature tests:

1. User tidak bisa akses role di luar permission.
2. Scorekeeper tidak bisa update match yang bukan assignment.
3. Scorekeeper bisa update skor match assignment.
4. Match final tidak bisa diedit langsung.
5. Revisi skor final wajib alasan.
6. Klasemen hanya memakai match final.
7. Ranking hanya memakai match final.
8. Dua PDAM dari provinsi sama mendapat `regional_committee_id` yang sama.
9. Klasemen medali mengakumulasi hasil final ke Pimpinan Daerah, bukan PDAM.
10. Import menolak baris invalid.
11. Public tidak melihat data draft.
12. Audit log tercatat saat skor/finalisasi berubah.

## Frontend/E2E Tests

Critical flows:

1. Public membuka jadwal tanpa login.
2. Public melihat live score.
3. Admin membuat event, cabor, venue, PDAM, tim, atlet.
4. Verifikator memverifikasi peserta.
5. Admin membuat match dan assignment scorekeeper.
6. Scorekeeper input skor dan selesai match.
7. Admin/Koordinator finalisasi match.
8. Public melihat hasil final, bracket, ranking.
9. Public melihat klasemen medali Pimpinan Daerah dan nama PDAM pada detail pertandingan.

## UAT Criteria

- Semua P0 pass.
- Zero critical bug saat go-live.
- Maksimal 5 minor bug terbuka dengan workaround jelas.
- Minimal 10 match simulasi berhasil end-to-end.
- Panitia bisa menjalankan flow tanpa developer.

## Load Test Minimum

- 1.000 public user membuka live score dan jadwal.
- 100 panitia login.
- 50 scorekeeper update skor bersamaan.
- Public page tetap responsif saat polling aktif.
