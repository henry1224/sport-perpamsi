# RBAC Matrix Sport PERPAMSI

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

Policy wajib memblokir horizontal privilege escalation meski URL diketahui.
