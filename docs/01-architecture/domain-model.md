# Model Domain Sport PERPAMSI

## Bounded Context

### Event Management

Mengelola event, cabor, kategori, venue, jadwal, dan konfigurasi publikasi.

Entities: Event, Sport, Category, Venue, Schedule, EventSetting.

### Participant Management

Mengelola PDAM, tim, atlet, dokumen, dan verifikasi.

Entities: Pdam, Team, Athlete, ParticipantDocument, VerificationRecord.

### Match Operation

Mengelola match, assignment scorekeeper, input hasil setelah pertandingan selesai, status match, finalisasi, dan revisi.

Entities: Match, MatchParticipant, Score, ScoreRevision, CommitteeAssignment.

### Competition Result

Mengelola bracket, klasemen, ranking, dan rekap hasil.

Entities: Bracket, BracketNode, Standing, RankingSnapshot, MedalStanding.

### Public Content

Mengelola info event, pengumuman, banner, livestream URL, dan konten publik.

Entities: Announcement, Banner, LivestreamLink, PublicPageConfig.

### Identity and Access

Mengelola user, role, permission, dan scope akses panitia.

Entities: User, Role, Permission, UserAssignment.

### Audit and Reporting

Mengelola audit log, export, dan snapshot laporan.

Entities: AuditLog, ExportJob, ReportSnapshot.

## Aturan Lintas Context

1. Public hanya membaca data published atau final.
2. Match final hanya bisa berubah lewat score revision.
3. Klasemen dan ranking hanya dihitung dari match final.
4. Assignment panitia membatasi akses scorekeeper ke match tertentu.
5. Audit log wajib untuk perubahan skor, jadwal, verifikasi, assignment, dan finalisasi.
6. Import data tidak langsung commit sebelum preview dan validasi.
7. Reporting membaca data operasional; reporting tidak mengubah source data.
