# Data Standard Sport PERPAMSI

> Kondisi schema nyata dan entity target wajib dipisahkan. `Bracket`, `Standing`, `MedalRanking`, dan `AuditLog` generik masih target; status agenda sudah tersedia di migration aktif. Detail mengikuti [data dictionary](./data-dictionary.md).

Dokumen kanonik per menu: [SOT Registry](../SOT-REGISTRY.md). Sumber kebenaran identitas akun PD: [delegation-standard.md](./delegation-standard.md). Semantik registrasi peserta: [team-entry-standard.md](./team-entry-standard.md). Kontrol risiko: [risk-register.md](../06-security/risk-register.md).

## Single Source of Truth

- Aplikasi menjadi sumber resmi akun PD, peserta, agenda, jadwal, skor, hasil, bracket, dan klasemen.
- Data publik hanya berasal dari data terbit, terverifikasi, atau final.
- Kode internal tidak ditampilkan mentah; UI memakai label Indonesia dari glossary.
- Pengguna tidak boleh mengirim identitas PD, scope cabor, atau status yang seharusnya diturunkan server.

## Entitas Utama

**NYATA:**

- Province dan RegionalCommittee/PD PERPAMSI.
- CommitteeApplication dan User.
- Sport, SportCategory, SportRegulation, TournamentEvent.
- EventEntry, EntryTeam, dan EntryMember sesuai [standar multi-team](./team-entry-standard.md).
- Venue dan EventAgenda.
- TournamentMatch, MatchScore, ScoreAudit.
- SportAssignment dan audit per entity.

**TARGET — belum memiliki tabel aktif:** Bracket terpisah, Standing, MedalRanking, dan AuditLog generik.

## Relasi Inti

- Satu provinsi memiliki satu PD PERPAMSI.
- Satu PD PERPAMSI memiliki banyak pengguna, pengajuan, registrasi cabor, dan pemain melalui entry.
- Satu cabor memiliki banyak kategori, versi peraturan, kompetisi, agenda, pertandingan, dan assignment panitia.
- Master kategori tidak langsung tampil ke PD; pilihan registrasi berasal dari kompetisi yang dipublikasikan Admin.
- Batas maksimum kategori boleh null; snapshot publikasi menjaga arti tanpa batas meski master berubah.
- Official disimpan terpisah bila modul official dibuat dan tidak dihitung sebagai pemain.
- Satu `EventEntry` menghubungkan PD PERPAMSI dengan satu kompetisi sebagai parent registrasi.
- Satu parent memiliki satu atau lebih `EntryTeam`; setiap team memiliki pemain sesuai snapshot.
- Satu agenda dapat terkait cabor, kompetisi, dan satu venue.
- Satu match terkait kompetisi, venue, dua `EntryTeam`, skor, status, dan pemenang.
- Panitia hanya mengelola cabor atau match yang ditugaskan.

## Status Data

Seluruh nilai nyata, target, label Indonesia, tone badge, dan hubungan antar-status hanya mengikuti [`STATUS-VOCABULARY.md`](./STATUS-VOCABULARY.md). Dokumen ini tidak mendefinisikan ulang enum.

## Master Data

- Cabor, kategori, peraturan, venue, dan agenda dikelola Admin melalui CRUD dengan status aktif/arsip.
- Master yang sudah dipakai tidak dihapus; gunakan nonaktif/arsip.
- Peraturan memakai versi dan tanggal berlaku. Kompetisi menyimpan versi yang digunakan.
- Publikasi registrasi menyimpan snapshot aturan sehingga perubahan master tidak mengubah kompetisi berjalan.
- Hari agenda diturunkan dari tanggal.
- Jadwal wajib menolak bentrok waktu pada venue sama.

## Import dan Export

- Import peserta, pemain, agenda, dan jadwal wajib memiliki preview, validasi, dry-run, transaksi, dan audit.
- Seeder hanya mengisi baseline secara idempotent dan tidak menimpa data operasional Admin.
- Export memakai label Indonesia dan tidak memuat data pribadi yang tidak diperlukan.

## Audit dan Retensi

- Audit wajib untuk verifikasi, role, assignment, pemain, jadwal, skor, finalisasi, revisi, dan publikasi.
- Audit mencatat aktor, aksi, waktu, entitas, alasan, nilai sebelum, dan nilai sesudah.
- Audit append-only dan tidak memakai soft delete.
- Entry yang sudah masuk match tidak boleh dihapus fisik.

## Query dan Performa

- List besar memakai pagination server-side.
- Search memakai debounce frontend, limit backend, rate limit, dan index.
- Public cache 30–120 detik; invalidasi saat publikasi, finalisasi, atau revisi.
- Admin write tidak membaca cache publik.

## Data Lock

- `Pendaftaran Dibuka`: registrasi boleh berubah.
- `Pendaftaran Ditutup`: registrasi baru ditolak; verifikasi dapat diselesaikan.
- `Bracket Dikunci`: seluruh team aktif harus efektif verified; participant/roster/seeding snapshot tidak berubah tanpa workflow unlock/reseed berwenang.
- `Sedang Berlangsung`: hanya operasi pertandingan yang diizinkan.
- `Selesai`: perubahan wajib melalui koreksi beralasan dan audit.
