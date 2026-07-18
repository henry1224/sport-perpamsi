<script setup>
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';
import Panel from '../Components/Panel.vue';
import MatchCard from '../Components/MatchCard.vue';
import StatusBadge from '../Components/StatusBadge.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  agenda: Array, sports: Array, results: Array, provinceRankings: Array, assets: Object,
});

const dateLabels = [...new Set(props.agenda.map((i) => i.date))].map((date) => {
  const item = props.agenda.find((a) => a.date === date);
  return { date, day: item.day, count: props.agenda.filter((a) => a.date === date).length };
});
const featuredAgenda = props.agenda.slice(0, 7);
const sportCards = props.sports.filter((s) => s.type !== 'official');
const venueCount = new Set(props.agenda.map((i) => i.venue)).size;
const activityCount = props.agenda.length;
const sportCount = props.sports.filter((s) => s.type === 'sport').length;
const exhibitionCount = props.sports.filter((s) => s.type === 'exhibition').length;
const topResults = props.results.slice(0, 3);
</script>

<template>
  <PublicLayout>
    <section class="hero-arena">
      <div class="hero-copy">
        <p class="eyebrow">PORPAMNAS IX KALTIM · BALIKPAPAN</p>
        <h1><span>Pekan Olahraga Antar</span><span>Perusahaan Air Minum</span><span>Nasional.</span></h1>
        <p>Public board untuk agenda, hasil, cabor, venue, bracket, dan ranking wilayah. Padat, cepat dibaca, tetap resmi.</p>
        <div class="hero-actions">
          <Link class="primary-button" href="/hasil">Hasil Terbaru</Link>
          <Link class="secondary-button" href="/agenda">Agenda Kegiatan</Link>
        </div>
      </div>
      <div class="hero-visual" aria-hidden="true">
        <div class="power-ring" />
        <img class="mascot beru" :src="assets.beru" alt="" />
        <img class="mascot ganga" :src="assets.ganga" alt="" />
      </div>
      <div class="hero-stats">
        <div><strong>{{ activityCount }}</strong><span>Agenda</span></div>
        <div><strong>{{ sportCount }}</strong><span>Cabor</span></div>
        <div><strong>{{ exhibitionCount }}</strong><span>Eksibisi</span></div>
        <div><strong>{{ venueCount }}</strong><span>Venue</span></div>
      </div>
    </section>

    <section class="match-strip">
      <MatchCard v-for="r in topResults" :key="`${r.sport}-${r.team_a}`"
        :sport="r.sport" :team-a="r.team_a" :team-b="r.team_b"
        :score="r.score" :status="r.status" :venue="r.venue" :time="r.time" />
    </section>

    <section class="content-grid">
      <Panel>
        <SectionTitle eyebrow="Tournament Schedule" title="Agenda Kegiatan" meta="06–10 Okt" />
        <div class="date-tabs">
          <button v-for="d in dateLabels" :key="d.date" type="button">
            <small>{{ d.day }}</small>
            <strong>{{ d.date.slice(8, 10) }}</strong>
            <span>{{ d.count }} acara</span>
          </button>
        </div>
        <div class="agenda-list">
          <div v-for="item in featuredAgenda" :key="`${item.date}-${item.title}-${item.start_time}`" class="agenda-row">
            <span :class="['type-pill', item.type]">{{ item.type }}</span>
            <div>
              <strong>{{ item.title }}</strong>
              <small>{{ item.venue }}</small>
            </div>
            <time>{{ item.start_time }}<template v-if="item.end_time">–{{ item.end_time }}</template></time>
          </div>
        </div>
      </Panel>

      <aside class="side-stack">
        <Panel>
          <SectionTitle eyebrow="Medal Table" title="Provinsi" />
          <div v-for="(rank, i) in provinceRankings.slice(0,4)" :key="rank.name" class="rank-row">
            <span>{{ i + 1 }}</span>
            <div><strong>{{ rank.name }}</strong><small>{{ rank.gold }}G · {{ rank.silver }}S · {{ rank.bronze }}B</small></div>
            <b>{{ rank.gold }}</b>
          </div>
          <Link class="panel-more" href="/ranking">Lihat semua →</Link>
        </Panel>
        <Panel class="featured-panel">
          <p>Featured Event</p>
          <h2>Opening Ceremony</h2>
          <span>BSCC Dome · 17.00–23.00 WITA</span>
          <StatusBadge status="scheduled" />
        </Panel>
      </aside>
    </section>

    <section class="game-section">
      <SectionTitle eyebrow="Game Roster" title="Cabor & Eksibisi" :meta="`${sportCards.length} aktivitas`" />
      <div class="game-grid">
        <Link v-for="sport in sportCards" :key="sport.code" href="/cabor" class="game-card">
          <img v-if="assets.mascots[sport.code]" :src="assets.mascots[sport.code]" alt="" />
          <span>{{ sport.type }}</span>
          <h3>{{ sport.name }}</h3>
          <p>{{ sport.default_format.replaceAll('_', ' ') }}</p>
        </Link>
      </div>
    </section>
  </PublicLayout>
