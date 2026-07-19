# Git Workflow Sport PERPAMSI

Setiap perubahan kode wajib mengikuti `docs/09-development/coding-standard.md` dan setiap perubahan tampilan wajib mengikuti `docs/04-design/public-admin-ui-standard.md`.

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

1. Sync branch target.
2. Buat branch sesuai kategori.
3. Baca dokumen relevan saja dari `docs/README.md`.
4. Kerjakan perubahan kecil dan terarah.
5. Jalankan test/check relevan.
6. Review diff sendiri.
7. Commit dengan pesan jelas.
8. Push branch.
9. Buat PR bila memakai review flow.
10. Merge setelah review/check selesai.

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
- [ ] Diff hanya berisi perubahan yang relevan.
- [ ] Tidak ada credential atau data sensitif.
- [ ] Test/check relevan sudah dijalankan.
- [ ] Dokumentasi/progress diperbarui bila behavior berubah.

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
