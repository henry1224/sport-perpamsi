# RBAC Matrix Sport PERPAMSI

> Kode saat ini hanya mengeksekusi 4 dari 9 role di matrix ini dan belum memakai Laravel Policy. Drift dan aksi lihat `docs/00-project/audit-2026-07-22.md` (D12, D13).

- Scorekeeper dan Koordinator Cabor hanya dapat melihat match dengan kombinasi cabor dan venue pada assignment aktif.
- Akses tanpa assignment atau melalui URL match di luar scope menghasilkan `403`.

## Role

1. Super Admin.
2. Admin Event.
3. Pengurus Daerah.
4. Koordinator Cabor.
5. Verifikator Peserta.
6. Scorekeeper.
7. Content Officer.
8. Auditor.
9. Public.

## Prinsip

- Permission diperiksa per action di backend.
- Akun privat wajib berstatus terverifikasi/aktif.
- Pengurus Daerah dibatasi `regional_committee_id`.
- Panitia dibatasi `sport_assignments` dan bila perlu assignment match.
- Client tidak menentukan role, scope, PD, status, atau aktor verifikasi.
- Perubahan role/assignment/status tercatat audit.

## Matrix

| Action | Super | Admin | PD | Koordinator | Verifikator | Scorekeeper | Auditor |
|---|---|---|---|---|---|---|---|
| Verifikasi akun PD | yes | yes | no | no | no | no | view |
| Kelola master cabor/venue/agenda | yes | yes | no | scoped | no | no | view |
| Registrasi cabor/pemain | yes | view | own | view | view | no | view |
| Verifikasi peserta | yes | yes | no | scoped view | yes | no | view |
| Kelola assignment | yes | yes | no | no | no | no | view |
| Kelola jadwal | yes | yes | no | scoped | view | assigned view | view |
| Input skor | yes | yes | no | scoped | no | assigned | view |
| Finalisasi | yes | yes | no | scoped | no | no | view |
| Revisi final | yes | approval | no | request | no | no | view |
| Audit/export | yes | yes | own | scoped | scoped | assigned | yes |

Admin mengelola master dan publikasi. PD hanya mengelola roster pemain milik PD sendiri. Panitia tidak dapat mengubah master, kuota, atau snapshot registrasi. Informasi teknis publik hanya read-only.

Pembuatan akun login internal dilakukan melalui menu `Pengguna` dan menerima role `super_admin`, `admin_event`, `scorekeeper`, atau `sport_coordinator`. Akun `pd_admin` dipisahkan dan wajib mengikuti registrasi serta verifikasi Pengurus Daerah. Assignment cabor dan venue Panitia tetap dikelola melalui menu `Panitia & Akses`.

- Super Admin dapat melihat detail, membuat, mengubah, menonaktifkan, dan menghapus akun internal lain. Akun sendiri tidak dapat dinonaktifkan, diturunkan role, atau dihapus.
- Perubahan status Aktif/Nonaktif akun internal hanya dapat dilakukan Super Admin; request langsung dari Admin tetap menghasilkan `403`.
- Admin (`admin_event`) dapat melihat detail seluruh akun internal, tetapi hanya dapat mengubah nama, email, dan password akun sendiri. Admin tidak dapat membuat, mengubah akun lain, menonaktifkan, atau menghapus pengguna.
- Penghapusan akun yang sudah memiliki histori aktivitas ditolak; gunakan status Nonaktif agar histori tetap utuh.

Policy wajib memblokir horizontal privilege escalation meski URL diketahui.
