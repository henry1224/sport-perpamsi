# Public, Admin, dan PD UI Standard

## Design Direction

- Public: dark navy tournament, resmi, energetic, readable.
- Admin dan PD: portal terang bersama, utilitarian, rapi, dan padat.

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

## Scrollbar

- Public memakai track navy dengan thumb cyan dan hover kuning.
- Admin dan PD memakai track abu terang dengan thumb biru portal.
- Modal mengikuti tema pemanggil: dark untuk public, light untuk Admin dan PD.
- Semua area scroll vertikal dan horizontal memakai pola yang sama.

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
- Search debounce 300 ms.
- Pagination atas dan bawah jika data panjang.
- Card/list memakai nama display ringkas, nama lengkap di detail/tooltip.

### Ranking

- Filter: event, cabor, kategori, provinsi, dan PD PERPAMSI.
- Medal count tidak boleh terlalu dekat dengan nama wilayah.
- Sorting harus jelas.

## Admin dan PD UI Standard

### Layout

- Admin dan PD memakai token, card, tabel, form, modal, badge, pagination, dan focus state yang sama.
- Portal lebih utilitarian, bukan dekoratif.
- Sidebar/topbar konsisten.
- Background portal `#eef3f6`, surface putih, header card `#f7f9fa`.
- Radius card `14px`; radius control `8px`.
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
- Semua daftar data utama memakai pola visual `AdminDataTable`.
- Daftar utama PD memakai `AdminDataTable`; tabel detail roster mengikuti token tabel yang sama.
- Search memakai debounce 300 ms untuk request server.
- Pilihan per halaman konsisten: `10`, `25`, `50`, `100`.
- Footer menampilkan rentang data, total data, halaman aktif, dan total halaman.
- Navigasi pagination memakai tombol `Sebelumnya` dan `Berikutnya`.

### Filter dan Modal

- Toolbar filter memakai dasar putih atau abu muda `#f8fafb` dengan border `#d9e3e9`.
- Urutan kontrol: `Pencarian`, `Filter`, lalu `Per Halaman`.
- Semua modal memakai komponen `Modal.vue`; modal custom per halaman dilarang.
- Modal Public memakai tema dark navy yang senada dengan portal publik.
- Modal Admin dan PD memakai `theme="light"` dengan dasar putih, header `#f7f9fb`, dan backdrop navy transparan.
- Tombol primary memakai biru, secondary putih outline, dan danger merah lembut.

### Aksi Data

- Aksi `Lihat`, `Edit`, `Aktifkan/Nonaktifkan`, dan `Hapus` memakai tombol ikon konsisten dengan tooltip serta `aria-label`.
- Tombol status berada pada kolom aksi: data aktif menampilkan aksi `Nonaktifkan`, data nonaktif menampilkan aksi `Aktifkan`.
- Data aktif tidak dapat dihapus.
- Data nonaktif hanya dapat dihapus bila tidak memiliki relasi; UI menonaktifkan tombol dan server tetap wajib menolak request ilegal.

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
- [ ] Master Venue memakai tabel, pencarian, per-page, pagination, dan modal CRUD.
- [ ] Detail venue publik menyediakan tautan Google Maps yang dapat dibuka pada perangkat peserta.
