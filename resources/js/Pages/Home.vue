<script setup>
const props = defineProps({
  agenda: Array,
  sports: Array,
  results: Array,
  provinceRankings: Array,
  assets: Object,
});

const dateLabels = [...new Set(props.agenda.map((item) => item.date))].map((date) => {
  const item = props.agenda.find((agenda) => agenda.date === date);
  return { date, day: item.day, count: props.agenda.filter((agenda) => agenda.date === date).length };
});

const featuredAgenda = props.agenda.slice(0, 6);
const sportCards = props.sports.filter((sport) => sport.type !== 'official');
const venueCount = new Set(props.agenda.map((item) => item.venue)).size;
const activityCount = props.agenda.length;
const sportCount = props.sports.filter((sport) => sport.type === 'sport').length;
const exhibitionCount = props.sports.filter((sport) => sport.type === 'exhibition').length;
</script>

<template>
  <main class="event-shell">
    <aside class="rail" aria-label="Navigasi public">
      <div class="rail-brand">
        <img :src="assets.porpamnas" alt="PORPAMNAS IX" />
      </div>
      <a href="#hero">Home</a>
      <a href="#agenda">Agenda</a>
      <a href="#hasil">Hasil</a>
      <a href="#cabor">Cabor</a>
      <a href="#ranking">Ranking</a>
    </aside>

    <div class="content">
      <nav class="topbar" aria-label="Brand PORPAMNAS">
        <div class="brand-chip">
          <img class="brand-mark porpamnas" :src="assets.porpamnas" alt="PORPAMNAS IX" />
          <span class="brand-divider" aria-hidden="true" />
          <img class="brand-mark ptmb" :src="assets.ptmb" alt="PTMB dan TRUST" />
        </div>
        <div class="top-meta">
          <span>Balikpapan</span>
          <strong>06–10 Okt 2026</strong>
        </div>
      </nav>

      <section id="hero" class="hero-grid">
        <div class="hero-copy panel hero-panel">
          <p class="eyebrow">PORPAMNAS IX KALTIM</p>
          <h1>Agenda, hasil, dan ranking dalam satu arena.</h1>
          <p class="hero-lead">
            Portal public untuk mengikuti semua kegiatan: venue, jam pertandingan, hasil final, bracket, cabor, dan ranking PDAM hingga provinsi.
          </p>
          <div class="hero-actions">
            <a class="primary-button" href="#agenda">Lihat Agenda</a>
            <a class="secondary-button" href="#hasil">Hasil Terbaru</a>
            <a class="secondary-button" href="#ranking">Ranking Wilayah</a>
          </div>
          <div class="metrics-strip" aria-label="Statistik event">
            <div><strong>{{ activityCount }}</strong><span>Agenda</span></div>
            <div><strong>{{ sportCount }}</strong><span>Cabor</span></div>
            <div><strong>{{ exhibitionCount }}</strong><span>Eksibisi</span></div>
            <div><strong>{{ venueCount }}</strong><span>Venue</span></div>
          </div>
        </div>

        <div class="mascot-panel panel" aria-hidden="true">
          <div class="orbital-ring" />
          <img class="mascot mascot-beru" :src="assets.beru" alt="" />
          <img class="mascot mascot-ganga" :src="assets.ganga" alt="" />
          <div class="floating-ticket">
            <span>Opening</span>
            <strong>BSCC Dome</strong>
            <small>17.00–23.00 WITA</small>
          </div>
        </div>
      </section>

      <section class="marquee" aria-label="Highlight cabor">
        <span v-for="sport in sportCards" :key="sport.code">{{ sport.name }}</span>
      </section>

      <section class="dashboard-grid">
        <article id="agenda" class="panel agenda-board">
          <div class="section-heading split">
            <div>
              <p>Weekly Schedule</p>
              <h2>Agenda Kegiatan</h2>
            </div>
            <a href="#cabor">Lihat Cabor</a>
          </div>
          <div class="date-tabs">
            <button v-for="date in dateLabels" :key="date.date" type="button">
              <span>{{ date.day }}</span>
              <strong>{{ date.date.slice(8, 10) }}</strong>
              <small>{{ date.count }} acara</small>
            </button>
          </div>
          <div class="agenda-list">
            <div v-for="item in featuredAgenda" :key="`${item.date}-${item.title}-${item.start_time}`" class="agenda-card">
              <span :class="['badge', item.type]">{{ item.type }}</span>
              <div>
                <strong>{{ item.title }}</strong>
                <small>{{ item.venue }}</small>
              </div>
              <time>{{ item.start_time }}<template v-if="item.end_time">–{{ item.end_time }}</template></time>
            </div>
          </div>
        </article>

        <article id="hasil" class="panel result-board">
          <div class="section-heading">
            <p>Final Record</p>
            <h2>Hasil Terbaru</h2>
          </div>
          <div v-for="result in results" :key="`${result.sport}-${result.team_a}`" class="result-card">
            <span>{{ result.sport }}</span>
            <div class="teams">
              <strong>{{ result.team_a }}</strong>
              <small>vs</small>
              <strong>{{ result.team_b }}</strong>
            </div>
            <b>{{ result.score }}</b>
          </div>
        </article>

        <article id="ranking" class="panel ranking-board">
          <div class="section-heading">
            <p>Medal Table</p>
            <h2>Ranking Provinsi</h2>
          </div>
          <div v-for="(rank, index) in provinceRankings" :key="rank.name" class="ranking-card">
            <span class="rank-no">{{ index + 1 }}</span>
            <div>
              <strong>{{ rank.name }}</strong>
              <small>{{ rank.gold }} emas · {{ rank.silver }} perak · {{ rank.bronze }} perunggu</small>
            </div>
            <b>{{ rank.gold }}</b>
          </div>
        </article>
      </section>

      <section id="cabor" class="sports-section">
        <div class="section-heading split">
          <div>
            <p>Game Titles</p>
            <h2>Cabor & Eksibisi</h2>
          </div>
          <span>{{ sportCards.length }} aktivitas</span>
        </div>
        <div class="sport-grid">
          <article v-for="sport in sportCards" :key="sport.code" class="sport-card">
            <img v-if="assets.mascots[sport.code]" :src="assets.mascots[sport.code]" alt="" />
            <span>{{ sport.type }}</span>
            <h3>{{ sport.name }}</h3>
            <p>{{ sport.default_format.replaceAll('_', ' ') }}</p>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>

