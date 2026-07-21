# ERD v1 Baseline

Addendum wajib: satu `province` memiliki satu `regional_committee`; setiap `event_entry` menyimpan `pdam_id`, `province_id`, dan `regional_committee_id`. Lihat [delegation-standard.md](./delegation-standard.md).

## Core Tables

```mermaid
erDiagram
    USERS ||--o{ COMMITTEE_ASSIGNMENTS : has
    PROVINCES ||--o{ REGENCIES : has
    PROVINCES ||--o{ PDAMS : contains
    REGENCIES ||--o{ PDAMS : contains
    EVENTS ||--o{ SPORTS : configures
    EVENTS ||--o{ VENUES : has
    EVENTS ||--o{ MATCHES : schedules
    PDAMS ||--o{ TEAMS : owns
    PDAMS ||--o{ ATHLETES : owns
    SPORTS ||--o{ SPORT_CATEGORIES : has
    SPORT_CATEGORIES ||--o{ MATCHES : groups
    TEAMS ||--o{ MATCH_PARTICIPANTS : plays
    MATCHES ||--o{ MATCH_PARTICIPANTS : has
    MATCHES ||--o{ MATCH_SCORES : records
    MATCHES ||--o{ MATCH_SCORE_SEGMENTS : details
    MATCHES ||--o{ MATCH_RESULTS : finalizes
    MATCHES ||--o{ BRACKET_NODES : appears_in
    BRACKETS ||--o{ BRACKET_NODES : has
    EVENTS ||--o{ RANKING_SNAPSHOTS : has
    PDAMS ||--o{ RANKING_SNAPSHOTS : ranked_as_pdam
    REGENCIES ||--o{ RANKING_SNAPSHOTS : ranked_as_regency
    PROVINCES ||--o{ RANKING_SNAPSHOTS : ranked_as_province
    USERS ||--o{ AUDIT_LOGS : performs
```

## Catatan

- `MATCH_PARTICIPANTS` menjaga fleksibilitas peserta tim/individu.
- `MATCH_SCORE_SEGMENTS` menyimpan set, game, quarter, babak, ronde.
- `MATCH_RESULTS` menyimpan keputusan akhir, walkover, diskualifikasi, atau no contest.
- `BRACKET_NODES` mendukung knockout dan lower bracket lewat field `bracket_type`.
