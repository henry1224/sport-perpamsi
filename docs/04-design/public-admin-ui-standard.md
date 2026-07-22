# Public & Admin UI Standard

## Design Direction

Tema utama: dark navy tournament portal, resmi, energetic, readable.

Dilarang:

- Card putih polos default.
- Gradient ungu/pink generik.
- Glassmorphism berlebihan.
- Shadow acak antar halaman.
- Spacing rapat tanpa hierarchy.

## Token Visual

| Token | Nilai | Fungsi |
|---|---|---|
| Navy base | `#071126` | Background panel utama |
| Navy card | `#08142d` | Card, filter, table |
| Navy row | `#0B1B3F` | Row match/list |
| Cyan | `#36C2F0` | Active path, info, accent |
| Teal | `#20C6B7` | Winner, success |
| Yellow | `#F6C64A` | Active tab, CTA utama |
| Orange | `#F05A28` | Shadow/energy accent |

## Spacing

- Section top/bottom desktop: 40-72px.
- Card padding desktop: 18-24px.
- Card gap: 16-24px.
- Filter block gap: 8-12px.
- Table row height minimal: 44px.
- Match row height minimal: 43px.

## Typography

- Heading utama harus lega: line-height minimal `.98` untuk multi-line besar.
- Uppercase boleh dipakai untuk label, bukan body panjang.
- Body public maksimal 2 kalimat pendek.
- Button label harus action oriented: `Lihat Hasil`, `Buka Bracket`, `Pilih Kategori`.

## Public Page Standard

### Header

- Active menu wajib jelas.
- Nav tetap ringkas.
- CTA kanan satu saja.

### Home

- Hero headline maksimal 3 baris.
- Mascot boleh overlap, tapi tidak menutup teks/card.
- Copy harus resmi, bukan “AI generic”.

### Agenda/Hasil

- Filter di atas list.
- Card/list punya hierarchy: cabor, peserta, venue, waktu, status.
- Empty state wajib ada.

### Bracket

- Jika cabor punya kategori, kategori tampil di bawah tab cabor.
- Main view default `Round 64`.
- Opsi: `Round 16`, `Round 32`, `Round 64`, `Round 128`, `Round Awal`.
- Round awal tampil list compact, bukan full bracket.
- Hover winner path line aktif halus dan tidak putus.
- Final tidak sticky terhadap scroll horizontal.

### Peserta PD PERPAMSI

- Server-side pagination.
- Search debounce minimal 400 ms.
- Pagination atas dan bawah jika data panjang.
- Card/list memakai nama display ringkas, nama lengkap di detail/tooltip.

### Ranking

- Filter: event, cabor, kategori, provinsi, dan PD PERPAMSI.
- Medal count tidak boleh terlalu dekat dengan nama wilayah.
- Sorting harus jelas.

## Admin UI Standard

### Layout

- Admin lebih utilitarian, bukan dekoratif.
- Sidebar/topbar konsisten.
- Form area jelas: label, input, help text, error.
- Table admin lebih padat dari public, tapi tetap readable.

### Form

- Field wajib punya label.
- Error tampil dekat field.
- Submit disabled saat loading.
- Write action berisiko wajib confirmation.

### Table

- Server-side pagination.
- Filter sticky di atas table.
- Bulk action tidak muncul jika belum ada selection.
- Status memakai badge konsisten.

### Score Input

- Panitia hanya melihat match yang ditugaskan.
- Input skor setelah pertandingan selesai.
- Tombol submit punya loading state.
- Koreksi skor wajib alasan.
- Perubahan winner wajib preview downstream impact.

## Component Rules

- Buat component jika dipakai minimal 2 tempat.
- Jangan buat abstraction untuk satu pemakaian.
- Nama component harus domain-based: `MatchCard`, `StatusBadge`, `SectionTitle`.
- Component public dan admin boleh beda jika kebutuhan UX beda.

## UI Review Checklist

- [ ] Active state jelas.
- [ ] Empty/loading/error state ada.
- [ ] Spacing antar card lega.
- [ ] Text tidak tertutup mascot/card.
- [ ] Data panjang pakai ellipsis + title/detail.
- [ ] Mobile tidak rusak total.
- [ ] Tidak ada warna/shape baru tanpa alasan.
- [ ] Kuota null tampil sebagai `Tidak dibatasi` di Admin dan `Minimal {n} pemain` di publik/PD.
- [ ] Informasi teknis cabor mudah dipindai dan ikon hanya tampil bila aset tersedia.
