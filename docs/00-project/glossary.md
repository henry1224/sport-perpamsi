# Glossary Sport PERPAMSI

## Istilah Utama

- Event: satu penyelenggaraan PORPAMNAS.
- PD PERPAMSI: identitas resmi satu provinsi dengan format `PD PERPAMSI {Nama Provinsi}`.
- Pengurus Daerah: pengguna terverifikasi yang mengelola satu PD PERPAMSI.
- Pengajuan Pengurus Daerah: permintaan akses baru untuk mengelola PD PERPAMSI suatu provinsi.
- Registrasi Cabor: pendaftaran PD PERPAMSI pada satu cabor/kategori.
- Pemain: atlet individu atau anggota tim yang terhubung ke registrasi cabor.
- Cabor: cabang olahraga.
- Kategori: pembagian pertandingan dalam cabor, misalnya putra, putri, tunggal, ganda, atau beregu.
- Peraturan Cabor: aturan terversi yang mengatur format, jumlah pemain, skor, dan ketentuan pertandingan.
- Venue: lokasi kegiatan atau pertandingan.
- Agenda: kegiatan terjadwal yang dapat terkait cabor, kompetisi, dan venue.
- Match: satu pertandingan terjadwal.
- Assignment Panitia: batas akses pengguna panitia terhadap cabor atau pertandingan tertentu.
- Scorekeeper: panitia yang memasukkan skor pertandingan yang ditugaskan.
- Koordinator Cabor: panitia yang mengelola satu atau beberapa cabor yang ditugaskan.
- Finalisasi: penguncian hasil pertandingan sebagai hasil resmi.
- Revisi Skor: perubahan hasil final dengan alasan, persetujuan, dan audit.
- Bracket: bagan pertandingan knockout.
- Klasemen Cabor: peringkat dalam satu kompetisi atau kategori.
- Klasemen Medali: peringkat medali berdasarkan PD PERPAMSI.
- Audit Log: catatan append-only atas perubahan data penting.
- Public Page: halaman tanpa login untuk penonton, peserta, media, dan stakeholder.

## Status Tampilan Indonesia

Kode internal tetap stabil dalam database, tetapi UI dan export wajib memakai label Indonesia.

| Kode | Label |
|---|---|
| `pending` | Menunggu Verifikasi |
| `verified` | Terverifikasi |
| `rejected` | Ditolak |
| `revision_required` | Perlu Perbaikan |
| `registration_open` | Pendaftaran Dibuka |
| `registration_closed` | Pendaftaran Ditutup |
| `bracket_locked` | Bracket Dikunci |
| `scheduled` | Terjadwal |
| `live` | Sedang Berlangsung |
| `final` | Selesai |
| `disputed` | Dalam Sengketa |
