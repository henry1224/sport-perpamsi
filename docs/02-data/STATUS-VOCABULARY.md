# Source of Truth Vocabulary Status

Dokumen ini satu-satunya sumber kebenaran kode status, label Bahasa Indonesia, dan hubungan status lintas modul. Dokumen lain tidak boleh mendefinisikan ulang vocabulary; cukup merujuk ke sini.

## Cara Membaca

- **Nyata**: sudah dipakai kode atau dibatasi database saat ini.
- **Target**: tujuan produk yang belum sepenuhnya tersedia. Target tidak boleh ditulis seolah sudah aktif.
- Kode internal stabil dalam Bahasa Inggris. UI dan export wajib memakai label Indonesia.
- Jika dokumen dan kode berbeda, kolom **Nyata** menjelaskan perilaku sistem saat ini; kolom **Target** menjelaskan gap yang direncanakan.
- Sumber label frontend saat ini: `resources/js/lib/status.js`. Label lokal tidak boleh mengganti arti kode yang sama.

## Vocabulary Kanonik

| Field | Nilai nyata | Label Indonesia kanonik | Enforcement nyata | Target / gap |
|---|---|---|---|---|
| `users.role` | `super_admin`, `pd_admin`, `scorekeeper`, `sport_coordinator` | Super Admin, Pengurus Daerah, Scorekeeper, Koordinator Cabor | Konvensi kode; belum ada DB CHECK | Role target PRD/RBAC (`admin_event`, `verifikator_peserta`, `content_officer`, `auditor`) belum tersedia. Jangan tampilkan sebagai role aktif. |
| `users.account_status` | `pending`, `revision_required`, `verified`, `rejected` | Menunggu Verifikasi, Perlu Perbaikan, Terverifikasi, Ditolak | Konvensi kode; default DB `verified`; belum ada DB CHECK | Tambah constraint dan tetapkan apakah status panitia mengikuti workflow terpisah. |
| `committee_applications.status` | `pending`, `revision_required`, `verified`, `rejected` | Menunggu Verifikasi, Perlu Perbaikan, Terverifikasi, Ditolak | Konvensi kode; default DB `pending`; belum ada DB CHECK | Sinkronisasi transaksional dan audit dengan `users.account_status`. |
| `tournament_events.status` | `registration_draft`, `registration_open`, `registration_closed`, `bracket_locked`; kode/filter juga mengenal `ongoing`, `completed` | Draft, Pendaftaran Dibuka, Pendaftaran Ditutup, Bracket Dikunci, Sedang Berlangsung, Selesai | Konvensi kode; default DB `registration_draft`; belum ada DB CHECK | Transition ke `ongoing` dan `completed` belum tersedia pada controller. `archived` masih target dokumen. |
| `event_entries.verification_status` | `draft`, `pending`, `revision_required`, `verified`, `rejected`, `cancelled` | Draft, Menunggu Verifikasi, Perlu Perbaikan, Terverifikasi, Ditolak, Dibatalkan | Konvensi kode; default legacy DB `verified`; belum ada DB CHECK | Tambah CHECK dan ubah default agar direct insert tidak otomatis terverifikasi. |
| `entry_teams.verification_status_override` | `null`, `draft`, `pending`, `revision_required`, `verified`, `rejected`, `cancelled` | Mengikuti Status Registrasi, Draft, Menunggu Verifikasi, Perlu Perbaikan, Terverifikasi, Ditolak, Dibatalkan | DB CHECK; nullable | Tentukan apakah override `draft` benar-benar dibutuhkan; kode aktif tidak menggunakannya. |
| `entry_members.verification_status` | `pending`, `revision_required`, `verified`, `rejected` | Menunggu Verifikasi, Perlu Perbaikan, Terverifikasi, Ditolak | DB CHECK; default `pending` | Tetapkan workflow official: saat ini official mendapat default `pending`, tetapi tidak diverifikasi dan badge tidak ditampilkan. |
| `matches.status` | `scheduled`, `live`, `paused`, `postponed`, `walkover`, `final`, `verified`, `disputed`, `cancelled` | Terjadwal, Sedang Berlangsung, Dijeda, Ditunda, Menang WO, Final, Terverifikasi, Dalam Sengketa, Dibatalkan | DB CHECK; default `scheduled` | State machine dan transisi lengkap belum tersedia. Tentukan satu status hasil resmi: `final` atau `verified`; ranking saat ini hanya membaca `final`. |
| `matches.side` | `left`, `right`, `final` | Kiri, Kanan, Final | Konvensi/seeder; default `left` | Dokumentasikan semantik bracket dan tambahkan constraint bila tetap dipakai. |
| `sport_assignments.assignment_role` | `scorekeeper`, `sport_coordinator` | Scorekeeper, Koordinator Cabor | Konvensi kode; default `scorekeeper` | Hilangkan kemungkinan drift terhadap `users.role`, atau dokumentasikan beda fungsi keduanya. |
| `event_agendas.status` | `draft`, `published`, `cancelled` | Draft, Dipublikasikan, Dibatalkan | DB CHECK; default `draft` | Controller saat ini hanya mengubah `published_at`, bukan `status`. Pilih satu mekanisme publikasi dan sinkronkan. |
| `event_agendas.type` | `sport`, `exhibition`, `official` | Pertandingan, Eksibisi, Resmi | Validasi controller; DB free string | Tambah DB CHECK jika vocabulary sudah final. |
| `sport_categories.competition_type` | `individual`, `doubles`, `team` | Perorangan, Ganda/Pasangan, Beregu | DB CHECK | Pisahkan dengan jelas dari target snapshot `participant_unit`; jangan anggap key target sudah tersimpan. |
| `sports.type` | `sport`, `exhibition` | Cabang Olahraga, Eksibisi | Validasi controller/konvensi | Konsistenkan dengan `event_agendas.type`. |

