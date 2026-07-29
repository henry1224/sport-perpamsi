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

Vocabulary, label, kondisi nyata, dan status target hanya mengikuti [`STATUS-VOCABULARY.md`](../02-data/STATUS-VOCABULARY.md). Dokumen ini tidak mendefinisikan ulang enum. Ringkasannya:

- Nyata dan memiliki transition Admin: `registration_draft`, `registration_open`, `registration_closed`, `participants_locked`, `bracket_locked`.
- Dikenal filter/model tetapi belum memiliki transition controller: `ongoing`, `completed`.
- Target dokumen, belum tersedia sebagai status aktif: `archived`.

Visibility portal PD tetap mensyaratkan `registration_published_at`; kemampuan submit juga mensyaratkan status `registration_open` dan periode aktif.

## Snapshot Regulasi

`tournament_events.registration_rules` minimum menyimpan:

- Nama kategori.
- Jenis kompetisi.
- Tipe skor.
- Format kompetisi.
- Penanda turunan `uses_bracket`; nilainya berasal dari format Data Lomba, bukan input kedua.
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
8. Verifikasi seluruh entry sebelum peserta dikunci.

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
- Semua format memakai aksi `Kunci Peserta` setelah seluruh team dan pemain efektif verified.
- `tournament_events.format` adalah sumber tunggal mode kompetisi; `uses_bracket` hanya snapshot turunan untuk audit dan UI.
- Format bracket memberi nomor seed deterministik, mengisi `bracket_size` serta `seed_locked_at`, lalu mengubah status menjadi `bracket_locked`.
- Format non-bracket tidak memberi seed atau `bracket_size`, mengisi waktu penguncian, lalu mengubah status menjadi `participants_locked`.
- Generator pertandingan bracket hanya tersedia untuk format bracket. Swiss, ranking skor/nilai, dan fun games tidak boleh menjalankan generator bracket.
- Progres verifikasi Data Lomba menampilkan `pemain verified / seluruh pemain` dan `team verified / seluruh team`; kedua hitungan wajib lengkap sebelum peserta dikunci.

## Alur Pengurus Daerah

Seluruh semantik parent entry, tambah team, kuota, roster, official, submit/resubmit, verifikasi hybrid, label team, dokumen, dan bracket eligibility **hanya** mengikuti [Standar Entry dan Multi-Team](../02-data/team-entry-standard.md). Status dan badge mengikuti [Status Vocabulary](../02-data/STATUS-VOCABULARY.md).

Dokumen publikasi ini hanya menetapkan batas integrasi:

1. Dashboard PD hanya memuat `TournamentEvent` dengan `registration_published_at` terisi.
2. PD memilih paket kompetisi resmi dan validasi registrasi membaca snapshot publish, bukan master yang dapat berubah.
3. Publicasi menentukan periode dan availability; aturan perilaku entry/team/member tetap milik standar multi-team.
4. UI memakai label Bahasa Indonesia dan memisahkan badge status dari informasi progres seperti sisa kuota atau pemain belum lengkap.

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
