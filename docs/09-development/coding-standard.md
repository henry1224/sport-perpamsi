# Coding Standard Sport PERPAMSI

## Prinsip

- Route hanya mapping URL ke controller.
- Controller tipis: validasi request, panggil service/action, return response.
- Query data besar tidak ditulis di Vue atau route.
- Public data read memakai service/query class.
- Admin write memakai action/service terpisah dan wajib audit.
- Tidak menambah dependency jika Laravel/Vue/CSS native cukup.

## Struktur Backend

```text
app/
├── Http/Controllers/Public
├── Http/Controllers/Admin
├── Support/Porpamnas
├── Actions
├── Models
└── Policies
```

Aturan:

- `app/Http/Controllers/Public`: page public dan endpoint public read.
- `app/Http/Controllers/Admin`: halaman admin dan panitia.
- `app/Support/Porpamnas`: service/query read-model sementara.
- `app/Actions`: proses write penting seperti input skor, koreksi skor, generate bracket.
- `app/Models`: Eloquent model untuk entity database.
- `app/Policies`: authorization admin/panitia.

## Route

Wajib:

```php
Route::get('/bracket', [PublicPageController::class, 'bracket'])->name('bracket');
```

Dilarang:

```php
Route::get('/bracket', fn () => Inertia::render('Bracket', DB::table('matches')->get()));
```

## Controller

- Maksimal berisi request validation, service/action call, response.
- Tidak boleh ada query kompleks.
- Tidak boleh ada transform data panjang.
- Tidak boleh ada file read/write langsung kecuali controller khusus upload/download.

## Service dan Action

- Service read boleh mengembalikan props public/admin.
- Action write harus punya nama kerja jelas: `SubmitMatchScore`, `RecalculateBracket`, `LockBracketSeed`.
- Write action wajib transactional jika menyentuh lebih dari satu tabel.
- Update skor wajib membuat `score_audits`.

## Database

- Public URL tidak memakai internal integer `id`.
- Public entity memakai `public_id` UUID atau `slug`.
- Semua list besar wajib pagination.
- Semua filter public wajib punya index yang sesuai.
- Status memakai string enum yang terdokumentasi.

## Vue

- Page boleh mengatur layout dan state UI lokal.
- Page tidak boleh membuat business rule berat.
- Component harus kecil dan punya tanggung jawab jelas.
- Nama prop eksplisit; hindari prop generik seperti `data` jika shape penting.
- Search public wajib debounce sebelum request server.

## CSS

- Gunakan class scoped per page/component.
- Gunakan token warna dari standar UI.
- Hindari style inline kecuali CSS variable dinamis untuk layout.
- Jangan pakai efek generic seperti glassmorphism berlebihan.
- Jangan membuat card/list baru tanpa mengikuti standar spacing dan state.

## Security

- Semua admin route wajib auth sebelum production.
- Write request wajib CSRF, validasi, authorization, audit.
- Public search wajib throttle backend.
- `per_page` public wajib dibatasi.

## Review Checklist

- [ ] Route tipis.
- [ ] Controller tipis.
- [ ] Query besar ada di service/query class.
- [ ] Write action audit-ready.
- [ ] Public list pakai pagination.
- [ ] Search pakai debounce/throttle.
- [ ] UI mengikuti token warna/spacing.
- [ ] Build dan test lulus.