## Hubungan Status Registrasi

Model kanonik:

```text
EventEntry.verification_status                     status parent
        │
        ├── EntryTeam.verification_status_override  keputusan khusus team, nullable
        │       │
        │       └── effective_team_status = override ?? parent
        │
        └── EntryMember.verification_status         keputusan pemain individual
```

### Aturan derivasi

1. Status efektif team selalu:

   ```text
   entry_team.verification_status_override
   ?? event_entry.verification_status
   ```

   Sumber kode: `app/Models/EntryTeam.php::effectiveStatus()`. Agregasi dashboard wajib memakai ekspresi yang sama.

2. Persetujuan team ke `verified` hanya sah setelah seluruh pemain team `verified`.
3. Persetujuan team aktif terakhir mengubah parent otomatis ke `verified` jika `team_addition_opened_at` kosong.
4. Penambahan team baru mengubah parent `verified` kembali ke `pending`; override team lama tetap `verified`, sehingga team lama tidak ikut terbuka.
5. Revisi pemain mengubah status pemain ke `revision_required` dan membuka team terkait dengan override `revision_required`; team saudara tetap terkunci.
6. Public tidak menampilkan status verifikasi internal. Hanya team efektif `verified` boleh masuk seed, bracket, hasil, dan klasemen.

## Arti Badge Kanonik

| Kode | Label | Tone kanonik | Arti operasional |
|---|---|---|---|
| `draft` / `registration_draft` | Draft | Netral | Belum diajukan atau dipublikasikan. |
| `pending` | Menunggu Verifikasi | Peringatan/amber | Sudah diajukan; menunggu tindakan verifikator. |
| `revision_required` | Perlu Perbaikan | Peringatan/amber | Pemilik data harus memperbaiki dan mengirim ulang. Bukan status gagal final. |
| `verified` | Terverifikasi | Sukses/hijau | Pemeriksaan selesai dan data sah. |
| `rejected` | Ditolak | Bahaya/merah | Keputusan penolakan; tidak boleh diproses sampai dibuka ulang oleh Admin. |
| `cancelled` | Dibatalkan | Netral/abu | Dihentikan, tetap disimpan sebagai histori. |
| `registration_open` | Pendaftaran Dibuka | Sukses/hijau | Kompetisi terlihat dan menerima pendaftaran dalam periode aktif. |
| `registration_closed` | Pendaftaran Ditutup | Netral/abu | Kompetisi terlihat, tetapi tidak menerima pendaftaran biasa. |
| `bracket_locked` | Bracket Dikunci | Informasi/biru | Peserta/seeding dikunci; registrasi tidak dapat berubah biasa. |
| `scheduled` | Terjadwal | Informasi/biru | Match memiliki jadwal dan belum berjalan. |
| `live` | Sedang Berlangsung | Bahaya/merah | Match sedang berjalan. `LIVE` boleh dipakai sebagai aksen visual, bukan label berbeda. |
| `paused` | Dijeda | Peringatan/amber | Match berjalan tetapi sementara dihentikan. |
| `postponed` | Ditunda | Peringatan/amber | Jadwal match ditunda. |
| `walkover` | Menang WO | Peringatan/amber | Hasil ditetapkan tanpa pertandingan normal. |
| `final` | Final | Sukses/hijau | Hasil final menurut kode saat ini dan dipakai ranking. |
| `verified` pada match | Terverifikasi | Sukses/hijau | Tersedia di DB, tetapi belum dipakai ranking; semantik target harus diputuskan. |
| `disputed` | Dalam Sengketa | Bahaya/merah | Hasil dipersoalkan dan tidak boleh dianggap final. |

