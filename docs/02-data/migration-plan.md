# Migration Plan Laravel/PostgreSQL

Migration wajib mengikuti [delegation-standard.md](./delegation-standard.md) dan [risk-register.md](../06-security/risk-register.md).
Penghapusan struktur wajib mengikuti [database-lifecycle-standard.md](./database-lifecycle-standard.md).

## Urutan Migration Target

1. `users`: tambah `account_status`, kontak, dan waktu verifikasi.
2. `provinces`, `regencies`, `regional_committees`.
3. `committee_applications`: pengajuan akses PD PERPAMSI.
4. `sports`, `sport_categories`, `sport_rules`.
5. `venues`: detail lokasi, fasilitas, peta, kontak, dan status aktif.
6. `tournament_events`: status, batas registrasi, dan versi peraturan.
7. `event_entries`: relasi PD PERPAMSI tanpa PDAM.
8. `entry_members`: daftar pemain fleksibel.
9. `event_agendas`: relasi kompetisi, status publikasi, dan deskripsi.
10. `matches`, `match_scores`, `score_audits`.
11. `sport_assignments`: panitia per cabor/match.
12. `audit_logs`, import, dan export jobs.

## Migrasi Data Lama

1. Backup database dan uji restore.
2. Buat kolom/tabel baru tanpa menghapus kolom lama.
3. Ubah nama PD menjadi `PD PERPAMSI {nama provinsi}`.
4. Migrasikan entry lama ke `regional_committee_id` dan `entry_members`.
5. Validasi jumlah entry, pemain, match, dan hasil sebelum/sesudah.
6. Ubah aplikasi membaca model baru.
7. Hapus ketergantungan `pdam_id`, `province_id`, `regency_id`, `athlete_1`, `athlete_2`, dan `team_name` setelah verifikasi produksi.
8. Tabel PDAM legacy tidak dihapus pada migration yang sama; arsipkan setelah tidak ada referensi.

## Status Phase 2 — 21 Juli 2026

- Registrasi baru menulis `regional_committee_id`, `registration_key`, dan `entry_members`; `pdam_id` bernilai null.
- `athlete_1` dan `athlete_2` lama dibackfill ke `entry_members` tanpa menghapus kolom sumber.
- Seeder demo masih menulis kolom legacy untuk kompatibilitas bracket, lalu membuat roster pada `entry_members`.
- Penghapusan kolom/tabel legacy ditunda sampai audit referensi, observasi rilis, backup, dan upgrade test lulus.

## Constraint Wajib

- Unique satu PD PERPAMSI per provinsi.
- Unique satu pengajuan aktif per provinsi.
- Unique email pengguna.
- Unique assignment per pengguna, role tugas, dan scope.
- Unique registrasi per PD/kompetisi sesuai aturan kategori.
- Check waktu agenda dan batas jumlah pemain pada layer aplikasi/database yang memungkinkan.
- Restrict delete untuk master yang sudah dipakai.

## Seed Awal

- Provinsi Indonesia dan PD PERPAMSI per provinsi.
- Super Admin; akun PD demo hanya untuk environment non-production.
- Cabor, kategori, peraturan baseline, dan label status Indonesia.
- Venue dan agenda resmi yang sudah diverifikasi.
- Seeder tidak membuat akun produksi dengan password default.

## Rollback

- Migration perubahan relasi wajib punya backup dan prosedur forward-fix.
- Rollback tidak boleh menghidupkan kembali identitas lama yang sudah dinyatakan tidak berlaku.
- Perubahan destructive dilakukan terpisah setelah verifikasi data dan persetujuan.
