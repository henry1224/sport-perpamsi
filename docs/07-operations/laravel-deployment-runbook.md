# Laravel Deployment Runbook

## Production Components

- Web server/PHP-FPM.
- Node SSR process untuk Inertia public SSR.
- PostgreSQL.
- Redis untuk cache/queue bila tersedia.
- Queue worker.
- Scheduler.
- Storage untuk upload dokumen.

## Deploy Steps

1. Pull release code.
2. Install dependency production.
3. Build frontend asset.
4. Build/start SSR server.
5. Run migration.
6. Cache config/routes/views.
7. Restart PHP-FPM/queue/SSR.
8. Run smoke test public dan login admin.
9. Cek log error.

## Smoke Test

- Home public terbuka.
- Jadwal public terbuka.
- Login admin berhasil.
- Panitia melihat match tugas.
- Input hasil test di staging berhasil.
- Queue berjalan.
- SSR process hidup.
