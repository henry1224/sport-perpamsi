# Glossary Sport PERPAMSI

## Istilah Utama

- Event: satu penyelenggaraan PORPAMNAS.
- PD PERPAMSI: identitas resmi satu provinsi dengan format `PD PERPAMSI {Nama Provinsi}`.
- Pengurus Daerah: pengguna terverifikasi yang mengelola satu PD PERPAMSI.
- Pengajuan Pengurus Daerah: permintaan akses baru untuk mengelola PD PERPAMSI suatu provinsi.
- Registrasi Cabor: pendaftaran PD PERPAMSI pada satu cabor/kategori.
- Kompetisi Registrasi: paket cabor, kategori, format, regulasi, dan periode yang ditetapkan Admin sebagai satu `tournament_event`.
- Registrasi Dipublikasikan: kompetisi sudah disahkan Admin dan dapat terlihat pada portal PD.
- Snapshot Regulasi: salinan aturan registrasi yang dikunci saat publish dan tidak mengikuti perubahan master kategori.
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
- Kuota Pemain: batas jumlah `entry_members`; maksimum boleh kosong jika panduan tidak membatasi.
- Official: manajer, pelatih, atau pendamping kontingen; bukan pemain dan tidak masuk `entry_members`.
- Snapshot Registrasi: salinan kategori, kuota, format, dan versi peraturan saat kompetisi dipublikasikan.

## Status Tampilan Indonesia

Kode internal tetap stabil dalam database, tetapi UI dan export wajib memakai label Indonesia.

| Kode | Label |
|---|---|
| `draft` | Draft |
| `pending` | Menunggu Verifikasi |
| `verified` | Terverifikasi |
| `rejected` | Ditolak |
| `revision_required` | Perlu Perbaikan |
| `cancelled` | Dibatalkan |
| `registration_open` | Pendaftaran Dibuka |
| `registration_closed` | Pendaftaran Ditutup |
| `bracket_locked` | Bracket Dikunci |
| `scheduled` | Terjadwal |
| `live` | Sedang Berlangsung |
| `final` | Selesai |
| `disputed` | Dalam Sengketa |