### Label konteks vs status

Teks seperti `Pemain belum lengkap`, `Tim belum disetujui`, atau `Menunggu tim tambahan` adalah **alasan/progres**, bukan pengganti label status. Pola wajib:

```text
Badge status: Menunggu Verifikasi
Informasi progres: Pemain belum lengkap
```

Dilarang menampilkan alasan sebagai badge status karena satu kode `pending` akan terlihat seperti beberapa status berbeda.

## Audit Konsistensi Badge — 27 Juli 2026

Temuan ini adalah gap nyata; bukan definisi baru:

1. `pending` tampil sebagai `Menunggu Verifikasi`, `Pendaftaran dalam proses`, `Dalam proses`, `Tim belum disetujui`, `Pemain belum lengkap`, dan `Menunggu tim tambahan` pada layar berbeda.
2. `verified` tampil sebagai `Terverifikasi`, `Pendaftaran selesai`, dan `Selesai` pada alur registrasi.
3. Warna team `verified` berbeda: Admin hijau, portal PD biru.
4. `StatusBadge.vue` memakai key mati `revision`, sedangkan DB memakai `revision_required`.
5. UI skor hanya menyediakan 5 dari 9 status match; `paused`, `postponed`, `walkover`, dan `cancelled` hilang.
6. Staff dan sebagian public menampilkan kode mentah status match.
7. Ranking hanya memakai `final`; match `verified` tidak dihitung.
8. `event_entries.verification_status` tidak punya CHECK dan default DB legacy adalah `verified`.
9. Filter mengenal `ongoing`/`completed`, tetapi controller belum menyediakan transition ke dua status itu.
10. `event_agendas.status` dan `published_at` dapat berbeda karena publish hanya mengubah timestamp.
11. `users.account_status` dan `committee_applications.status` memakai vocabulary sama tetapi berasal dari workflow berbeda untuk akun PD dan panitia.
12. `assignment_role` menduplikasi `users.role` dan dapat drift bila role user berubah.
13. `cancelled` entry/team tidak memiliki tone badge eksplisit di semua layar.
14. Official mendapat status default `pending`, tetapi workflow verifikasi dan badge official tidak ada.
15. Dashboard Admin menyebut role target yang belum tersedia seolah role aktif.

Detail kode aktual: `resources/js/lib/status.js`, `resources/js/Components/StatusBadge.vue`, `resources/js/Pages/Admin/Entries.vue`, `resources/js/Pages/Pd/EventEntries.vue`, `resources/js/Pages/Pd/Dashboard.vue`, `resources/js/Pages/Admin/Events.vue`, `resources/js/Pages/Admin/CommitteeApplications.vue`, `resources/js/Pages/Staff/Matches.vue`, `resources/js/Pages/AdminScores.vue`, dan `app/Support/Porpamnas/PublicDataService.php`.

## Gate Perubahan Status

Setiap penambahan/perubahan status wajib sekaligus memeriksa:

1. migration/default/CHECK constraint;
2. model dan semua transition controller/action;
3. `resources/js/lib/status.js` sebagai label tunggal;
4. badge/tone pada Admin, PD, Staff, dan Public;
5. filter, option form, agregasi dashboard, dan empty state;
6. audit log, authorization, serta test state transition;
7. ranking/publication bila status menentukan eligibility;
8. dokumen SoT menu pada `docs/SOT-REGISTRY.md`.

Perubahan belum selesai bila satu layar masih menampilkan kode mentah atau memakai label/tone berbeda untuk arti yang sama.
