# Delivery Plan Sport PERPAMSI

## Target

- Go-live awal September.
- Public mampu diakses lebih dari 1.000 pengguna.
- Panitia mampu dipakai lebih dari 100 pengguna.
- Minimal 50 scorekeeper aktif dapat input skor saat event berjalan.

## Prinsip Delivery

- Kunci scope v1 cepat, jangan membuka fitur baru tanpa trade-off.
- Bangun P0 dulu sampai bisa dipakai end-to-end.
- UAT panitia dilakukan sebelum polish visual.
- Data event final lebih penting dari fitur tambahan.
- Freeze fitur sebelum go-live, hanya bugfix setelah itu.

## Prioritas

### P0 Wajib Go-Live

- Auth dan role dasar.
- Master data event, PDAM, cabor, kategori, venue, tim, atlet.
- Verifikasi peserta dasar.
- Jadwal dan match.
- Assignment panitia ke cabor, venue, atau match.
- Input skor, status match, finalisasi hasil.
- Live score public.
- Audit log perubahan skor dan finalisasi.
- Backup dan deployment production.

### P1 Sangat Disarankan

- Bracket knockout dasar.
- Klasemen sederhana.
- Ranking PDAM.
- Profil PDAM.
- Import/export CSV atau Excel.
- Konten event, pengumuman, banner, livestream URL.
- Cache public page.

### P2 Jika Waktu Cukup

- Dashboard insight lanjutan.
- Tampilan venue/proyektor.
- Polish animasi esport.
- Laporan lanjutan.
- Notifikasi non-push.

## Fase Pengembangan

### Fase 1: Foundation

- Kunci scope v1.
- Finalisasi data model.
- Finalisasi role dan permission.
- Finalisasi workflow skor.
- Buat prototype UI public, admin, panitia.

### Fase 2: Core Admin

- Bangun auth dan role.
- Bangun master data.
- Bangun peserta dan verifikasi.
- Bangun import data awal.
- Bangun admin table dengan search, filter, pagination.

### Fase 3: Match Operation

- Bangun jadwal dan match.
- Bangun assignment panitia.
- Bangun input skor.
- Bangun finalisasi dan revisi skor.
- Bangun audit log.

### Fase 4: Public Experience

- Bangun public home.
- Bangun live score.
- Bangun jadwal public.
- Bangun bracket.
- Bangun klasemen dan ranking.
- Bangun profil PDAM dan info event.

### Fase 5: Stabilization

- UAT panitia.
- Load test 1.000 public user dan 100 panitia.
- Security check dasar.
- Perbaikan bug prioritas.
- Setup monitoring, backup, domain, SSL.

### Fase 6: Go-Live Readiness

- Data final masuk.
- Akun panitia dibuat.
- Assignment panitia selesai.
- Training panitia.
- Simulasi minimal 10 match end-to-end.
- Freeze fitur dan go-live.

## Definition of Done v1

- P0 selesai dan lolos UAT.
- P1 utama selesai atau ada fallback manual.
- Public page nyaman di mobile.
- Scorekeeper bisa input skor tanpa bantuan developer.
- Admin bisa koreksi data dan finalisasi hasil.
- Backup production tersedia sebelum event.
- Support event punya prosedur fallback saat internet venue bermasalah.
