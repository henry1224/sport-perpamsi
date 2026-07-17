# Architecture Standard Sport PERPAMSI

## Target Beban

- Public lebih dari 1.000 pengguna.
- Panitia lebih dari 100 pengguna.
- Minimal 50 scorekeeper aktif input bersamaan.

## Prinsip Arsitektur

- Satu aplikasi web responsive untuk public, admin, dan panitia.
- PostgreSQL menjadi database relasional utama.
- Public hanya membaca data published atau final.
- Data draft dan audit internal tidak boleh muncul di public.
- Realtime v1 memakai polling ringan 5-15 detik.
- Websocket ditunda sampai polling terbukti tidak cukup.

## Komponen Sistem

- Frontend public mobile-first.
- Admin panel.
- Panitia/scorekeeper panel.
- Backend API.
- Database relasional.
- Cache untuk public endpoint ramai.
- Object storage atau storage server untuk file.
- Background job untuk import, export, hitung klasemen, dan proses berat.
- Monitoring error dan log aplikasi.

## Performa

- Public endpoint harus cacheable.
- Live score hanya mengambil match aktif atau berubah.
- Admin table memakai server-side pagination.
- Query public wajib memakai index yang sesuai filter utama.
- Payload public harus ringkas.
- Upload dokumen dibatasi ukuran dan tipe file.

## Keamanan

- Admin dan panitia wajib login.
- Permission dicek di backend.
- Password memakai hash aman.
- CSRF/session protection atau token auth mengikuti stack final.
- File upload dibatasi tipe dan ukuran.
- Audit log untuk perubahan skor, jadwal, verifikasi, assignment, finalisasi.
- Public tidak boleh mengakses dokumen internal, audit internal, data draft.

## Operasional

- Backup database harian.
- Backup manual sebelum event dimulai.
- Backup manual setelah setiap hari event selesai.
- Monitoring error aktif di production.
- Admin teknis standby saat pertandingan berlangsung.
- Fallback manual disiapkan bila internet venue bermasalah.

## Load Test Minimum

- 1.000 public user membuka live score dan jadwal.
- 100 panitia login membuka admin/panitia panel.
- 50 scorekeeper update skor bersamaan.
- Public page tetap responsif saat live score polling aktif.
