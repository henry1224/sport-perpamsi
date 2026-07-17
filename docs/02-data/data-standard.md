# Data Standard Sport PERPAMSI

## Single Source of Truth

- Aplikasi menjadi sumber resmi untuk jadwal, peserta, skor, hasil final, bracket, klasemen, dan ranking.
- Data public hanya berasal dari data published atau final.
- Data draft tidak muncul di public.
- Hasil final hanya dikunci oleh role berwenang.

## Entitas Utama

- Event.
- PDAM.
- Cabang olahraga.
- Kategori.
- Venue.
- Tim.
- Atlet.
- Dokumen.
- Match.
- Score.
- Bracket.
- Klasemen.
- Ranking.
- User.
- Role.
- Assignment panitia.
- Audit log.

## Relasi Inti

- Event memiliki banyak cabor, venue, jadwal, tim, match, dokumen, dan konten.
- PDAM memiliki banyak tim, atlet, dokumen, dan hasil pertandingan.
- Cabor memiliki banyak kategori, tim, match, bracket, dan klasemen.
- Tim dimiliki satu PDAM dan mengikuti satu cabor/kategori pada event.
- Atlet dimiliki satu PDAM dan dapat terhubung ke satu atau lebih tim sesuai aturan event.
- Match terhubung ke event, cabor, kategori, venue, dua peserta, skor, status, dan pemenang.
- Bracket terhubung ke match sebelumnya dan match berikutnya.
- Klasemen dihitung dari hasil match final.
- Audit log terhubung ke aktor, aksi, dan entitas yang berubah.

## Status Data

- Peserta: draft, diajukan, diverifikasi, ditolak, revisi.
- Match: draft, terjadwal, berlangsung, jeda, selesai, final, revisi.
- Konten: draft, published, archived.
- Dokumen: pending, valid, rejected, revision_required.

## Import dan Export

- Data awal PDAM, tim, atlet, dan jadwal dapat diimpor dari CSV atau Excel.
- Import wajib punya preview sebelum simpan.
- Baris invalid ditolak dengan alasan error.
- Export tersedia untuk peserta, jadwal, hasil, ranking.
- Format template import harus dikunci sebelum pengumpulan data.

## Audit

- Audit wajib untuk skor, jadwal, verifikasi, assignment panitia, finalisasi, revisi.
- Audit mencatat aktor, aksi, waktu, entitas, nilai sebelum, nilai sesudah.
- Audit log append-only.
- Public hanya melihat status final, bukan audit internal.

## Kualitas Data

- Nama PDAM harus konsisten.
- Cabor dan kategori harus dikunci sebelum jadwal dibuat.
- Jadwal tidak boleh bentrok venue dan waktu.
- Tim tidak boleh tampil di public sebelum diverifikasi.
- Ranking hanya dihitung dari match final.
