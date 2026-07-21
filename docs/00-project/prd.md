# PRD Aplikasi Sport PERPAMSI

Gambaran status aplikasi dan hubungan seluruh alur tersedia di [application-flow.md](./application-flow.md).

## 1. Ringkasan

Aplikasi Sport PERPAMSI adalah platform manajemen event olahraga PERPAMSI untuk panitia, peserta PDAM, dan publik. Fokus v1 adalah mengelola event, peserta, pertandingan, input hasil setelah pertandingan selesai, bracket, jadwal, dan publikasi informasi event dalam satu tempat.

Dokumen ini menjadi PRD awal untuk menyatukan arah produk, data, operasional panitia, UI, dan arsitektur v1. Detail teknis final seperti migration, API contract, dan desain high-fidelity tetap dibuat pada tahap berikutnya.

Dokumen pendukung:

- [Delivery plan](./delivery-plan.md): fase pengembangan dan prioritas build.
- [UI standard](../04-design/ui-standard.md): standar tampilan public, admin, dan panitia.
- [Architecture standard](../01-architecture/architecture-standard.md): arsitektur, performa, keamanan, dan operasional.
- [Data standard](../02-data/data-standard.md): relasi, governance, import/export, dan audit.
- [Team plan](../07-operations/team-plan.md): struktur tim, ownership, dan support event.

## 2. Latar Belakang

Event olahraga PERPAMSI membutuhkan sistem yang bisa:

- Menampilkan jadwal, hasil, bracket, dan klasemen secara cepat.
- Membantu panitia input hasil pertandingan dengan alur yang aman.
- Memberi publik akses mudah ke informasi pertandingan dan profil PDAM.
- Menjadi arsip data event, peserta, dokumen, dan audit keputusan skor.

Saat ini kebutuhan utama adalah satu aplikasi yang rapi, mudah dipakai saat event berlangsung, dan cukup fleksibel untuk banyak cabang olahraga.

## 3. Tujuan Produk

- Menyediakan pusat informasi resmi event olahraga PERPAMSI.
- Mempercepat publikasi hasil, jadwal, bracket, dan klasemen.
- Mengurangi input manual berulang oleh panitia.
- Mendukung verifikasi peserta dan dokumen PDAM.
- Menyediakan insight publik seperti klasemen medali Kontingen Provinsi, performa cabor, dan statistik dasar.

## 4. Target Pengguna

### 4.1 Admin

Admin mengatur event, master data, verifikasi, konten publik, dan audit.

Kebutuhan utama:

- Membuat dan mengelola event.
- Mengatur cabang olahraga, kategori, venue, dan jadwal.
- Mengelola PDAM, tim, atlet, dan dokumen.
- Memverifikasi peserta dan revisi data.
- Melihat riwayat perubahan skor dan keputusan final.

### 4.2 Panitia / Scorekeeper

Panitia bertugas menginput skor pertandingan di lapangan.

Kebutuhan utama:

- Melihat daftar match sesuai cabor atau venue.
- Input hasil setelah pertandingan selesai.
- Menandai match selesai.
- Mengajukan revisi skor bila terjadi kesalahan.
- Melihat status finalisasi pertandingan.

### 4.3 Public

Public adalah penonton, peserta, media, dan stakeholder event.

Kebutuhan utama:

- Melihat live score.
- Melihat jadwal dan hasil pertandingan.
- Melihat bracket, klasemen cabor, dan klasemen medali Kontingen Provinsi.
- Membaca profil PDAM dan info event.
- Mengakses link livestream bila tersedia.

## 5. Modul Public

### 5.1 Hasil Terbaru

- Menampilkan hasil pertandingan terbaru.
- Memperbarui hasil setelah panitia menyimpan input.
- Menampilkan status match: belum mulai, berlangsung, jeda, selesai, final.
- Menampilkan nama provinsi, cabor, kategori, venue, dan waktu.

### 5.2 Bracket

- Menampilkan bracket knockout per cabor dan kategori.
- Menampilkan pemenang tiap match.
- Mendukung status match yang belum punya lawan karena menunggu hasil sebelumnya.

### 5.3 Jadwal

- Filter berdasarkan tanggal, cabor, venue, dan PDAM.
- Menampilkan jadwal pertandingan dan hasil setelah match selesai.
- Menandai perubahan jadwal bila ada revisi.

