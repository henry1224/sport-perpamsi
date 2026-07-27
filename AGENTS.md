# Instruksi Pengembangan Sport PERPAMSI

Sebelum mengubah kode, skema, seeder, data master, alur registrasi, role, status, atau tampilan data:

1. Wajib baca `docs/09-development/git-workflow.md`.
2. Wajib baca `docs/00-project/phase-execution-standard.md`; task di luar phase aktif tidak boleh dikerjakan tanpa pengecualian tertulis.
3. Wajib mulai dari `docs/SOT-REGISTRY.md` untuk menemukan dokumen kanonik menu dan seluruh data yang saling terhubung.
4. Kode status, label Bahasa Indonesia, tone badge, filter, dan state transition hanya mengikuti `docs/02-data/STATUS-VOCABULARY.md`.
5. Wajib baca `docs/README.md` dan dokumen domain yang terkait.
6. Jangan bekerja langsung di `main`; buat branch sesuai jenis pekerjaan dan nomor phase aktif.
7. Satu branch hanya memuat satu konteks utama.
8. Perubahan relasi data wajib memperbarui dokumentasi, migration plan, test strategy, UAT, dan risk register.
9. Jalankan test/check relevan dan review diff sebelum commit.
10. Perubahan migration atau seeder wajib membaca `docs/02-data/database-lifecycle-standard.md`; struktur legacy tidak boleh dihapus sebelum seluruh deletion gate lulus.

Sumber kebenaran model Pengurus Daerah adalah `docs/02-data/delegation-standard.md`.
Sumber kontrol risiko adalah `docs/06-security/risk-register.md`.
