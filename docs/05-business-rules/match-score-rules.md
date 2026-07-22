# Match dan Score Rules

> Kode saat ini memakai vocabulary status Inggris (`scheduled/live/final/verified/disputed`) tanpa CHECK enum dan tanpa state machine. Drift dan aksi lihat `docs/00-project/audit-2026-07-22.md` (D14).

## Status Match

1. draft: match belum siap dipublish.
2. terjadwal: match tampil di public jadwal.
3. berlangsung: scorekeeper dapat input skor.
4. jeda: scorekeeper dapat input koreksi skor sementara.
5. selesai: pertandingan selesai, menunggu finalisasi.
6. final: hasil resmi terkunci.
7. revisi: hasil final sedang diperbaiki oleh role berwenang.

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
