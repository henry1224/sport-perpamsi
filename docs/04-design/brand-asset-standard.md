# Brand Asset Standard PORPAMNAS IX

## Arah Desain

Tema visual: **arena sport nasional tropis-futuristik**.

- Gelap premium sebagai dasar public page.
- Biru PERPAMSI sebagai warna struktur dan navigasi.
- Merah-oranye maskot sebagai aksen energi/live/highlight.
- Hijau-toska air sebagai aksen identitas PERPAMSI.
- Logo official tampil bersih, tidak ditimpa efek berlebihan.
- Maskot dipakai sebagai visual hero, empty state, dan sport identity, bukan dekorasi acak.

## Lokasi Asset

- Logo PORPAMNAS: `public/assets/brand/logos/porpamnas/`.
- Logo PTMB: `public/assets/brand/logos/ptmb/`.
- Maskot: `public/assets/brand/mascots/`.
- Manifest asset: `data/seed/brand_assets.csv`.

## Logo Utama

### Header Public Dark

- Pakai logo full-color: `public/assets/brand/logos/porpamnas/porpamnas-ix.png` atau `public/assets/brand/logos/porpamnas/vertical-porpamnas-ix.png`.
- Pasangkan dengan logo full-color PTMB: `public/assets/brand/logos/ptmb/logo-ptmb-landscape.png`.
- Beri safe area berupa glass/white chip agar logo full-color tetap terbaca di background gelap.
- Tinggi logo desktop: 40-56px.
- Tinggi logo mobile: 32-40px.
- Jarak antar logo minimal 24px desktop, 14px mobile.

### Header Light / Admin

- Pakai logo full-color sebagai default.
- Versi hitam hanya dipakai untuk dokumen/print/header admin yang benar-benar putih dan formal.
- Versi putih hanya dipakai untuk overlay foto/video gelap bila full-color tidak terbaca.

### App Icon / Compact

- Pakai `public/assets/brand/logos/porpamnas/porpamnas-ix.png`.
- Pakai `public/assets/brand/logos/ptmb/only-logo-ptmb.png` bila perlu partner lockup kecil.

## Aturan Varian Logo

- Default sistem: logo full-color.
- Varian hitam: print, dokumen formal, atau background putih murni.
- Varian putih: background gelap solid, foto, atau hero tanpa white chip.
- Jangan jadikan logo hitam/putih sebagai brand utama public page.
- Jika memakai background gelap premium, gunakan full-color di atas white/glass chip.

## Maskot

### Hero Public

- Pakai maskot utama `beru.png` dan `ganga.png` sebagai pasangan hero.
- Letakkan di kanan hero desktop, overlap dengan gradient ring/stadium glow.
- Mobile: satu maskot saja, ukuran 180-240px, ditempatkan setelah headline.
- Jangan pakai lebih dari 2 maskot dalam satu hero.

### Cabor Cards

- Bulu tangkis: `bulu-tangkis.png`.
- Catur: `catur.png`.
- Mini Football: `mini-football.png`.
- Tenis Lapangan: `tenis-lapangan.png`.
- Tenis Meja: `tenis-meja.png`.
- Vokal: `vokal.png`.
- Voli: `voli.png`.

### Empty State dan Success State

- Empty bracket: maskot cabor opacity 65%, ukuran 160px.
- Hasil final/menang: maskot kecil 72-96px dengan accent glow.
- Error state: jangan gunakan maskot celebratory; gunakan card netral.

## Layout Public Page

### Home

- Top bar: logo PORPAMNAS kiri, logo PTMB/PERPAMSI kanan.
- Hero: headline besar, tanggal/lokasi, CTA `Lihat Agenda`, `Lihat Hasil`.
- Mascot stage: Beru/Ganga dengan background glow biru-toska dan aksen flame.
- Content grid: hasil terbaru, agenda hari ini, ranking, cabor.

### Agenda

- Gunakan timeline horizontal tanggal.
- Card agenda pakai aksen warna berdasarkan tipe:
  - `sport`: biru/toska.
  - `exhibition`: amber/gold.
  - `official`: navy solid.
- Venue dan jam dibuat sangat terbaca.

### Cabor Grid

- Card cabor pakai maskot cabor sebagai floating figure.
- Nama cabor besar.
- Info cepat: venue, jam terdekat, status.
- Hover desktop: maskot naik 8px, glow bertambah.

### Ranking

- Ranking PDAM/wilayah memakai table gelap dengan podium top 3.
- Top 1 punya gold glow tipis, bukan warna penuh berlebihan.
- Toggle ranking: PDAM, Kabupaten/Kota, Provinsi.

## Token Warna

```css
:root {
  --porpamnas-navy: #0B1F4D;
  --porpamnas-blue: #1946A3;
  --porpamnas-sky: #36C2F0;
  --porpamnas-aqua: #20C6B7;
  --porpamnas-flame: #F05A28;
  --porpamnas-gold: #F6C64A;
  --porpamnas-ink: #071126;
  --porpamnas-card: #10275C;
  --porpamnas-soft: #EAF4FF;
}
```

## CSS Utility Awal

```css
.brand-shell {
  background:
    radial-gradient(circle at 80% 10%, rgba(54, 194, 240, .22), transparent 34rem),
    radial-gradient(circle at 10% 80%, rgba(240, 90, 40, .16), transparent 28rem),
    linear-gradient(135deg, #071126 0%, #0B1F4D 54%, #10275C 100%);
  color: white;
}

.mascot-stage {
  position: relative;
  isolation: isolate;
}

.mascot-stage::before {
  content: "";
  position: absolute;
  inset: 12% 4% 8% 12%;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(54, 194, 240, .32), rgba(246, 198, 74, .16));
  filter: blur(22px);
  z-index: -1;
}

.sport-card {
  background: linear-gradient(160deg, rgba(255,255,255,.12), rgba(255,255,255,.04));
  border: 1px solid rgba(255,255,255,.16);
  border-radius: 28px;
  box-shadow: 0 24px 80px rgba(0,0,0,.28);
}

.brand-chip {
  display: inline-flex;
  align-items: center;
  gap: 18px;
  padding: 10px 16px;
  border-radius: 22px;
  background: rgba(255, 255, 255, .9);
  border: 1px solid rgba(255, 255, 255, .42);
  box-shadow: 0 18px 60px rgba(0, 0, 0, .18);
  backdrop-filter: blur(18px);
}
```

## Accessibility

- Logo wajib punya `alt` deskriptif.
- Maskot dekoratif pakai `alt=""` jika tidak membawa informasi.
- Jangan menyampaikan status hanya lewat warna.
- Kontras text di atas background gelap minimal WCAG AA.

## Larangan

- Jangan stretch logo.
- Jangan memberi shadow tebal pada logo official.
- Jangan mencampur semua maskot dalam satu screen.
- Jangan pakai maskot sebagai background opacity sangat rendah di belakang teks panjang.
- Jangan taruh logo warna penuh di background warna ramai tanpa safe area.
