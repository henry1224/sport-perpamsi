# Data Dictionary Target

> Drift antara kolom yang ditulis di sini dan migration nyata dicatat pada `docs/00-project/audit-2026-07-22.md`. `EntryTeam`, `identity_hash`, status agenda, public ID, dan index jadwal sudah tersedia; gap aktif utama adalah wiring data operasional dan CRUD kompetisi.

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
- `sports.is_active = false`: cabor tetap tersimpan sebagai histori, tetapi tidak tampil publik dan tidak dapat dipilih untuk event atau agenda baru. Relasi lama tidak dihapus otomatis.
- `sports.default_max_officials_per_pd`, `official_roles`, `allow_member_cross_category`, `max_categories_per_member`, `official_can_compete`: default aturan registrasi pada level cabor.
- `sport_categories` menyimpan sport_id, code, name, competition_type (`individual`, `doubles`, atau `team`), scoring_type, min_members, max_members nullable, active. Phase 4B memisahkan unit peserta, kuota team per PD, dan anggota per team pada snapshot kompetisi; publish tidak menerima batas null.
- `sport_categories.default_max_teams_per_pd`: nilai awal kuota team saat Data Lomba dibuat; dapat dioverride pada draft.
- `sport_regulations`: sport_id, version, title, content, document_url, is_active, created_by.
- `master_data_audits`: entity_type, entity_id, action, before_json, after_json, user_id.

## SportRule

- `sport_id`, `sport_category_id` nullable.
- `version`, `title`, `content`, `document_path`, `effective_at`, `is_active`.

## TournamentEvent

- `sport_id`, `sport_category_id`, `sport_rule_id`.
- `code`, `name`, `format`, `status`, `registration_open_at`, `registration_close_at`, `seed_locked_at`.
- `registration_published_at`, `registration_published_by`: waktu dan Admin yang menetapkan paket registrasi resmi.
- `registration_rules`: snapshot kategori, format, tipe skor, `participant_unit`, `min_teams_per_pd`, `max_teams_per_pd`, `min_members_per_team`, `max_members_per_team`, `max_officials_per_pd`, `official_roles`, `allow_member_cross_category`, `max_categories_per_member`, `official_can_compete`, `avoid_same_pd_in_round`, serta versi regulasi.
- `sport_regulation_id`: versi regulasi resmi yang dipilih Admin untuk kompetisi.
- `event_publication_audits`: action, before/after, aktor, dan waktu publish, publish ulang, tutup, atau tarik publikasi.

## EventEntry

- Parent administratif unik per `regional_committee_id + tournament_event_id`.
- `registration_key`: key unik `{event_id}:{regional_committee_id}`; null hanya pada data legacy sebelum backfill.
- `verification_status`: default seluruh team (`draft`, `pending`, `revision_required`, `verified`, `rejected`, `cancelled`).
- `submitted_at`, `verified_by`, `verified_at`, `verification_note`.
- `entry_registration_audits`: action, before/after parent/team/roster, aktor, alasan, dan waktu.
- `pdam_id`, `province_id`, `regency_id`, `athlete_1`, `athlete_2`, `team_name`: kolom legacy sementara, tidak ditulis flow target.

## EntryTeam

- `event_entry_id`: parent registrasi.
- `team_no`: integer positif, unik per parent, dialokasikan server, immutable setelah submit.
- `display_name_snapshot`: `PD PERPAMSI {provinsi} #{team_no}` bila snapshot histori diperlukan.
- `verification_status_override`: nullable; effective status = override atau status parent.
- `verification_note`, `verified_by`, `verified_at`, `cancelled_at` untuk keputusan override.
- `seed_no` dan referensi seeding/match berada pada unit team, bukan parent.
- Team yang sudah dipakai operasi turnamen tidak dihapus; gunakan status.

Status implementasi: tersedia secara kode dan migration; UAT manual masih wajib.

## EntryMember

- `entry_team_id`, `name`, `normalized_name`, `member_type`, `gender`, `shirt_number`, `position`.
- `identity_type`, `identity_number`, `identity_hash`: NIK/KTA dan hash stabil untuk mendeteksi pemain/official rangkap tanpa mengandalkan nama.
- `pdam_id`: asal perusahaan pemain, wajib sebelum submit dan null untuk official; referensi ke Master PDAM nasional.
- `documents`: JSON path file privat. Pemain memakai `photo`, `registration_form`, `identity_card`, `pension_card`, `employee_decree`; official memakai `photo` dan `identity_card`.
- Identitas kanonik/`identity_hash` mencegah pemain sama berada pada dua team dalam kompetisi yang sama.
- `entry_team_id` tidak dapat dipindahkan setelah team efektif verified.
- `status` dan catatan verifikasi bila verifikasi per pemain dipakai.
- Official masuk tabel ini dengan `member_type = official`, `entry_team_id = null`, dan peran pada `position`.

Semantik parent, team, status efektif, dan penguncian roster mengikuti [standar multi-team](./team-entry-standard.md).

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

- Match menyimpan event, venue, slot `entry_team`, jadwal, status, dan pemenang team.
- MatchScore menyimpan payload skor dan aktor verifikasi.
- Audit menyimpan before/after, alasan, aktor, dan waktu secara append-only.
- Match dianggap terjadwal hanya bila `event_agenda_id`, `venue_id`, dan `scheduled_at` terisi konsisten.
- Seeder baseline tidak boleh membuat match operasional; data demo wajib eksplisit dan dapat dibersihkan tanpa menyentuh master.

Label Indonesia untuk seluruh status mengikuti `docs/00-project/glossary.md`.
- `event_agenda_audits`: agenda, action, alasan perubahan, before/after JSON, user, dan waktu untuk update/publish.
