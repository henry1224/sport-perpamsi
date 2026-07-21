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
| GET | `/agenda` | Agenda event per tanggal/venue/cabor |
| GET | `/matches/{public_id}` | Detail match public |
| GET | `/brackets` | Bracket per cabor/kategori |
| GET | `/standings` | Klasemen/ranking public |
| GET | `/rankings/regional-committees` | Klasemen medali Pimpinan Daerah |
| GET | `/pdams/{slug}` | Profil PDAM |
| GET | `/info` | Info event dan pengumuman |

## Admin/Panitia Write Actions

| Method | Path | Fungsi |
|---|---|---|
| POST | `/admin/events` | Buat event |
| POST | `/admin/pdams` | Buat PDAM |
| POST | `/admin/event-entries` | Daftarkan PDAM/tim/atlet ke cabor; Pimpinan Daerah ditetapkan otomatis |
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
/sports/mini-football
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

## Addendum v2: Query Public dan Throttle

### Public List Query

| Endpoint | Query |
|---|---|
| `GET /pdams` | `page`, `per_page`, `search`, `province`, `regency` |
| `GET /rankings/regional-committees` | `page`, `per_page`, `event`, `sport`, `category`, `province`, `search` |
| `GET /brackets` | `event`, `sport`, `category`, `round`, `mode` |
| `GET /matches` | `page`, `per_page`, `event`, `sport`, `category`, `venue`, `status`, `date`, `search` |

### Bracket Response Shape

```json
{
  "event": "porpamnas-ix-kaltim",
  "sport": "badminton",
  "category": "ganda-campuran",
  "mode": "round-64",
  "rounds": [],
  "left_rounds": [],
  "right_rounds": [],
  "final_match": null,
  "early_rounds_url": "/brackets?mode=early&sport=badminton&category=ganda-campuran"
}
```

### Throttle Minimum

- Public search: 30 request/menit/IP.
- Public bracket/ranking cacheable: 120 request/menit/IP.
- Admin write score: 20 request/menit/user.
- Login: gunakan throttle Laravel default atau lebih ketat.

### SSR dan Cache Header

- SSR untuk initial public page.
- Data sering berubah pakai short cache dan ETag bila memungkinkan.
- Admin/panitia write tidak boleh cache.
