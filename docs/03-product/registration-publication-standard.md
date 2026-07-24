# Standar Publikasi Registrasi Cabor

> Workflow revisi regulasi berversi setelah entry masuk (butir 69) belum diimplementasikan. Drift dan aksi lihat `docs/00-project/audit-2026-07-22.md` (D23).

Dokumen ini menjadi sumber kebenaran hubungan Admin, regulasi kompetisi, kategori yang tampil di portal Pengurus Daerah, dan pendaftaran pemain.

## Prinsip

1. Master cabor dan kategori tidak otomatis menjadi pilihan Pengurus Daerah.
2. Admin membuat satu `tournament_event` sebagai paket registrasi resmi untuk satu cabor dan kategori.
3. Admin menetapkan format dan aturan registrasi pada Master Cabor/Kategori serta menerbitkan Regulasi aktif sebelum membuat Data Lomba.
4. Data Lomba draft mewarisi format, regulasi aktif terbaru, kuota, dan aturan official dari master tanpa override manual.
5. Perubahan aturan dilakukan pada master; draft disinkronkan ulang, sedangkan snapshot terpublikasi tetap tidak berubah.
6. Publikasi menyimpan snapshot regulasi pada kompetisi.
7. Portal PD hanya menampilkan kompetisi dengan `registration_published_at` terisi.
8. Submit hanya tersedia saat status `registration_open` dan berada dalam periode buka/tutup.
9. Perubahan master kategori atau Format Bawaan setelah publikasi tidak mengubah snapshot kompetisi.
10. Setelah entry pertama masuk, snapshot tidak boleh diedit langsung.
11. Admin wajib memeriksa regulasi aktif terbaru dan preview paket sebelum publish.
12. Publikasi hanya dapat ditarik selama belum ada entry.
13. Publish, publish ulang, tutup, dan tarik publikasi tercatat audit.
14. Master Cabor/Kategori/Regulasi menjadi sumber tunggal draft; snapshot terpublikasi tidak berubah saat master diperbarui.

## Hierarki Aturan Dinamis

1. **Master Cabor**: format, jumlah official, peran official, izin rangkap kategori, batas kategori per atlet, dan izin official bermain.
2. **Master Kategori**: maksimum team per PD serta minimum/maksimum anggota per team.
3. **Data Lomba draft**: referensi operasional yang disinkronkan dari master dan tidak memiliki override aturan.
4. **Snapshot publish**: mengunci seluruh nilai untuk registrasi berjalan; perubahan master hanya berlaku pada Data Lomba baru/draft.

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
- Unit peserta: individual, pasangan, atau team.
- Minimum dan maksimum team per PD; maksimum wajib integer positif.
- Minimum dan maksimum anggota per team.
- Aturan komposisi anggota bila ada.
- `avoid_same_pd_in_round`, default `true`.
- ID, versi, dan judul regulasi.
- `snapshot_version`.
- Maksimum official per PD dan daftar perannya.
- Aturan atlet merangkap kategori dan maksimum kategori per atlet.
- Aturan official boleh atau tidak boleh menjadi atlet.

Snapshot regulasi berversi tidak berubah ketika master regulasi berikutnya diterbitkan.

## Alur Admin

1. Kelola master cabor dan kategori aktif.
2. Tetapkan regulasi melalui technical meeting.
3. Buat kompetisi berstatus `registration_draft` dengan memilih Cabor dan Kategori.
4. Sistem membentuk nama, kode, format, regulasi, kuota, serta aturan official dari master.
5. Tetapkan periode registrasi dan preview paket yang akan dilihat PD.
6. Publikasikan; sistem membuat snapshot, waktu publikasi, dan aktor publikasi.
7. Tutup registrasi manual atau otomatis pada batas waktu.
8. Verifikasi seluruh entry sebelum seeding atau bracket dikunci.

## CRUD Data Lomba

