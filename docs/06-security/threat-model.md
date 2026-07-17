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