### 5.4 Profil PDAM

- Menampilkan nama provinsi dan daftar cabor yang diikuti.
- Menampilkan rekap hasil pertandingan PDAM.
- Menampilkan ranking dan statistik dasar PDAM.
- Menampilkan asal provinsi dan kabupaten/kota PDAM.

### 5.5 Livestream

- Menampilkan embed atau link livestream per venue atau match.
- Menampilkan status livestream tersedia atau belum tersedia.
- v1 cukup menyimpan URL eksternal, bukan hosting video sendiri.

### 5.6 Info Event

- Menampilkan deskripsi event, tanggal, lokasi, venue, peraturan umum, dan kontak panitia.
- Menampilkan pengumuman resmi.
- Menampilkan sponsor atau partner bila diperlukan.

## 6. Modul Admin

### 6.1 Event

- Buat, ubah, dan arsipkan event.
- Atur tanggal mulai, tanggal selesai, lokasi, status publikasi, dan deskripsi.
- Satu event aktif menjadi fokus v1.

### 6.2 Cabang Olahraga

- Kelola cabor, kategori, format pertandingan, dan aturan skor dasar.
- Format awal: knockout, group stage, dan klasemen sederhana.
- Detail aturan khusus cabor ditunda bila terlalu kompleks untuk v1.

### 6.3 PDAM

- Kelola profil PDAM, logo, wilayah, dan kontak perwakilan.
- Hubungkan PDAM ke tim, atlet, dokumen, dan hasil pertandingan.

### 6.4 Peserta

- Kelola tim dan atlet per PDAM.
- Upload atau catat dokumen peserta.
- Tandai status peserta: draft, diajukan, diverifikasi, ditolak.

### 6.5 Konten

- Kelola info event, pengumuman, banner, dan link livestream.
- Publikasi konten ke halaman public.

### 6.6 Verifikasi

- Review data peserta dan dokumen.
- Catat alasan penolakan atau revisi.
- Simpan status verifikasi per peserta atau tim.

### 6.7 Audit

- Catat perubahan penting: skor, jadwal, data peserta, verifikasi, dan finalisasi match.
- Tampilkan siapa melakukan perubahan, kapan, dan nilai sebelum/sesudah bila relevan.

## 7. Modul Panitia / Scorekeeper

### 7.1 Input Hasil Pertandingan

- Scorekeeper memilih match selesai dari daftar yang ditugaskan.
- Scorekeeper menginput hasil sesuai format cabor.
- Sistem menyimpan waktu update terakhir.
- Public melihat hasil terbaru setelah update tersimpan.

### 7.2 Finalisasi Match

- Setelah pertandingan selesai, scorekeeper menandai match selesai.
- Admin atau role berwenang dapat mengunci hasil menjadi final.
- Match final tidak bisa diubah tanpa proses revisi.

### 7.3 Revisi Skor

- Revisi skor membutuhkan alasan.
- Revisi setelah finalisasi harus tercatat di audit log.
- Public dapat melihat status hasil final, bukan seluruh detail audit internal.

### 7.4 Pembagian Kerja Panitia

- Admin Event mengatur data utama, publikasi, dan konfigurasi event.
- Koordinator Cabor mengatur jadwal, venue, peserta, dan hasil pada cabor tertentu.
- Verifikator Peserta memeriksa dokumen dan status peserta.
- Scorekeeper Venue menginput skor pada match yang ditugaskan.
- Content Officer mengelola pengumuman, banner, dan link livestream.
- Setiap panitia hanya melihat dan mengubah data sesuai tugasnya.

### 7.5 Assignment Panitia

- Scorekeeper dapat ditugaskan berdasarkan event, cabor, venue, atau match tertentu.
- Daftar match scorekeeper hanya menampilkan pertandingan yang menjadi tugasnya.
- Admin dapat mengganti assignment saat event berjalan.
- Perubahan assignment tercatat di audit log.

### 7.6 Workflow Skor

- Status match: draft, terjadwal, berlangsung, jeda, selesai, final, revisi.
- Scorekeeper input hasil setelah match selesai.
- Scorekeeper menandai match selesai setelah pertandingan berakhir.
- Koordinator Cabor atau Admin Event mengunci hasil menjadi final.
- Revisi setelah final wajib memiliki alasan dan aktor berwenang.
- Jika dua petugas membuka match yang sama, sistem memakai update terakhir yang valid dan menampilkan waktu update serta aktor terakhir.

