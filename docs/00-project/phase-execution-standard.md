# Standar Eksekusi Phase

Dokumen ini mengontrol urutan pengembangan agar pekerjaan tidak melompat ke phase berikutnya sebelum exit criteria phase aktif terpenuhi.

## Sumber Kebenaran

1. Urutan dan status teknis: [development-roadmap.md](./development-roadmap.md).
2. Scope produk: [prd.md](./prd.md).
3. Target delivery tingkat tinggi: [delivery-plan.md](./delivery-plan.md).
4. Jika nomor fase delivery berbeda dengan phase teknis, **phase teknis pada development roadmap yang dipakai untuk branch dan pengerjaan harian**.

## Phase Aktif

**Phase 4 — Registrasi Cabor dan Pemain.**

Pekerjaan yang boleh dimulai:

- Draft roster sebelum submit.
- Permintaan dan pengiriman perbaikan roster.
- Pembatalan registrasi sesuai status yang diizinkan.
- Audit submit, verifikasi, penolakan, perbaikan, dan pembatalan.
- Test, UAT, dan dokumentasi yang diperlukan exit criteria Phase 4.

Pekerjaan Phase 5–7 tidak boleh diperluas sebelum Phase 4 selesai. Implementasi assignment cabor/venue yang sudah terlanjur dibuat tetap **dibekukan** sampai Phase 5 aktif.

## Gate Sebelum Mulai Pekerjaan

Setiap task wajib menjawab:

- [ ] Task masuk phase berapa?
- [ ] Phase tersebut sedang aktif?
- [ ] Dependency dari phase sebelumnya sudah selesai?
- [ ] Branch memakai nomor phase yang benar?
- [ ] Acceptance criteria dan test tersedia?
- [ ] Dokumen data, keamanan, dan UAT yang terdampak sudah diketahui?

Jika jawaban kedua atau ketiga `tidak`, task tidak dikerjakan.

## Gate Menutup Phase

Phase hanya boleh ditandai selesai jika:

- [ ] Seluruh checklist phase selesai.
- [ ] Exit criteria phase terpenuhi.
- [ ] Test relevan lulus.
- [ ] ERD, data dictionary, migration plan, RBAC, risk register, test strategy, dan UAT senada.
- [ ] Tidak ada risiko kritis terbuka.
- [ ] Perubahan sudah direview dan dikomit.

Setelah semua gate lulus, ubah `Phase Aktif` pada dokumen ini dan status roadmap dalam commit yang sama.

## Pengecualian

Lompatan phase hanya boleh untuk:

1. Bug produksi yang menghambat phase aktif.
2. Perbaikan keamanan kritis.
3. Dependency teknis minimum yang benar-benar memblokir phase aktif.

Pengecualian wajib dicatat pada roadmap dengan alasan, scope minimum, dan phase asal. Pekerjaan lanjutan tetap dibekukan sampai phase tersebut aktif.

## Format Task

Gunakan format ini sebelum coding:

```text
Phase: 4
Task: Draft dan perbaikan roster
Dependency: kompetisi dan snapshot regulasi sudah dipublikasikan
Acceptance: PD menyimpan draft, submit, memperbaiki roster, dan membatalkan sesuai status
Test: ownership PD, transisi status, snapshot roster, dan audit perubahan
Dokumen: roadmap, data dictionary, risk register, test strategy, UAT
```

Task tanpa nomor phase tidak boleh masuk branch fitur.

## Urutan Saat Ini

1. Selesaikan Phase 4.
2. Lanjutkan Phase 5.
3. Kerjakan Phase 6.
4. Selesaikan Phase 7 dan go-live.