<style scoped>
.event-shell {
  min-height: 100vh;
  background:
    linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px) 0 0 / 64px 64px,
    radial-gradient(circle at 76% 10%, rgba(54, 194, 240, .26), transparent 32rem),
    radial-gradient(circle at 12% 76%, rgba(240, 90, 40, .18), transparent 28rem),
    linear-gradient(135deg, #061025 0%, #0A1D4B 48%, #10275C 100%);
  color: #fff;
  display: grid;
  grid-template-columns: 92px 1fr;
}

.rail {
  position: sticky;
  top: 0;
  height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 18px;
  padding: 24px 14px;
  background: rgba(4, 12, 30, .62);
  border-right: 1px solid rgba(255,255,255,.12);
  backdrop-filter: blur(18px);
  z-index: 5;
}
.rail-brand { width: 58px; height: 58px; display: grid; place-items: center; border-radius: 20px; background: rgba(255,255,255,.92); }
.rail-brand img { width: 44px; }
.rail a { writing-mode: vertical-rl; transform: rotate(180deg); color: rgba(255,255,255,.66); text-decoration: none; font-size: 11px; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
.rail a:hover { color: #F6C64A; }

.content { width: min(1280px, calc(100vw - 132px)); margin: 0 auto; padding: 26px 0 72px; }
.topbar { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
.brand-chip { display: inline-flex; align-items: center; gap: 16px; padding: 10px 16px; border-radius: 24px; background: rgba(255,255,255,.92); box-shadow: 0 20px 60px rgba(0,0,0,.2); backdrop-filter: blur(18px); }
.brand-mark { object-fit: contain; }
.brand-mark.porpamnas { width: 74px; height: 52px; }
.brand-mark.ptmb { width: 184px; height: 48px; }
.brand-divider { width: 1px; align-self: stretch; background: rgba(7, 17, 38, .18); }
.top-meta { display: grid; gap: 3px; text-align: right; color: rgba(255,255,255,.64); text-transform: uppercase; letter-spacing: .1em; font-size: 11px; }
.top-meta strong { color: white; font-size: 14px; }

.panel {
  background: linear-gradient(160deg, rgba(255,255,255,.13), rgba(255,255,255,.045));
  border: 1px solid rgba(255,255,255,.15);
  border-radius: 34px;
  box-shadow: 0 24px 80px rgba(0,0,0,.28);
  backdrop-filter: blur(18px);
}
.hero-grid { display: grid; grid-template-columns: 1.08fr .92fr; gap: 22px; align-items: stretch; }
.hero-panel { padding: clamp(28px, 4vw, 54px); }
.eyebrow { margin: 0; color: #F6C64A; font-weight: 950; letter-spacing: .18em; text-transform: uppercase; }
h1 { max-width: 780px; margin: 14px 0 20px; font-size: clamp(44px, 6.4vw, 96px); line-height: .86; letter-spacing: -.07em; }
.hero-lead { max-width: 660px; color: rgba(255,255,255,.76); font-size: 18px; line-height: 1.75; }
.hero-actions { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 30px; }
.primary-button, .secondary-button { border-radius: 999px; padding: 14px 22px; text-decoration: none; font-weight: 950; }
.primary-button { color: #161006; background: linear-gradient(135deg, #F05A28, #F6C64A); }
.secondary-button { color: white; border: 1px solid rgba(255,255,255,.22); }
.metrics-strip { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 36px; }
.metrics-strip div { padding: 16px; border-radius: 20px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1); }
.metrics-strip strong { display: block; color: #36C2F0; font-size: 30px; }
.metrics-strip span { color: rgba(255,255,255,.64); font-size: 12px; text-transform: uppercase; letter-spacing: .12em; }

.mascot-panel { position: relative; min-height: 560px; overflow: hidden; isolation: isolate; }
.orbital-ring { position: absolute; inset: 13% 8%; border: 1px solid rgba(255,255,255,.22); border-radius: 999px; transform: rotate(-14deg); box-shadow: inset 0 0 80px rgba(54,194,240,.22); }
.mascot { position: absolute; bottom: -18px; max-height: 505px; object-fit: contain; filter: drop-shadow(0 28px 42px rgba(0,0,0,.42)); }
.mascot-beru { left: 2%; transform: rotate(-4deg); }
.mascot-ganga { right: -4%; transform: rotate(5deg); }
.floating-ticket { position: absolute; left: 26px; bottom: 28px; padding: 18px 20px; border-radius: 24px; background: rgba(255,255,255,.9); color: #071126; box-shadow: 0 24px 60px rgba(0,0,0,.28); }
.floating-ticket span { display: block; color: #F05A28; font-size: 11px; font-weight: 950; letter-spacing: .14em; text-transform: uppercase; }
.floating-ticket strong { display: block; margin: 4px 0; }

.marquee { display: flex; gap: 10px; overflow: hidden; margin: 22px 0; padding: 12px; border-radius: 24px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); }
.marquee span { flex: 0 0 auto; padding: 10px 14px; border-radius: 999px; background: rgba(54,194,240,.13); color: #BFEFFF; font-size: 12px; font-weight: 950; letter-spacing: .12em; text-transform: uppercase; }

.dashboard-grid { display: grid; grid-template-columns: 1.36fr .9fr .88fr; gap: 18px; }
.agenda-board, .result-board, .ranking-board { padding: 24px; }
.section-heading { margin-bottom: 18px; }
.section-heading p { margin: 0; color: #36C2F0; font-size: 12px; font-weight: 950; letter-spacing: .16em; text-transform: uppercase; }
.section-heading h2 { margin: 4px 0 0; font-size: 30px; letter-spacing: -.04em; }
.section-heading.split { display: flex; justify-content: space-between; align-items: end; gap: 18px; }
.section-heading a, .section-heading span { color: #F6C64A; font-size: 12px; font-weight: 950; text-decoration: none; text-transform: uppercase; letter-spacing: .12em; }
.date-tabs { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; margin-bottom: 16px; }
.date-tabs button { display: grid; gap: 2px; padding: 14px; border: 1px solid rgba(255,255,255,.13); border-radius: 18px; color: white; background: rgba(255,255,255,.07); text-align: left; }
.date-tabs strong { color: #F6C64A; font-size: 26px; line-height: 1; }
.date-tabs span, .date-tabs small { color: rgba(255,255,255,.65); }
.agenda-list { display: grid; gap: 10px; }
.agenda-card { display: grid; grid-template-columns: auto 1fr auto; gap: 14px; align-items: center; padding: 14px; border-radius: 20px; background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1); }
.badge { border-radius: 999px; padding: 6px 9px; font-size: 10px; font-weight: 950; text-transform: uppercase; }
.badge.sport { color: #BFEFFF; background: rgba(54,194,240,.16); }
.badge.exhibition { color: #FFE6A3; background: rgba(246,198,74,.18); }
.badge.official { color: #FFD4C2; background: rgba(240,90,40,.18); }
.agenda-card strong, .result-card strong, .ranking-card strong { display: block; }
.agenda-card small, .result-card small, .ranking-card small { color: rgba(255,255,255,.64); }
.agenda-card time { color: #F6C64A; font-weight: 950; }
.result-card, .ranking-card { padding: 16px 0; border-top: 1px solid rgba(255,255,255,.13); }
.result-card:first-of-type, .ranking-card:first-of-type { border-top: 0; padding-top: 0; }
.result-card > span { color: #36C2F0; font-size: 12px; font-weight: 950; text-transform: uppercase; letter-spacing: .12em; }
.teams { margin: 8px 0; display: grid; gap: 4px; }
.result-card b { color: #F6C64A; font-size: 38px; line-height: 1; }
.ranking-card { display: grid; grid-template-columns: 34px 1fr auto; gap: 12px; align-items: center; }
.rank-no { display: grid; width: 30px; height: 30px; place-items: center; border-radius: 50%; background: rgba(246,198,74,.18); color: #F6C64A; font-weight: 950; }
.ranking-card b { color: #F6C64A; font-size: 28px; }

.sports-section { margin-top: 22px; }
.sport-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
.sport-card { position: relative; min-height: 220px; padding: 24px; overflow: hidden; background: linear-gradient(160deg, rgba(255,255,255,.12), rgba(255,255,255,.045)); border: 1px solid rgba(255,255,255,.14); border-radius: 30px; box-shadow: 0 24px 80px rgba(0,0,0,.24); }
.sport-card img { position: absolute; right: -16px; bottom: -20px; max-height: 190px; filter: drop-shadow(0 18px 32px rgba(0,0,0,.35)); transition: transform .25s ease; }
.sport-card:hover img { transform: translateY(-10px) rotate(3deg); }
.sport-card span { color: #F6C64A; font-size: 12px; font-weight: 950; letter-spacing: .14em; text-transform: uppercase; }
.sport-card h3 { max-width: 62%; margin: 8px 0; font-size: 28px; line-height: 1; }
.sport-card p { max-width: 58%; color: rgba(255,255,255,.68); text-transform: capitalize; }

@media (max-width: 1020px) {
  .event-shell { grid-template-columns: 1fr; }
  .rail { position: static; height: auto; flex-direction: row; overflow-x: auto; justify-content: flex-start; }
  .rail a { writing-mode: horizontal-tb; transform: none; }
  .content { width: min(100% - 28px, 1280px); }
  .hero-grid, .dashboard-grid, .sport-grid { grid-template-columns: 1fr; }
  .mascot-panel { min-height: 340px; }
  .mascot { max-height: 320px; }
  .mascot-ganga { display: none; }
  .date-tabs { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .metrics-strip { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 620px) {
  .brand-chip { gap: 10px; padding: 8px 10px; }
  .brand-mark.porpamnas { width: 54px; height: 40px; }
  .brand-mark.ptmb { width: 118px; }
  .top-meta { display: none; }
  h1 { font-size: 46px; }
  .agenda-card { grid-template-columns: 1fr; }
}
</style>
