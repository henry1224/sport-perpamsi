# Load Test Plan v1

## Target

- 1.000 public user membuka home, hasil terbaru, jadwal, bracket, ranking.
- 100 panitia login dan membuka dashboard tugas.
- 50 panitia input hasil dalam periode pendek.

## Skenario

1. Public browse home dan jadwal.
2. Public refresh hasil terbaru setiap 10 detik.
3. Public buka bracket cabor populer.
4. Panitia login bersamaan.
5. Panitia submit hasil match.
6. Admin finalisasi beberapa match.

## Metrik Minimum

- Public page p95 di bawah 1.5 detik untuk endpoint cacheable.
- Write action p95 di bawah 2.5 detik.
- Error rate di bawah 1%.
- Tidak ada deadlock database saat input hasil bersamaan.
- Queue tidak menumpuk lebih dari 5 menit.

## Catatan

- Script final dibuat setelah aplikasi Laravel tersedia.
- Tool bisa memakai k6 atau Artillery.
- Load test dilakukan di staging yang mirip production.

## Addendum v2: Test Pagination, Bracket, dan Search

### Skenario Tambahan

1. 1.000 user membuka `/peserta?page=1` bersamaan.
2. 1.000 user membuka ranking dengan filter provinsi dan cabor.
3. 500 user membuka bracket `Round 64` cabor populer.
4. 200 user membuka `Round Awal` dan mengganti page.
5. 100 user melakukan pencarian provinsi dengan debounce 400 ms.
6. 50 panitia submit skor dalam 1 menit.
7. 10 admin koreksi skor dan memicu recalculation bracket/ranking.

### Batas Aman

- `per_page` maksimum public: 100.
- Search kosong harus cacheable.
- Search pendek kurang dari 2 karakter tidak mengirim request.
- Ranking snapshot dipakai jika query live melewati target p95.

### Metrik Tambahan

- Public search p95 di bawah 800 ms.
- Pagination p95 di bawah 700 ms untuk indexed query.
- Bracket `Round 64` p95 di bawah 1 detik.
- Admin submit skor tidak membuat duplicate match score.
