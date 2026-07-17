# Observability Checklist v1

## Health Check

- [ ] App HTTP health check.
- [ ] PostgreSQL connection check.
- [ ] Queue worker check.
- [ ] SSR process check.
- [ ] Storage write/read check.

## Logs

- [ ] Error log Laravel bisa dibaca.
- [ ] Web server log aktif.
- [ ] Queue failure log aktif.
- [ ] SSR error log aktif.
- [ ] Audit log aplikasi aktif.

## Backup

- [ ] Backup otomatis harian aktif.
- [ ] Restore test pernah dilakukan.
- [ ] Backup manual sebelum event.
- [ ] Backup manual setelah event harian.

## Alert Minimum

- [ ] App down.
- [ ] Database down.
- [ ] Queue worker mati.
- [ ] SSR process mati.
- [ ] Disk hampir penuh.
- [ ] Error rate naik.
