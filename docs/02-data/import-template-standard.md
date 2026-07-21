# Import Template Standard

Import hanya untuk data yang sudah memiliki owner dan aturan validasi. Semua import wajib preview, dry-run, transaksi, audit, dan hasil baris error.

## PD PERPAMSI

PD dibuat dari master provinsi; tidak diimpor sebagai entitas bebas.

| Kolom | Wajib | Aturan |
|---|---|---|
| province_code | Ya | Harus cocok master provinsi |
| display_name | Tidak | Diabaikan; sistem membentuk nama resmi |

## Pengajuan Pengurus Daerah

| Kolom | Wajib |
|---|---|
| province_code | Ya |
| applicant_name | Ya |
| position | Ya |
| email | Ya |
| phone | Ya |

Import akun tidak mengaktifkan pengguna otomatis; tetap membutuhkan verifikasi Admin dan mekanisme set password aman.

## Registrasi dan Pemain

| Kolom | Wajib |
|---|---|
| province_code | Ya |
| tournament_event_code | Ya |
| member_name | Ya |
| member_type | Ya |
| gender | Sesuai kategori |
| shirt_number | Sesuai cabor |
| position | Sesuai cabor |

Validasi duplikasi dan jumlah anggota memakai versi peraturan kompetisi.

## Agenda/Jadwal

| Kolom | Wajib |
|---|---|
| date | Ya |
| start_time | Ya |
| end_time | Tidak |
| title | Ya |
| type | Ya |
| venue_code | Ya |
| sport_code | Tidak |
| tournament_event_code | Tidak |
| status | Ya |

Hari tidak diimpor. Sistem menolak bentrok venue dan waktu selesai sebelum waktu mulai.

## Match

Peserta memakai kode/public ID `event_entry`, bukan nama bebas. Import tidak boleh melewati assignment, status, atau audit.