## 8. Data Utama

### 8.1 PDAM

- Nama PDAM.
- Wilayah.
- Logo.
- Kontak perwakilan.
- Status keikutsertaan event.

### 8.2 Cabang Olahraga

- Nama cabor.
- Kategori.
- Format kompetisi.
- Aturan skor dasar.

### 8.3 Tim

- Nama tim.
- PDAM pemilik.
- Cabor dan kategori.
- Daftar atlet.
- Status verifikasi.

### 8.4 Atlet

- Nama atlet.
- Nomor identitas internal event bila diperlukan.
- PDAM.
- Tim.
- Dokumen pendukung.
- Status verifikasi.

### 8.5 Venue

- Nama venue.
- Alamat atau lokasi.
- Cabor yang berlangsung di venue.
- Link livestream bila ada.

### 8.6 Match

- Event, cabor, kategori, venue, waktu.
- Peserta A dan peserta B.
- Skor berjalan.
- Status match.
- Pemenang.
- Status finalisasi.

### 8.7 Bracket

- Struktur ronde.
- Posisi peserta.
- Relasi match sebelumnya dan berikutnya.
- Pemenang yang maju ke ronde berikutnya.

### 8.8 Klasemen

- PDAM atau tim.
- Main, menang, seri, kalah bila format mendukung.
- Poin, skor masuk, skor keluar, selisih skor.
- Peringkat.

### 8.9 Dokumen

- Jenis dokumen.
- Pemilik dokumen: PDAM, tim, atau atlet.
- File atau URL dokumen.
- Status verifikasi.
- Catatan verifikator.

### 8.10 Audit Log

- Aktor.
- Aksi.
- Waktu.
- Entitas yang diubah.
- Nilai sebelum dan sesudah bila relevan.

### 8.11 Relasi Data Inti

- Event memiliki banyak cabor, venue, jadwal, tim, match, dokumen, dan konten.
- PDAM memiliki banyak tim, atlet, dokumen, dan hasil pertandingan.
- Cabor memiliki banyak kategori, tim, match, bracket, dan klasemen.
- Tim dimiliki satu PDAM dan mengikuti satu cabor/kategori pada event.
- Atlet dimiliki satu PDAM dan dapat terhubung ke satu atau lebih tim sesuai aturan event.
- Match terhubung ke event, cabor, kategori, venue, dua peserta, skor, status, dan pemenang.
- Bracket terhubung ke match sebelumnya dan match berikutnya.
- Klasemen dihitung dari hasil match final.
- Audit log terhubung ke aktor, aksi, dan entitas yang berubah.

### 8.12 Sumber Data dan Import

- Data utama bisa dibuat manual dari admin.
- Data awal PDAM, tim, atlet, dan jadwal dapat diimpor dari CSV atau Excel.
- Import wajib menampilkan preview sebelum disimpan.
- Sistem wajib menolak baris yang tidak valid dan memberi alasan error.
- Export CSV atau Excel disediakan untuk peserta, jadwal, hasil, dan ranking.
- File dokumen peserta disimpan sebagai file atau URL, bukan diolah sebagai data sensitif kompleks pada v1.

### 8.13 Data Governance

- Aplikasi menjadi single source of truth untuk jadwal, peserta, skor, hasil final, bracket, klasemen, dan ranking event.
- Data public hanya berasal dari data yang sudah dipublikasikan atau final sesuai statusnya.
- Hasil final hanya bisa dikunci oleh role berwenang.
- Semua perubahan skor, jadwal, verifikasi, assignment panitia, dan finalisasi wajib tercatat.
- Data draft tidak muncul di public.
- Setiap data penting memiliki status, aktor terakhir, dan waktu update terakhir.

### 8.14 Matriks Hak Akses v1

