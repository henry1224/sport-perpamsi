# Domain Model Sport PERPAMSI — Nyata dan Target

- **NYATA**: entity memiliki tabel/model atau struktur aktif pada migration saat ini.
- **TARGET**: konsep roadmap yang belum memiliki tabel/schema aktif; tidak boleh dipakai seolah tersedia.
- Detail field nyata mengikuti `docs/02-data/data-dictionary.md` dan migration.

## Identity and Access

**NYATA:** User, CommitteeApplication, SportAssignment.

**TARGET:** Role dan Permission sebagai entity terpisah. Saat ini role hanya string pada `users.role`.

Aturan: status akun dan scope PD/cabor/match diperiksa pada setiap write.

## Regional Delegation

**NYATA:** Province, RegionalCommittee, EventEntry, EntryTeam, EntryMember, dan tabel audit verifikasi per entity.

**TARGET:** VerificationRecord generik. Saat ini audit nyata tersimpan pada `entry_registration_audits`, `entry_team_audits`, dan `entry_member_audits`.

Aturan: satu provinsi satu PD PERPAMSI; satu `EventEntry` menjadi parent registrasi PD/kompetisi; satu atau lebih `EntryTeam` menjadi unit peserta; pemain tidak bergantung pada PDAM dan dimiliki tepat satu team. Status efektif team mengikuti override team atau status default parent sesuai [standar multi-team](../02-data/team-entry-standard.md).

## Competition Master

**NYATA:** Sport, SportCategory, SportRegulation, TournamentEvent, Venue, EventAgenda.

`SportRule` bukan entity nyata; nama kanonik saat ini `SportRegulation` / `sport_regulations`.

Aturan: peraturan berversi, `max_members` dapat null, publikasi membuat snapshot aturan, venue tidak bentrok, master terpakai tidak dihapus.

## Tournament Operations

**NYATA:** EntryTeam, TournamentMatch (`matches`), MatchScore, ScoreAudit. Seed dasar tersimpan pada `entry_teams.seed_no` dan bracket lewat relasi match.

**TARGET:** SeedingSnapshot, Bracket, Standing, MedalRanking sebagai entity terpisah. Target belum memiliki tabel aktif.

Aturan: seed, bracket, match, hasil, standing, dan medali menunjuk `EntryTeam`; bracket lock memiliki precondition effective status verified. Participant/roster snapshot penuh masih target bila belum ada struktur migration.

## Public and Reporting

**NYATA:** query/projection dari `PublicDataService` dan audit per entity.

**TARGET:** PublishedView, AuditLog generik, ExportJob, ReportSnapshot. Tidak ada tabel generik tersebut saat ini.

Aturan: public hanya membaca data terbit/final dan memakai identitas PD PERPAMSI.

## Aturan Lintas Context

1. Client tidak menentukan identitas, scope, status, atau aktor verifikasi.
2. Audit wajib untuk verifikasi, assignment, jadwal, pemain, skor, finalisasi, dan revisi.
3. Import memakai preview, validasi, transaksi, dan rollback.
4. Reporting tidak mengubah source data.
5. Risiko kritis/tinggi harus memiliki kontrol dan test sebelum phase selesai.
6. Informasi teknis publik menggabungkan master aktif dan baseline panduan; transaksi registrasi tetap membaca snapshot kompetisi.
