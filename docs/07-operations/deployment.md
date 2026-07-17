# Deployment Standard v1

## Environment

- Local: development developer.
- Staging: UAT panitia dan simulasi event.
- Production: event live.

## Production Minimum

- Domain dan SSL aktif.
- Database backup otomatis.
- Error monitoring aktif.
- Log aplikasi bisa dibaca admin teknis.
- File upload tersimpan di storage yang dibackup.
- Config production tidak memakai debug mode.

## Release Checklist

- Migration berjalan di staging.
- P0 test pass.
- UAT panitia pass.
- Backup production dibuat sebelum release.
- Smoke test setelah deploy.
- Rollback plan tersedia.
