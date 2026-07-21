# Standar Publikasi Registrasi Cabor

Dokumen ini menjadi sumber kebenaran hubungan Admin, regulasi kompetisi, kategori yang tampil di portal Pengurus Daerah, dan pendaftaran pemain.

## Prinsip

1. Master cabor dan kategori tidak otomatis menjadi pilihan Pengurus Daerah.
2. Admin membuat satu `tournament_event` sebagai paket registrasi resmi untuk satu cabor dan kategori.
3. Admin menetapkan format, regulasi, jumlah pemain, dan periode registrasi sebelum publikasi.
4. Publikasi menyimpan snapshot regulasi pada kompetisi.
5. Portal PD hanya menampilkan kompetisi dengan `registration_published_at` terisi.
6. Submit hanya tersedia saat status `registration_open` dan berada dalam periode buka/tutup.
7. Perubahan master kategori setelah publikasi tidak mengubah snapshot kompetisi.
8. Setelah entry pertama masuk, snapshot tidak boleh diedit langsung.

## Status Kompetisi

| Status | Fungsi | Tampil di Portal PD | Bisa Daftar |
|---|---|---:|---:|
| `registration_draft` | Admin menyiapkan kompetisi dan regulasi | Tidak | Tidak |
| `registration_open` | Registrasi resmi dibuka | Ya | Ya, dalam periode |
| `registration_closed` | Registrasi ditutup | Ya | Tidak |
| `bracket_locked` | Peserta dan bracket dikunci | Ya bila pernah dipublikasikan | Tidak |
| `ongoing` | Kompetisi berlangsung | Ya bila pernah dipublikasikan | Tidak |
| `completed` | Kompetisi selesai | Ya bila pernah dipublikasikan | Tidak |
| `archived` | Arsip internal | Tidak | Tidak |

## Snapshot Regulasi

`tournament_events.registration_rules` minimum menyimpan:

- Nama kategori.
- Jenis kompetisi.
- Tipe skor.
- Format kompetisi.
- Minimum pemain.
- Maksimum pemain.

Jika regulasi berversi dibangun, snapshot ditambah ID versi, nomor versi, dan checksum dokumen.

## Alur Admin

1. Kelola master cabor dan kategori aktif.
2. Tetapkan regulasi melalui technical meeting.
3. Buat kompetisi berstatus `registration_draft`.
4. Pilih kategori, format, batas pemain, dan periode registrasi.
5. Preview pilihan yang akan dilihat PD.
6. Publikasikan; sistem membuat snapshot, waktu publikasi, dan aktor publikasi.
7. Tutup registrasi manual atau otomatis pada batas waktu.
8. Verifikasi seluruh entry sebelum seeding atau bracket dikunci.

## Alur Pengurus Daerah

1. Dashboard hanya memuat kompetisi yang dipublikasikan Admin.
2. PD memilih paket kompetisi resmi, bukan seluruh master kategori.
3. Form dan validasi pemain memakai snapshot regulasi kompetisi.
4. PD mengirim satu entry per kompetisi kecuali regulasi mengizinkan multi-entry.
5. PD tetap dapat melihat entry setelah registrasi ditutup, tetapi tidak dapat mengirim entry baru.

## Perubahan Setelah Publikasi

- Belum ada entry: Admin boleh memperbaiki lalu mempublikasikan ulang.
- Sudah ada entry: Admin hanya boleh menutup registrasi; perubahan regulasi membutuhkan revisi berversi dan audit.
- Perubahan jumlah pemain tidak boleh diam-diam membatalkan entry lama.
- Perubahan kategori yang mengubah identitas kompetisi membuat `tournament_event` baru.

## Constraint dan Test Wajib

- Status default kompetisi `registration_draft`.
- Kompetisi belum terpublikasi tidak muncul dan detail mengembalikan 404 untuk PD.
- Submit ditolak bila belum terpublikasi, status bukan `registration_open`, belum masuk waktu buka, atau melewati waktu tutup.
- Validasi roster memakai `registration_rules`, bukan master kategori aktif.
- Satu PD tidak dapat mendaftarkan kompetisi sama dua kali.
- Publish, perubahan regulasi, dan penutupan registrasi wajib diaudit saat audit event tersedia.
