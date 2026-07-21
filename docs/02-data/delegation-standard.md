# Standar PD PERPAMSI dan Registrasi Peserta

## Tujuan

Menetapkan identitas, registrasi akun, registrasi cabor, pemain, verifikasi, dan nama publik tanpa ketergantungan pada PDAM atau instansi asal.

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
EventEntry 1 ── N EntryMember
EventEntry 1 ── N MatchParticipant/Match
Final Match ── MedalStanding RegionalCommittee
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
2. Pengurus Daerah memilih cabor dan kategori yang pendaftarannya dibuka.
3. Sistem membuat satu `event_entry` untuk PD PERPAMSI pada kompetisi tersebut.
4. Nama tampil diturunkan dari PD PERPAMSI; client tidak boleh mengirim nama bebas.
5. Pemain disimpan sebagai banyak `entry_members`, bukan kolom pemain tetap.
6. Jumlah dan atribut pemain divalidasi dari kategori dan versi peraturan cabor.
7. Registrasi diajukan, diperbaiki bila perlu, lalu diverifikasi.
8. Hanya registrasi terverifikasi yang dapat masuk seed, grup, bracket, match, dan klasemen.
9. Registrasi tidak dihapus setelah dipakai pertandingan; gunakan status pembatalan dan audit.

## Constraint Wajib

- Unique `regional_committees.province_id`.
- Unique pengajuan aktif per provinsi.
- Unique email pengguna.
- Unique registrasi PD PERPAMSI per kompetisi, kecuali aturan kategori mengizinkan lebih dari satu entry.
- Unique pemain pada scope event/cabor/kategori sesuai identitas yang disepakati.
- Foreign key memakai restrict untuk master yang sudah dipakai.
- Perubahan status, verifikasi, role, pemain, dan pembatalan tercatat pada audit log.

## Sumber Kebenaran

- Nama PD: `PD PERPAMSI {provinces.name}`.
- Ruang kerja: `regional_committees`.
- Pengajuan akses: `committee_applications`.
- Pengguna: `users`.
- Registrasi cabor: `event_entries`.
- Pemain: `entry_members`.
- Hasil: `matches`, `match_scores`, `score_audits`.
- Risiko dan kontrol: `docs/06-security/risk-register.md`.