- Admin dapat membuat kompetisi draft dari cabor dan kategori aktif.
- Cabor nonaktif tidak dapat dipakai untuk kompetisi atau agenda baru dan tidak tampil publik; data kompetisi, registrasi, pertandingan, serta histori existing tetap disimpan.
- Perubahan status aktif cabor hanya mengubah `is_active`; default registrasi bawaan wajib tetap utuh.
- Form draft hanya memilih Cabor dan Kategori aktif.
- Sistem membentuk kode dan nama serta mengambil format, regulasi aktif terbaru, kuota team, official, dan aturan rangkap dari master.
- Periode registrasi diisi pada modal publikasi, bukan modal draft.
- Bila ringkasan tidak sesuai, Admin wajib memperbaiki master dan menyinkronkan draft; override tersembunyi dilarang.
- Pembuatan kompetisi tidak membuat participant, bracket, match, skor, atau audit skor otomatis.
- Kompetisi draft tanpa entry dapat diubah atau diarsipkan; kompetisi yang sudah memiliki entry tidak dihapus permanen.
- `sport_category_id` wajib sesuai dengan `sport_id`; `sport_regulation_id` selalu memakai regulasi aktif terbaru cabor saat publish.
- Generator bracket/seeding hanya dijalankan melalui aksi terpisah setelah seluruh team efektif verified dan Admin mengonfirmasi peserta.
- Data Lomba menampilkan progres `team terverifikasi / seluruh team aktif`; tombol `Kunci Bracket` hanya aktif ketika nilainya sama dan minimal dua team tersedia.
- Penguncian bracket memberi nomor seed deterministik, mengisi `bracket_size` serta `seed_locked_at`, lalu mengubah status menjadi `bracket_locked`.
- Progres verifikasi Data Lomba menampilkan `pemain verified / seluruh pemain` dan `team verified / seluruh team`; kedua hitungan wajib lengkap sebelum bracket dikunci.

## Alur Pengurus Daerah

1. Dashboard hanya memuat kompetisi yang dipublikasikan Admin.
2. PD memilih paket kompetisi resmi, bukan seluruh master kategori.
3. Sistem membuat satu parent `EventEntry` per PD/kompetisi.
4. PD membuat `EntryTeam` sampai batas `max_teams_per_pd` pada snapshot.
5. Portal PD menampilkan label aturan dalam Bahasa Indonesia, sisa kuota team, jumlah pemain per team, batas official, dan kebijakan official bertanding; kode internal seperti `group_or_knockout` tidak ditampilkan.
6. Setelah submit, penambahan team baru terkunci. Perbaikan per-team hanya membuka team dengan override `revision_required`; perubahan kuota atau penambahan team membutuhkan revisi parent.
7. Penolakan tidak otomatis membuka form. Admin membuka kembali parent sebagai `revision_required` atau mereset/mengubah override team, kemudian PD mendapat tombol pengajuan ulang sesuai lingkup perbaikan.
8. Pendaftaran yang dibatalkan PD dapat diajukan ulang hanya selama periode pendaftaran masih terbuka; setelah ditutup, data tetap terkunci sebagai histori.
5. Form dan validasi pemain bekerja per team memakai snapshot anggota per team.
6. Label team dibentuk server; client tidak mengirim nomor atau nama bebas.
7. PD submit parent; status parent menjadi default seluruh team.
8. Admin dapat memverifikasi parent sekaligus dan memberi override team tertentu.
9. Hanya team efektif verified masuk seed/bracket.
10. PD tetap melihat parent/team setelah registrasi ditutup, tetapi tidak dapat membuat team baru.
11. Pemain dan official mengisi NIK/KTA pada form terpisah; submit memvalidasi dokumen wajib serta aturan rangkap dari snapshot.

Semantik lengkap mengikuti [standar multi-team](../02-data/team-entry-standard.md).

## Perubahan Setelah Publikasi

- Belum ada entry: Admin boleh memperbaiki lalu mempublikasikan ulang.
- Sudah ada entry: Admin hanya boleh menutup registrasi; perubahan regulasi membutuhkan revisi berversi dan audit.
- Perubahan jumlah team atau anggota per team tidak boleh diam-diam membatalkan parent/team lama.
- Perubahan kategori yang mengubah identitas kompetisi membuat `tournament_event` baru.

## Constraint dan Test Wajib

- Status default kompetisi `registration_draft`.
- Kompetisi belum terpublikasi tidak muncul dan detail mengembalikan 404 untuk PD.
- Submit ditolak bila belum terpublikasi, status bukan `registration_open`, belum masuk waktu buka, atau melewati waktu tutup.
- Validasi jumlah team dan roster per team memakai `registration_rules`, bukan master kategori aktif.
- Satu PD hanya memiliki satu parent entry per kompetisi; banyak team sah sampai batas snapshot.
- Publish, perubahan regulasi, dan penutupan registrasi wajib diaudit saat audit event tersedia.
- Regulasi harus aktif dan berasal dari cabor kompetisi yang sama.
- Tarik publikasi ditolak setelah entry pertama tersedia.
