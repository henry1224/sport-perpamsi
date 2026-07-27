# Deployment Standard v1

## Environment

- Local: development developer.
- Staging: UAT panitia dan simulasi event.
- Production: event live.

## Production Minimum

- Domain dan SSL aktif.
- Database backup otomatis.
- PostgreSQL restore test minimal sekali sebelum go-live.
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
# Batas Upload Registrasi

- Reverse proxy wajib menerima request minimal `512M` (`client_max_body_size 512M` pada Nginx).
- PHP wajib memakai `upload_max_filesize=2M`, `post_max_size=512M`, dan `max_file_uploads=100`.
- Batas bisnis per dokumen dikontrol aplikasi sebesar 1 MB. PHP dibuat sedikit lebih besar agar file 1–2 MB mendapat pesan validasi Laravel, bukan error server.
- Setelah konfigurasi PHP/Nginx berubah, restart service dan verifikasi nilai aktif sebelum UAT registrasi.
