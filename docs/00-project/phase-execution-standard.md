# Standar Eksekusi Phase

Dokumen ini mengontrol urutan pengembangan agar pekerjaan tidak melompat ke phase berikutnya sebelum exit criteria phase aktif terpenuhi.

## Sumber Kebenaran

1. Urutan dan status teknis: [development-roadmap.md](./development-roadmap.md).
2. Scope produk: [prd.md](./prd.md).
3. Target delivery tingkat tinggi: [delivery-plan.md](./delivery-plan.md).
4. Jika nomor fase delivery berbeda dengan phase teknis, **phase teknis pada development roadmap yang dipakai untuk branch dan pengerjaan harian**.

## Phase Aktif

**Phase 3 — Kompetisi dan Publikasi Registrasi.**

Pekerjaan yang boleh dimulai:

- Preview paket kompetisi sebelum publikasi.
- Penetapan versi regulasi ke kompetisi.
- Tarik publikasi sebelum ada pendaftaran.
- Audit publikasi, penutupan, dan penarikan kompetisi.
- Test, UAT, dan dokumentasi yang diperlukan exit criteria Phase 3.

Pekerjaan Phase 4–7 tidak boleh diperluas sebelum Phase 3 selesai. Implementasi assignment cabor/venue yang sudah terlanjur dibuat dicatat sebagai pekerjaan awal Phase 5 dan **dibekukan** sampai Phase 5 aktif.

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
Phase: 3
Task: Preview dan publikasi kompetisi
Dependency: master cabor, kategori, dan regulasi tersedia
Acceptance: Admin memilih versi regulasi, preview snapshot, publish, dan tarik publikasi sebelum ada entry
Test: snapshot regulasi, audit publikasi, larangan tarik setelah entry
Dokumen: roadmap, data dictionary, risk register, test strategy, UAT
```

Task tanpa nomor phase tidak boleh masuk branch fitur.

## Urutan Saat Ini

1. Selesaikan Phase 3.
2. Tutup gap Phase 4.
3. Lanjutkan Phase 5.
4. Kerjakan Phase 6.
5. Selesaikan Phase 7 dan go-live.