| Fitur | Super Admin | Admin Event | Koordinator Cabor | Verifikator | Scorekeeper | Content Officer | Public |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Kelola event | Ya | Ya | Tidak | Tidak | Tidak | Tidak | Tidak |
| Kelola master data | Ya | Ya | Terbatas cabor | Tidak | Tidak | Tidak | Tidak |
| Verifikasi peserta | Ya | Ya | Lihat | Ya | Tidak | Tidak | Tidak |
| Kelola jadwal | Ya | Ya | Ya | Tidak | Lihat | Tidak | Lihat published |
| Input skor | Ya | Ya | Ya | Tidak | Match tugas | Tidak | Lihat |
| Finalisasi skor | Ya | Ya | Ya | Tidak | Tidak | Tidak | Lihat final |
| Revisi skor final | Ya | Ya | Dengan izin | Tidak | Tidak | Tidak | Tidak |
| Kelola konten public | Ya | Ya | Tidak | Tidak | Tidak | Ya | Lihat |
| Lihat audit log | Ya | Ya | Terbatas cabor | Terbatas verifikasi | Terbatas match | Tidak | Tidak |

## 9. Ranking dan Insight Publik

Insight publik bertujuan membuat event terasa hidup dan mudah diikuti.

Fitur awal:

- Klasemen medali berdasarkan akumulasi hasil seluruh PDAM ke Kontingen Provinsi provinsinya.
- Nama provinsi dipakai pada peserta match, bracket, dan klasemen; PDAM hanya metadata asal internal.
- Rekap performa PDAM per cabor.
- Statistik match: total pertandingan, pertandingan selesai, pertandingan berlangsung.
- Highlight pertandingan final atau semifinal.
- PDAM paling aktif berdasarkan jumlah cabor yang diikuti.

Catatan v1:

- Rumus ranking harus jelas di halaman public.
- Ranking wilayah memakai data relasi PDAM ke provinsi dan kabupaten/kota.
- Bila aturan ranking berbeda per event, admin perlu bisa memilih aturan dasar.
- Insight lanjutan seperti prediksi, rating pemain, dan analitik mendalam ditunda.

## 10. Arah UI

Arah visual menggunakan gaya esport yang modern, gelap, dan energik, tapi tetap mudah dibaca untuk stakeholder resmi.

Prinsip UI:

- Dark theme sebagai identitas utama.
- Aksen warna kuat untuk status live, menang, kalah, dan final.
- Kartu match besar untuk live score.
- Bracket mudah discroll di mobile.
- Jadwal cepat difilter.
- Public page mobile-first.
- Admin page fokus pada tabel, filter, dan aksi cepat.

Referensi rasa visual:

- Esport dashboard.
- Live tournament board.
- Scoreboard arena.

Batasan:

- PRD ini tidak menetapkan desain final.
- Detail warna, typography, layout, dan component system dibuat pada tahap UI design.

### 10.1 Prioritas Tampilan Public

- Home public menampilkan live score, jadwal hari ini, highlight match, dan ranking ringkas.
- Live score harus bisa dibaca cepat di layar mobile dan layar venue.
- Jadwal memiliki filter cepat: tanggal, cabor, venue, PDAM, status.
- Halaman match menampilkan skor, status, venue, waktu, peserta, dan riwayat update singkat.
- Bracket mobile memakai horizontal scroll dengan label ronde jelas.
- Ranking menampilkan rumus perhitungan agar tidak menimbulkan sengketa.

### 10.2 Prioritas Tampilan Admin dan Panitia

- Dashboard admin menampilkan jumlah match hari ini, match live, pending verifikasi, revisi skor, dan data belum lengkap.
- Scorekeeper mendapat layar input skor yang besar, cepat, dan minim tombol.
- Tombol finalisasi dan revisi diberi konfirmasi agar tidak salah tekan.
- Setiap form penting menampilkan status simpan dan waktu update terakhir.
- Admin table wajib punya search, filter, pagination, dan export pada data besar.

### 10.3 Aksesibilitas dan Operasional Lapangan

- Kontras warna harus aman untuk penggunaan outdoor atau layar proyektor.
- Status tidak boleh bergantung pada warna saja; wajib ada teks status.
- Tombol input skor cukup besar untuk perangkat tablet atau laptop lapangan.
- Public page tetap dapat dibuka tanpa login.
- Admin dan panitia wajib login.

## 11. Arsitektur Produk v1

Target awal: public lebih dari 1.000 pengguna dan panitia lebih dari 100 pengguna pada waktu event.

### 11.1 Prinsip Arsitektur

- Satu aplikasi web responsive untuk public, admin, dan panitia.
- Database relasional menjadi sumber data utama.
- Public page membaca data published/final, bukan data draft internal.
- Realtime v1 boleh memakai polling ringan 5-15 detik untuk live score.
- Websocket ditunda kecuali polling tidak cukup saat load test.
- File statis, gambar, dan dokumen peserta disimpan di object storage atau storage server yang terpisah dari kode aplikasi.
- Audit log ditulis append-only, tidak diedit manual.

