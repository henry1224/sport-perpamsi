# API Contract Target

## Prinsip

- Write wajib auth, status akun, permission, assignment, validasi, transaksi, dan audit.
- Public hanya memakai `public_id`/slug dan data terbit/final.
- Status response memiliki `code` internal dan `label` Indonesia.

## Auth dan Pengajuan PD

| Method | Path | Fungsi |
|---|---|---|
| GET | `/login` | Form masuk |
| GET | `/register` | Form daftar Pengurus Daerah |
| POST | `/register` | Buat pengajuan akses PD |
| GET | `/registration-status` | Lihat status pengajuan |
| GET | `/admin/committee-applications` | Daftar pengajuan |
| POST | `/admin/committee-applications/{id}/verify` | Verifikasi |
| POST | `/admin/committee-applications/{id}/revision` | Minta perbaikan |
| POST | `/admin/committee-applications/{id}/reject` | Tolak dengan alasan |

## Portal Pengurus Daerah

| Method | Path | Fungsi |
|---|---|---|
| GET | `/pd/dashboard` | Ringkasan PD |
| GET | `/pd/events` | Kompetisi yang dapat didaftarkan |
| POST | `/pd/events/{event}/entries` | Buat registrasi PD |
| PUT | `/pd/entries/{entry}` | Ubah draft registrasi |
| POST | `/pd/entries/{entry}/members` | Tambah pemain |
| PUT | `/pd/entries/{entry}/members/{member}` | Ubah pemain |
| DELETE | `/pd/entries/{entry}/members/{member}` | Hapus pemain sebelum submit |
| POST | `/pd/entries/{entry}/submit` | Ajukan verifikasi |

## Master Admin

CRUD resource tersedia untuk `sports`, `sport-categories`, `sport-rules`, `tournament-events`, `venues`, dan `agendas`. Delete master yang sudah dipakai harus ditolak; gunakan nonaktif/arsip.

## Panitia dan Pertandingan

| Method | Path | Fungsi |
|---|---|---|
| POST | `/admin/sport-assignments` | Tugaskan panitia ke cabor/scope |
| DELETE | `/admin/sport-assignments/{id}` | Nonaktifkan assignment |
| GET | `/committee/matches` | Match sesuai assignment |
| POST | `/committee/matches/{match}/score` | Input skor |
| POST | `/committee/matches/{match}/finalize` | Finalisasi sesuai permission |
| POST | `/admin/matches/{match}/revision` | Revisi final beralasan |

## Public

- `/agenda`, `/venue`, `/cabor`, `/peserta`, `/bracket`, `/hasil`, `/ranking`.
- Nama peserta menggunakan `PD PERPAMSI {provinsi}`.
- Filter: event, cabor, kategori, venue, tanggal, status, dan pencarian.
- Throttle dan cache mengikuti data standard.

## Status Response

```json
{
  "status": {
    "code": "registration_open",
    "label": "Pendaftaran Dibuka"
  }
}
```

UI tidak boleh menampilkan `code` secara langsung.
