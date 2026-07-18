<script setup>
import { ref, computed } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';

const props = defineProps({ pdams: { type: Array, default: () => [] } });

// ponytail: hardcoded demo. Replace with real tournaments/groups/matches data model.
const tournaments = [
  {
    code: 'mini-football', name: 'Mini Football', format: 'Grup → Knockout',
    groups: [
      { name: 'Grup A', standings: [
        { team: 'PT Manuntung Balikpapan', p: 3, w: 3, d: 0, l: 0, gf: 8, ga: 2, pts: 9 },
        { team: 'PDAM Surabaya', p: 3, w: 2, d: 0, l: 1, gf: 6, ga: 4, pts: 6 },
        { team: 'PDAM Makassar', p: 3, w: 1, d: 0, l: 2, gf: 4, ga: 5, pts: 3 },
        { team: 'PDAM Denpasar', p: 3, w: 0, d: 0, l: 3, gf: 1, ga: 8, pts: 0 },
      ]},
      { name: 'Grup B', standings: [
        { team: 'PTK Samarinda', p: 3, w: 2, d: 1, l: 0, gf: 7, ga: 3, pts: 7 },
        { team: 'PDAM Jakarta', p: 3, w: 2, d: 0, l: 1, gf: 5, ga: 3, pts: 6 },
        { team: 'PT Mahakam Kukar', p: 3, w: 1, d: 0, l: 2, gf: 3, ga: 5, pts: 3 },
        { team: 'PDAM Medan', p: 3, w: 0, d: 1, l: 2, gf: 2, ga: 6, pts: 1 },
      ]},
      { name: 'Grup C', standings: [
        { team: 'PT Taman Bontang', p: 3, w: 3, d: 0, l: 0, gf: 9, ga: 3, pts: 9 },
        { team: 'PDAM Bandung', p: 3, w: 1, d: 1, l: 1, gf: 4, ga: 4, pts: 4 },
        { team: 'PDAM Semarang', p: 3, w: 1, d: 0, l: 2, gf: 3, ga: 5, pts: 3 },
        { team: 'PDAM Palembang', p: 3, w: 0, d: 1, l: 2, gf: 2, ga: 6, pts: 1 },
      ]},
      { name: 'Grup D', standings: [
        { team: 'Batiwakkal Berau', p: 3, w: 2, d: 1, l: 0, gf: 6, ga: 2, pts: 7 },
        { team: 'PDAM Yogyakarta', p: 3, w: 2, d: 0, l: 1, gf: 5, ga: 3, pts: 6 },
        { team: 'PDAM Padang', p: 3, w: 1, d: 0, l: 2, gf: 4, ga: 5, pts: 3 },
        { team: 'PDAM Banjarmasin', p: 3, w: 0, d: 1, l: 2, gf: 1, ga: 6, pts: 1 },
      ]},
    ],
    knockout: [
      { name: 'Perempat Final', matches: [
        { id: 'M01', a: 'PT Balikpapan', sa: 3, b: 'PDAM Jakarta', sb: 1, status: 'Full time' },
        { id: 'M02', a: 'PTK Samarinda', sa: 2, b: 'PDAM Surabaya', sb: 3, status: 'Full time' },
        { id: 'M03', a: 'PT Bontang', sa: 1, b: 'PDAM Yogya', sb: 0, status: 'Full time' },
        { id: 'M04', a: 'Batiwakkal', sa: 2, b: 'PDAM Bandung', sb: 1, status: 'Full time' },
      ]},
      { name: 'Semi Final', matches: [
        { id: 'M05', a: 'PT Balikpapan', sa: null, b: 'PDAM Surabaya', sb: null, status: 'Jadwal' },
        { id: 'M06', a: 'PT Bontang', sa: null, b: 'Batiwakkal', sb: null, status: 'Jadwal' },
      ]},
      { name: 'Final', matches: [
        { id: 'M07', a: 'TBD', sa: null, b: 'TBD', sb: null, status: 'Jadwal' },
      ]},
    ],
  },
  {
    code: 'volleyball', name: 'Voli Putra', format: 'Grup → Knockout',
    groups: [
      { name: 'Grup A', standings: [
        { team: 'PDAM Surabaya', p: 2, w: 2, d: 0, l: 0, gf: 6, ga: 1, pts: 6 },
        { team: 'PT Taman Bontang', p: 2, w: 1, d: 0, l: 1, gf: 4, ga: 3, pts: 3 },
        { team: 'PDAM Bandung', p: 2, w: 0, d: 0, l: 2, gf: 1, ga: 6, pts: 0 },
      ]},
      { name: 'Grup B', standings: [
        { team: 'PT Mahakam Kukar', p: 2, w: 2, d: 0, l: 0, gf: 6, ga: 2, pts: 6 },
        { team: 'PDAM Jakarta', p: 2, w: 1, d: 0, l: 1, gf: 4, ga: 4, pts: 3 },
        { team: 'PDAM Makassar', p: 2, w: 0, d: 0, l: 2, gf: 2, ga: 6, pts: 0 },
      ]},
    ],
    knockout: [
      { name: 'Semi Final', matches: [
        { id: 'V01', a: 'PDAM Surabaya', sa: 3, b: 'PDAM Jakarta', sb: 1, status: 'Full time' },
        { id: 'V02', a: 'PT Mahakam', sa: 2, b: 'PT Taman Bontang', sb: 3, status: 'Full time' },
      ]},
      { name: 'Final', matches: [
        { id: 'V03', a: 'PDAM Surabaya', sa: null, b: 'PT Taman Bontang', sb: null, status: 'Jadwal' },
      ]},
    ],
  },
  {
    code: 'badminton', name: 'Bulu Tangkis', format: 'Single Elimination',
    groups: [],
    knockout: [
      { name: 'Perempat Final', matches: [
        { id: 'B01', a: 'Batiwakkal', sa: 2, b: 'PDAM Makassar', sb: 0, status: 'Full time' },
        { id: 'B02', a: 'PDAM Bandung', sa: 2, b: 'PDAM Semarang', sb: 1, status: 'Full time' },
        { id: 'B03', a: 'PDAM Surabaya', sa: 2, b: 'PT Bontang', sb: 0, status: 'Full time' },
        { id: 'B04', a: 'PDAM Jakarta', sa: 1, b: 'PT Balikpapan', sb: 2, status: 'Full time' },
      ]},
      { name: 'Semi Final', matches: [
        { id: 'B05', a: 'Batiwakkal', sa: null, b: 'PDAM Bandung', sb: null, status: 'Jadwal' },
        { id: 'B06', a: 'PDAM Surabaya', sa: null, b: 'PT Balikpapan', sb: null, status: 'Jadwal' },
      ]},
      { name: 'Final', matches: [
        { id: 'B07', a: 'TBD', sa: null, b: 'TBD', sb: null, status: 'Jadwal' },
      ]},
    ],
  },
  {
    code: 'chess', name: 'Catur', format: 'Swiss System',
    groups: [{ name: 'Klasemen Swiss (7 ronde)', standings: [
      { team: 'PDAM Jakarta', p: 7, w: 5, d: 2, l: 0, gf: 6, ga: 1, pts: 6.0 },
      { team: 'PDAM Semarang', p: 7, w: 5, d: 1, l: 1, gf: 5.5, ga: 1.5, pts: 5.5 },
      { team: 'PDAM Surabaya', p: 7, w: 4, d: 2, l: 1, gf: 5, ga: 2, pts: 5.0 },
      { team: 'PT Balikpapan', p: 7, w: 4, d: 1, l: 2, gf: 4.5, ga: 2.5, pts: 4.5 },
      { team: 'PDAM Makassar', p: 7, w: 3, d: 2, l: 2, gf: 4, ga: 3, pts: 4.0 },
    ]}],
    knockout: [],
  },
];

