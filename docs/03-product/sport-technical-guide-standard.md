# Standar Informasi Teknis Cabor

## Tujuan

Menyatukan panduan teknis, master kategori, publikasi kompetisi, registrasi PD, agenda, tugas panitia, dan informasi publik tanpa menggandakan sumber aturan.

## Sumber Kebenaran

1. `sports`: identitas cabor dan format bawaan.
2. `sport_categories`: kategori aktif serta minimum/maksimum pemain.
3. `sport_regulations`: salinan aturan berversi yang diturunkan dari baseline panduan dan dapat direvisi Admin.
4. `tournament_events.registration_rules`: snapshot kategori, kuota, format, dan versi peraturan saat dipublikasikan.
5. `data/seed/sport_technical_guides.json`: satu sumber awal panduan slide 5–23. Seeder membentuk regulasi v1; runtime publik membaca aturan terbaru dari `sport_regulations` dan hanya memakai JSON untuk jadwal/venue.

Baseline JSON bukan tempat transaksi dan belum memiliki CRUD. Tambah tabel/CRUD hanya jika Admin harus mengubah informasi teknis tanpa deployment.

## Aturan Kuota

- `min_members` selalu minimal 1.
- `max_members` boleh `null` jika panduan tidak menyebut batas maksimum.
- Nilai pasti tetap divalidasi pada registrasi.
- Snapshot `max_members = null` tetap berarti tidak dibatasi walau master berubah setelah publikasi.
- Official, pelatih, manajer, dan pendamping tidak masuk `entry_members`.

## Alur Data

```text
Panduan Teknis
→ Master Cabor/Kategori/Peraturan
→ Kompetisi Draft
→ Snapshot dan Publikasi
→ Portal PD dan Roster Pemain
→ Verifikasi Admin
→ Agenda/Venue dan Assignment Panitia
→ Pertandingan/Skor
→ Informasi Publik
```

## Informasi Publik Minimum

- nama dan ikon cabor jika aset tersedia;
- jadwal, venue, dan alamat;
- kategori serta kuota pemain;
- sistem pertandingan dan syarat peserta;
- ketentuan official dan biaya;
- format bawaan serta nomor slide sumber.

## Ambiguitas Terbuka

- Golf tidak memiliki maksimum peserta pada panduan; sistem memakai minimum satu tanpa maksimum.
- Tenis Meja Ganda Campuran dibaca sebagai satu pasangan sampai klarifikasi resmi.
- Kuota lintas kategori Tenis Lapangan perlu konfirmasi; validasi saat ini berlaku per kategori.
