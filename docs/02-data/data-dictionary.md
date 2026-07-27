# Data Dictionary — Nyata dan Target

Dokumen ini membedakan schema **NYATA** (tersedia di migration aktif) dan **TARGET** (belum tersedia). Migration tetap bukti akhir kondisi nyata. Vocabulary enum mengikuti [`STATUS-VOCABULARY.md`](./STATUS-VOCABULARY.md).

> Audit 27 Juli 2026 menemukan kolom nyata dan target tercampur. Setiap field target di bawah diberi label eksplisit agar tidak dianggap sudah dapat dipakai kode.

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
- **TARGET — belum ada di migration:** `is_active` untuk status penggunaan.

## CommitteeApplication

**NYATA:**

- `regional_committee_id`, `active_committee_id`: PD yang diajukan dan unique guard pengajuan aktif.
- `user_id`: pemohon; nama, jabatan, email, dan telepon nyata tersimpan pada `users`.
- `status`, `review_note`, `reviewed_by`, `reviewed_at`.

**TARGET — belum ada di migration:**

- `mandate_document_path`: dokumen mandat privat.
- `submitted_at`: waktu submit terpisah dari timestamps standar.
- `applicant_name` bukan target kolom bila identitas user tetap menjadi sumber tunggal.

## User

**NYATA:** `regional_committee_id`, `role`, `account_status`, `email_verified_at`, `name`, `position`, `email`, `phone`, password hashed.

**TARGET — belum ada di migration:** `last_login_at`.

Password tidak pernah diexport. Nilai role dan status mengikuti `STATUS-VOCABULARY.md`.

## Sport dan Category

- **NYATA `sports`:** `public_id`, `code`, `name`, `type`, `default_format`, `score_template`, `is_active`, default aturan official, dan timestamps.
- `sports.is_active = false`: cabor tetap tersimpan sebagai histori, tetapi tidak tampil publik dan tidak dapat dipilih untuk event atau agenda baru. Relasi lama tidak dihapus otomatis.
- `sports.default_max_officials_per_pd`, `official_roles`, `allow_member_cross_category`, `max_categories_per_member`, `official_can_compete`: default aturan registrasi pada level cabor.
- `sport_categories` menyimpan sport_id, code, name, competition_type (`individual`, `doubles`, atau `team`), scoring_type, min_members, max_members nullable, active. Phase 4B memisahkan unit peserta, kuota team per PD, dan anggota per team pada snapshot kompetisi; publish tidak menerima batas null.
- `sport_categories.default_max_teams_per_pd`: sumber kuota team untuk Data Lomba draft; perubahan dilakukan pada Master Kategori dan otomatis disinkronkan sampai Data Lomba dipublikasikan.
- **NYATA `sport_regulations`:** `sport_id`, `version`, `title`, `content`, `document_url`, `technical_guide`, `is_active`, `created_by`.
- **TARGET — belum ada di migration:** scope `sport_category_id` dan tanggal `effective_at`. Nama field file nyata adalah `document_url`, bukan `document_path`.
- **NYATA `master_data_audits`:** `entity_type`, `entity_id`, `action`, `before_json`, `after_json`, `user_id`.

`SportRule` bukan entity/table nyata. Gunakan model `SportRegulation` dan tabel `sport_regulations`.

## TournamentEvent

- `sport_id`, `sport_category_id`, `sport_rule_id`.
- `code`, `name`, `format`, `status`, `registration_open_at`, `registration_close_at`, `seed_locked_at`.
- `registration_published_at`, `registration_published_by`: waktu dan Admin yang menetapkan paket registrasi resmi.
- **NYATA `registration_rules`:** snapshot kategori, format, tipe skor, `max_teams_per_pd`, `min_members_per_team`, `max_members_per_team`, `max_officials_per_pd`, `official_roles`, aturan rangkap, `avoid_same_pd_in_round`, dan referensi regulasi sesuai payload publish saat ini.
- **TARGET — belum terbukti ditulis kode:** `participant_unit`, `min_teams_per_pd`, `snapshot_version`, `member_gender_rule`, `regulation_version`. Jangan dibaca kode sebelum producer snapshot tersedia.
- `sport_regulation_id`: regulasi aktif terbaru dari Master Regulasi saat draft disinkronkan; versi tersebut dikunci saat publikasi.
- `event_publication_audits`: action, before/after, aktor, dan waktu publish, publish ulang, tutup, atau tarik publikasi.

## EventEntry

- Parent administratif unik per `regional_committee_id + tournament_event_id`.
- `registration_key`: key unik `{event_id}:{regional_committee_id}`; null hanya pada data legacy sebelum backfill.
- `verification_status`: status parent (`draft`, `pending`, `revision_required`, `verified`, `rejected`, `cancelled`); `verified` diisi otomatis saat team aktif terakhir disetujui dan kembali `pending` ketika team tambahan diajukan.
- `submitted_at`, `verified_by`, `verified_at`, `verification_note`.
- `entry_registration_audits`: action, before/after parent/team/roster, aktor, alasan, dan waktu.
- `pdam_id`, `province_id`, `regency_id`, `athlete_1`, `athlete_2`, `team_name`: kolom legacy sementara, tidak ditulis flow target.

## EntryTeam

- `event_entry_id`: parent registrasi.
- `team_no`: integer positif, unik per parent, dialokasikan server, immutable setelah submit.
- **NYATA:** `label` menyimpan label team `PD PERPAMSI {provinsi} #{team_no}`.
- **TARGET — belum ada di migration:** `display_name_snapshot` terpisah bila histori membutuhkan snapshot immutable.
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

**NYATA:** `public_id`, `code`, `name`, `address`, `city`, `contact_name`, `contact_phone`, `latitude`, `longitude`, `map_url`, `facilities`, `is_active`.

**TARGET — belum ada di migration:** `capacity`, `access_notes`, `photo_path`.

Koordinat opsional memakai rentang bumi; public memakai koordinat, `map_url`, atau alamat sebagai fallback.

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
