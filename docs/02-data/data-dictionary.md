# Data Dictionary Target

## Venue dan Agenda Phase 5

- `venues.is_active`: hanya venue aktif dapat dipilih untuk agenda baru.
- `venues.facilities`, `map_url`, `contact_name`, `contact_phone`: detail operasional lokasi.
- `event_agendas.tournament_event_id`: kompetisi terkait, nullable untuk acara umum.
- `event_agendas.published_at`: agenda hanya tampil publik setelah terisi.
- Konflik agenda ditolak bila tanggal, venue, dan rentang waktunya beririsan.
- `matches.event_agenda_id`, `venue_id`, `scheduled_at`: sumber jadwal dan scope akses panitia.

## RegionalCommittee / PD PERPAMSI

- `id`: ID internal.
- `public_id`: UUID publik bila dibutuhkan.
- `province_id`: FK unik ke provinsi.
- `name`: `PD PERPAMSI {nama provinsi}`, dibentuk server.
- `is_active`: status penggunaan.

## CommitteeApplication

- `regional_committee_id`: PD yang diajukan.
- `applicant_name`, `position`, `email`, `phone`.
- `mandate_document_path`: dokumen privat opsional/wajib sesuai kebijakan.
- `status`: pending, revision_required, verified, rejected.
- `review_note`, `reviewed_by`, `reviewed_at`, `submitted_at`.

## User

- `regional_committee_id`: scope PD untuk Pengurus Daerah.
- `role`, `account_status`, `email_verified_at`, `last_login_at`.
- Password disimpan hashed; tidak pernah diexport.

## Sport dan Category

- `sports`: code, name, type, description, active.
- `sport_categories`: sport_id, code, name, competition_type, scoring_type, min_members, max_members nullable, active. `max_members = null` berarti panduan tidak menetapkan maksimum.
- `sport_regulations`: sport_id, version, title, content, document_url, is_active, created_by.
- `master_data_audits`: entity_type, entity_id, action, before_json, after_json, user_id.

## SportRule

- `sport_id`, `sport_category_id` nullable.
- `version`, `title`, `content`, `document_path`, `effective_at`, `is_active`.

## TournamentEvent

- `sport_id`, `sport_category_id`, `sport_rule_id`.
- `code`, `name`, `format`, `status`, `registration_open_at`, `registration_close_at`, `seed_locked_at`.
- `registration_published_at`, `registration_published_by`: waktu dan Admin yang menetapkan paket registrasi resmi.
- `registration_rules`: snapshot kategori, format, tipe skor, serta batas pemain saat publikasi; key maksimum bernilai null harus dipertahankan sebagai tanpa batas.
- `sport_regulation_id`: versi regulasi resmi yang dipilih Admin untuk kompetisi.
- `event_publication_audits`: action, before/after, aktor, dan waktu publish, publish ulang, tutup, atau tarik publikasi.

## EventEntry

- `regional_committee_id`: PD peserta.
- `tournament_event_id`: kompetisi.
- `registration_key`: key unik `{event_id}:{regional_committee_id}` untuk registrasi baru; null pada data legacy.
- `display_name`: snapshot nama PD PERPAMSI.
- `verification_status`, `submitted_at`, `verified_by`, `verified_at`, `verification_note`.
- Status roster: `draft`, `pending`, `revision_required`, `verified`, `rejected`, `cancelled`.
- `entry_registration_audits`: action, before/after roster, aktor, dan waktu perubahan.
- `pdam_id`, `province_id`, `regency_id`, `athlete_1`, `athlete_2`, `team_name`: kolom legacy sementara, tidak ditulis flow baru.

## EntryMember

- `event_entry_id`, `name`, `normalized_name`, `member_type`, `gender`, `shirt_number`, `position`.
- Unique `event_entry_id + normalized_name` mencegah nama pemain ganda dalam satu roster.
- `identity_hash`/identitas terkontrol untuk pencegahan duplikasi sesuai kebijakan privasi.
- `status` dan catatan verifikasi bila verifikasi per pemain dipakai.
- Official tidak masuk tabel ini.

## Venue

- `code`, `name`, `address`, `city`, `capacity`, `contact_name`, `contact_phone`.
- `latitude`, `longitude`: koordinat opsional dengan rentang valid bumi.
- `map_url`: URL Google Maps opsional; public memakai koordinat atau alamat sebagai fallback.
- `facilities`, `access_notes`, `photo_path`, `is_active`.

## EventAgenda

- `tournament_event_id` nullable, `sport_id` nullable, `venue_id`.
- `date`, `start_time`, `end_time`, `title`, `type`, `description`, `status`, `published_at`.
- Hari diturunkan dari tanggal.

## SportAssignment

- `user_id`, `sport_id`, `venue_id`; scope event/match ditambahkan saat jadwal pertandingan memakai relasi venue.
- `assignment_role`, `is_active`, `assigned_by`, `assigned_at`, `revoked_at`.
- `sport_assignment_audits` mencatat penetapan, aktivasi ulang, dan pencabutan.

## Match, Score, Audit

- Match menyimpan event, venue, slot entry, jadwal, status, pemenang.
- MatchScore menyimpan payload skor dan aktor verifikasi.
- Audit menyimpan before/after, alasan, aktor, dan waktu secara append-only.

Label Indonesia untuk seluruh status mengikuti `docs/00-project/glossary.md`.
- `event_agenda_audits`: agenda, action, alasan perubahan, before/after JSON, user, dan waktu untuk update/publish.
