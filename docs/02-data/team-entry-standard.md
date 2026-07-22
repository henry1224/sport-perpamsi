# Standar Entry dan Multi-Team

> Status: **Target Phase 4B**. Kode saat ini masih memakai `EventEntry` sebagai registrasi, roster, dan peserta pertandingan. Implementasi schema, API, UI, dan test belum tersedia.

Dokumen ini menjadi sumber kebenaran untuk satu PD PERPAMSI yang mengirim satu atau lebih unit peserta pada kompetisi yang sama.

## Model Kanonik

```text
TournamentEvent
└── EventEntry                 satu amplop registrasi PD per kompetisi
    └── EntryTeam 1..N         unit peserta kompetisi
        └── EntryMember 1..N   pemain milik tepat satu team
```

- `EventEntry` menyimpan pengajuan, submission, dan status verifikasi default.
- `EntryTeam` menjadi unit seed, grup, bracket, match, hasil, standing, dan medali.
- `EntryMember` selalu dimiliki satu `EntryTeam`.
- Satu `EventEntry` aktif wajib unik pada `(tournament_event_id, regional_committee_id)`.
- `EntryTeam` wajib unik pada `(event_entry_id, team_no)`.
- Official, pelatih, manajer, dan pendamping tidak dihitung sebagai `EntryMember`.

## Unit Peserta

| Contoh kategori | `participant_unit` | Anggota per team | Contoh banyak team |
|---|---|---:|---|
| Catur perorangan | `individual` | 1 | lima pecatur = lima team |
| Catur beregu | `team` | sesuai regulasi | satu regu = satu team |
| Bulu tangkis ganda | `pair` | 2 | dua pasangan = dua team |
| Golf individual | `individual` | 1 | lima pegolf = lima team |
| Padel | `pair` | 2 | dua pasangan = dua team |
| Voli/Mini Football | `team` | sesuai regulasi | satu roster = satu team |

Tidak ada angka kuota global per cabor. Technical meeting menetapkan kuota setiap kompetisi sebelum publikasi.

## Nomor dan Nama Team

- `team_no` adalah integer positif yang dialokasikan server secara transaksional.
- Client tidak boleh mengirim `team_no`, `display_name`, atau nama team bebas.
- Nomor tidak dipadatkan, tidak diubah, dan tidak dipakai ulang setelah team pernah disubmit.
- Team yang dibatalkan tetap mempertahankan nomor untuk histori dan audit.
- Nama publik wajib dibentuk server:

```text
PD PERPAMSI {province.name} #{team_no}
```

Contoh: `PD PERPAMSI Kalimantan Timur #2`.

## Snapshot Registrasi

`tournament_events.registration_rules` minimum menyimpan:

```json
{
  "snapshot_version": 2,
  "participant_unit": "individual|pair|team",
  "min_teams_per_pd": 1,
  "max_teams_per_pd": 1,
  "min_members_per_team": 1,
  "max_members_per_team": 1,
  "member_gender_rule": null,
  "avoid_same_pd_in_round": true,
  "format": "...",
  "score_template": "...",
  "regulation_id": 1,
  "regulation_version": "v1"
}
```

Aturan:

- `max_teams_per_pd` wajib integer `>= 1`; `null` tidak diizinkan.
- `min_teams_per_pd` default `1` dan tidak boleh melebihi maksimum.
- Batas anggota per team wajib sesuai unit: individual `1/1`, pasangan `2/2`, beregu mengikuti regulasi.
- Publish ditolak bila kuota team atau anggota belum lengkap/tidak konsisten.
- Validasi registrasi selalu membaca snapshot, bukan master yang dapat berubah.
- Team aktif dihitung terhadap `max_teams_per_pd`; team cancelled tidak memakai kuota, tetapi nomornya tidak digunakan ulang.
- Perubahan sebelum ada entry memakai republish dan audit.
- Perubahan setelah entry masuk wajib workflow regulasi berversi dan tidak boleh diam-diam membatalkan team existing.

## Verifikasi Hybrid

Status parent dan override memakai kode:

- `draft`
- `pending`
- `revision_required`
- `verified`
- `rejected`
- `cancelled`

Status efektif team dihitung server:

```text
effective_team_status =
    entry_team.verification_status_override
    ?? event_entry.verification_status
```