</template>

<style scoped>
.hero-arena { position: relative; display: grid; min-height: 900px; padding: 70px 0 270px; }
.hero-arena::before { content: "PORPAMNAS"; position: absolute; left: -12px; top: 74px; z-index: 0; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.07); font-size: clamp(80px, 15vw, 220px); font-weight: 1000; letter-spacing: -.09em; line-height: .7; pointer-events: none; }
.hero-copy { position: relative; z-index: 3; align-self: center; max-width: 900px; padding-bottom: 42px; }
.eyebrow { width: fit-content; margin: 0 0 18px; padding: 8px 12px; color: #071126; background: #F6C64A; font-size: 12px; font-weight: 1000; letter-spacing: .18em; text-transform: uppercase; transform: skew(-10deg); }
h1 { max-width: 1120px; margin: 0 0 22px; color: #fff; font-size: clamp(46px, 6.2vw, 100px); line-height: .86; letter-spacing: -.078em; text-transform: uppercase; text-shadow: 9px 9px 0 rgba(240,90,40,.35), -2px -2px 0 rgba(54,194,240,.3), 0 0 28px rgba(5,11,28,.92); }
h1 span { display: block; white-space: nowrap; }
.hero-copy p:not(.eyebrow) { max-width: 590px; color: rgba(255,255,255,.72); font-size: 18px; line-height: 1.7; }
.hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 28px; }
.primary-button, .secondary-button { padding: 15px 22px; text-decoration: none; font-size: 12px; font-weight: 1000; letter-spacing: .14em; text-transform: uppercase; clip-path: polygon(10px 0,100% 0,calc(100% - 10px) 100%,0 100%); }
.primary-button { color: #071126; background: #F6C64A; box-shadow: 10px 10px 0 rgba(240,90,40,.55); }
.secondary-button { color: #fff; background: rgba(7,17,38,.72); border: 1px solid rgba(255,255,255,.22); }
.hero-visual { position: absolute; inset: 0; z-index: 2; overflow: visible; isolation: isolate; pointer-events: none; }
.hero-visual::before { content: ""; position: absolute; inset: 10% 0 6% 34%; background: linear-gradient(150deg, rgba(54,194,240,.22), rgba(7,17,38,.18)); border: 1px solid rgba(255,255,255,.14); clip-path: polygon(10% 0,100% 0,90% 100%,0 100%); box-shadow: inset 0 0 0 9px rgba(255,255,255,.025), 0 34px 90px rgba(0,0,0,.35); }
.power-ring { position: absolute; inset: 20% 5% 16% 46%; border: 1px solid rgba(246,198,74,.34); border-radius: 999px; transform: rotate(-16deg); box-shadow: inset 0 0 80px rgba(54,194,240,.18), 0 0 70px rgba(246,198,74,.12); }
.mascot { position: absolute; bottom: 0; z-index: 2; object-fit: contain; filter: drop-shadow(0 38px 48px rgba(0,0,0,.5)); }
.mascot.beru { right: 19%; max-height: 680px; transform: rotate(-4deg); }
.mascot.ganga { right: -5%; max-height: 610px; transform: rotate(5deg); }
.hero-stats { position: absolute; left: 0; bottom: 16px; z-index: 4; display: grid; grid-template-columns: repeat(4, 1fr); width: min(720px, 54vw); gap: 0; background: #08142d; border: 1px solid rgba(255,255,255,.16); box-shadow: 12px 12px 0 rgba(54,194,240,.18); }
.hero-stats div { padding: 18px 20px; border-right: 1px dashed rgba(255,255,255,.18); }
.hero-stats div:last-child { border-right: 0; }
.hero-stats strong { display: block; color: #36C2F0; font-size: 38px; line-height: 1; }
.hero-stats span { color: rgba(255,255,255,.62); font-size: 11px; font-weight: 1000; letter-spacing: .15em; text-transform: uppercase; }

.match-strip { position: relative; z-index: 5; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 24px; margin: 24px 0 72px; }
.content-grid { display: grid; grid-template-columns: 1.42fr .78fr; gap: 24px; margin-bottom: 190px; }
.side-stack { display: grid; gap: 18px; }
:deep(.panel) { border-radius: 0; background: linear-gradient(180deg, rgba(11,31,77,.9), rgba(5,11,28,.92)); border: 1px solid rgba(255,255,255,.13); box-shadow: 8px 8px 0 rgba(0,0,0,.22); }

.date-tabs { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; margin-bottom: 16px; }
.date-tabs button { display: grid; gap: 2px; padding: 14px; color: white; background: #0a1938; border: 1px solid rgba(255,255,255,.12); text-align: left; cursor: pointer; clip-path: polygon(9px 0,100% 0,calc(100% - 9px) 100%,0 100%); }
.date-tabs button:first-child { color: #071126; background: #F6C64A; border-color: #F6C64A; }
.date-tabs button:first-child strong, .date-tabs button:first-child small, .date-tabs button:first-child span { color: #071126; }
.date-tabs strong { color: #F6C64A; font-size: 28px; line-height: 1; }
.date-tabs small, .date-tabs span { color: rgba(255,255,255,.65); font-size: 11px; }
.agenda-list { display: grid; gap: 8px; }
.agenda-row { display: grid; grid-template-columns: auto 1fr auto; gap: 14px; align-items: center; padding: 13px 14px; background: rgba(255,255,255,.055); border-left: 4px solid rgba(54,194,240,.75); }
.type-pill { width: fit-content; padding: 6px 9px; font-size: 10px; font-weight: 1000; text-transform: uppercase; }
.type-pill.sport { color: #BFEFFF; background: rgba(54,194,240,.16); }
.type-pill.exhibition { color: #FFE6A3; background: rgba(246,198,74,.18); }
.type-pill.official { color: #FFD4C2; background: rgba(240,90,40,.18); }
.agenda-row div { display: grid; align-content: center; gap: 4px; }
.agenda-row small { color: rgba(255,255,255,.64); }
.agenda-row time { color: #F6C64A; font-weight: 1000; }

.rank-row { display: grid; grid-template-columns: 34px 1fr auto; gap: 12px; align-items: center; padding: 15px 0; border-top: 1px dashed rgba(255,255,255,.16); }
.rank-row:first-of-type { border-top: 0; padding-top: 0; }
.rank-row > span { color: #071126; background: #F6C64A; display: grid; width: 30px; height: 30px; place-items: center; font-weight: 1000; clip-path: polygon(8px 0,100% 0,calc(100% - 8px) 100%,0 100%); }
.rank-row div { display: grid; gap: 6px; }
.rank-row small { color: rgba(255,255,255,.6); font-size: 11px; }
.rank-row b { color: #F6C64A; font-size: 30px; }
.panel-more { display: inline-block; margin-top: 14px; color: #36C2F0; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; font-size: 12px; text-decoration: none; }

.featured-panel { min-height: 220px; display: grid; align-content: end; gap: 10px; background: linear-gradient(150deg, rgba(240,90,40,.45), rgba(8,20,45,.92)); }
.featured-panel p { margin: 0; color: #F6C64A; font-weight: 1000; letter-spacing: .14em; text-transform: uppercase; font-size: 12px; }
.featured-panel h2 { margin: 0; font-size: 34px; line-height: 1; }
.featured-panel span { color: rgba(255,255,255,.72); }

.game-section { margin-top: 0; }
.game-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 22px 18px; }
.game-card { position: relative; display: block; min-height: 226px; overflow: hidden; padding: 24px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 8px 8px 0 rgba(54,194,240,.13); text-decoration: none; color: inherit; clip-path: polygon(18px 0,100% 0,100% calc(100% - 18px),calc(100% - 18px) 100%,0 100%,0 18px); }
.game-card::after { content: ""; position: absolute; inset: auto -20% -40% auto; width: 210px; height: 210px; background: radial-gradient(circle, rgba(246,198,74,.28), transparent 62%); }
.game-card img { position: absolute; right: -18px; bottom: -24px; z-index: 1; max-height: 194px; filter: drop-shadow(0 18px 32px rgba(0,0,0,.38)); transition: transform .25s ease; }
.game-card:hover img { transform: translateY(-10px) rotate(3deg); }
.game-card span { color: #F6C64A; font-size: 12px; font-weight: 1000; letter-spacing: .14em; text-transform: uppercase; }
.game-card h3 { position: relative; z-index: 2; max-width: 62%; margin: 8px 0; font-size: 30px; line-height: 1; }
.game-card p { position: relative; z-index: 2; max-width: 58%; color: rgba(255,255,255,.68); text-transform: capitalize; }

@media (max-width: 1080px) {
  .content-grid, .match-strip, .game-grid { grid-template-columns: 1fr; }
  .hero-stats { position: static; width: 100%; grid-column: 1 / -1; }
  .hero-visual { inset: auto 0 110px; height: 420px; }
  .mascot { bottom: -40px; }
  .mascot.beru { right: 6%; max-height: 390px; }
  .mascot.ganga { display: none; }
}
@media (max-width: 640px) {
  h1 { font-size: 50px; }
  .hero-arena { padding-top: 36px; }
  .hero-stats, .date-tabs { grid-template-columns: repeat(2, 1fr); }
  .agenda-row { grid-template-columns: 1fr; }
}
</style>
