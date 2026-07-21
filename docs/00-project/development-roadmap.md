# Roadmap Pengembangan Terkendali

## Aturan Eksekusi

1. Hanya satu phase berstatus `Active`.
2. Fitur phase berikutnya tidak dikerjakan sebelum exit criteria phase aktif selesai.
3. Setiap perubahan wajib memiliki route/UI, authorization, validasi, audit bila penting, dan test sesuai risiko.
4. Data demo tidak boleh dianggap fitur production.
5. Satu pull request hanya untuk satu menu atau satu alur bisnis.
6. Dokumen yang menjadi sumber kerja: roadmap ini, PRD, application flow, delegation standard, dan RBAC matrix.

## Status Saat Ini

```text
Phase 0  Active   Stabilkan baseline repository
Phase 1  Partial  Login dan portal role dasar
Phase 2  Planned  Master data admin
Phase 3  Partial  Registrasi daerah dan verifikasi
Phase 4  Planned  Panitia dan pembatasan akses
Phase 5  Partial  Operasional lomba dan skor
Phase 6  Partial  Public dan klasemen medali
Phase 7  Planned  Production readiness
```

Registrasi dan skor saat ini merupakan prototype yang dibangun lebih awal. Jangan menambah fiturnya sebelum Phase 0–2 selesai.

## Phase 0 — Baseline Repository

Status: `Active`

Tujuan: menghasilkan satu baseline yang dapat dimigrasikan, dites, dan dilanjutkan tanpa perubahan campur aduk.

Scope:

- [ ] Kelompokkan perubahan saat ini berdasarkan domain.
- [x] Pastikan seluruh migration dapat dijalankan dari database kosong.
- [x] Pastikan seeder menghasilkan akun, wilayah, PD, PDAM, cabor, event, entry, dan match yang konsisten.
- [x] Pastikan seluruh test dan build lulus.
- [ ] Tandai data demo/fallback secara jelas.
- [ ] Sinkronkan roadmap, application flow, ERD, dan RBAC.
- [ ] Buat baseline commit setelah review manusia.

Exit criteria:

- `php artisan migrate:fresh --seed` sukses.
- `php artisan test` sukses.
- `npm run build` sukses.
- Tidak ada route tanpa middleware yang seharusnya internal.
- Tidak ada perubahan fitur baru tercampur dalam baseline.

## Phase 1 — Identity dan Portal

Status: `Partial`

Tujuan: memisahkan portal Pengurus Daerah, Admin, dan Public dengan batas akses yang pasti.

Scope:

- [x] Login dan logout.
- [x] Role dasar `pd_admin` dan `super_admin`.
- [x] Middleware portal PD dan Admin.
- [x] Layout/menu portal terpisah.
- [ ] Halaman profil dan ganti password.
- [ ] Test akses 401/403 untuk setiap kelompok route.
- [ ] Session timeout dan pencatatan login penting.

Exit criteria:

- Pengurus Daerah tidak dapat membuka route Admin.
- Admin tidak terikat ke satu Pimpinan Daerah.
- Public tidak dapat membuka route internal.
- Redirect login sesuai role teruji.

## Phase 2 — Master Data Admin

Status: `Planned`

Tujuan: Admin memiliki sumber data resmi sebelum registrasi dibuka.

Urutan menu:

1. Provinsi dan Pimpinan Daerah.
2. PDAM.
3. Cabang olahraga dan kategori.
4. Venue.
5. Event/tournament event.
6. Agenda dan jadwal dasar.

Scope setiap menu:

- List, search, filter, create, edit, status aktif.
- Validasi unique dan relasi.
- Audit perubahan penting.
- Import hanya setelah CRUD stabil.

Exit criteria:

- Admin dapat menyiapkan event tanpa mengubah CSV atau menjalankan seeder.
- Pengurus Daerah hanya melihat PDAM dari provinsinya.
- Event yang dibuka registrasinya memiliki cabor dan kategori valid.

## Phase 3 — Registrasi Pengurus Daerah

Status: `Partial / Frozen`

