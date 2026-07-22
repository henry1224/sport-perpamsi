# Public Identifier Standard

> Slug dan `public_id` belum diterapkan pada seluruh entitas publik. Drift dan aksi implementasi lihat `docs/00-project/audit-2026-07-22.md` (D8).

- ID internal `bigint` tidak boleh tampil publik.
- `public_id` UUID untuk resource sensitif atau detail transaksi.
- `slug` untuk event, PD PERPAMSI, cabor, kategori, venue, dan konten.
- Slug PD dibentuk dari nama resmi, contoh `pd-perpamsi-kalimantan-timur`.
- Perubahan nama tidak boleh memutus URL lama; simpan redirect bila slug berubah.
- Endpoint privat tetap memeriksa authorization meski memakai UUID.

```text
/participants/pd-perpamsi-kalimantan-timur
/sports/mini-football
/venues/bscc-dome
/matches/{public_id}
```