- Submit parent mengubah status default menjadi `pending`.
- Verifikasi parent menjadi keputusan default seluruh team tanpa override.
- Admin dapat memberi override `revision_required`, `verified`, `rejected`, atau `cancelled` pada team tertentu.
- Override tidak mengubah team lain dan tidak terhapus ketika status parent berubah.
- Reset override adalah action eksplisit, berizin, dan diaudit.
- API/UI selalu menampilkan parent status, nullable override, dan effective status.
- Seed/bracket hanya menerima team dengan effective status `verified`.
- Bracket lock ditolak bila team aktif masih efektif `pending` atau `revision_required`.
- Audit mencatat before/after, aktor, alasan, waktu, dan sumber keputusan (`parent_default` atau `team_override`).

## Penguncian Roster

- Team efektif verified mengunci seluruh roster.
- Perpindahan atau substitusi pemain antar-team setelah verified dilarang total.
- Larangan mencakup update FK, swap, delete lalu create, dan perubahan identitas untuk menyamarkan pemain yang sama.
- Koreksi typo yang tidak mengganti orang wajib action koreksi berizin, alasan, bukti, dan audit; kepemilikan team tidak berubah.
- Pembukaan revisi satu team tidak membuka team saudara.
- Team yang sudah direferensikan seed, match, result, standing, atau medal tidak boleh dihapus; gunakan status dan audit.
- Roster snapshot saat bracket lock/match menjaga histori walau data administratif kemudian dikoreksi.

## Seeding Sesama PD

- `avoid_same_pd_in_round` wajib ada pada snapshot registrasi dan default `true`.
- Aturan berarti best-effort menghindari team dari PD sama pada penempatan ronde awal bila alternatif valid tersedia.
- Algoritme harus deterministik untuk input dan seed yang sama.
- Bila mustahil, sistem meminimalkan pertemuan satu PD pada ronde paling awal lalu melakukan relaksasi deterministik.
- Seeding snapshot menyimpan versi algoritme, entrant `entry_team_id`, seed awal, label, konflik yang tidak terhindarkan, urutan relaksasi, aktor, waktu, dan alasan lock/reseed.
- Relaksasi tidak mengubah skor atau tie-break pertandingan yang sudah dimainkan.

## Match, Hasil, dan Medali

- Participant pada seed, grup, bracket, match, result, dan standing adalah `EntryTeam`.
- Kategori individual tetap direpresentasikan sebagai team beranggota satu.
- Medal result menyimpan `entry_team_id`.
- Setiap team dapat memenangkan medali secara independen.
- Satu PD boleh memperoleh beberapa medali pada kategori sama.
- Klasemen PD mengagregasi melalui `EntryTeam → EventEntry → RegionalCommittee` tanpa deduplikasi per kategori.
- Hasil kategori menampilkan label team bernomor; klasemen agregat menampilkan nama PD PERPAMSI.

## Lifecycle

```text
Admin tetapkan kuota hasil technical meeting
→ publish snapshot
→ PD membuat satu EventEntry
→ PD membuat EntryTeam sampai batas snapshot
→ PD mengisi EntryMember per team
→ submit parent
→ Admin verifikasi parent atau override team
→ hanya team efektif verified masuk seed
→ bracket lock menyimpan participant/roster snapshot
→ match, hasil, standing, dan medali menunjuk EntryTeam
```

## Constraint Wajib

- Unique parent `(tournament_event_id, regional_committee_id)`.
- Unique team `(event_entry_id, team_no)`.
- `team_no > 0` dan immutable setelah submit.
- Jumlah team aktif tidak melebihi snapshot.
- Jumlah/komposisi anggota setiap team memenuhi snapshot.
- Pemain tidak terdaftar pada dua team dalam kompetisi sama sesuai identitas kanonik.
- Label, PD, status, aktor, dan nomor team tidak dipercaya dari client.
- Team efektif non-verified tidak masuk operasi turnamen.
- Match participant harus berasal dari kompetisi yang sama.
- Perubahan parent, team, roster, override, seeding, pembatalan, dan koreksi tercatat append-only.

## Gate Implementasi Phase 4B

Phase 6 tidak boleh dimulai sampai:

1. migration dan backfill parent/team lulus;
2. entry lama dipetakan ke team `#1` tanpa rebuild match lama;
3. snapshot kuota dan seeding tersedia;
4. effective status diuji;
5. larangan transfer roster diuji;
6. seed/match participant memakai `EntryTeam`;
7. risk register tidak memiliki risiko kritis terbuka.
