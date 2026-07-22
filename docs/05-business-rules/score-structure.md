# Score Structure v1

> Tabel `match_score_segments` dan `match_results` belum ada; parser skor hanya menerima `\d+-\d+`. Drift dan aksi lihat `docs/00-project/audit-2026-07-22.md` (D15).

## Prinsip

- Struktur skor harus fleksibel, tapi tidak menjadi rule engine semua cabor.
- Semua match punya skor utama untuk public card.
- Detail skor disimpan per segment: set, game, babak, quarter, half, ronde, atau heat.
- Pemenang tetap ditentukan oleh panitia/aturan cabor, bukan disimpulkan paksa dari satu field skor.
- Participant selalu `EntryTeam`; kategori individual memakai team beranggota satu.

## Model Konseptual

### matches

- id.
- event_id.
- sport_id.
- category_id.
- venue_id.
- entry_team_a_id.
- entry_team_b_id.
- scheduled_at.
- status.
- winner_entry_team_id.
- finalized_at.

### match_scores

- id.
- match_id.
- entry_team_a_score.
- entry_team_b_score.
- score_label: total, current, aggregate.
- updated_by.
- updated_at.

### match_score_segments

- id.
- match_id.
- segment_no.
- segment_type: set, game, quarter, half, round, heat.
- entry_team_a_score.
- entry_team_b_score.
- winner_entry_team_id.
- status.

### match_results

- id.
- match_id.
- result_type: win_loss, draw, walkover, disqualified, no_contest.
- winner_entry_team_id.
- note.
- finalized_by.
- finalized_at.

## Template Skor per Jenis Cabor

| Jenis Cabor | Skor Utama | Segment |
|---|---|---|
| Mini Football | gol A-B | babak 1, babak 2, penalti opsional |
| Basket | poin A-B | quarter 1-4, overtime opsional |
| Voli | set menang A-B | set 1-5 berisi poin |
| Bulutangkis/tenis meja | game/set menang A-B | game/set 1-3 atau 1-5 berisi poin |
| Catur | poin match/game | board/round bila beregu |
| Lari/renang | waktu | heat/final, lane opsional |
| Panahan/menembak | total poin | ronde/seri |
| Golf | total pukulan/nilai | ronde |
| Padel | set menang A-B | set berisi game/poin |
| Vokal | nilai juri | kategori/ronde penilaian |

## Participant Ranking Non-Head-to-Head

Untuk golf, lari, renang, panahan, vokal, dan format time/distance/score ranking, hasil disimpan per `EntryTeam` dan tidak dipaksa menjadi match dua sisi. Setiap hasil menyimpan nilai, unit, rank, status final, aktor, waktu, dan audit. Kategori individual tetap memakai team beranggota satu.

## Keputusan v1

- Public card menampilkan skor utama dan status.
- Match detail menampilkan segment bila tersedia.
- Admin dapat input segment sesuai template cabor.
- Jika cabor belum punya template, gunakan skor utama manual dan catatan hasil.
- Informasi teknis publik menjelaskan sistem pertandingan, tetapi struktur skor transaksi tetap mengikuti `score_template` cabor dan snapshot kompetisi.
