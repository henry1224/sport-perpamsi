# Standar Informasi Teknis Cabor

## Tujuan

Menyatukan panduan teknis, master kategori, publikasi kompetisi, registrasi PD, agenda, tugas panitia, dan informasi publik tanpa menggandakan sumber aturan.

## Sumber Kebenaran

1. `sports`: identitas cabor dan format bawaan.
2. `sport_categories`: kategori aktif serta minimum/maksimum pemain.
3. `sport_regulations`: aturan berversi yang diturunkan dari baseline panduan dan dapat direvisi Admin; versi aktif terbaru otomatis dipakai draft/publikasi berikutnya.
4. `tournament_events.registration_rules`: snapshot kategori, kuota, format, dan versi peraturan saat dipublikasikan; draft tidak menjadi sumber override master.
5. `data/seed/sport_technical_guides.json`: satu sumber awal panduan slide 5–23. Seeder membentuk regulasi v1; runtime publik membaca aturan terbaru dari `sport_regulations` dan hanya memakai JSON untuk jadwal/venue.

Baseline JSON bukan tempat transaksi dan belum memiliki CRUD. Tambah tabel/CRUD hanya jika Admin harus mengubah informasi teknis tanpa deployment.

## Aturan Kuota

- Setiap kategori ditetapkan sebagai unit `individual`, `pair`, atau `team`.
- Technical meeting wajib menetapkan `max_teams_per_pd >= 1` untuk setiap kompetisi; tidak ada kuota global per cabor dan nilai null tidak diizinkan saat publish.
- `min_teams_per_pd` default 1.
- `min_members_per_team` dan `max_members_per_team` wajib terisi; individual `1/1`, pasangan `2/2`, beregu mengikuti regulasi.
- Nilai pasti divalidasi dari snapshot registrasi.
- Perubahan master/technical meeting berikutnya tidak mengubah snapshot kompetisi terpublikasi.
- Official, pelatih, manajer, dan pendamping disimpan sebagai `entry_members.member_type = official`, terpisah dari team pemain.
- Detail unit peserta mengikuti [standar multi-team](../02-data/team-entry-standard.md).

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
- kategori, unit peserta, batas team per PD, dan anggota per team;
- sistem pertandingan dan syarat peserta;
- ketentuan official dan biaya;
- format bawaan; metadata versi dan nomor slide sumber tidak ditampilkan kepada publik.

## Ambiguitas Terbuka

- Golf tidak memiliki maksimum peserta pada panduan; technical meeting wajib menetapkan `max_teams_per_pd` sebelum kompetisi dipublikasikan.
- Tenis Meja Ganda Campuran dibaca sebagai satu pasangan sampai klarifikasi resmi.
- Kuota lintas kategori Tenis Lapangan perlu konfirmasi; validasi target berlaku per kompetisi/kategori.
- Katalog tidak menyimpan angka kuota team global; keputusan technical meeting menjadi snapshot kompetisi.