### 11.2 Komponen Teknis Minimum

- Frontend public mobile-first.
- Admin panel untuk master data, verifikasi, jadwal, konten, dan audit.
- Panel panitia/scorekeeper untuk input skor dan status match.
- API backend untuk autentikasi, data event, skor, jadwal, bracket, klasemen, dan ranking.
- Database relasional untuk data utama.
- Cache untuk halaman public yang ramai: jadwal, live score, ranking, profil PDAM.
- Background job untuk import, export, hitung ulang klasemen, dan generate bracket bila perlu.
- Monitoring error dan log aplikasi.
- Backup database otomatis harian dan backup manual sebelum event dimulai.

### 11.3 Strategi Skala dan Performa

- Public endpoint harus cacheable dan tidak bergantung pada session login.
- Query utama public wajib memakai pagination, filter terindeks, dan payload ringkas.
- Live score hanya mengambil data match aktif atau berubah, bukan seluruh event.
- Admin table memakai server-side pagination.
- Upload dokumen dibatasi ukuran dan tipe file.
- Load test dilakukan minimal untuk 1.000 public user, 100 panitia login, dan 50 scorekeeper aktif input bersamaan.

### 11.4 Keamanan Minimum

- Login wajib untuk admin dan panitia.
- Password disimpan dengan hash aman.
- Role dan permission dicek di backend, bukan hanya UI.
- CSRF/session protection atau token auth wajib sesuai stack yang dipakai.
- Input file dibatasi tipe, ukuran, dan aksesnya.
- Audit log wajib mencatat perubahan penting.
- Public tidak boleh melihat data dokumen internal, audit internal, atau data draft.

### 11.5 Operasional Event

- Data peserta dan jadwal dibekukan sebelum hari event, kecuali perubahan oleh admin.
- Minimal satu admin teknis standby selama pertandingan berlangsung.
- Ada prosedur fallback: scorekeeper mencatat skor manual jika internet venue bermasalah.
- Ada halaman status internal untuk melihat service sehat, jumlah match live, dan error terakhir.
- Backup database dilakukan sebelum hari pertama event dan setelah setiap hari event selesai.

## 12. Tim Pengembangan dan Operasional

Tim minimum agar target awal September realistis:

- Product Owner: pemilik keputusan scope, prioritas, dan aturan event.
- Lead Architect/Tech Lead: menjaga arsitektur, data model, security, dan keputusan teknis.
- Project Manager/Scrum Master: menjaga timeline, blocker, koordinasi, dan rilis.
- UI/UX Designer: membuat flow public, admin, panitia, dan prototype cepat.
- Frontend Developer 1: public page, live score, jadwal, bracket, ranking.
- Frontend Developer 2: admin panel dan panel panitia.
- Backend Developer 1: data model, auth, role, master data, peserta, dokumen.
- Backend Developer 2: match, skor, bracket, klasemen, ranking, audit log.
- QA Engineer: test plan, regression test, UAT, load test sederhana.
- DevOps/Infrastructure: deployment, domain, SSL, backup, monitoring, server sizing.
- Data/Event Coordinator: menyiapkan data PDAM, cabor, venue, peserta, jadwal, dan aturan ranking.
- Support Event: standby saat event, bantu panitia, catat issue lapangan.

Jika tim harus lebih kecil, minimum aman adalah 1 PM/PO, 1 Tech Lead, 2 Fullstack, 1 UI/UX, 1 QA paruh waktu, 1 DevOps paruh waktu, dan 1 Data/Event Coordinator.

## 13. Rencana Delivery Menuju Awal September

### 13.1 Milestone

- Minggu 1: kunci scope v1, finalisasi data model, role, workflow skor, dan prototype UI utama.
- Minggu 2: bangun auth, role, master data, import awal, public shell, dan admin dasar.
- Minggu 3: bangun jadwal, match, input skor, finalisasi, audit log, dan live score public.
- Minggu 4: bangun bracket, klasemen, ranking, profil PDAM, konten event, dan export.
- Minggu 5: UAT panitia, perbaikan flow lapangan, load test, security check, backup, dan deployment staging.
- Minggu 6: data final, training panitia, simulasi event, freeze fitur, dan go-live.

