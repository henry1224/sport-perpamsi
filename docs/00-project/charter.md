# Piagam Proyek Sport PERPAMSI

## Nama Proyek

Sport PERPAMSI

## Latar Belakang Masalah

Event olahraga PERPAMSI membutuhkan satu sistem resmi untuk menggantikan pencatatan manual jadwal, peserta, skor, bracket, klasemen, dan ranking. Tanpa sistem terpusat, data mudah berbeda antar-panitia, publikasi skor lambat, audit keputusan sulit, dan informasi publik tersebar.

## Tujuan Utama

1. Menjadi pusat data resmi event olahraga PERPAMSI.
2. Menyediakan live score, jadwal, bracket, klasemen Pimpinan Daerah, dan profil PDAM untuk publik.
3. Membantu panitia input skor dan finalisasi hasil secara aman.
4. Mengurangi input manual berulang dan konflik data.
5. Menyediakan audit log untuk perubahan skor, jadwal, peserta, assignment, dan finalisasi.
6. Siap dipakai awal September untuk lebih dari 1.000 public user dan 100 panitia.

## Ruang Lingkup Masuk v1

1. Aplikasi web responsive untuk public, admin, dan panitia.
2. Master data event, provinsi, Pimpinan Daerah, PDAM, cabor, kategori, venue, tim, atlet.
3. Verifikasi peserta dan dokumen dasar.
4. Jadwal, match, input skor, finalisasi, dan revisi skor.
5. Live score, bracket knockout dasar, klasemen cabor, dan klasemen medali Pimpinan Daerah.
6. Import/export CSV atau Excel untuk data operasional utama.
7. Role dan assignment panitia dasar.
8. Audit log dan backup production.

## Ruang Lingkup Keluar v1

1. Mobile app native.
2. Hosting livestream internal.
3. Rule engine semua cabor kompleks.
4. Websocket penuh bila polling masih cukup.
5. Sistem protes resmi lengkap.
6. Role permission granular sangat detail.
7. Statistik atlet mendalam dan prediksi pertandingan.

## Metrik Keberhasilan

1. Public dapat melihat jadwal dan live score tanpa login.
2. Scorekeeper dapat input skor match yang ditugaskan tanpa bantuan developer.
3. Match final tidak bisa diubah tanpa revisi tercatat.
4. Ranking dan klasemen dihitung dari match final.
5. Data draft dan audit internal tidak terlihat di public.
6. Simulasi minimal 10 match berjalan end-to-end sebelum go-live.
7. Load test dasar lulus untuk 1.000 public user dan 100 panitia.
