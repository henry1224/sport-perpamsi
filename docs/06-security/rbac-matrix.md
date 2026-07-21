# RBAC Matrix Sport PERPAMSI

## Roles

1. Super Admin
2. Admin Event
3. Koordinator Cabor
4. Verifikator Peserta
5. Scorekeeper
6. Content Officer
7. Auditor/Read Only
8. Public

## Prinsip

1. Permission berbasis action, bukan hanya page.
2. Koordinator Cabor hanya mengelola cabor yang ditugaskan.
3. Scorekeeper hanya mengubah match yang ditugaskan.
4. Verifikator hanya mengubah status verifikasi peserta/dokumen.
5. Nama kontingen diturunkan dari provinsi akun Pengurus Daerah; operator tidak boleh menggantinya bebas.
6. Content Officer hanya mengelola konten public.
7. Public tidak boleh melihat data draft, dokumen internal, dan audit internal.
8. Auditor read-only tidak boleh mengubah data.

## Matrix Ringkas

| Module/Action | Super | Admin | Cabor | Verif | Score | Content | Auditor | Public |
|---|---|---|---|---|---|---|---|---|
| View public page | yes | yes | yes | yes | yes | yes | yes | yes |
| Manage event | yes | yes | no | no | no | no | view | no |
| Manage master data | yes | yes | scoped | no | no | no | view | no |
| Verify participant | yes | yes | view | yes | no | no | view | no |
| Manage schedule | yes | yes | scoped | no | view | no | view | published |
| Input score | yes | yes | scoped | no | assigned | no | view | view |
| Finalize score | yes | yes | scoped | no | no | no | view | final |
| Revise final score | yes | yes | with approval | no | no | no | view | no |
| Manage public content | yes | yes | no | no | no | yes | view | view |
| Manage users/roles | yes | yes | no | no | no | no | view | no |
| View audit log | yes | yes | scoped | scoped | assigned | no | yes | no |
| Export data | yes | yes | scoped | scoped | no | no | yes | no |

## Permission Naming

- `<module>.view`
- `<module>.create`
- `<module>.update`
- `<module>.submit`
- `<module>.approve`
- `<module>.finalize`
- `<module>.revise`
- `<module>.export`
- `<module>.audit.view`

Contoh:

- `matches.score.update`
- `matches.finalize`
- `matches.revise`
- `participants.verify`
- `schedules.update`
- `reports.export`
