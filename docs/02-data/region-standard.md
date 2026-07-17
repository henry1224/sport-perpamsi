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

## Ranking Wilayah

- Ranking provinsi dihitung dari akumulasi medali/hasil seluruh PDAM dalam provinsi tersebut.
- Ranking kabupaten/kota dihitung dari akumulasi medali/hasil seluruh PDAM dalam kabupaten/kota tersebut.
- Ranking wilayah hanya memakai hasil final.
- Jika PDAM belum punya wilayah, hasilnya tetap masuk ranking PDAM, tapi tidak masuk ranking wilayah sampai wilayah dilengkapi.
- Rumus ranking wilayah mengikuti ranking PDAM: emas, perak, perunggu, total medali, lalu nama wilayah.

## Query View yang Disarankan

- `medal_rankings_by_pdam`: agregasi medali per PDAM.
- `medal_rankings_by_regency`: agregasi dari PDAM ke kabupaten/kota.
- `medal_rankings_by_province`: agregasi dari PDAM ke provinsi.

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
