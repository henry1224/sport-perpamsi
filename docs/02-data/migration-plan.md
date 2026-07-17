# Migration Plan Laravel/PostgreSQL v1

## Urutan Migration

1. users, roles, permissions, role_user.
2. events.
3. pdams.
4. sports, sport_categories.
5. venues.
6. teams, athletes, athlete_team.
7. participant_documents, verification_records.
8. matches, match_participants.
9. match_scores, match_score_segments, match_results.
10. brackets, bracket_nodes.
11. standings, ranking_snapshots.
12. committee_assignments.
13. announcements, banners, livestream_links.
14. imports, export_jobs.
15. audit_logs.

## Default Field

- `id` sebagai `bigint` primary key.
- `public_id` UUID untuk entity yang tampil di URL public.
- `created_at` dan `updated_at`.
- `deleted_at` hanya untuk entity yang boleh diarsipkan.

## Constraint Wajib

- Foreign key untuk relasi inti.
- Unique email user.
- Unique `public_id` per table.
- Check score tidak negatif.
- Unique assignment per user, role, event, scope.
- Audit log tidak memakai soft delete.

## Seed Awal

- Role default.
- Permission default.
- Cabor v1: bulu tangkis, futsal, tenis meja, tenis lapangan, voli, catur.
- Format kompetisi default.
- Status dictionary aplikasi.
