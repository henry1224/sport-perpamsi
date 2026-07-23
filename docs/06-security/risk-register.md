# Risk Register Sport PERPAMSI

Dokumen ini menjadi daftar risiko aktif. Setiap perubahan alur, data, role, jadwal, pertandingan, atau publikasi wajib meninjau tabel ini.

- Agenda ganda pada venue sama dikendalikan dengan validasi irisan waktu pada create/update; concurrency database diuji sebelum go-live.
- Venue nonaktif tidak dapat dipilih untuk agenda baru; agenda lama tetap dipertahankan untuk histori.
- Kebocoran match lintas assignment dimitigasi dengan query scope dan pemeriksaan objek pada route detail; tanpa assignment menghasilkan daftar kosong.

## Skala

- Dampak: rendah, sedang, tinggi, kritis.
- Status: terbuka, dimitigasi, diterima, ditutup.
- Kontrol wajib harus tersedia sebelum modul dinyatakan selesai.

## Registrasi Pengurus Daerah

| Risiko | Dampak | Kontrol wajib | Verifikasi |
|---|---|---|---|
| Dua pengguna mengajukan provinsi sama bersamaan | Tinggi | Unique pengajuan aktif per provinsi dan transaksi saat submit/verifikasi | Test concurrency/unique constraint |
| Pengajuan membuat PD provinsi duplikat | Tinggi | PD provinsi dibuat dari master provinsi; registrasi hanya mengajukan akses | Test satu provinsi satu PD |
| Akun belum diverifikasi dapat masuk portal | Kritis | `account_status` dicek middleware pada setiap route privat | Feature test akses pending/ditolak |
| Pengguna mengganti provinsi lewat request manual | Kritis | Provinsi diturunkan dari pengajuan terverifikasi; field tidak dipercaya dari client | Feature test tampering |
| Pengajuan ditolak tanpa alasan | Sedang | Alasan wajib dan audit aktor/waktu | Validation test |
| Email atau nomor telepon dipakai ulang | Tinggi | Unique email; normalisasi dan unique nomor telepon bila diwajibkan | Migration dan feature test |
| Akun bersama dipakai banyak orang | Tinggi | Akun personal, reset password, sesi dapat dicabut, audit login | UAT keamanan akun |

## Registrasi Cabor dan Pemain

| Risiko | Dampak | Kontrol wajib | Verifikasi |
|---|---|---|---|
| Pemain ganda pada cabor/kategori sama | Tinggi | Identitas pemain ternormalisasi dan unique sesuai aturan event | Feature test duplikasi |
| Official bertanding melanggar regulasi | Tinggi | Backend membandingkan identitas official dan pemain lintas cabor berdasarkan snapshot `official_can_compete` | Feature test official rangkap |
| Satu PD mendaftarkan cabor sama berulang | Tinggi | `registration_key` unik dan validasi registrasi aktif per PD/event | Feature test registrasi ulang |
| PD mengubah roster saat pending/verified | Kritis | Backend hanya menerima perubahan draft, revisi, ditolak, atau dibatalkan | Feature test transisi status |
| PD mengubah atau membatalkan roster daerah lain | Kritis | Scope `regional_committee_id` dari session diperiksa backend | Feature test horizontal access |
| Pembatalan menghapus histori roster | Tinggi | Pembatalan mengubah status menjadi `cancelled` dan menulis audit | Feature test cancellation |
| Perubahan roster tidak terlacak | Tinggi | Audit before/after untuk seluruh transisi roster | Audit test |
| Request memalsukan PD atau instansi asal | Kritis | Scope PD diambil dari pengguna terautentikasi; request tidak menerima `pdam_id` atau PD | Feature test payload |
| Jumlah pemain melebihi aturan cabor | Tinggi | Batas min/max berasal dari master kategori/peraturan | Boundary test |
| Registrasi dilakukan setelah penutupan | Tinggi | Status event dicek backend, bukan hanya tombol UI | Feature test status |
| Kategori berubah setelah peserta terdaftar | Tinggi | Kategori terkunci setelah registrasi pertama atau memakai workflow migrasi | Test lock kategori |
| Kategori atau sistem pertandingan berbeda dari panduan panitia | Tinggi | Baseline seed dan migration mengacu slide 5-23; kompetisi terpublikasi memakai snapshot | Seeder dan migration test |
| Seluruh master kategori tampil sebagai pilihan PD | Tinggi | Dashboard hanya membaca kompetisi dengan `registration_published_at`; detail unpublished 404 | Feature test publikasi |
| Master kategori berubah setelah publish | Tinggi | Snapshot `registration_rules` pada kompetisi | Feature test snapshot |
| Kompetisi baru otomatis terbuka | Tinggi | Default status `registration_draft`; publish action eksplisit | Migration dan feature test |
| Bracket dikunci sebelum verifikasi selesai | Kritis | Precondition: tidak ada pengajuan menunggu/perbaikan | Feature test bracket lock |
| Penghapusan peserta merusak histori match | Kritis | Restrict delete; gunakan pembatalan/status dan audit | FK test dan UAT |
| Unique lama menolak multi-team sah atau membuat parent ganda | Kritis | Satu parent unik `(event, PD)` dan child team unik `(entry, team_no)` | Migration dan feature test |
| PD membuat team melebihi kuota technical meeting | Tinggi | `max_teams_per_pd` wajib pada snapshot; hitung team aktif secara transaksional | Boundary/concurrency test |
| Client memalsukan nomor atau label team | Tinggi | Nomor dan `PD PERPAMSI {provinsi} #{team_no}` dibentuk server | Payload tampering test |
| Pemain dipindah/substitusi antar-team setelah verified | Kritis | `entry_team_id` immutable setelah verified; koreksi bukan substitusi dan wajib audit | Feature test transfer/swap/delete-create |
| Override verifikasi satu team memengaruhi team lain | Kritis | Effective-status resolver tunggal; override nullable terisolasi dan reset eksplisit | Feature test isolasi/reset override |
| Team belum efektif verified masuk seed/bracket | Kritis | Lock memeriksa setiap effective status team aktif | Feature test bracket lock |
| Team satu PD bertemu di ronde awal walau alternatif tersedia | Tinggi | Snapshot `avoid_same_pd_in_round=true`; generator affiliation-aware | Pairing test |
| Constraint seeding mustahil dipenuhi | Tinggi | Relaksasi deterministik minimum dan audit konflik | Determinism/audit test |
| Nomor team berubah atau dipakai ulang setelah submit | Kritis | `team_no` immutable; cancellation mempertahankan nomor | Lifecycle test |
| Roster historis berubah setelah match | Kritis | Participant dan roster snapshot pada bracket lock/match | History regression test |
| Satu PD mendapat banyak medali tetapi ranking mendeduplikasi kategori | Tinggi | Medal participant per `EntryTeam`; agregasi tanpa deduplikasi per PD/kategori | Ranking multi-medal test |

