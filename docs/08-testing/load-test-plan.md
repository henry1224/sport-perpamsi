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
