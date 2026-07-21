# Data Dictionary Target

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
- `sport_categories`: sport_id, code, name, competition_type, scoring_type, min_members, max_members, active.

## SportRule

- `sport_id`, `sport_category_id` nullable.
- `version`, `title`, `content`, `document_path`, `effective_at`, `is_active`.

## TournamentEvent

- `sport_id`, `sport_category_id`, `sport_rule_id`.
- `code`, `name`, `format`, `status`, `registration_open_at`, `registration_close_at`, `seed_locked_at`.

## EventEntry

- `regional_committee_id`: PD peserta.
- `tournament_event_id`: kompetisi.
- `display_name`: snapshot nama PD PERPAMSI.
- `status`, `submitted_at`, `verified_by`, `verified_at`, `verification_note`, `cancelled_at`.

## EntryMember

- `event_entry_id`, `name`, `member_type`, `gender`, `shirt_number`, `position`.
- `identity_hash`/identitas terkontrol untuk pencegahan duplikasi sesuai kebijakan privasi.
- `status` dan catatan verifikasi bila verifikasi per pemain dipakai.

## Venue

- `code`, `name`, `address`, `city`, `capacity`, `contact_name`, `contact_phone`.
- `latitude`, `longitude`, `map_url`, `facilities`, `access_notes`, `photo_path`, `is_active`.

## EventAgenda

- `tournament_event_id` nullable, `sport_id` nullable, `venue_id`.
- `date`, `start_time`, `end_time`, `title`, `type`, `description`, `status`, `published_at`.
- Hari diturunkan dari tanggal.

## SportAssignment

- `user_id`, `sport_id`, `tournament_event_id` nullable, `match_id` nullable.
- `assignment_role`, `is_active`, `assigned_by`, `assigned_at`, `revoked_at`.

## Match, Score, Audit

- Match menyimpan event, venue, slot entry, jadwal, status, pemenang.
- MatchScore menyimpan payload skor dan aktor verifikasi.
- Audit menyimpan before/after, alasan, aktor, dan waktu secara append-only.

Label Indonesia untuk seluruh status mengikuti `docs/00-project/glossary.md`.
