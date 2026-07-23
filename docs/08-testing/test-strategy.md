# Test Strategy Sport PERPAMSI

Setiap kontrol kritis/tinggi pada [risk-register.md](../06-security/risk-register.md) wajib memiliki test atau bukti UAT.

## Phase 5

- Feature test memastikan hanya Admin mengelola venue/agenda.
- Feature test memastikan rentang waktu bertabrakan pada venue dan tanggal sama ditolak.
- Migration PostgreSQL dan build frontend wajib lulus sebelum merge.
- Feature test memastikan panitia tanpa assignment melihat daftar kosong dan URL di luar scope menghasilkan `403`.

## Backend Feature Tests

1. Satu provinsi hanya memiliki satu PD PERPAMSI.
2. Dua pengajuan aktif untuk provinsi sama ditolak, termasuk request bersamaan.
3. Akun menunggu, ditolak, nonaktif, atau ditangguhkan tidak dapat mengakses portal.
4. Pengguna tidak dapat mengganti provinsi atau PD melalui request manual.
5. Penolakan/perbaikan pengajuan wajib alasan dan audit.
6. Pengurus Daerah hanya mengelola registrasi milik PD sendiri.
7. Registrasi ditolak saat pendaftaran ditutup.
8. Pemain duplikat dan jumlah pemain di luar batas aturan ditolak.
9. Registrasi baru menyimpan `pdam_id` null, key PD/event unik, dan roster secara transaksional.
10. Portal PD hanya memuat kompetisi yang dipublikasikan Admin.
11. Route dan submit kompetisi unpublished ditolak.
12. Validasi roster memakai snapshot walau master kategori berubah.
13. Publish ditolak untuk kategori tidak aktif/tidak cocok dan periode invalid.
14. Kategori/peraturan yang sudah dipakai tidak dapat diubah tanpa workflow versi.
15. Format Data Lomba draft selalu mengikuti Format Bawaan Master Cabor; snapshot terpublikasi tidak berubah saat master diperbarui.
16. Seed kategori, roster, Format Bawaan, dan sistem skor sesuai panduan teknis slide 5-23.
17. Bracket tidak dapat dikunci jika verifikasi belum selesai.
18. Venue dan waktu agenda yang bertabrakan ditolak.
19. Panitia tidak dapat mengakses cabor atau match di luar assignment.
20. Scorekeeper tidak dapat mengubah hasil final langsung.
21. Revisi final wajib alasan, approval, dan audit.
22. Klasemen hanya memakai hasil final/terverifikasi.
23. Public tidak melihat draft, data pribadi, ID internal, atau audit.
24. Seeder dapat dijalankan ulang tanpa menimpa data operasional.
25. Hanya Admin terverifikasi dapat membuat akun panitia serta menetapkan atau mencabut assignment cabor dan venue.
26. Admin dapat mengelola cabor/kategori, regulasi selalu membuat versi baru, dan seluruh perubahan master tercatat audit.
27. Publish wajib memakai regulasi dari cabor yang sama, snapshot menyimpan versinya, dan unpublish ditolak setelah entry tersedia.
28. PD dapat menyimpan draft, submit, memperbaiki, mengirim ulang, dan membatalkan roster sendiri tanpa menghapus histori.
29. PD tidak dapat mengubah atau membatalkan roster milik PD lain maupun roster pending/verified.

## Phase 4B — Multi-Team Registration

Implementasi otomatis utama: `MultiTeamRegistrationTest`, `TournamentEventPublicationTest`, `RegionalCommitteeRegistrationTest`, dan `EntryRosterWorkflowTest`. Migration fresh dijalankan oleh `RefreshDatabase`; upgrade PostgreSQL diverifikasi melalui migration `000019`–`000020` dan pemeriksaan nol orphan.

30. Satu PD hanya memiliki satu parent entry per kompetisi dan dapat membuat banyak team sampai batas snapshot.
31. `team_no` unik, positif, stabil, dialokasikan server, tahan concurrent insert, dan tidak dipakai ulang setelah cancellation.
32. Label team persis `PD PERPAMSI {provinsi} #{team_no}` dan payload client tidak dapat memalsukan label/PD/nomor.
33. Publish ditolak bila `max_teams_per_pd` atau batas anggota per team belum ditetapkan technical meeting.
34. Catur individual menghasilkan team beranggota satu; bulu tangkis ganda menghasilkan team beranggota dua; golf individual menghasilkan beberapa team beranggota satu.
35. Jumlah team aktif dan anggota tiap team divalidasi dari snapshot, bukan master terbaru.
36. Verifikasi parent berlaku kepada team tanpa override.
37. Override satu team tidak mengubah team lain; reset override mengembalikan effective status ke parent.
38. API/UI menghasilkan parent status, override nullable, dan effective status yang sama.
39. Team belum efektif verified tidak masuk seed/bracket; bracket lock menolak pending/revision_required.
40. Pemain tidak dapat dipindah, ditukar, delete-create, atau disubstitusi antar-team setelah verified.
41. Koreksi identitas dalam team sama wajib kewenangan, alasan, bukti, dan audit.
42. `avoid_same_pd_in_round=true` memisahkan team satu PD pada ronde awal bila alternatif valid tersedia.
43. Relaksasi pairing hanya terjadi saat mustahil, deterministik, dan tercatat audit/snapshot.
44. Snapshot tetap tidak berubah setelah master atau hasil technical meeting berikutnya berubah.
45. Match/result tetap menunjuk participant dan roster snapshot historis.
46. Setiap team dapat meraih medali; satu PD dapat memperoleh beberapa medali kategori sama dan seluruhnya masuk klasemen.
47. Migration backfill membuat team `#1` tanpa mengubah match/hasil lama dan tanpa orphan/cross-event participant.

