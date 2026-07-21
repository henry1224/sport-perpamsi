# Postgres Standard v1

## Keputusan

Sport PERPAMSI memakai PostgreSQL sebagai database utama v1.

## Alasan

- Relasi data event, peserta, match, bracket, klasemen, role, dan audit cocok memakai database relasional.
- PostgreSQL kuat untuk transaksi, constraint, index, JSON terbatas, dan query reporting.
- Fitur backup, restore, dan observability production matang.

## Prinsip Database

- Database menjadi single source of truth.
- Constraint database dipakai untuk data penting, bukan hanya validasi aplikasi.
- Foreign key dipakai untuk relasi inti.
- Soft delete hanya untuk data operasional yang perlu arsip; audit log tidak dihapus.
- Status memakai enum aplikasi atau lookup table, jangan angka magic.
- Timestamp wajib untuk data penting: `created_at`, `updated_at`, dan bila perlu `deleted_at`.

## Tipe Data Awal

- ID internal: `bigint` auto-increment.
- ID publik: `public_id` UUID untuk URL/link share.
- Slug publik: `slug` untuk halaman readable seperti event, PD PERPAMSI, cabor, kategori, venue, dan konten.
- Nama/kode: `varchar` dengan panjang jelas.
- Deskripsi/catatan: `text`.
- Tanggal/waktu pertandingan: `timestamptz`.
- Skor: `integer` untuk format sederhana; detail set/babak bisa JSON atau tabel score detail bila cabor butuh.
- Audit before/after: `jsonb`.
- Metadata ringan: `jsonb`, hanya bila struktur sering berubah.

## Index Minimum

- `matches(event_id, scheduled_at)` untuk jadwal.
- `matches(event_id, status)` untuk live score.
- `matches(sport_id, category_id, status)` untuk cabor/kategori.
- `event_entries(tournament_event_id, regional_committee_id)` untuk registrasi PD.
- `committee_assignments(user_id, event_id)` untuk akses panitia.
- `audit_logs(entity_type, entity_id, created_at)` untuk audit detail.
- `audit_logs(actor_id, created_at)` untuk audit per aktor.

## Constraint Minimum

- User email unik.
- Satu pengajuan aktif unik per provinsi.
- Registrasi unik per PD PERPAMSI dan kompetisi sesuai aturan kategori.
- Match tidak boleh punya peserta sama pada slot A dan B.
- Score tidak boleh negatif.
- Assignment panitia tidak boleh duplikat untuk user, event, scope, dan role yang sama.
- Public entity wajib punya unique `slug` sesuai scope.
- Entity dengan URL public non-readable wajib punya unique `public_id`.

## Transaction Rules

- Update skor dan audit log harus dalam satu transaksi.
- Finalisasi match dan hitung ulang bracket/klasemen harus konsisten.
- Import data valid harus commit batch; baris invalid tidak boleh masuk diam-diam.
- Revisi skor final wajib menyimpan alasan dan audit dalam satu transaksi.

## Backup dan Restore

- Backup otomatis harian.
- Backup manual sebelum event dimulai.
- Backup manual setelah event selesai setiap hari.
- Restore test minimal sekali sebelum go-live.
- Retensi backup minimum 14 hari selama periode event.

## Yang Belum Dikunci

- Struktur detail skor per cabor.
- Apakah status memakai enum DB, lookup table, atau enum aplikasi.
- Strategi read replica bila traffic public jauh di atas target.
- Partition audit log belum perlu untuk v1.
