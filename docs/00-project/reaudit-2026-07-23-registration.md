# Reaudit Data Lomba dan Registrasi PD — 23 Juli 2026

## Scope

Alur yang diperiksa: Data Lomba draft → snapshot aturan registrasi → publikasi → dashboard PD → registrasi tim/pemain/official → verifikasi Admin.

## Hasil

| Area | Status | Bukti |
|---|---|---|
| Data Lomba | Sinkron | Cabor, kategori, regulasi, kuota tim/pemain, official, dan aturan rangkap disalin ke `registration_rules` |
| Portal PD | Sinkron | Hanya kompetisi terpublikasi yang dapat dipilih dan validasi membaca snapshot event |
| Pemain dan official | Sinkron | Form, kuota, peran, dokumen, dan tampilan dipisahkan |
| Identitas rangkap | Diperbaiki | Deteksi memakai hash `identity_type + identity_number`, bukan nama |
| Dokumen peserta | Diperbaiki | File disimpan privat pada disk `local`; hanya status kelengkapan dikirim ke UI |
| Verifikasi Admin | Diperbaiki | Admin melihat NIK/KTA dan jumlah dokumen pemain/official |
| Asal pemain | Diperbaiki | Setiap pemain memilih `entry_members.pdam_id` dari 514 Master PDAM pada 38 provinsi |

## Audit Database Lokal

- Data Lomba: 15; seluruhnya memiliki kategori dan regulasi.
- Data Lomba terpublikasi saat audit: 1.
- Registrasi PD: 475.
- Anggota existing: 993; seluruhnya belum memiliki NIK/KTA karena data lama tidak menyediakan sumber identitas yang aman untuk backfill.
- Master PDAM: 514 data pada 38 provinsi; asal PDAM anggota legacy tidak ditebak dan dilengkapi saat revisi.
- Data lama tidak ditebak dari nama. NIK/KTA dan dokumen wajib dilengkapi ketika roster diedit atau diajukan ulang.

## Persyaratan Peserta

Sumber acuan: `https://porpamnas.perpamsi.or.id/` dan arahan registrasi PORPAMNAS.

Pemain wajib melengkapi sebelum submit:

1. Foto 3×4.
2. Form Pendaftaran.
3. KTP Pemain.
4. Kartu DAPENMA PAMSI atau dana pensiun lainnya.
5. SK Karyawan Tetap.

Official wajib melengkapi foto 3×4 dan KTP. Draft boleh disimpan belum lengkap; submit ditolak sampai seluruh identitas dan dokumen wajib tersedia.

## Batas Aktif

- Identitas kanonik organisasi belum memiliki tabel `players`; NIK/KTA disimpan per `entry_member` dan dipakai untuk deteksi rangkap.
- Deteksi rangkap berbasis identitas hanya efektif untuk anggota yang sudah memiliki NIK/KTA; 993 anggota legacy masih menunggu pelengkapan manual.
- Unduh dokumen privat oleh Admin belum dibuat; verifikator saat ini melihat identitas dan jumlah kelengkapan.
- Aturan kuota, peran official, dan izin rangkap tetap mengikuti snapshot Data Lomba, bukan nilai bebas dari form PD.
- Mulai standardisasi 23 Juli 2026, draft Data Lomba tidak menerima override format/regulasi/kuota/official; seluruh nilai diwarisi dari master dan dikunci saat publish.