## Gap Recovery Phase 3–5

48. Admin dapat membuat, mengubah, dan mengarsipkan Data Lomba draft dengan memilih cabor serta kategori yang saling sesuai.
49. Membuat Data Lomba tidak otomatis membuat entry, team, match, skor, atau audit skor.
50. Pembuatan dan publish ditolak bila kategori tidak sesuai atau cabor belum memiliki regulasi aktif.
51. Match dianggap terjadwal hanya setelah agenda, venue, dan waktu terisi dari agenda yang valid.
52. Assignment panitia hanya membuka match dengan pasangan cabor dan venue yang sesuai.
53. Seeder baseline tidak membuat data pertandingan demo; demo seeder eksplisit dapat dijalankan dan dibersihkan terpisah.
54. Cleanup demo menghapus score audit, match score, dan match tanpa menghapus master cabor, kategori, regulasi, venue, atau PD.
55. Status `bracket_locked` ditolak bila kompetisi belum pernah dipublikasikan atau masih memiliki team belum efektif verified.
56. Data Lomba draft mengambil format, regulasi aktif, kuota, dan aturan official dari master tanpa override manual.
57. Perubahan default master tidak mengubah snapshot kompetisi yang sudah dipublikasikan.
58. Snapshot menyimpan kuota/peran official serta aturan atlet merangkap kategori.
59. Official ditolak bila juga terdaftar sebagai pemain dan snapshot tidak mengizinkan official bertanding.
60. Jika official boleh bertanding, portal PD menampilkan daftar cabor tempat official tersebut juga tercatat sebagai pemain.
61. Menonaktifkan cabor menghilangkannya dari publik dan pilihan transaksi baru tanpa menghapus histori.
62. Toggle status cabor mempertahankan seluruh default aturan registrasi.
63. Halaman error `403`, `404`, `419`, `500`, dan `503` memakai template serta aset `STOP.png`.
64. Nama sama pada identitas berbeda dicatat sebagai risiko sampai `player_id`/NIK/KTA tersedia.
65. Submit pemain ditolak tanpa foto, form pendaftaran, KTP, kartu pensiun, dan SK karyawan tetap.
66. Submit official ditolak tanpa foto dan KTP.
67. Draft mempertahankan dokumen existing saat form diperbarui tanpa upload ulang.
68. Identitas NIK/KTA yang sama memicu aturan official rangkap walau nama berbeda.
69. Submit pemain ditolak tanpa asal PDAM valid; official tidak meminta PDAM.
70. Master PDAM mendukung pencarian, filter provinsi, tambah, dan edit tanpa menghapus referensi pemain.
71. Perubahan Master Cabor, Kategori, atau Regulasi menyinkronkan Data Lomba draft terkait tanpa mengubah snapshot terpublikasi.

## Frontend/E2E

1. Pengguna memilih Masuk atau Daftar Pengurus Daerah.
2. Pengajuan baru menampilkan status Indonesia dan tidak langsung membuka portal.
3. Admin memverifikasi, meminta perbaikan, atau menolak pengajuan.
4. Admin preview dan mempublikasikan paket kompetisi yang sudah fix.
5. Pengurus Daerah hanya memilih kompetisi terpublikasi dan memasukkan pemain sesuai snapshot.
6. Admin mengelola cabor, kategori, peraturan, venue, agenda, dan assignment panitia.
7. Panitia hanya melihat cabor dan pertandingan tugasnya.
8. Public melihat label `PD PERPAMSI {provinsi} #{team_no}` pada peserta/bracket/hasil dan nama PD tanpa nomor pada klasemen agregat.
9. Tidak ada kode mentah seperti `registration_open` atau `bracket_locked` pada UI/export.

## Load dan Concurrency

- Submit pengajuan provinsi sama secara bersamaan.
- 1.000 pengguna publik membuka agenda/hasil.
- 100 panitia login.
- 50 scorekeeper memperbarui match berbeda.
- Double submit skor tidak membuat dua hasil.

## Exit Criteria

### Bukti Phase 5

- Venue dapat dinonaktifkan dan tidak tersedia untuk agenda baru.
- Update agenda terpublikasi membutuhkan alasan dan menghasilkan audit before/after dengan aktor.
- Publikasi agenda menghasilkan audit append-only.
- Upgrade migration menyisakan nol kategori nonaktif dan tidak menghapus kategori aktif.

- Semua test P0/P1 lulus.
- Tidak ada risiko kritis terbuka.
- Risiko tinggi memiliki kontrol dan bukti test/UAT.
- Migration fresh, migration upgrade, seed ulang, dan restore test lulus.
## Koreksi Tipe Master Cabor

- Feature test memastikan Padel, Golf, dan Vokal hasil seed bertipe `exhibition`.
- Feature test membandingkan format bawaan seluruh cabor dengan katalog resmi.
