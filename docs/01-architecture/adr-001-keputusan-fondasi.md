# ADR-001 Keputusan Fondasi v1

## Status

Accepted untuk v1.

## Keputusan

1. Aplikasi v1 berupa web responsive, bukan mobile native.
2. Public, admin, dan panitia berada dalam satu sistem aplikasi.
3. PostgreSQL menjadi database relasional utama.
4. Laravel + Inertia + Vue.js menjadi stack aplikasi.
5. SSR aktif untuk public page, tidak wajib untuk admin/panitia.
6. Primary key internal memakai `bigint`; URL publik memakai `public_id` UUID.
7. Live score v1 memakai polling 5-15 detik.
8. Websocket ditunda sampai load test membuktikan polling tidak cukup.
9. Public endpoint dibuat cacheable.
10. Audit log append-only untuk data penting.
11. Import/export memakai CSV atau Excel.

## Alasan

- Deadline awal September butuh scope kecil dan stabil.
- Web responsive cukup untuk public dan panitia lapangan.
- Laravel + Inertia + Vue mempercepat delivery dalam satu codebase.
- SSR memberi initial HTML lebih baik untuk public page dan SEO.
- Polling lebih cepat dibangun dan cukup untuk 1.000 public user jika endpoint cacheable.
- PostgreSQL cocok untuk relasi event, peserta, match, bracket, klasemen, audit, constraint, index, dan backup production.
- `bigint` menjaga index kecil dan join cepat; UUID tetap dipakai sebagai public identifier.

## Konsekuensi

- UI mobile harus serius karena tidak ada native app.
- Endpoint live score harus ringan.
- Query public wajib terindeks dan dipantau.
- Jika load test gagal, websocket atau cache strategy dinaikkan prioritasnya.
