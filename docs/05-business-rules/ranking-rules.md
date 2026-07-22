# Ranking Rules

> Sort saat ini belum menyertakan total medali dan tidak ada cache/invalidasi publik. Drift dan aksi lihat `docs/00-project/audit-2026-07-22.md` (D16, D17).

## Klasemen Medali PD PERPAMSI

Setiap `EntryTeam` merupakan peserta medali independen. Medali ditelusuri ke PD melalui `EntryTeam → EventEntry → regional_committee_id` dari team efektif terverifikasi.

Satu PD boleh memperoleh lebih dari satu medali pada kategori sama; semua medali team dihitung tanpa deduplikasi per PD/kategori.

Urutan: emas, perak, perunggu, total medali, lalu nama PD PERPAMSI atau keputusan resmi panitia.

Hanya match final/terverifikasi yang dihitung. Revisi hasil wajib menghitung ulang klasemen dan menginvalidasi cache publik.

## Klasemen Cabor

Mengikuti format dan versi peraturan yang ditetapkan pada kompetisi. Perubahan peraturan setelah kompetisi berjalan tidak mengubah histori tanpa workflow resmi.
