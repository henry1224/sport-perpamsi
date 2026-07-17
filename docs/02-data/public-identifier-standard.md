# Public Identifier Standard v1

## Keputusan

- `id` internal memakai `bigint` dan tidak tampil di URL publik.
- `slug` dipakai untuk halaman publik yang readable dan SEO-friendly.
- `public_id` UUID dipakai untuk detail entity yang rawan bentrok atau tidak cocok memakai slug.

## Field

| Field | Fungsi | Contoh | Tampil Publik |
|---|---|---|---|
| `id` | Primary key internal | `123` | Tidak |
| `public_id` | Identifier publik aman | `018f8c2a-7b8e-7c3a-9d2f-6a4b1c0e9f11` | Ya |
| `slug` | URL readable | `pdam-kota-makassar` | Ya |

## Entity yang Memakai Slug

- Event: `/events/perpamsi-2026`.
- PDAM: `/pdams/pdam-kota-makassar`.
- Cabor: `/sports/mini-football`.
- Kategori: `/brackets/mini-football/putra`.
- Venue: `/venues/gor-a` bila halaman venue dipublikasi.
- Konten/info: `/info/peraturan-umum`.

## Entity yang Memakai Public ID

- Match detail: `/matches/{public_id}`.
- Link share hasil match.
- Dokumen publik terbatas bila nanti ada.
- Entity yang jumlahnya banyak, mirip, berubah nama, atau tidak punya nama natural.

## Pola URL Public

```text
/events/perpamsi-2026
/pdams/pdam-kota-makassar
/sports/mini-football
/schedule?date=2026-10-07&sport=mini-football
/matches/018f8c2a-7b8e-7c3a-9d2f-6a4b1c0e9f11
/brackets/mini-football/putra
/standings/mini-football/putra
```

## Aturan Slug

- Lowercase.
- Gunakan tanda hubung `-`.
- Tidak memakai spasi, underscore, atau karakter khusus.
- Slug harus unique sesuai scope.
- Jika nama berubah, slug lama sebaiknya redirect ke slug baru bila sudah pernah dipublikasi.

## Constraint Minimum

- `events.slug` unique.
- `pdams.slug` unique.
- `sports.slug` unique.
- `sport_categories` unique per `sport_id` dan `slug`.
- `venues.slug` unique per `event_id` bila venue event-specific.
- `matches.public_id` unique.

## Catatan Security

- Jangan expose `id` internal di URL, API public, atau HTML data attribute public.
- Backend tetap resolve slug/public_id menjadi `id` internal sebelum query relasi.
- Permission tetap dicek backend untuk endpoint private, walaupun memakai `public_id`.
