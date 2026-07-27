# Match dan Score Rules

> Kode saat ini memakai vocabulary status Inggris (`scheduled/live/final/verified/disputed`) tanpa CHECK enum dan tanpa state machine. Drift dan aksi lihat `docs/00-project/audit-2026-07-22.md` (D14).

## Status Match

Vocabulary nyata, label Indonesia, tone badge, dan gap state machine hanya mengikuti [`STATUS-VOCABULARY.md`](../02-data/STATUS-VOCABULARY.md). Dokumen ini menetapkan rule transisi target, bukan enum baru:

1. Match terjadwal dapat dimulai oleh role dalam scope assignment.
2. Match berlangsung dapat dijeda, ditunda, diselesaikan normal, atau ditetapkan walkover sesuai izin.
3. Hasil resmi wajib memiliki satu status final kanonik; keputusan antara `final` dan `verified` masih gap yang harus ditutup sebelum state machine dianggap selesai.
4. Sengketa/revisi tidak boleh mengubah hasil resmi tanpa alasan dan audit.
5. Pembatalan mempertahankan histori match dan tidak menghasilkan ranking.

## Aturan Input Skor

- Scorekeeper hanya bisa input hasil match yang ditugaskan.
- Input hasil v1 dilakukan setelah pertandingan selesai, bukan live point-by-point.
- Match selesai bisa dikunci menjadi final oleh Koordinator Cabor atau Admin Event.
- Match final tidak bisa diedit langsung.
- Revisi skor final wajib memiliki alasan.
- Semua perubahan skor penting masuk audit log.
- Match menunjuk dua `EntryTeam` dari kompetisi sama dan menyimpan snapshot label/roster participant.
- Team efektif verified terkunci; pencabutan verifikasi setelah bracket lock wajib workflow unlock/reseed resmi.
- Perpindahan atau substitusi anggota antar-team setelah verified dilarang total.

## Aturan Ranking

- Ranking hanya memakai match final.
- Rumus ranking harus tampil di public.
- Jika aturan ranking berbeda per event, Admin Event memilih aturan dasar sebelum event dimulai.
- Perubahan rumus setelah event berjalan harus disetujui PIC event.
