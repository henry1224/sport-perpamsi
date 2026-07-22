# Standar Eksekusi Phase

Dokumen ini mengontrol urutan pengembangan agar pekerjaan tidak melompat ke phase berikutnya sebelum exit criteria phase aktif terpenuhi.

## Sumber Kebenaran

1. Urutan dan status teknis: [development-roadmap.md](./development-roadmap.md).
2. Scope produk: [prd.md](./prd.md).
3. Target delivery tingkat tinggi: [delivery-plan.md](./delivery-plan.md).
4. Jika nomor fase delivery berbeda dengan phase teknis, **phase teknis pada development roadmap yang dipakai untuk branch dan pengerjaan harian**.

## Phase Aktif

**Phase 5 — Venue, Agenda, dan Panitia**, dengan dependency terbuka **Phase 4B — Multi-Team Registration**.

Phase 5 boleh menyelesaikan UAT dan review venue/agenda. Phase 5 tidak boleh ditutup dan Phase 6 tidak boleh dimulai sampai Phase 4B selesai, karena seed, bracket, match, skor, dan medali harus menunjuk `EntryTeam`, bukan parent `EventEntry`.

Pekerjaan yang boleh dimulai:

- CRUD venue lengkap dan status aktif.
- Agenda/jadwal dengan publikasi dan deteksi konflik waktu.
- Kamus label status Indonesia.
- Aktivasi assignment panitia yang sudah tersedia.
- Policy dan menu panitia berdasarkan scope cabor dan venue.
- Test, UAT, dan dokumentasi yang diperlukan exit criteria Phase 5.
- Dokumentasi target Phase 4B: parent entry, multi-team, kuota hasil technical meeting, verifikasi hybrid, roster immutable, dan migration/backfill plan.
- Implementasi Phase 4B hanya pada branch `features/phase-4b/multi-team-registration` setelah standar disahkan.

Pekerjaan Phase 6–7 tidak boleh diperluas sebelum Phase 5 selesai. Assignment cabor/venue yang sudah tersedia sekarang boleh dilanjutkan dan wajib dihubungkan ke agenda serta policy akses.

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
Phase: 5
Task: Venue dan agenda pertandingan
Dependency: kompetisi dan roster terverifikasi tersedia
Acceptance: Admin mengelola venue, membuat agenda tanpa konflik, dan menetapkan scope panitia
Test: konflik waktu, venue aktif, assignment scope, dan audit perubahan
Dokumen: roadmap, data dictionary, risk register, test strategy, UAT
```

Task tanpa nomor phase tidak boleh masuk branch fitur.

## Urutan Saat Ini

1. Selesaikan dokumentasi dan implementasi Phase 4B.
2. Selesaikan UAT/review Phase 5 dan tutup hanya setelah dependency 4B lulus.
3. Kerjakan Phase 6 dengan participant `EntryTeam`.
4. Selesaikan Phase 7 dan go-live.
