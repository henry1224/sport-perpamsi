# Data Dictionary v1

## Event

- id: ID event.
- public_id: UUID untuk link publik bila diperlukan.
- slug: URL readable event.
- name: nama event.
- start_date: tanggal mulai.
- end_date: tanggal selesai.
- location: lokasi utama.
- status: draft, published, archived.

## PDAM

- id: ID PDAM.
- public_id: UUID untuk link publik bila diperlukan.
- slug: URL readable PDAM.
- name: nama PDAM.
- region: wilayah.
- logo_url: logo.
- contact_name: nama perwakilan.
- contact_phone: kontak perwakilan.

## Team

- id: ID tim.
- event_id: event.
- pdam_id: pemilik tim.
- sport_id: cabor.
- category_id: kategori.
- name: nama tim.
- verification_status: draft, diajukan, diverifikasi, ditolak, revisi.

## Athlete

- id: ID atlet.
- pdam_id: PDAM.
- name: nama atlet.
- identity_number: nomor identitas internal event bila dipakai.
- verification_status: status verifikasi.

## Match

- id: ID match.
- public_id: UUID untuk URL detail match public.
- event_id: event.
- sport_id: cabor.
- category_id: kategori.
- venue_id: venue.
- participant_a_id: peserta A.
- participant_b_id: peserta B.
- scheduled_at: waktu pertandingan.
- status: draft, terjadwal, berlangsung, jeda, selesai, final, revisi.
- winner_id: pemenang.
- finalized_at: waktu finalisasi.

## Score

- id: ID skor.
- match_id: match.
- participant_a_score: skor peserta A.
- participant_b_score: skor peserta B.
- period_label: set/babak/ronde bila perlu.
- updated_by: user terakhir.
- updated_at: waktu update.

## AuditLog

- id: ID audit.
- actor_id: user.
- action: aksi.
- entity_type: tipe entitas.
- entity_id: ID entitas.
- before_value: nilai sebelum.
- after_value: nilai sesudah.
- created_at: waktu aksi.
