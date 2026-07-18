<script setup>
import { ref, computed, nextTick, onMounted } from 'vue';
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
const hoveredCode = ref(null);
const bracketMode = ref('main');
const mainLimit = ref(32);
const bracketScroll = ref(null);
const current = computed(() => tournaments.find((t) => t.code === active.value));
const winnerOf = (m) => (m.sa == null || m.sb == null) ? null : (m.sa > m.sb ? 'a' : m.sb > m.sa ? 'b' : null);
const shortName = (name = '') => name
  .replace(/^(perumda|perumdam|perusahaan umum daerah|pdam|pt)\s+(air\s+minum\s+)?/i, '')
  .replace(/\b(kabupaten|kota)\b/gi, '')
  .replace(/\s+/g, ' ')
  .trim();
const participants = computed(() => (props.pdams.length ? props.pdams : [])
  .filter((p) => `${p.name} ${p.city || ''}`.toLowerCase().includes(search.value.toLowerCase()))
  .map((p) => ({ code: p.code, short: shortName(p.name), full: p.name })));
const generatedKnockout = computed(() => {
  if (props.pdams.length && !participants.value.length) return [];
  if (!participants.value.length) return current.value.knockout;
  const size = 2 ** Math.ceil(Math.log2(participants.value.length));
  let matchNo = 1;
  let entrants = participants.value.slice();
  while (entrants.length < size) entrants.push(null);
  const rounds = [];
  while (entrants.length > 1) {
    const count = entrants.length / 2;
    const name = count === 1 ? 'Final' : count === 2 ? 'Semi Final' : count === 4 ? 'Perempat Final' : `Round of ${count * 2}`;
    const winners = [];
    rounds.push({ name, matches: Array.from({ length: count }, (_, i) => {
      const id = `M${String(matchNo++).padStart(3, '0')}`;
      const a = entrants[i * 2];
      const b = entrants[i * 2 + 1];
      const sa = a && b ? ((i + rounds.length) % 4) + 1 : (a ? 1 : 0);
      const sb = a && b ? ((i * 2 + rounds.length) % 3) : (b ? 1 : 0);
      const winner = !b || (a && sa >= sb) ? a : b;
      winners.push(winner);
      return { id, a: a?.short || 'BYE', fa: a?.full || '', ca: a?.code || '', b: b?.short || 'BYE', fb: b?.full || '', cb: b?.code || '', sa, sb, winner: winner?.code || '', pathCodes: [a?.code, b?.code].filter(Boolean), status: 'Final' };
    }) });
    entrants = winners;
  }
  return rounds;
});
const bracketRounds = computed(() => generatedKnockout.value);
const mainRounds = computed(() => bracketRounds.value.filter((round) => round.matches.length <= mainLimit.value));
const earlyRounds = computed(() => bracketRounds.value.filter((round) => round.matches.length > 32));
const splitRounds = computed(() => {
  if (!mainRounds.value.length) return { left: [], right: [], final: null };
  const rounds = mainRounds.value.map((round, roundIndex) => {
    const sideCount = Math.ceil(round.matches.length / 2);
    return {
      ...round,
      left: round.matches.slice(0, sideCount),
      right: round.matches.slice(sideCount),
      roundIndex,
    };
  });
  const final = rounds.at(-1);
  return {
    left: rounds.slice(0, -1).map((round) => ({ ...round, matches: round.left })),
    right: rounds.slice(0, -1).map((round) => ({ ...round, matches: round.right })).reverse(),
    final: final?.matches[0] || null,
  };
});
const baseMatches = computed(() => bracketRounds.value[0]?.matches.length || 1);
const visibleBaseMatches = computed(() => mainRounds.value[0]?.matches.length || baseMatches.value);
const matchStyle = (stage, index) => {
  const span = Math.max(1, visibleBaseMatches.value / 2 / Math.max(1, stage.matches.length));
  return { gridRow: `${index * span * 2 + span} / span 2`, '--join': `calc(var(--slot-h) * ${span})` };
};
const isActive = (m) => hoveredCode.value && m.pathCodes?.includes(hoveredCode.value);