## Master Cabor dan Peraturan

| Risiko | Dampak | Kontrol wajib | Verifikasi |
|---|---|---|---|
| Format atau template skor tidak cocok | Tinggi | Nilai kode terbatas, label Indonesia terpusat, validasi per cabor | Seeder dan validation test |
| Peraturan berubah saat kompetisi berjalan | Tinggi | Versi peraturan, tanggal berlaku, snapshot versi pada event | Feature test versioning |
| Cabor dihapus saat dipakai | Kritis | `restrictOnDelete` dan arsip/nonaktif | Migration test |
| Status internal tampil mentah | Sedang | Satu kamus label Indonesia untuk seluruh UI/export | UI test/status audit |
| Seeder menimpa perubahan admin | Tinggi | Seeder idempotent hanya mengisi baseline; data operasional tidak ditimpa | Rerun seeder test |
| Publish dilakukan tanpa kategori/regulasi valid | Tinggi | Action Admin memvalidasi kategori aktif, cabor cocok, periode, dan snapshot | Feature test publish |
| Regulasi cabor lain dipasang ke kompetisi | Kritis | Backend memvalidasi regulasi berasal dari cabor kompetisi | Feature test publish |
| Publikasi ditarik setelah peserta masuk | Kritis | Backend menolak unpublish jika entry sudah ada | Feature test unpublish |
| Perubahan status publikasi tanpa jejak | Tinggi | Audit append-only untuk publish, republish, close, dan unpublish | Audit test |

## Venue, Agenda, dan Jadwal

Backup database wajib dibuat sebelum migration penghapusan kategori nonaktif. Recovery memakai restore backup karena migration bersifat forward-only.

| Risiko | Dampak | Kontrol wajib | Verifikasi |
|---|---|---|---|
| Venue dipakai dua kegiatan pada waktu sama | Tinggi | Deteksi overlap dalam transaksi | Feature test overlap |
| Hari tidak sesuai tanggal | Sedang | Hari diturunkan dari tanggal, bukan input manual | Unit test formatter |
| Jam selesai sebelum jam mulai | Tinggi | Check/validation `end_time > start_time` | Validation test |
| Venue nonaktif masih dapat dipilih | Sedang | Query pilihan hanya venue aktif; backend tetap memvalidasi | Feature test |
| Master cabor/kategori dihapus setelah dipakai | Tinggi | Tidak menyediakan endpoint delete; gunakan status aktif dan foreign key restrict | Feature test master data |
| Regulasi lama tertimpa revisi | Tinggi | Setiap revisi membuat nomor versi baru dan audit append-only | Feature test versi regulasi |
| Agenda draft tampil publik | Tinggi | Public hanya membaca status terbit | Feature test publikasi |
| Perubahan jadwal tidak terlacak | Tinggi | `event_agenda_audits` menyimpan before/after, aktor, alasan, action, dan waktu | `VenueAgendaManagementTest` |
| Match operasional tidak memiliki agenda, venue, atau waktu | Kritis | Jadwal dianggap valid hanya bila tiga relasi terisi konsisten dalam transaksi | Feature test wiring match |
| Agenda seed dianggap sebagai jadwal pertandingan final | Tinggi | Bedakan baseline agenda dan match operasional; UAT wajib memeriksa relasi | Audit data dan UAT |

