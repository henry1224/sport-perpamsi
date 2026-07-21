# Git Workflow Sport PERPAMSI

Setiap perubahan kode wajib mengikuti `docs/09-development/coding-standard.md` dan setiap perubahan tampilan wajib mengikuti `docs/04-design/public-admin-ui-standard.md`.

Dokumen ini **wajib dibaca sebelum setiap perubahan aplikasi**, terutama perubahan migration, model, seeder, master data, relasi, role, status, registrasi, jadwal, dan skor. Instruksi ini juga dicatat pada `AGENTS.md` di root repository agar berlaku otomatis untuk seluruh pekerjaan.

Perubahan database atau seeder juga wajib membaca `docs/02-data/database-lifecycle-standard.md`. Tidak boleh drop tabel/kolom hanya karena disebut legacy atau belum memiliki menu UI.

## Prinsip

- Branch harus mencerminkan jenis pekerjaan.
- Satu branch berisi satu konteks utama.
- Jangan campur dokumentasi, bug, feature, refactor, style, test, dan chore tanpa alasan jelas.
- Commit kecil dan mudah direview.
- Push branch setelah commit siap direview.
- Merge ke `main` hanya saat perubahan sudah dicek.

## Format Branch

| Jenis Pekerjaan | Format | Contoh |
|---|---|---|
| Dokumentasi | `docs/{nama-dokumen}` | `docs/git-workflow` |
| Bug fix | `bug/{nama-bug}` | `bug/live-score-cache` |
| Feature/modul | `features/{phase}/{nama-fitur}` | `features/phase-3/input-skor` |
| Refactor teknis | `refactor/{nama-refactor}` | `refactor/match-service` |
| Styling/UI | `style/{nama-ui}` | `style/live-score-card` |
| Test-only | `test/{nama-test}` | `test/scorekeeper-assignment` |
| Chore/tooling | `chore/{nama-pekerjaan}` | `chore/setup-ci` |

## Aturan Branch

- Gunakan huruf kecil.
- Gunakan tanda hubung `-`.
- Hindari nama generik seperti `update`, `fix`, `feature`.
- Gunakan `docs/...` untuk PRD, standar, runbook, checklist, dan dokumen pendukung.
- Gunakan `bug/...` untuk bug spesifik.
- Gunakan `features/{phase}/...` hanya untuk fitur aplikasi.

## Workflow Harian

1. Baca `AGENTS.md` dan dokumen ini.
2. Baca `docs/00-project/phase-execution-standard.md` dan pastikan task berada pada phase aktif.
3. Pastikan working tree bersih dan sync `main`.
4. Buat branch dengan nomor phase yang benar; jangan mengubah `main` langsung.
5. Baca dokumen relevan dari `docs/README.md`.
6. Kerjakan perubahan kecil dan terarah.
7. Perbarui roadmap, dokumen, dan risk register bila data/alur berubah.
8. Jalankan test/check relevan.
9. Review diff sendiri.
10. Commit dengan pesan jelas.
11. Push branch.
12. Buat PR bila memakai review flow.
13. Merge setelah review/check selesai.

## Commit Message

Gunakan pola singkat:

```text
<type>: <ringkasan pendek>
```

Type yang dipakai:

- `docs`: dokumentasi.
- `feat`: fitur baru.
- `fix`: bug fix.
- `refactor`: perubahan struktur tanpa behavior baru.
- `style`: styling/UI tanpa logic baru.
- `test`: test-only.
- `chore`: tooling, config, maintenance.

Contoh:

```text
docs: tambah standar git workflow
feat: tambah input skor scorekeeper
fix: blokir edit match final
refactor: rapikan service klasemen
style: rapikan kartu live score
test: tambah test assignment scorekeeper
chore: setup deployment config
```

## Sebelum Commit

- [ ] Branch sesuai konteks pekerjaan.
- [ ] Task berada pada phase aktif atau memiliki pengecualian tertulis.
- [ ] Exit criteria phase sebelumnya sudah terpenuhi.
- [ ] Diff hanya berisi perubahan yang relevan.
- [ ] Tidak ada credential atau data sensitif.
- [ ] Test/check relevan sudah dijalankan.
- [ ] Dokumentasi/progress diperbarui bila behavior berubah.
- [ ] Perubahan data sudah dicek terhadap ERD, migration plan, test strategy, UAT, dan risk register.
- [ ] Penghapusan database lolos seluruh gate pada database lifecycle standard.

## Sebelum Push

- [ ] Commit message jelas.
- [ ] Working tree bersih.
- [ ] Remote branch sesuai nama lokal.
- [ ] Jika push ke GitHub gagal via HTTPS, gunakan remote SSH.

Command dasar:

```bash
git status --short
git add <file>
git commit -m "docs: tambah standar git workflow"
git push -u origin docs/git-workflow
```

## Merge Flow v1

- Branch utama: `main`.
- Semua pekerjaan non-trivial memakai branch terpisah.
- Dokumentasi cepat boleh merge setelah self-review.
- Feature/bug wajib punya checklist verifikasi.
- Setelah merge, hapus branch bila tidak dipakai lagi.
