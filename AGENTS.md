# Instruksi Pengembangan Sport PERPAMSI

Sebelum mengubah kode, skema, seeder, data master, alur registrasi, role, status, atau tampilan data:

1. Wajib baca `docs/09-development/git-workflow.md`.
2. Wajib baca `docs/00-project/phase-execution-standard.md`; task di luar phase aktif tidak boleh dikerjakan tanpa pengecualian tertulis.
3. Wajib baca `docs/README.md` dan dokumen domain yang terkait.
4. Jangan bekerja langsung di `main`; buat branch sesuai jenis pekerjaan dan nomor phase aktif.
5. Satu branch hanya memuat satu konteks utama.
6. Perubahan relasi data wajib memperbarui dokumentasi, migration plan, test strategy, UAT, dan risk register.
7. Jalankan test/check relevan dan review diff sebelum commit.
8. Perubahan migration atau seeder wajib membaca `docs/02-data/database-lifecycle-standard.md`; struktur legacy tidak boleh dihapus sebelum seluruh deletion gate lulus.

Sumber kebenaran model Pengurus Daerah adalah `docs/02-data/delegation-standard.md`.
Sumber kontrol risiko adalah `docs/06-security/risk-register.md`.