### 13.2 Prioritas Build

- P0: auth, role, master data, peserta, jadwal, match, input skor, finalisasi, live score, audit log.
- P1: bracket, klasemen, ranking, profil PDAM, import/export, konten event, livestream URL.
- P2: dashboard insight, tampilan venue, polish UI, laporan lanjutan.

### 13.3 Definition of Done v1

- Fitur P0 dan P1 selesai dan lolos UAT.
- Data event utama sudah masuk dan diverifikasi.
- Panitia sudah mendapat akun dan assignment.
- Public page lolos cek mobile dan performa dasar.
- Backup, monitoring, domain, SSL, dan deployment production siap.
- Simulasi minimal 10 match berjalan dari jadwal sampai skor final.

## 14. Scope v1

Masuk v1:

- Manajemen satu event aktif.
- Master data PDAM, cabor, venue, tim, atlet.
- Verifikasi peserta dasar.
- Jadwal pertandingan.
- Input dan update skor realtime atau near-realtime.
- Finalisasi hasil match.
- Bracket knockout dasar.
- Klasemen sederhana.
- Halaman public untuk live score, jadwal, bracket, ranking, profil PDAM, dan info event.
- Klasemen medali per Kontingen Provinsi.
- Audit log untuk perubahan skor dan finalisasi.
- Link livestream eksternal.
- Role dasar untuk admin, koordinator cabor, verifikator, scorekeeper, content officer.
- Assignment panitia ke cabor, venue, atau match.
- Import/export CSV atau Excel untuk data operasional utama.
- Cache public page untuk beban lebih dari 1.000 pengguna.
- Backup, monitoring error, dan prosedur fallback event.

## 15. Ditunda Setelah v1

- Multi-event kompleks dengan arsip besar.
- Mobile app native.
- Hosting livestream internal.
- Statistik atlet mendalam.
- Prediksi pertandingan.
- Integrasi payment atau ticketing.
- Sistem protes resmi lengkap.
- Role permission granular sangat detail.
- Generator bracket otomatis untuk semua format cabor kompleks.
- Notifikasi push.
- Websocket penuh bila polling sudah cukup.
- Rule engine ranking/cabor yang sangat granular.
- Workflow approval berlapis untuk semua perubahan data.

## 16. Acceptance Criteria Awal

- Public bisa melihat jadwal pertandingan tanpa login.
- Public bisa melihat agenda event per tanggal, venue, dan cabor tanpa login.
- Public bisa melihat skor match yang sedang berlangsung.
- Public bisa melihat bracket dan hasil final match.
- Public bisa melihat klasemen medali Kontingen Provinsi.
- Public bisa melihat ranking medali per kabupaten/kota dan provinsi.
- Admin bisa membuat event, cabor, venue, PDAM, tim, atlet, dan match.
- Admin bisa memverifikasi atau menolak peserta dengan catatan.
- Scorekeeper bisa input skor untuk match yang dipilih.
- Match yang sudah final tidak bisa diubah tanpa revisi tercatat.
- Perubahan skor penting tercatat di audit log.
- Link livestream bisa ditampilkan pada halaman public.
- Halaman public tetap nyaman dibaca di mobile.
- Admin bisa menugaskan panitia ke cabor, venue, atau match.
- Scorekeeper hanya bisa mengubah match yang ditugaskan.
- Public tidak bisa melihat data draft, dokumen internal, dan audit internal.
- Import data menampilkan preview dan error baris tidak valid.
- Sistem tetap layak dipakai pada skenario 1.000 public user dan 100 panitia.
- Backup production tersedia sebelum event dimulai.

## 17. Risiko dan Pertanyaan Terbuka

- Aturan skor dan ranking bisa berbeda per cabor.
- Format kompetisi perlu dikunci untuk v1 agar tidak terlalu luas.
- Realtime bisa memakai polling dulu bila websocket belum diperlukan.
- Hak akses scorekeeper perlu disesuaikan dengan jumlah panitia lapangan.
- Struktur dokumen peserta perlu disepakati dengan panitia.
- Deadline awal September membuat scope harus dikunci cepat.
- Kualitas data awal dari panitia/PDAM menjadi risiko utama.
- Koneksi internet venue bisa mempengaruhi input skor realtime.
- Keputusan rumus ranking dan aturan cabor harus disahkan sebelum build klasemen.
- Perlu PIC final yang berwenang saat terjadi sengketa skor.

