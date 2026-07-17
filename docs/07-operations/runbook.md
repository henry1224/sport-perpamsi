# Runbook Operasional Event

## Sebelum Event

- Pastikan domain dan SSL aktif.
- Pastikan backup database berjalan.
- Import data PDAM, tim, atlet, jadwal.
- Verifikasi data peserta.
- Buat akun panitia.
- Assign panitia ke cabor, venue, atau match.
- Simulasi minimal 10 match end-to-end.
- Freeze fitur.

## Saat Event

- Monitor error aplikasi.
- Monitor match live dan update skor.
- Support scorekeeper jika ada masalah login atau assignment.
- Catat issue lapangan.
- Jika internet venue bermasalah, scorekeeper mencatat skor manual.
- Admin teknis input ulang saat koneksi normal.

## Setelah Event Harian

- Backup database manual.
- Export hasil sementara.
- Cek match yang belum final.
- Cek revisi skor yang belum selesai.
- Review issue hari itu.

## Eskalasi

- Issue skor: Scorekeeper ke Koordinator Cabor ke Admin Event.
- Issue data peserta: Verifikator ke Admin Event.
- Issue teknis: Support Event ke Tech Lead/DevOps.
- Sengketa hasil final: PIC Event membuat keputusan final.