const active = ref(tournaments[0].code);
const search = ref('');
const current = computed(() => tournaments.find((t) => t.code === active.value));
const winnerOf = (m) => (m.sa == null || m.sb == null) ? null : (m.sa > m.sb ? 'a' : m.sb > m.sa ? 'b' : null);
const shortName = (name = '') => name
  .replace(/^(perumda|perumdam|perusahaan umum daerah|pdam|pt)\s+(air\s+minum\s+)?/i, '')
  .replace(/\b(kabupaten|kota)\b/gi, '')
  .replace(/\s+/g, ' ')
  .trim();
const participants = computed(() => (props.pdams.length ? props.pdams : [])
  .filter((p) => `${p.name} ${p.city || ''}`.toLowerCase().includes(search.value.toLowerCase()))
  .map((p) => ({ short: shortName(p.name), full: p.name })));
const generatedKnockout = computed(() => {
  if (props.pdams.length && !participants.value.length) return [];
  if (!participants.value.length) return current.value.knockout;
  const size = 2 ** Math.ceil(Math.log2(participants.value.length));
  let matchNo = 1;
  let count = size / 2;
  const rounds = [];
  while (count >= 1) {
    const name = count === 1 ? 'Final' : count === 2 ? 'Semi Final' : count === 4 ? 'Perempat Final' : `Round of ${count * 2}`;
    const firstRound = rounds.length === 0;
    rounds.push({ name, matches: Array.from({ length: count }, (_, i) => {
      const a = firstRound ? participants.value[i * 2] : null;
      const b = firstRound ? participants.value[i * 2 + 1] : null;
      return { id: `M${String(matchNo++).padStart(3, '0')}`, a: a?.short || (firstRound ? 'BYE' : 'TBD'), fa: a?.full || '', b: b?.short || (firstRound ? 'BYE' : 'TBD'), fb: b?.full || '', sa: null, sb: null, status: 'Jadwal' };
    }) });
    count /= 2;
  }
  return rounds;
});
const bracketRounds = computed(() => generatedKnockout.value);
const baseMatches = computed(() => bracketRounds.value[0]?.matches.length || 1);
const matchStyle = (stage, index) => {
  const span = baseMatches.value / stage.matches.length;
  return { gridRow: `${index * span * 2 + span} / span 2`, '--join': `calc(var(--slot-h) * ${span})` };
};
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Standings & Knockout" title="Bracket per Cabor" :meta="current?.format" />
    </div>

    <nav class="tabs">
      <button v-for="t in tournaments" :key="t.code" :class="{ active: active === t.code }" @click="active = t.code">{{ t.name }}</button>
      <input v-model="search" type="search" placeholder="Cari PDAM" aria-label="Cari PDAM" />
    </nav>

    <section v-if="current.groups.length" class="groups">
      <p class="table-note"><span>M</span> Main <span>W</span> Menang <span>D</span> Seri <span>L</span> Kalah <span>GF</span> Skor dibuat <span>GA</span> Skor kemasukan <span>Pts</span> Poin</p>
      <div v-for="g in current.groups" :key="g.name" class="group-card">
        <header><p>Group Stage</p><h3>{{ g.name }}</h3></header>
        <table>
          <thead><tr><th>#</th><th>Tim</th><th>M</th><th>W</th><th>D</th><th>L</th><th>GF</th><th>GA</th><th>Pts</th></tr></thead>
          <tbody>
            <tr v-for="(row, i) in g.standings" :key="row.team" :class="{ qualified: i < 2 }">
              <td><span class="pos">{{ i + 1 }}</span></td><td><strong>{{ row.team }}</strong></td>
              <td>{{ row.p }}</td><td>{{ row.w }}</td><td>{{ row.d }}</td><td>{{ row.l }}</td><td>{{ row.gf }}</td><td>{{ row.ga }}</td><td><b>{{ row.pts }}</b></td>
            </tr>
          </tbody>
        </table>
        <p class="legend"><span class="dot" />Lolos ke babak berikutnya</p>
      </div>
    </section>

    <section v-if="bracketRounds.length" class="knockout-wrap">
      <SectionTitle eyebrow="Knockout Bracket" title="Bracket PDAM" :meta="`${participants.length || baseMatches * 2} peserta · scroll horizontal + vertical`" />
      <div class="bracket-scroll">
        <div class="bracket" :style="{ '--rounds': bracketRounds.length, '--rows': baseMatches * 2 }">
          <div v-for="(stage, si) in bracketRounds" :key="stage.name" class="round">
            <p class="round-label">{{ stage.name }}</p>
            <div v-for="(m, mi) in stage.matches" :key="m.id" :style="matchStyle(stage, mi)" :class="['match-wrap', { 'has-next': si < bracketRounds.length - 1, top: mi % 2 === 0, bot: mi % 2 === 1 }]">
              <span class="match-tag">{{ m.status }}</span>
              <div class="card">
                <div :class="['row', { win: winnerOf(m) === 'a', lose: winnerOf(m) === 'b' }]">
                  <span class="team" :title="m.fa || m.a">{{ m.a }}</span><span class="score">{{ m.sa ?? '–' }}</span>
                </div>
                <div :class="['row', { win: winnerOf(m) === 'b', lose: winnerOf(m) === 'a' }]">
                  <span class="team" :title="m.fb || m.b">{{ m.b }}</span><span class="score">{{ m.sb ?? '–' }}</span>
                </div>
              </div>
              <span v-if="si > 0" class="connector in" />
              <span v-if="si < bracketRounds.length - 1" class="connector out" />
              <span class="match-id">{{ m.id }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 28px; padding: 16px; background: rgba(5,11,28,.56); border: 1px solid rgba(255,255,255,.12); }
