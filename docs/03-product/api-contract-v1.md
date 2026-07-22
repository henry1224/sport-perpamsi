# API Contract Target

> Endpoint scorekeeper/koordinator/admin match, filter server-side, dan status response `{code, label}` belum diimplementasikan. Drift dan aksi lihat `docs/00-project/audit-2026-07-22.md` (D9, D10, D11).

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
| GET | `/pd/events/{event}` | Detail kompetisi terpublikasi |
| POST | `/pd/events/{event}/entries` | Buat satu parent registrasi PD |
| PUT | `/pd/entries/{entry}` | Ubah draft parent |
| POST | `/pd/entries/{entry}/teams` | Tambah team sampai batas snapshot; nomor dialokasikan server |
| PUT | `/pd/entries/{entry}/teams/{team}` | Ubah metadata draft team yang diizinkan |
| DELETE | `/pd/entries/{entry}/teams/{team}` | Batalkan team sebelum lock; nomor tidak digunakan ulang |
| POST | `/pd/entries/{entry}/teams/{team}/members` | Tambah pemain pada team |
| PUT | `/pd/entries/{entry}/teams/{team}/members/{member}` | Ubah pemain sebelum verified |
| DELETE | `/pd/entries/{entry}/teams/{team}/members/{member}` | Hapus pemain sebelum verified |
| POST | `/pd/entries/{entry}/submit` | Ajukan parent dan seluruh team untuk verifikasi |

Client tidak boleh mengirim `team_no`, label team, PD, status, atau effective status. Perpindahan anggota antar-team setelah verified ditolak.

## Verifikasi Registrasi Admin

| Method | Path | Fungsi |
|---|---|---|
| POST | `/admin/entries/{entry}/verify` | Verifikasi default parent |
| POST | `/admin/entries/{entry}/revision` | Minta revisi default parent dengan alasan |
| POST | `/admin/entries/{entry}/reject` | Tolak default parent dengan alasan |
| POST | `/admin/entries/{entry}/teams/{team}/verify` | Override team menjadi verified |
| POST | `/admin/entries/{entry}/teams/{team}/revision` | Override revisi team dengan alasan |
| POST | `/admin/entries/{entry}/teams/{team}/reject` | Override penolakan team dengan alasan |
| DELETE | `/admin/entries/{entry}/teams/{team}/verification-override` | Reset override; team kembali mewarisi status parent |

Response team wajib memuat `parent_status`, nullable `verification_status_override`, dan `effective_verification_status`, masing-masing dengan `code` dan label Indonesia.

## Master Admin

CRUD resource tersedia untuk `sports`, `sport-categories`, `sport-rules`, `tournament-events`, `venues`, dan `agendas`. Delete master yang sudah dipakai harus ditolak; gunakan nonaktif/arsip.

| Method | Path | Fungsi |
|---|---|---|
| GET | `/admin/events` | Daftar draft dan kompetisi terpublikasi |
| POST | `/admin/events/{event}/publish` | Validasi dan simpan snapshot regulasi serta periode |
| POST | `/admin/events/{event}/close` | Tutup registrasi tanpa menghapus entry |

Publish ditolak bila kategori tidak aktif, cabor tidak cocok, periode invalid, atau regulasi belum lengkap. Publikasi ulang ditolak setelah entry masuk.

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
- Hasil kategori, peserta, dan bracket memakai label unit `PD PERPAMSI {provinsi} #{team_no}`; klasemen agregat memakai nama PD tanpa nomor.
- Filter: event, cabor, kategori, venue, tanggal, status, dan pencarian.
- Throttle dan cache mengikuti data standard.
- `/cabor` mengirim kategori aktif dan informasi teknis: jadwal, venue, sistem, syarat, official, biaya, dan slide sumber.
- `max_teams_per_pd` dan batas anggota per team wajib terisi sebelum publish; nilainya ditetapkan technical meeting per kompetisi.

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