Tujuan: Pengurus Daerah hanya mengurus atlet/tim dan cabor yang didaftarkan.

Scope:

- [x] Dashboard Pengurus Daerah.
- [x] Pilih event/cabor dan PDAM wilayah.
- [x] Delegasi Pimpinan Daerah otomatis.
- [x] Submit, hapus pending, verifikasi, dan penolakan dasar.
- [ ] Master atlet terpisah dan penggunaan ulang atlet antar-cabor.
- [ ] Dokumen peserta.
- [ ] Kuota dan aturan peserta per kategori.
- [ ] Catatan revisi dan submit ulang.
- [ ] Audit registrasi.
- [ ] Penutupan registrasi berdasarkan status event.

Exit criteria:

- Pengurus Daerah tidak dapat mendaftarkan PDAM provinsi lain.
- Entry tidak dapat diverifikasi jika data wajib belum lengkap.
- Entry terverifikasi menjadi satu-satunya sumber peserta kompetisi.
- Perubahan setelah verifikasi tercatat.

## Phase 4 — Panitia dan Assignment

Status: `Planned`

Tujuan: Admin membatasi panitia berdasarkan tugas, bukan memberi akses Super Admin.

Role minimum:

- Admin Event.
- Koordinator Cabor.
- Verifikator Peserta.
- Scorekeeper.
- Auditor/Read Only.

Scope:

- CRUD akun panitia.
- Assignment event, cabor, venue, atau match.
- Permission per action.
- Menu otomatis mengikuti permission.
- Nonaktifkan akun tanpa menghapus riwayat.

Exit criteria:

- Scorekeeper hanya dapat mengubah match tugasnya.
- Verifikator tidak dapat mengubah skor.
- Koordinator hanya mengelola cabor tugasnya.
- Auditor hanya membaca data dan audit.

## Phase 5 — Operasional Lomba

Status: `Partial`

Tujuan: menjalankan kompetisi dari entry terverifikasi sampai hasil resmi.

Urutan:

1. Tutup registrasi.
2. Seeding dan bracket/grup.
3. Jadwal dan venue.
4. Assignment panitia.
5. Input skor.
6. Finalisasi.
7. Revisi hasil final.

Exit criteria:

- Bracket hanya memakai entry terverifikasi.
- Pemenang maju otomatis tanpa duplikasi.
- Hasil final tidak dapat diedit langsung.
- Revisi final membutuhkan alasan dan audit.
- Format non-bracket memiliki alur ranking yang jelas.

## Phase 6 — Public dan Klasemen

Status: `Partial`

Tujuan: seluruh halaman public memakai data operasional resmi.

Scope:

- Hapus fallback demo setelah admin data siap.
- Published schedule dan hasil final saja.
- Bracket dan klasemen cabor.
- Klasemen medali Pimpinan Daerah.
- Penetapan perunggu sesuai regulasi cabor.
- Cache endpoint ramai.

Exit criteria:

- Public tidak melihat draft, pending, atau data internal.
- Match tetap menampilkan PDAM/tim/atlet.
- Medali terakumulasi ke Pimpinan Daerah.
- Hasil public sama dengan hasil final admin.

## Phase 7 — Production Readiness

Status: `Planned`

Scope:

- UAT seluruh role.
- Load test public dan panitia.
- Backup dan restore test.
- Monitoring error, queue, database, dan storage.
- HTTPS, environment production, rate limit, dan security review.
- Training serta runbook hari pertandingan.

Exit criteria:

- UAT ditandatangani penanggung jawab.
- Restore backup berhasil diuji.
- Simulasi pertandingan end-to-end berhasil.
- Tidak ada blocker P0 terbuka.

## Urutan Kerja Berikutnya

Jangan lanjut ke menu baru. Kerjakan urutan ini:

1. Selesaikan Phase 0.
2. Tutup gap akses Phase 1.
3. Bangun master data Phase 2 satu menu per perubahan.
4. Kembali menyelesaikan registrasi Phase 3.
5. Baru bangun panitia dan operasional lomba.
