# Standar PD PERPAMSI dan Registrasi Peserta

> User kedua PD via undangan (butir 8) dan identitas kanonik pemain lintas event (constraint terakhir) belum diimplementasikan. Drift dan aksi lihat `docs/00-project/audit-2026-07-22.md` (D21, D22).

## Tujuan

Menetapkan identitas kontingen, registrasi akun, registrasi cabor, pemain, verifikasi, dan nama publik berbasis PD PERPAMSI. PDAM tidak menjadi identitas kontingen, tetapi wajib dicatat sebagai asal setiap pemain.

## Identitas Kanonik

- Satu provinsi memiliki satu PD PERPAMSI.
- Nama resmi: `PD PERPAMSI {provinces.name}`.
- Contoh: `PD PERPAMSI Aceh`, `PD PERPAMSI Kalimantan Timur`.
- Nama ini dipakai pada portal, peserta, bracket, hasil, klasemen, dan laporan.
- `regional_committees` tetap nama teknis tabel sampai refactor terpisah disetujui.

## Relasi Kanonik

```text
Province 1 ── 1 RegionalCommittee
RegionalCommittee 1 ── N User
RegionalCommittee 1 ── N CommitteeApplication
RegionalCommittee 1 ── N EventEntry
TournamentEvent 1 ── N EventEntry
EventEntry 1 ── N EntryTeam
EntryTeam 1 ── N EntryMember
EntryTeam 1 ── N MatchParticipant/Match
Final Result ── MedalStanding EntryTeam ── RegionalCommittee
```

## Pengajuan Akun Pengurus Daerah

1. Master provinsi dan PD PERPAMSI dibuat lebih dulu oleh sistem/admin.
2. Registrasi publik tidak membuat PD PERPAMSI baru; registrasi membuat pengajuan akses.
3. Pengguna memilih provinsi, lalu mengisi penanggung jawab, jabatan, email, telepon, kata sandi, dan dokumen mandat bila diwajibkan.
4. Satu provinsi hanya boleh memiliki satu pengajuan aktif pada saat yang sama.
5. Akun berstatus menunggu, perlu perbaikan, ditolak, nonaktif, atau ditangguhkan tidak dapat masuk portal.
6. Admin wajib memberi alasan saat menolak atau meminta perbaikan.
7. Setelah verifikasi, pengguna ditautkan ke PD PERPAMSI provinsinya.
8. Penambahan pengguna kedua pada PD yang sama harus melalui undangan atau persetujuan Admin.

## Registrasi Cabor dan Pemain

1. Pengurus Daerah masuk ke portal PD PERPAMSI.
2. Pengurus Daerah memilih paket kompetisi/cabor/kategori yang telah dipublikasikan Admin.
3. Sistem membuat satu parent `event_entry` untuk PD PERPAMSI pada kompetisi tersebut.
4. PD membuat satu atau lebih `entry_teams` sampai batas dinamis hasil technical meeting pada snapshot.
5. Nama team dibentuk server sebagai `PD PERPAMSI {provinsi} #{team_no}`; client tidak boleh mengirim nama atau nomor bebas.
6. Pemain disimpan sebagai banyak `entry_members` pada team, bukan langsung sebagai participant parent atau kolom pemain tetap.
7. Jumlah team serta jumlah/atribut pemain per team divalidasi dari snapshot regulasi kompetisi.
8. Registrasi diajukan pada parent; Admin memverifikasi default parent dan dapat memberi override eksplisit per team.
9. Hanya team dengan effective status `verified` yang dapat masuk seed, grup, bracket, match, dan klasemen.
10. Perpindahan pemain antar-team setelah verified dilarang total.
11. Parent/team tidak dihapus setelah dipakai pertandingan; gunakan status pembatalan dan audit.
12. Semantik lengkap mengikuti [standar multi-team](./team-entry-standard.md).

## Constraint Wajib

- Unique `regional_committees.province_id`.
- Unique pengajuan aktif per provinsi.
- Unique email pengguna.
- Unique parent registrasi PD PERPAMSI per kompetisi.
- Unique `team_no` per parent; jumlah team aktif tidak melebihi snapshot `max_teams_per_pd`.
- Kompetisi tanpa waktu publikasi tidak boleh tampil atau menerima registrasi.
- Unique pemain pada scope event/cabor/kategori sesuai identitas yang disepakati; pemain verified tidak dapat dipindahkan antar-team.
- Foreign key memakai restrict untuk master yang sudah dipakai.
- Perubahan status, verifikasi, role, pemain, dan pembatalan tercatat pada audit log.

## Sumber Kebenaran

- Nama PD: `PD PERPAMSI {provinces.name}`.
- Ruang kerja: `regional_committees`.
- Pengajuan akses: `committee_applications`.
- Pengguna: `users`.
- Registrasi cabor: parent `event_entries`.
- Unit peserta: target `entry_teams`.
- Pemain: `entry_members` milik team.
- Hasil: `matches`, `match_scores`, `score_audits` dengan participant `entry_team_id`.
- Risiko dan kontrol: `docs/06-security/risk-register.md`.