## 18. Non-Goals v1

- Tidak membuat desain UI final.
- Tidak membuat database migration.
- Tidak membuat API contract detail.
- Tidak membuat aplikasi mobile native.
- Tidak membuat sistem livestream sendiri.
- Tidak membuat rule engine semua cabor.

## 15. Addendum Lead Architect: Cabor, Kategori, Bracket, Public Data, dan Integrasi Admin

### 15.1 Prinsip Produk

- Cabor tidak selalu langsung punya bracket.
- Jika cabor punya kategori, public wajib memilih kategori lebih dulu.
- Jika cabor tidak punya kategori, public langsung melihat bracket, klasemen, atau ranking sesuai format cabor.
- Bracket adalah milik `tournament_event`, bukan langsung milik `sports`.
- Admin dan public harus memakai sumber data yang sama agar perubahan skor tidak membuat data ganda.

### 15.2 Alur Public Cabor

```text
Pilih Cabor
→ Ada kategori?
  → Ya: pilih kategori
      → tampil bracket/klasemen kategori
  → Tidak: tampil bracket/klasemen langsung
```

Contoh kategori awal:

| Cabor | Kategori |
|---|---|
| Bulu Tangkis | Tunggal Putra, Tunggal Putri, Ganda Putra, Ganda Putri, Ganda Campuran, Beregu |
| Tenis Meja | Tunggal Putra, Tunggal Putri, Ganda Putra, Ganda Putri, Ganda Campuran, Beregu |
| Tenis Lapangan | Tunggal Putra, Tunggal Putri, Ganda Putra, Ganda Putri, Ganda Campuran |
| Padel | Ganda Putra, Ganda Putri, Ganda Campuran |
| Voli | Putra, Putri bila tersedia |
| Mini Football | Umum/Putra sesuai regulasi panitia |
| Golf | Individual, Team |
| Vokal | Solo, Duet, Grup |
| Catur | Langsung tampil, kategori opsional bila regulasi menambah kelompok |

### 15.3 Bracket Besar 500+ Peserta

- Data bracket boleh dimulai dari `Round of 512` atau `Round of 1024` sesuai jumlah peserta.
- Public default tidak menampilkan semua round besar sebagai bracket visual.
- Public default menampilkan `Main Bracket` mulai `Round 64`.
- Opsi public: `Round 16`, `Round 32`, `Round 64`, `Round 128`, dan `Round Awal`.
- `Round Awal` tampil sebagai list/table compact, bukan full bracket.
- Mirror bracket dipakai untuk main bracket: kiri dan kanan bertemu di final tengah.
- Hover card harus menampilkan jalur history pemenang ke ronde berikutnya dengan line aktif.

### 15.4 Skor dan Winner

- Panitia input skor setelah pertandingan selesai.
- Sistem menghitung winner dari `score_payload` sesuai aturan cabor/kategori.
- Winner otomatis masuk ke `next_match_id` dan `next_slot`.
- Jika skor dikoreksi, sistem recalculation dari match yang berubah sampai downstream terakhir.
- Semua koreksi wajib tersimpan di audit log.

### 15.5 Public Data Banyak

- Halaman PDAM public wajib pagination server-side.
- Search dan filter memakai debounce minimal 400 ms.
- Public ranking wajib filter: event, cabor, kategori, provinsi, kabupaten/kota, PDAM.
- Public list besar tidak boleh render semua data sekaligus.
- SSR digunakan untuk first page dan state filter awal.
- Query filter harus memakai index database.

### 15.6 Keamanan dan Anti-Spam Search

- Public search dibatasi debounce di frontend dan throttle di backend.
- Endpoint public read tetap cacheable.
- Admin write wajib auth, role, CSRF, validasi, dan audit.
- Input skor wajib idempotent untuk mencegah double submit.
- Public tidak boleh menerima internal integer `id`; gunakan `slug` atau `public_id`.

### 15.7 Kesiapan September

- Freeze data peserta dan kategori wajib sebelum bracket lock.
- Setelah bracket locked, peserta baru hanya boleh masuk waiting list atau mengisi slot `BYE` bila match belum dimulai.
- Sebelum go-live wajib UAT panitia: input skor, koreksi skor, bracket update, ranking update, dan pagination public.
