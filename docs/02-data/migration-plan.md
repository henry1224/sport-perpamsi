# Migration Plan Laravel/PostgreSQL v1

## Urutan Migration

1. users, roles, permissions, role_user.
2. provinces, regencies.
3. events.
4. pdams.
5. sports, sport_categories.
6. venues.
7. teams, athletes, athlete_team.
8. participant_documents, verification_records.
9. matches, match_participants.
10. match_scores, match_score_segments, match_results.
11. brackets, bracket_nodes.
12. standings, ranking_snapshots.
13. committee_assignments.
14. announcements, banners, livestream_links.
15. imports, export_jobs.
16. audit_logs.

## Default Field

- `id` sebagai `bigint` primary key.
- `public_id` UUID untuk entity yang tampil di URL public.
- `slug` untuk entity public yang readable.
- `created_at` dan `updated_at`.
- `deleted_at` hanya untuk entity yang boleh diarsipkan.

## Constraint Wajib

- Foreign key untuk relasi inti.
- Unique email user.
- Unique `public_id` per table.
- Unique `slug` sesuai scope.
- Check score tidak negatif.
- Unique assignment per user, role, event, scope.
- Audit log tidak memakai soft delete.

## Seed Awal

- Role default.
- Permission default.
- Provinsi dan kabupaten/kota Indonesia.
- Cabor v1: bulu tangkis, futsal, tenis meja, tenis lapangan, voli, catur.
- Format kompetisi default.
- Status dictionary aplikasi.
