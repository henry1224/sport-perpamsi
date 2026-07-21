# Risk Register Sport PERPAMSI

Dokumen ini menjadi daftar risiko aktif. Setiap perubahan alur, data, role, jadwal, pertandingan, atau publikasi wajib meninjau tabel ini.

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
| Satu PD mendaftarkan cabor sama berulang | Tinggi | `registration_key` unik dan validasi registrasi aktif per PD/event | Feature test registrasi ulang |
| Request memalsukan PD atau instansi asal | Kritis | Scope PD diambil dari pengguna terautentikasi; request tidak menerima `pdam_id` atau PD | Feature test payload |
| Jumlah pemain melebihi aturan cabor | Tinggi | Batas min/max berasal dari master kategori/peraturan | Boundary test |
| Registrasi dilakukan setelah penutupan | Tinggi | Status event dicek backend, bukan hanya tombol UI | Feature test status |
| Kategori berubah setelah peserta terdaftar | Tinggi | Kategori terkunci setelah registrasi pertama atau memakai workflow migrasi | Test lock kategori |
| Seluruh master kategori tampil sebagai pilihan PD | Tinggi | Dashboard hanya membaca kompetisi dengan `registration_published_at`; detail unpublished 404 | Feature test publikasi |
| Master kategori berubah setelah publish | Tinggi | Snapshot `registration_rules` pada kompetisi | Feature test snapshot |
| Kompetisi baru otomatis terbuka | Tinggi | Default status `registration_draft`; publish action eksplisit | Migration dan feature test |
| Bracket dikunci sebelum verifikasi selesai | Kritis | Precondition: tidak ada pengajuan menunggu/perbaikan | Feature test bracket lock |
| Penghapusan peserta merusak histori match | Kritis | Restrict delete; gunakan pembatalan/status dan audit | FK test dan UAT |

## Master Cabor dan Peraturan

| Risiko | Dampak | Kontrol wajib | Verifikasi |
|---|---|---|---|
| Format atau template skor tidak cocok | Tinggi | Nilai kode terbatas, label Indonesia terpusat, validasi per cabor | Seeder dan validation test |
| Peraturan berubah saat kompetisi berjalan | Tinggi | Versi peraturan, tanggal berlaku, snapshot versi pada event | Feature test versioning |
| Cabor dihapus saat dipakai | Kritis | `restrictOnDelete` dan arsip/nonaktif | Migration test |
| Status internal tampil mentah | Sedang | Satu kamus label Indonesia untuk seluruh UI/export | UI test/status audit |
| Seeder menimpa perubahan admin | Tinggi | Seeder idempotent hanya mengisi baseline; data operasional tidak ditimpa | Rerun seeder test |
| Publish dilakukan tanpa kategori/regulasi valid | Tinggi | Action Admin memvalidasi kategori aktif, cabor cocok, periode, dan snapshot | Feature test publish |

## Venue, Agenda, dan Jadwal

| Risiko | Dampak | Kontrol wajib | Verifikasi |
|---|---|---|---|
| Venue dipakai dua kegiatan pada waktu sama | Tinggi | Deteksi overlap dalam transaksi | Feature test overlap |
| Hari tidak sesuai tanggal | Sedang | Hari diturunkan dari tanggal, bukan input manual | Unit test formatter |
| Jam selesai sebelum jam mulai | Tinggi | Check/validation `end_time > start_time` | Validation test |
| Venue nonaktif masih dapat dipilih | Sedang | Query pilihan hanya venue aktif; backend tetap memvalidasi | Feature test |
| Agenda draft tampil publik | Tinggi | Public hanya membaca status terbit | Feature test publikasi |
| Perubahan jadwal tidak terlacak | Tinggi | Audit sebelum/sesudah, aktor, alasan | Audit test |

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

## Data, Import, dan Operasional

| Risiko | Dampak | Kontrol wajib | Verifikasi |
|---|---|---|---|
| Import merusak data existing | Kritis | Preview, validasi, transaksi, dry-run, audit | Import rollback test |
| Dokumen atau data pribadi bocor | Kritis | Storage privat, authorization download, minimisasi props publik | Security test |
| Data hilang saat deployment | Kritis | Backup sebelum migration dan restore test | Runbook evidence |
| Traffic publik memperlambat admin | Tinggi | Cache public, pagination, index, rate limit | Load test |
| Perubahan dilakukan langsung di `main` | Tinggi | `AGENTS.md`, workflow wajib, branch protection/PR | Review Git history |

## Definition of Done Risiko

Modul belum `Done` bila:

1. Kontrol risiko kritis atau tinggi belum diterapkan.
2. Test verifikasi belum tersedia.
3. Audit dan rollback belum jelas untuk perubahan data.
4. Dokumen ERD, migration, RBAC, test, dan UAT belum selaras.