onMounted(() => nextTick(() => {
  const el = bracketScroll.value;
  if (el) el.scrollLeft = (el.scrollWidth - el.clientWidth) / 2;
}));
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
      <SectionTitle eyebrow="Knockout Bracket" title="Bracket PDAM" :meta="`${participants.length || baseMatches * 2} peserta · main view mulai Round of ${mainLimit * 2}`" />
      <div class="bracket-tools">
        <button :class="{ active: bracketMode === 'main' }" @click="bracketMode = 'main'">Main Bracket</button>
        <button :class="{ active: bracketMode === 'main' && mainLimit === 8 }" @click="bracketMode = 'main'; mainLimit = 8">Round 16</button>
        <button :class="{ active: bracketMode === 'main' && mainLimit === 16 }" @click="bracketMode = 'main'; mainLimit = 16">Round 32</button>
        <button :class="{ active: bracketMode === 'main' && mainLimit === 32 }" @click="bracketMode = 'main'; mainLimit = 32">Round 64</button>
        <button :class="{ active: bracketMode === 'main' && mainLimit === 64 }" @click="bracketMode = 'main'; mainLimit = 64">Round 128</button>
        <button :class="{ active: bracketMode === 'early' }" @click="bracketMode = 'early'">Round Awal</button>
      </div>
      <div v-if="bracketMode === 'early'" class="early-rounds">
        <div v-for="round in earlyRounds" :key="round.name" class="early-card">
          <h3>{{ round.name }}</h3>
          <div class="early-list">
            <div v-for="m in round.matches.slice(0, 24)" :key="m.id" class="early-match">
              <b>{{ m.id }}</b>
              <span>{{ m.a }}</span><em>{{ m.sa }} - {{ m.sb }}</em><span>{{ m.b }}</span>
            </div>
          </div>
          <p v-if="round.matches.length > 24">+{{ round.matches.length - 24 }} match lain. Gunakan search PDAM untuk mempersempit.</p>
        </div>
      </div>
      <div v-else ref="bracketScroll" class="bracket-scroll">
        <div class="mirror-bracket" :style="{ '--left-rounds': splitRounds.left.length, '--right-rounds': splitRounds.right.length, '--rows': visibleBaseMatches }">
          <div class="bracket-side left-side">
            <div v-for="(stage, si) in splitRounds.left" :key="stage.name" class="round">
              <p class="round-label">{{ stage.name }}</p>
              <div v-for="(m, mi) in stage.matches" :key="m.id" :style="matchStyle(stage, mi)" :class="['match-wrap', { active: isActive(m), 'has-next': si < splitRounds.left.length - 1, top: mi % 2 === 0, bot: mi % 2 === 1 }]">
                <span class="match-tag">{{ m.status }}</span>
                <div class="card">
                  <div :class="['row', { win: winnerOf(m) === 'a', lose: winnerOf(m) === 'b' }]" @mouseenter="hoveredCode = m.ca" @mouseleave="hoveredCode = null">
                    <span class="team" :title="m.fa || m.a">{{ m.a }}</span><span class="score">{{ m.sa ?? '–' }}</span>
                  </div>
                  <div :class="['row', { win: winnerOf(m) === 'b', lose: winnerOf(m) === 'a' }]" @mouseenter="hoveredCode = m.cb" @mouseleave="hoveredCode = null">
                    <span class="team" :title="m.fb || m.b">{{ m.b }}</span><span class="score">{{ m.sb ?? '–' }}</span>
                  </div>
                </div>
                <span v-if="si > 0" class="connector in" />
                <span v-if="si < splitRounds.left.length - 1" class="connector out" />
                <span class="match-id">{{ m.id }}</span>
              </div>
            </div>
          </div>

          <div v-if="splitRounds.final" class="final-lane">
            <p class="round-label">Final</p>
            <div :class="['match-wrap final-match', { active: isActive(splitRounds.final) }]">
              <span class="match-tag">{{ splitRounds.final.status }}</span>
              <div class="card">
                <div :class="['row', { win: winnerOf(splitRounds.final) === 'a', lose: winnerOf(splitRounds.final) === 'b' }]" @mouseenter="hoveredCode = splitRounds.final.ca" @mouseleave="hoveredCode = null">
                  <span class="team" :title="splitRounds.final.fa || splitRounds.final.a">{{ splitRounds.final.a }}</span><span class="score">{{ splitRounds.final.sa ?? '–' }}</span>
                </div>
                <div :class="['row', { win: winnerOf(splitRounds.final) === 'b', lose: winnerOf(splitRounds.final) === 'a' }]" @mouseenter="hoveredCode = splitRounds.final.cb" @mouseleave="hoveredCode = null">
                  <span class="team" :title="splitRounds.final.fb || splitRounds.final.b">{{ splitRounds.final.b }}</span><span class="score">{{ splitRounds.final.sb ?? '–' }}</span>
                </div>
              </div>
              <span class="match-id">{{ splitRounds.final.id }}</span>
            </div>
          </div>

          <div class="bracket-side right-side">
            <div v-for="(stage, si) in splitRounds.right" :key="stage.name" class="round">
              <p class="round-label">{{ stage.name }}</p>
              <div v-for="(m, mi) in stage.matches" :key="m.id" :style="matchStyle(stage, mi)" :class="['match-wrap', { active: isActive(m), 'has-next': si > 0, top: mi % 2 === 0, bot: mi % 2 === 1 }]">
                <span class="match-tag">{{ m.status }}</span>
                <div class="card">
                  <div :class="['row', { win: winnerOf(m) === 'a', lose: winnerOf(m) === 'b' }]" @mouseenter="hoveredCode = m.ca" @mouseleave="hoveredCode = null">
                    <span class="team" :title="m.fa || m.a">{{ m.a }}</span><span class="score">{{ m.sa ?? '–' }}</span>
                  </div>
                  <div :class="['row', { win: winnerOf(m) === 'b', lose: winnerOf(m) === 'a' }]" @mouseenter="hoveredCode = m.cb" @mouseleave="hoveredCode = null">
                    <span class="team" :title="m.fb || m.b">{{ m.b }}</span><span class="score">{{ m.sb ?? '–' }}</span>
                  </div>
                </div>
                <span v-if="si < splitRounds.right.length - 1" class="connector in" />
                <span v-if="si > 0" class="connector out" />
                <span class="match-id">{{ m.id }}</span>
              </div>
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
.bracket-tools { position: relative; z-index: 2; display: flex; gap: 8px; margin: 18px 0 8px; }
.bracket-tools button { padding: 10px 16px; border: 1px solid rgba(255,255,255,.16); background: #08142d; color: #fff; font-size: 11px; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; cursor: pointer; }
.bracket-tools button.active { border-color: #F6C64A; background: #F6C64A; color: #071126; }
.early-rounds { position: relative; z-index: 2; display: grid; gap: 18px; margin-top: 18px; }
.early-card { padding: 18px; background: #08142d; border: 1px solid rgba(255,255,255,.12); }
.early-card h3 { margin: 0 0 14px; color: #F6C64A; font-size: 13px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
.early-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 8px; }
.early-match { display: grid; grid-template-columns: 56px minmax(0,1fr) 56px minmax(0,1fr); align-items: center; gap: 10px; padding: 10px 12px; background: #0B1B3F; border: 1px solid rgba(255,255,255,.08); }
.early-match b { color: rgba(255,255,255,.45); font-size: 10px; letter-spacing: .1em; }
.early-match span { overflow: hidden; color: rgba(255,255,255,.78); font-size: 13px; font-weight: 800; text-overflow: ellipsis; white-space: nowrap; }
.early-match em { color: #36C2F0; font-style: normal; font-weight: 1000; text-align: center; }
.early-card p { margin: 12px 0 0; color: rgba(255,255,255,.55); font-size: 12px; }
.bracket-scroll { position: relative; z-index: 1; max-height: 78vh; overflow: auto; padding: 20px 4px 24px; overscroll-behavior: contain; }
.mirror-bracket { --line: rgba(150,165,190,.72); --gap-x: 72px; --slot-h: 66px; display: grid; grid-template-columns: max-content 340px max-content; column-gap: 84px; min-width: calc((var(--left-rounds) + var(--right-rounds)) * 320px + (var(--left-rounds) + var(--right-rounds)) * var(--gap-x) + 520px); padding: 42px 12px 8px; }
.bracket-side { display: grid; grid-template-columns: repeat(var(--left-rounds), 320px); grid-template-rows: repeat(var(--rows), var(--slot-h)); column-gap: var(--gap-x); }
.right-side { grid-template-columns: repeat(var(--right-rounds), 320px); }
.final-lane { position: relative; z-index: 4; align-self: center; display: grid; align-content: center; min-height: calc(var(--rows) * var(--slot-h)); }
.final-match::before, .final-match::after { content: ''; position: absolute; top: 50%; width: 42px; height: 2px; background: var(--line); }
.final-match::before { left: -42px; }
.final-match::after { right: -42px; }
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
.match-wrap.active { --line: #36C2F0; z-index: 2; }
.match-wrap.active .card { border-color: rgba(54,194,240,.72); box-shadow: 0 0 0 1px rgba(54,194,240,.45), 0 0 24px rgba(54,194,240,.22); }
.match-wrap.active .row.win { background: linear-gradient(90deg, rgba(32,198,183,.5), rgba(54,194,240,.32) 62%, #0B1B3F); }
.match-wrap.active .connector, .match-wrap.active::before, .final-match.active::before, .final-match.active::after { background-color: #36C2F0; background-image: linear-gradient(90deg, transparent, rgba(246,198,74,.95), transparent); background-size: 220% 100%; box-shadow: 0 0 10px rgba(54,194,240,.46); animation: flow-line 4.4s ease-in-out infinite; }
.connector { position: absolute; z-index: 0; pointer-events: none; }
.connector.out { top: 50%; right: calc(var(--gap-x) / -2); width: calc(var(--gap-x) / 2); height: 2px; background: var(--line); }
.connector.in { top: 50%; left: calc(var(--gap-x) / -2); width: calc(var(--gap-x) / 2); height: 2px; background: var(--line); }
.right-side .connector.out { right: auto; left: calc(var(--gap-x) / -2); }
.right-side .connector.in { left: auto; right: calc(var(--gap-x) / -2); }
.match-wrap.has-next.top::before { content: ''; position: absolute; right: calc(var(--gap-x) / -2); top: 50%; width: 2px; height: var(--join); background: var(--line); }
.match-wrap.has-next.bot::before { content: ''; position: absolute; right: calc(var(--gap-x) / -2); bottom: 50%; width: 2px; height: var(--join); background: var(--line); }
.right-side .match-wrap.has-next.top::before, .right-side .match-wrap.has-next.bot::before { right: auto; left: calc(var(--gap-x) / -2); }
@keyframes flow-line { from { background-position: 220% 0; } to { background-position: -220% 0; } }
@media (max-width: 720px) { .mirror-bracket { --gap-x: 36px; column-gap: 48px; } .team { font-size: 12px; } }
</style>
