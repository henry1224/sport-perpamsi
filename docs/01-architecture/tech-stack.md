# Tech Stack v1

## Keputusan Stack

- Backend: Laravel.
- Frontend: Inertia + Vue.js.
- SSR: aktif untuk public page.
- Admin/panitia: Inertia client-side biasa, SSR boleh dimatikan.
- Database: PostgreSQL.
- Cache/queue: Redis bila tersedia; fallback database queue hanya untuk development.
- File storage: object storage atau storage server yang bisa dibackup.

## Alasan

- Laravel + Inertia + Vue cukup untuk public, admin, dan panitia dalam satu codebase.
- Laravel routing, policy, validation, queue, cache, dan migration mempercepat development.
- Inertia mengurangi kebutuhan membuat API frontend terpisah untuk admin/panitia.
- Vue cocok untuk live score, bracket, table, dan panel input skor.
- SSR berguna untuk public page karena initial HTML lebih cepat tampil dan lebih SEO-friendly.

## Batasan SSR

- SSR membutuhkan Node.js server/process di production.
- SSR dipakai untuk public route: home, live score, jadwal, bracket, ranking, profil PDAM, info event.
- SSR tidak wajib untuk admin dan panitia karena halaman login/private tidak butuh SEO.
- SSR process wajib dimonitor dan direstart saat deploy.

## Keputusan ID

- Primary key internal memakai `bigint` auto-increment.
- URL detail non-readable memakai `public_id` UUID.
- URL readable memakai `slug` untuk event, PDAM, cabor, kategori, venue, dan konten.
- Relasi database memakai `bigint` agar index kecil, join cepat, dan migration sederhana.
- UUID tidak dipakai sebagai primary key utama v1 karena dataset event belum membutuhkan distributed ID.
- `public_id` dipakai untuk menghindari ID enumeration pada halaman public atau link share.

## Struktur Aplikasi Laravel

- Controller tipis.
- Validasi di Form Request.
- Authorization di Policy/Gate.
- Business logic di Action/Service.
- Query table besar di Query Object atau scope model.
- Status transition match tidak ditulis langsung di controller.
- Audit log dipanggil dari service/action yang mengubah data penting.

## Referensi

- Laravel Inertia frontend: https://laravel.com/docs/13.x/frontend
- Inertia SSR: https://inertiajs.com/docs/v3/advanced/server-side-rendering
