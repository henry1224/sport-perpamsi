# Review Gap Menuju Development

## Review Lead Architect

- API contract v1 masih kurang; perlu agar frontend/backend bisa paralel.
- Migration plan masih kurang; perlu karena PostgreSQL sudah dipilih.
- ERD final belum bisa 100% final sebelum seluruh cabor dan regulasi disahkan, tapi baseline v1 bisa dibuat sekarang.
- Wireframe UI masih kurang; perlu screen map dan layout utama, bukan high-fidelity dulu.
- UAT checklist masih kurang; perlu untuk simulasi panitia sebelum go-live.
- Load test plan masih kurang; perlu untuk target 1.000 public user dan 100 panitia.
- Deployment detail masih kurang; perlu runbook Laravel/Inertia/SSR/PostgreSQL production.
- CI/CD belum wajib sekarang; checklist manual cukup sampai repo aplikasi dibuat.
- Env config masih kurang; perlu daftar environment Laravel, PostgreSQL, SSR, cache, mail, storage.
- Observability masih umum; perlu health check, error log, backup check, queue check.
- Data import template masih kurang; perlu kolom CSV/Excel untuk PDAM, tim, atlet, jadwal.
- Daftar cabor resmi sementara sudah ditentukan dan harus dibuat mudah ditambah.

## Keputusan Baru

- Cabor resmi sementara: bulu tangkis, futsal, tenis meja, tenis lapangan, voli, catur.
- Cabor harus configurable agar bisa ditambahkan nanti tanpa ubah kode besar.
- Panitia/scorekeeper v1 menginput hasil setelah pertandingan selesai, bukan live point-by-point.
- Sistem tetap menyimpan waktu input, aktor, status, dan audit sebagai history record.
- Hasil final menentukan peserta lanjut/gugur berdasarkan format kompetisi.
- Knockout wajib didukung.
- Lower bracket/double elimination disiapkan sebagai format kompetisi, aktif bila regulasi cabor memakainya.

## Referensi UI

- Esports Nations Cup sebagai inspirasi public event UI: https://esportsnationscup.com/en
- FIFA standings sebagai inspirasi tampilan standing/knockout navigation: https://www.fifa.com/en/tournaments/mens/worldcup/canadamexicousa2026/standings
