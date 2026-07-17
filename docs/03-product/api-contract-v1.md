# API Contract v1

## Prinsip

- Contract ini baseline untuk Laravel/Inertia dan endpoint public ringan.
- Admin/panitia boleh memakai Inertia page props.
- Public data yang sering diakses wajib bisa cacheable.
- Semua write action wajib cek permission backend.

## Public Read Endpoints

| Method | Path | Fungsi |
|---|---|---|
| GET | `/` | Public home event |
| GET | `/live-score` | Match aktif/hasil terbaru |
| GET | `/schedule` | Jadwal public dengan filter |
| GET | `/matches/{public_id}` | Detail match public |
| GET | `/brackets` | Bracket per cabor/kategori |
| GET | `/standings` | Klasemen/ranking public |
| GET | `/rankings/pdams` | Ranking medali per PDAM |
| GET | `/rankings/regencies` | Ranking medali per kabupaten/kota |
| GET | `/rankings/provinces` | Ranking medali per provinsi |
| GET | `/pdams/{slug}` | Profil PDAM |
| GET | `/info` | Info event dan pengumuman |

## Admin/Panitia Write Actions

| Method | Path | Fungsi |
|---|---|---|
| POST | `/admin/events` | Buat event |
| POST | `/admin/pdams` | Buat PDAM |
| POST | `/admin/sports` | Buat cabor |
| POST | `/admin/teams` | Buat tim |
| POST | `/admin/athletes` | Buat atlet |
| POST | `/admin/matches` | Buat match |
| POST | `/admin/assignments` | Assign panitia |
| POST | `/committee/matches/{id}/result` | Input hasil setelah pertandingan selesai |
| POST | `/admin/matches/{id}/finalize` | Finalisasi hasil |
| POST | `/admin/matches/{id}/revision` | Revisi hasil final |
| POST | `/admin/imports/{type}` | Import data |
| GET | `/admin/exports/{type}` | Export data |

## Response Public Ringkas

### Match Card

```json
{
  "public_id": "018f8c2a-7b8e-7c3a-9d2f-6a4b1c0e9f11",
  "sport": "Futsal",
  "category": "Putra",
  "venue": "Venue A",
  "scheduled_at": "2026-09-01T10:00:00+08:00",
  "status": "final",
  "participant_a": "PDAM A",
  "participant_b": "PDAM B",
  "score_a": 3,
  "score_b": 1,
  "winner": "PDAM A"
}
```

## Public URL Identifier

- Event, PDAM, cabor, kategori, venue, dan konten memakai `slug`.
- Match detail memakai `public_id` UUID.
- `id` internal tidak boleh tampil pada endpoint public.

Contoh:

```text
/events/perpamsi-2026
/pdams/pdam-kota-makassar
/sports/futsal
/matches/018f8c2a-7b8e-7c3a-9d2f-6a4b1c0e9f11
```

## Error Format

```json
{
  "message": "Validasi gagal",
  "errors": {
    "score_a": ["Skor tidak boleh negatif"]
  }
}
```
