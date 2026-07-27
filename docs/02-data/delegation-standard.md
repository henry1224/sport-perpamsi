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
5. Hanya akun dengan `users.account_status = verified` dapat masuk portal PD. Vocabulary nyata dan status target mengikuti [`STATUS-VOCABULARY.md`](./STATUS-VOCABULARY.md); `nonaktif` dan `ditangguhkan` belum tersedia pada kode/DB.
6. Admin wajib memberi alasan saat menolak atau meminta perbaikan.
7. Setelah verifikasi, pengguna ditautkan ke PD PERPAMSI provinsinya.
8. Penambahan pengguna kedua pada PD yang sama harus melalui undangan atau persetujuan Admin.

## Registrasi Cabor dan Pemain

Dokumen ini hanya menetapkan identitas PD dan kepemilikan data. Seluruh semantik parent entry, multi-team, kuota, pemain/official, snapshot, verifikasi hybrid, roster lock, seeding, hasil, dan medali **hanya** mengikuti [Standar Entry dan Multi-Team](./team-entry-standard.md).

Batas integrasi:

1. Pengurus Daerah memilih `TournamentEvent` yang telah dipublikasikan Admin.
2. `EventEntry` dimiliki `RegionalCommittee`; `EntryTeam` menjadi unit peserta; `EntryMember` menjadi anggota/official sesuai standar kanonik.
3. PDAM hanya asal pemain, bukan identitas kontingen.
4. Status dan label wajib mengikuti [`STATUS-VOCABULARY.md`](./STATUS-VOCABULARY.md).

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
- Unit peserta nyata: `entry_teams`.
- Pemain: `entry_members` milik team.
- Hasil: `matches`, `match_scores`, `score_audits` dengan participant `entry_team_id`.
- Risiko dan kontrol: `docs/06-security/risk-register.md`.
