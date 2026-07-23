# UAT Checklist

Bukti gate otomatis terbaru: [Eksekusi UAT Otomatis — 22 Juli 2026](./uat-execution-2026-07-22.md). Checklist manual tetap harus dijalankan sebelum phase ditutup.

## Registrasi Pengurus Daerah

- [ ] Pengguna dapat memilih Masuk atau Daftar.
- [ ] Pengguna dapat mengajukan akses untuk satu provinsi.
- [ ] Pengajuan provinsi yang sedang diproses tidak dapat diduplikasi.
- [ ] Akun belum terverifikasi tidak dapat masuk portal.
- [ ] Admin dapat verifikasi, minta perbaikan, atau menolak dengan alasan.
- [ ] Aktivitas verifikasi tercatat pada audit.

## Portal Pengurus Daerah

- [ ] Nama portal memakai `PD PERPAMSI {provinsi}`.
- [ ] Pengurus Daerah hanya melihat data PD sendiri.
- [x] Pengurus Daerah hanya melihat kompetisi yang dipublikasikan Admin.
- [-] Pengurus Daerah dapat menambah, mengedit draft, dan mengajukan daftar pemain; automated test lulus, manual UAT masih pending.
- [x] Jumlah dan duplikasi pemain divalidasi sesuai snapshot regulasi kompetisi.
- [ ] Official yang dilarang bertanding diblokir saat namanya juga terdaftar sebagai pemain.
- [ ] Official yang diizinkan bertanding menampilkan informasi cabor lain tempat ia menjadi pemain.
- [ ] Form pemain dan official tampil terpisah serta mudah dibedakan.
- [ ] Pemain tidak dapat submit tanpa lima dokumen wajib; official tidak dapat submit tanpa foto dan KTP.
- [ ] NIK/KTA sama terdeteksi sebagai rangkap walau nama ditulis berbeda.
- [ ] Roster legacy tanpa NIK/KTA dapat dibuka sebagai draft tetapi tidak dapat diajukan ulang sebelum identitas dan dokumen dilengkapi.
- [ ] Pengguna dapat mencari seluruh Master PDAM berdasarkan nama, kota, atau provinsi dan memilih asal untuk setiap pemain.
- [ ] Asal PDAM tampil pada riwayat registrasi dan verifikasi Admin tanpa mengganti nama kontingen PD PERPAMSI.
- [x] Status registrasi dan event pada portal PD tampil dalam Bahasa Indonesia.

## Phase 4B — Multi-Team

- [ ] Technical meeting menetapkan batas team per PD dan anggota per team untuk setiap kompetisi; publish tanpa batas lengkap ditolak.
- [ ] PD melihat satu parent registrasi dan daftar team bernomor pada kategori yang sama.
- [ ] PD dapat menambah team sampai batas snapshot dan menerima pesan Bahasa Indonesia saat batas tercapai.
- [ ] Team individual berisi satu pemain, pasangan berisi dua pemain, dan beregu mengikuti snapshot.
- [ ] Label team otomatis `PD PERPAMSI {provinsi} #{team_no}` dan tidak dapat diubah PD.
- [ ] Admin dapat memverifikasi seluruh parent sekaligus.
- [ ] Admin dapat meminta revisi/menolak hanya satu team tanpa mengubah team saudara.
- [ ] UI membedakan status parent, override, dan effective status dengan label Indonesia.
- [ ] Reset override mengembalikan team ke status parent dan tercatat audit.
- [ ] Pemindahan/substitusi pemain antar-team setelah verified ditolak dengan pesan Indonesia.
- [ ] Team belum efektif verified tidak masuk seed/bracket.
- [ ] Pairing ronde awal memisahkan team satu PD bila alternatif tersedia; relaksasi mustahil terlihat pada audit Admin.
- [ ] Public peserta, bracket, dan hasil menampilkan label team bernomor.
- [ ] Satu PD dapat memperoleh beberapa medali kategori sama dan semua medali masuk klasemen agregat.
- [ ] Team cancelled mempertahankan nomor dan histori; nomor tidak dipakai ulang.

## Master Admin

