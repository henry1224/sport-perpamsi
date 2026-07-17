# ERD Konseptual Sport PERPAMSI

## Relasi Utama

```mermaid
erDiagram
    EVENT ||--o{ SPORT : has
    EVENT ||--o{ VENUE : has
    EVENT ||--o{ TEAM : registers
    EVENT ||--o{ MATCH : schedules
    PDAM ||--o{ TEAM : owns
    PDAM ||--o{ ATHLETE : owns
    TEAM ||--o{ ATHLETE : includes
    SPORT ||--o{ CATEGORY : has
    SPORT ||--o{ MATCH : contains
    CATEGORY ||--o{ MATCH : groups
    VENUE ||--o{ MATCH : hosts
    MATCH ||--o{ SCORE : records
    MATCH ||--o{ SCORE_REVISION : revises
    MATCH ||--o{ AUDIT_LOG : audited_by
    USER ||--o{ COMMITTEE_ASSIGNMENT : assigned
    USER ||--o{ AUDIT_LOG : performs
    BRACKET ||--o{ MATCH : contains
    STANDING ||--o{ MATCH : calculated_from
```

## Catatan v1

- ERD ini konseptual, bukan migration final.
- `MatchParticipant` bisa dipakai bila peserta match tidak selalu team, misalnya individu.
- `Standing` dan `RankingSnapshot` boleh dihitung ulang dari match final.
- `AuditLog` wajib append-only.