## Panitia dan Hak Akses

| Risiko | Dampak | Kontrol wajib | Verifikasi |
|---|---|---|---|
| Panitia membuka cabor lain lewat URL | Kritis | Policy backend memeriksa assignment cabor | Feature test horizontal access |
| Scorekeeper mengubah match bukan tugasnya | Kritis | Assignment match/cabor dicek saat write | Feature test assignment |
| Admin menonaktifkan panitia tetapi sesi tetap aktif | Tinggi | Status pengguna dicek tiap request dan sesi dapat dicabut | Feature test suspended user |
| Finalisasi atau revisi tanpa kewenangan | Kritis | Permission action dan approval terpisah | Feature test RBAC |
| Role berubah tanpa jejak | Tinggi | Audit perubahan role dan assignment | Audit test |
| Panitia ditetapkan ke venue/cabor salah | Tinggi | Pilihan memakai ID master tervalidasi dan perubahan assignment tercatat audit | Feature test assignment Admin |

## Pertandingan, Skor, dan Klasemen

| Risiko | Dampak | Kontrol wajib | Verifikasi |
|---|---|---|---|
| Submit skor ganda | Tinggi | Idempotency/unique score per match dan transaksi | Feature test double submit |
| Skor final direvisi tanpa alasan | Kritis | Alasan wajib, approval, audit append-only | Feature test revisi |
| Pemenang tidak sesuai skor | Kritis | Kalkulasi server berdasarkan template skor | Unit test tiap template |
| Klasemen membaca match belum final | Tinggi | Query hanya status final/terverifikasi | Ranking test |
| Cache publik menampilkan data lama | Sedang | Invalidasi saat publikasi/finalisasi/revisi | Integration test cache |
| Bracket terkunci sebelum kompetisi dipublikasikan | Kritis | Lock mensyaratkan publikasi dan seluruh team efektif verified | Feature test bracket lock |
| Seeder demo membuat ratusan match seolah data operasional | Tinggi | Demo seeder eksplisit, tidak masuk baseline, dan memiliki cleanup terpisah | Rerun seed dan audit jumlah data |

## Data, Import, dan Operasional

| Risiko | Dampak | Kontrol wajib | Verifikasi |
|---|---|---|---|
| Import merusak data existing | Kritis | Preview, validasi, transaksi, dry-run, audit | Import rollback test |
| Dokumen atau data pribadi bocor | Kritis | Storage privat, authorization download, minimisasi props publik | Security test |
| Data hilang saat deployment | Kritis | Backup sebelum migration dan restore test | Runbook evidence |
| Traffic publik memperlambat admin | Tinggi | Cache public, pagination, index, rate limit | Load test |
| Perubahan dilakukan langsung di `main` | Tinggi | `AGENTS.md`, workflow wajib, branch protection/PR | Review Git history |
| Cleanup demo ikut menghapus master atau registrasi resmi | Kritis | Backup, daftar scope eksplisit, transaksi, dry-run jumlah baris, dan verifikasi pasca-cleanup | Restore test dan audit count |
| Kompetisi tidak memiliki kategori atau versi regulasi | Tinggi | Constraint aplikasi sebelum publish dan laporan data tidak lengkap | Feature test publish dan audit data |
| Dua orang berbeda memiliki nama normalisasi sama | Tinggi | Tampilkan informasi rangkap kepada operator dan blokir sesuai snapshot; migrasikan pencocokan ke `player_id`/NIK/KTA saat master identitas tersedia | UAT nama sama dan feature test setelah identitas kanonik tersedia |
| Cabor nonaktif masih dipakai pada transaksi baru | Tinggi | Pilihan dan validasi event/agenda hanya menerima cabor aktif; data historis tetap read-only | Feature test status cabor |

## Definition of Done Risiko

Modul belum `Done` bila:

1. Kontrol risiko kritis atau tinggi belum diterapkan.
2. Test verifikasi belum tersedia.
3. Audit dan rollback belum jelas untuk perubahan data.
4. Dokumen ERD, migration, RBAC, test, dan UAT belum selaras.
| Tipe cabor tidak konsisten antara UI dan database | Sedang | Backend hanya menerima `sport` atau `exhibition`; migration membackfill Padel, Golf, dan Vokal | `MasterDataTest` dan UAT edit master cabor |
| Format bawaan tidak dikenal saat kompetisi dibuat | Tinggi | Satu daftar format backend dipakai validasi dan dikirim ke UI; migration menyelaraskan master existing | `MasterDataTest` dan `TournamentEventPublicationTest` |
