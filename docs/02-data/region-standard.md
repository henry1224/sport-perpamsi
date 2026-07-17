# Region Standard Indonesia v1

## Keputusan

- Sistem menyimpan master provinsi dan kabupaten/kota Indonesia.
- PDAM terhubung ke provinsi dan kabupaten/kota.
- Kode wilayah memakai kode Kemendagri sebagai natural key.
- Primary key internal tetap `bigint`.
- URL public wilayah memakai `slug` bila nanti ada halaman wilayah.

## Data Awal

- Provinsi: 38 data.
- Kabupaten/kota: 514 data.
- Sumber seed: dataset wilayah berbasis Kepmendagri No 300.2.2-2138 Tahun 2025.
- Referensi dataset: https://github.com/cahyadsn/wilayah
- File seed:
  - `data/seed/indonesia_provinces.csv`
  - `data/seed/indonesia_regencies.csv`

## Tabel

### provinces

- id: `bigint` primary key.
- code: kode Kemendagri, unique.
- name: nama provinsi.
- slug: URL readable, unique.
- timestamps.

### regencies

- id: `bigint` primary key.
- province_id: FK ke `provinces.id`.
- code: kode Kemendagri, unique.
- name: nama kabupaten/kota.
- slug: URL readable, unique per province.
- timestamps.

## Relasi ke PDAM

- `pdams.province_id` nullable FK ke `provinces.id`.
- `pdams.regency_id` nullable FK ke `regencies.id`.
- Field lama `region` boleh tetap sebagai fallback display v1, tapi sumber resmi wilayah memakai relasi.

## Seeder

- Seeder Laravel: `database/seeders/IndonesiaRegionSeeder.php`.
- Migration stub:
  - `database/migrations/2026_01_01_000001_create_provinces_table.php`
  - `database/migrations/2026_01_01_000002_create_regencies_table.php`
  - `database/migrations/2026_01_01_000003_add_region_columns_to_pdams_table.php`
- Seeder membaca CSV agar data tidak membengkakkan file PHP.
- Seeder memakai `updateOrInsert` agar bisa dijalankan ulang.

## Catatan

- Data kecamatan/desa tidak masuk v1.
- Jika nanti butuh alamat detail venue/PDAM, tambah kecamatan/desa sebagai phase berikutnya.