.tabs button { padding: 10px 16px; border: 1px solid rgba(255,255,255,.16); background: #08142d; color: white; font-weight: 900; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; cursor: pointer; clip-path: polygon(9px 0,100% 0,calc(100% - 9px) 100%,0 100%); }
.tabs button.active { background: #F6C64A; color: #071126; border-color: #F6C64A; box-shadow: 6px 6px 0 rgba(240,90,40,.35); }
.tabs input { flex: 1 1 220px; min-width: 180px; padding: 10px 14px; border: 1px solid rgba(54,194,240,.3); background: #071126; color: #fff; font-weight: 800; outline: none; }
.groups { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 18px; margin-bottom: 40px; }
.group-card { position: relative; overflow: hidden; padding: 20px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 8px 8px 0 rgba(54,194,240,.13); clip-path: polygon(16px 0,100% 0,100% calc(100% - 16px),calc(100% - 16px) 100%,0 100%,0 16px); }
.group-card::before { content: ""; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(54,194,240,.11), transparent 46%); pointer-events: none; }
.group-card > * { position: relative; z-index: 1; }
.group-card header p { margin: 0; color: #36C2F0; font-size: 11px; font-weight: 950; letter-spacing: .16em; text-transform: uppercase; }
.group-card header h3 { margin: 4px 0 14px; font-size: 22px; letter-spacing: -.02em; }
.group-card table { width: 100%; border-collapse: collapse; font-size: 12px; }
.group-card th { text-align: left; padding: 8px 6px; color: rgba(255,255,255,.55); font-weight: 900; letter-spacing: .1em; text-transform: uppercase; border-bottom: 1px solid rgba(255,255,255,.12); }
.group-card td { padding: 10px 6px; border-bottom: 1px solid rgba(255,255,255,.06); }
.group-card td:nth-child(n+3) { text-align: center; color: rgba(255,255,255,.72); }
.group-card tr.qualified { background: linear-gradient(90deg, rgba(32,198,183,.14), transparent 60%); }
.pos { display: inline-grid; place-items: center; width: 24px; height: 24px; border-radius: 50%; background: rgba(255,255,255,.1); font-weight: 950; font-size: 11px; }
.qualified .pos { background: linear-gradient(135deg, #20C6B7, #36C2F0); color: #0A1F2B; }
.group-card b { color: #F6C64A; font-size: 14px; }
.legend { display: flex; align-items: center; gap: 8px; margin: 12px 0 0; color: rgba(255,255,255,.55); font-size: 11px; letter-spacing: .1em; text-transform: uppercase; }
.legend .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: linear-gradient(135deg, #20C6B7, #36C2F0); }
.table-note { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 8px 12px; margin: 0; padding: 12px 14px; color: rgba(255,255,255,.64); background: rgba(5,11,28,.56); border: 1px solid rgba(255,255,255,.12); font-size: 11px; line-height: 1.5; }
.table-note span { color: #36C2F0; font-weight: 1000; letter-spacing: .08em; }
.knockout-wrap { position: relative; overflow: hidden; margin-top: 8px; padding: 22px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 10px 10px 0 rgba(54,194,240,.13); }
.knockout-wrap::before { content: "KNOCKOUT"; position: absolute; right: 20px; top: -14px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.055); font-size: clamp(72px, 12vw, 160px); font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
.bracket-scroll { position: relative; z-index: 1; max-height: 78vh; overflow: auto; padding: 20px 4px 24px; overscroll-behavior: contain; }
.bracket { --line: rgba(150,165,190,.72); --gap-x: 72px; --slot-h: 66px; display: grid; grid-template-columns: repeat(var(--rounds), 320px); grid-template-rows: repeat(var(--rows), var(--slot-h)); column-gap: var(--gap-x); row-gap: 0; min-width: calc(var(--rounds) * 320px + var(--rounds) * var(--gap-x)); padding: 42px 12px 8px; }
.round { position: relative; display: grid; grid-template-rows: subgrid; grid-row: 1 / -1; }
.round-label { position: sticky; z-index: 3; top: 0; left: 0; margin: 0; padding: 8px 10px; color: #F6C64A; font-size: 11px; font-weight: 1000; letter-spacing: .18em; text-transform: uppercase; background: rgba(7,17,38,.94); border: 1px solid rgba(246,198,74,.2); }
.match-wrap { position: relative; display: grid; align-content: center; }
.match-tag { position: absolute; top: 3px; left: 0; color: rgba(255,255,255,.5); font-size: 10px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
.match-id { position: absolute; bottom: 3px; left: 0; color: rgba(255,255,255,.35); font-size: 10px; font-weight: 900; letter-spacing: .1em; }
.card { display: grid; gap: 1px; overflow: hidden; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); box-shadow: 6px 6px 0 rgba(0,0,0,.22); clip-path: polygon(10px 0,100% 0,100% calc(100% - 10px),calc(100% - 10px) 100%,0 100%,0 10px); }
.row { display: grid; grid-template-columns: minmax(0, 1fr) 34px; align-items: center; min-height: 43px; padding: 10px 12px 10px 14px; background: #0B1B3F; border-left: 3px solid transparent; }
.row + .row { border-top: 1px solid rgba(255,255,255,.08); }
.team { font-size: 14px; font-weight: 750; color: rgba(255,255,255,.75); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.score { text-align: right; font-size: 16px; font-weight: 950; color: rgba(255,255,255,.55); font-variant-numeric: tabular-nums; }
.row.win { border-left-color: #20C6B7; background: linear-gradient(90deg, rgba(32,198,183,.34), rgba(54,194,240,.2) 55%, #0B1B3F); box-shadow: inset 0 0 0 1px rgba(54,194,240,.34); }
.row.win .team, .row.win .score { color: #fff; font-weight: 1000; }
.row.win .score { color: #36C2F0; }
.row.lose .team, .row.lose .score { color: rgba(255,255,255,.45); }
.connector { position: absolute; z-index: 0; pointer-events: none; }
.connector.out { top: 50%; right: calc(var(--gap-x) / -2); width: calc(var(--gap-x) / 2); height: 2px; background: var(--line); }
.connector.in { top: 50%; left: calc(var(--gap-x) / -2); width: calc(var(--gap-x) / 2); height: 2px; background: var(--line); }
.match-wrap.has-next.top::before { content: ''; position: absolute; right: calc(var(--gap-x) / -2); top: 50%; width: 2px; height: var(--join); background: var(--line); }
.match-wrap.has-next.bot::before { content: ''; position: absolute; right: calc(var(--gap-x) / -2); bottom: 50%; width: 2px; height: var(--join); background: var(--line); }
@media (max-width: 720px) { .bracket { --gap-x: 36px; } .team { font-size: 12px; } }
</style>