- [x] Admin dapat mengelola cabor dan kategori tanpa menghapus histori.
- [x] Admin dapat membuat versi peraturan dan menetapkannya ke kompetisi melalui preview publikasi.
- [x] Admin dapat mempublikasikan dan menutup registrasi kompetisi.
- [x] Admin dapat mengubah Format Kompetisi saat draft dan tidak dapat mengubahnya setelah publikasi.
- [ ] Admin memverifikasi kategori, jumlah pemain, dan sistem pertandingan terhadap panduan teknis slide 5-23.
- [x] Admin dapat preview, menarik publikasi sebelum ada entry, dan melihat audit publikasi.
- [ ] Admin dapat menambah, mengedit, dan menonaktifkan venue beserta detailnya.
- [ ] Admin dapat mencari, mengatur jumlah data per halaman, mengisi koordinat, dan menghapus venue yang belum dipakai.
- [ ] Peserta dapat membuka panduan venue melalui Google Maps dari halaman publik.
- [ ] Admin dapat menambah, mengedit, dan mempublikasikan agenda tanpa bentrok venue/waktu; revisi terpublikasi wajib memiliki alasan dan audit.
- [ ] Admin dapat mengelola kompetisi dan status pendaftaran.
- [ ] Admin dapat membuat Data Lomba draft dengan memilih cabor dan kategori; format, regulasi, kuota, serta aturan official tampil otomatis dari master.
- [ ] Perubahan Master Cabor, Kategori, atau Regulasi menyinkronkan draft terkait tanpa mengubah Data Lomba terpublikasi.
- [ ] Membuat Data Lomba tidak otomatis membuat peserta, bracket, pertandingan, atau skor.
- [ ] Kompetisi tanpa kategori/regulasi valid tidak dapat dipublikasikan.
- [x] Admin dapat melihat, menyetujui, atau menolak registrasi serta pemain per PD PERPAMSI.
- [x] PD dapat menyimpan draft, mengirim roster, memperbaiki sesuai catatan, mengirim ulang, dan membatalkan registrasi.
- [x] Admin dapat meminta perbaikan roster dengan catatan.

## Panitia

- [x] Admin dapat membuat akun dan assignment panitia per cabor dan venue.
- [x] Panitia hanya melihat cabor/match tugasnya dan URL di luar assignment diblokir.
- [ ] Scorekeeper dapat input skor match tugasnya.
- [ ] Koordinator dapat finalisasi sesuai permission.
- [ ] Akun dinonaktifkan langsung kehilangan akses.
- [ ] Match tugas panitia memiliki agenda, venue, dan waktu yang sama dengan jadwal Admin.

## Public dan Hasil

- [ ] Public melihat agenda terbit, venue, cabor, bracket, hasil, dan klasemen.
- [ ] Cabor nonaktif tidak tampil publik; detail cabor panjang tampil melalui modal tanpa versi atau slide sumber.
- [ ] Halaman `403`, `404`, `419`, `500`, dan `503` menampilkan `STOP.png`, teks sesuai status, dan tindakan kembali.
- [ ] Nama participant publik memakai `PD PERPAMSI {provinsi} #{team_no}`; klasemen agregat memakai nama PD tanpa nomor.
- [ ] Draft dan data pribadi tidak tampil publik.
- [ ] Klasemen hanya memakai hasil final/terverifikasi.
- [ ] Revisi hasil memperbarui public dan tetap memiliki audit.

## Data dan Operasional

- [ ] Setelah backup dan migration cleanup, hanya kategori aktif yang tersisa; kompetisi dan data turunan kategori nonaktif tidak tampil atau tersisa.

- [ ] Seeder ulang tidak menimpa perubahan Admin.
- [ ] Import memiliki preview dan rollback saat invalid.
- [ ] Backup dan restore sudah diuji.
- [ ] Tidak ada status kode mentah pada UI/export.
- [x] Perubahan master kategori tidak mengubah snapshot kompetisi terpublikasi.
- [ ] `git diff`, test, build, migration upgrade, dan security review lulus sebelum merge.
- [ ] Master Cabor hanya menawarkan tipe `Cabang Olahraga` dan `Eksibisi`; Padel, Golf, dan Vokal tampil sebagai `Eksibisi` saat diedit.
- [ ] Format Bawaan pada form, tabel Master Cabor, dan editor kompetisi memakai label serta nilai yang sama dengan katalog resmi.
- [ ] Toggle aktif/nonaktif cabor tidak mengubah default kuota, peran official, aturan rangkap kategori, atau izin official bermain.
- [ ] Cabor nonaktif tidak dapat dipilih untuk event/agenda baru dan tidak menghapus data historis.
- [ ] Data pertandingan demo dapat dihapus tanpa menghapus master dan data registrasi yang dipertahankan.
- [ ] Seeder baseline tidak mengembalikan 756 match demo setelah cleanup.
- [ ] Tidak ada match operasional tanpa agenda, venue, atau jadwal.
- [ ] Tidak ada kompetisi operasional tanpa kategori dan versi regulasi.
- [ ] Status `bracket_locked` hanya dipakai setelah publikasi dan verifikasi peserta selesai.
