# Domain Model Sport PERPAMSI

## Identity and Access

Entities: User, CommitteeApplication, Role, Permission, SportAssignment.

Aturan: status akun dan scope PD/cabor/match diperiksa pada setiap write.

## Regional Delegation

Entities: Province, RegionalCommittee, EventEntry, EntryMember, VerificationRecord.

Aturan: satu provinsi satu PD PERPAMSI; entry dan pemain tidak bergantung pada PDAM.

## Competition Master

Entities: Sport, SportCategory, SportRule, TournamentEvent, Venue, EventAgenda.

Aturan: peraturan berversi, `max_members` dapat null, publikasi membuat snapshot aturan, venue tidak bentrok, master terpakai tidak dihapus.

## Tournament Operations

Entities: Match, MatchScore, ScoreAudit, Bracket, Standing, MedalRanking.

Aturan: bracket lock memiliki precondition, skor final direvisi melalui workflow audit.

## Public and Reporting

Entities: PublishedView, AuditLog, ExportJob, ReportSnapshot.

Aturan: public hanya membaca data terbit/final dan memakai identitas PD PERPAMSI.

## Aturan Lintas Context

1. Client tidak menentukan identitas, scope, status, atau aktor verifikasi.
2. Audit wajib untuk verifikasi, assignment, jadwal, pemain, skor, finalisasi, dan revisi.
3. Import memakai preview, validasi, transaksi, dan rollback.
4. Reporting tidak mengubah source data.
5. Risiko kritis/tinggi harus memiliki kontrol dan test sebelum phase selesai.
6. Informasi teknis publik menggabungkan master aktif dan baseline panduan; transaksi registrasi tetap membaca snapshot kompetisi.
