# Database dan Seeder Lifecycle Standard

Dokumen ini wajib dibaca sebelum mengubah migration, tabel, kolom, foreign key, model, factory, atau seeder.

## Klasifikasi

- **Active**: dibaca/ditulis aplikasi, route, job, test, atau laporan.
- **Legacy Active**: bukan model target, tetapi masih dibutuhkan alur lama atau migrasi data.
- **Deprecated**: tidak dipakai flow baru, memiliki pengganti, dan menunggu masa observasi.
- **Dead**: tidak memiliki referensi runtime, data wajib sudah dimigrasikan, dan aman dihapus.

Label `legacy` bukan izin menghapus.

## Gate Sebelum Menghapus Database

Semua poin wajib terpenuhi:

1. Cari referensi tabel/kolom pada `app`, `database`, `routes`, `resources`, `tests`, laporan, import, dan export.
2. Audit foreign key masuk/keluar, index, trigger, view, dan query raw PostgreSQL.
3. Hitung row dan data orphan pada database target.
4. Tentukan tabel/kolom pengganti dan jalankan backfill.
5. Bandingkan jumlah, checksum/sampel, serta hasil bisnis sebelum/sesudah.
6. Ubah aplikasi membaca struktur baru lebih dulu.
7. Lewati minimal satu release/masa observasi tanpa read/write ke struktur lama.
8. Backup dan restore test tersedia.
9. Migration destructive dibuat terpisah dan memiliki prosedur forward-fix.
10. ERD, data dictionary, migration plan, risk register, test strategy, dan UAT diperbarui.

Jika satu poin gagal, struktur tetap dipertahankan.

## Status Tabel Saat Ini

| Tabel | Status | Alasan |
|---|---|---|
| users, sessions, password_reset_tokens | Active | Auth dan akun |
| provinces, regional_committees | Active | Identitas PD PERPAMSI |
| committee_applications, committee_application_audits | Active | Pengajuan dan audit akun PD |
| sports, sport_categories, tournament_events | Active | Master dan kompetisi |
| event_agendas, venues | Active | Agenda publik |
| event_entries, matches, match_scores, score_audits | Active | Registrasi legacy dan pertandingan |
| cache, cache_locks, jobs, job_batches, failed_jobs | Active framework | Infrastruktur Laravel; jangan hapus hanya karena belum terlihat di UI |
| pdams, regencies | Legacy Active | Masih direferensikan registrasi cabor, controller, public service, seeder, dan test |

## Aturan Seeder

- Seeder baseline harus idempotent.
- Seeder tidak boleh menghapus seluruh tabel atau menimpa data operasional Admin.
- Seeder production tidak membuat password default atau data pribadi contoh.
- Seeder demo dipisahkan dari master dan hanya dijalankan pada environment non-production.
- Urutan seeder mengikuti foreign key: master → relasi → demo.
- Perubahan kode/status master memakai key stabil, bukan ID hardcoded.
- Sebelum menghapus seeder, cari pemanggilan pada `DatabaseSeeder`, test, deployment, dan runbook.
- Jalankan `migrate:fresh --seed` dan seed ulang pada database berisi perubahan Admin untuk membuktikan tidak ada overwrite.

## Bukti Review Wajib

Pull request perubahan database/seeder wajib mencantumkan:

- Daftar struktur yang berubah.
- Hasil audit referensi.
- Query validasi data.
- Strategi backfill dan rollback/forward-fix.
- Test migration fresh dan upgrade.
- Dampak seeder production/demo.
