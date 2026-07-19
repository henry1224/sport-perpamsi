# Threat Model v1

## Risiko Utama

1. User tanpa hak mengubah skor.
2. Scorekeeper mengubah match yang bukan tugasnya.
3. Data draft terlihat di public.
4. Dokumen peserta bocor ke public.
5. Import data merusak data existing.
6. Public traffic tinggi membuat admin lambat.
7. Akun panitia dipakai bersama tanpa kontrol.

## Mitigasi Minimum

- Permission dicek di backend.
- Assignment dicek setiap update skor.
- Public endpoint hanya membaca status published/final.
- Dokumen internal tidak punya public URL bebas.
- Import memakai preview dan validasi sebelum commit.
- Public endpoint cacheable dan dipisah dari session admin.
- Audit log mencatat aktor dan waktu perubahan.
- Password aman dan akun personal per panitia.

## Addendum v2: Risiko Search, Pagination, SSR, dan Input Skor

### Risiko Public Search Spam

- Risiko: user menekan search cepat dan membuat request per detik.
- Mitigasi frontend: debounce minimal 400 ms, cancel previous request, tampilkan loading ringan.
- Mitigasi backend: throttle per IP, limit `per_page`, index query.

### Risiko Query Berat

- Risiko: filter ranking/bracket tanpa index membuat DB lambat saat event.
- Mitigasi: index pada `event_id`, `sport_id`, `category_id`, `province_id`, `regency_id`, `status`, `scheduled_at`.
- Mitigasi tambahan: cache response public dan materialized ranking snapshot bila perlu.

### Risiko Skor Ganda

- Risiko: panitia double click submit skor.
- Mitigasi: idempotency key, disable submit saat pending, audit log, validasi status match.

### Risiko Data Bocor

- Public tidak boleh melihat internal integer ID, nomor telepon PIC, dokumen peserta, atau audit detail.
- Public hanya memakai `slug`, `public_id`, dan display field yang disetujui.

### Risiko SSR

- SSR tidak boleh membawa token admin ke public props.
- SSR props harus dipilah: public props dan admin props dipisah.
