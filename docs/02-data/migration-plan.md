# Migration Plan Laravel/PostgreSQL

Migration wajib mengikuti [delegation-standard.md](./delegation-standard.md) dan [risk-register.md](../06-security/risk-register.md).
Penghapusan struktur wajib mengikuti [database-lifecycle-standard.md](./database-lifecycle-standard.md).

## Urutan Migration Target

1. `users`: tambah `account_status`, kontak, dan waktu verifikasi.
2. `provinces`, `regencies`, `regional_committees`.
3. `committee_applications`: pengajuan akses PD PERPAMSI.
4. `sports`, `sport_categories`, `sport_rules`.
5. `venues`: detail lokasi, fasilitas, peta, kontak, dan status aktif.
6. `tournament_events`: status draft, snapshot regulasi, aktor/waktu publikasi, dan batas registrasi.
7. `event_entries`: relasi PD PERPAMSI tanpa PDAM.
8. `entry_members`: daftar pemain fleksibel.
9. `event_agendas`: relasi kompetisi, status publikasi, dan deskripsi.
10. `matches`, `match_scores`, `score_audits`.
11. `sport_assignments`: panitia per cabor dan venue, dilengkapi audit assignment; scope match mengikuti jadwal venue.
12. `event_agenda_audits`: audit before/after untuk update dan publikasi agenda.
13. Hapus kategori nonaktif dan seluruh data kompetisi turunannya setelah backup database; migration bersifat forward-only.
14. Tambah `venues.latitude` dan `venues.longitude` nullable untuk panduan Google Maps.
12. `audit_logs`, import, dan export jobs.

Status Phase 5: venue memiliki fasilitas, peta, kontak, dan status aktif; agenda memiliki relasi kompetisi, deskripsi, serta waktu publikasi. Agenda seed lama dibackfill sebagai terpublikasi.
Match memiliki relasi agenda, venue, dan waktu mulai; scope panitia mengikuti kombinasi cabor kompetisi dan venue match.

Phase 2 memakai `sport_regulations` untuk versi regulasi dan `master_data_audits` untuk audit perubahan cabor/kategori/regulasi. Master dinonaktifkan, bukan dihapus permanen.

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
- Data legacy tidak dipublikasikan otomatis; Admin meninjau dan mempublikasikan kompetisi yang sah.
- `athlete_1` dan `athlete_2` lama dibackfill ke `entry_members` tanpa menghapus kolom sumber.
- Seeder demo masih menulis kolom legacy untuk kompatibilitas bracket, lalu membuat roster pada `entry_members`.
- Penghapusan kolom/tabel legacy ditunda sampai audit referensi, observasi rilis, backup, dan upgrade test lulus.

## Sinkronisasi Panduan Teknis — 22 Juli 2026

- Migration `2026_07_22_000012` memperbarui Format Bawaan dan sistem skor sembilan cabor.
- Kategori resmi slide 5-23 ditambah atau diperbarui; kategori lama yang tidak tercantum hanya dinonaktifkan, tidak dihapus.
- Kompetisi yang sudah dipublikasikan tetap memakai snapshot dan tidak ikut berubah.
- Rollback data memakai forward-fix agar histori dan relasi lama tidak dihapus.

## Target Migration Phase 4B — Parent Entry dan Multi-Team

Migration Phase 4B wajib dibuat sebagai migration baru; migration lama tidak diedit.

1. Buat `entry_teams` tanpa menghapus relasi lama.
2. Jadikan `event_entries` parent unik untuk setiap `(tournament_event_id, regional_committee_id)`.
3. Backfill satu `EntryTeam` bernomor `1` untuk setiap entry lama yang valid.
4. Backfill label `PD PERPAMSI {province.name} #1` dari master provinsi, bukan input bebas.
5. Tambah `entry_team_id` nullable pada `entry_members`, lalu backfill seluruh member lama ke team `#1`.
6. Tambah FK team nullable pada match/result/seed target; backfill match lama ke team `#1` tanpa rebuild bracket atau mengubah hasil.
7. Tambah snapshot `participant_unit`, batas team/member, dan `avoid_same_pd_in_round` pada kompetisi; event lama ditandai `snapshot_version=legacy` sampai diverifikasi Admin.
8. Audit sebelum constraint: parent tanpa PD, committee tanpa province, duplikasi parent/team, orphan member, match lintas event, dan duplicate seed.
9. Setelah backfill valid, aktifkan unique parent `(event, PD)`, unique team `(event_entry_id, team_no)`, check nomor positif, FK, dan constraint participant satu event.
10. Ubah aplikasi membaca/menulis model baru pada branch implementasi terpisah.
11. Kolom/FK lama hanya dihapus setelah migration upgrade test, UAT, observasi rilis, backup/restore, dan seluruh deletion gate lulus.

Phase 6 tidak boleh membuat seed/bracket baru sebelum match participant memakai `EntryTeam`. Detail domain: [standar multi-team](./team-entry-standard.md).

## Constraint Wajib

- Unique satu PD PERPAMSI per provinsi.
- Unique satu pengajuan aktif per provinsi.
- Unique email pengguna.
- Unique assignment per pengguna, role tugas, dan scope.
- Unique parent registrasi per PD/kompetisi.
- Unique `team_no` per parent dan jumlah team tidak melebihi snapshot `max_teams_per_pd`.
- Check waktu agenda serta batas jumlah team dan anggota per team pada layer aplikasi/database yang memungkinkan.
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
## Koreksi Tipe Master Cabor — 22 Juli 2026

- Nilai `sports.type` dibatasi menjadi `sport` atau `exhibition` pada alur Admin.
- `padel`, `golf`, dan `vocal` dibackfill sebagai `exhibition`; cabor lain sebagai `sport`.
- `sports.default_format` dibackfill dari katalog resmi agar master dan kompetisi draft memakai kode format yang sama.
