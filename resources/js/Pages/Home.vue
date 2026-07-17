<script setup>
const props = defineProps({
  agenda: Array,
  sports: Array,
  results: Array,
  provinceRankings: Array,
  assets: Object,
});

const todayAgenda = props.agenda.slice(0, 8);
const sportCards = props.sports.filter((sport) => sport.type !== 'official').slice(0, 9);
</script>

<template>
  <main class="page-shell">
    <nav class="topbar" aria-label="Brand PORPAMNAS">
      <div class="brand-chip">
        <img class="brand-mark porpamnas" :src="assets.porpamnas" alt="PORPAMNAS IX" />
        <span class="brand-divider" aria-hidden="true" />
        <img class="brand-mark ptmb" :src="assets.ptmb" alt="PTMB dan TRUST" />
      </div>
      <a class="ghost-link" href="#agenda">Agenda</a>
    </nav>

    <section class="hero-section">
      <div class="hero-copy">
        <p class="eyebrow">Balikpapan · 6–10 Oktober 2026</p>
        <h1>Sport Board PORPAMNAS IX</h1>
        <p class="hero-lead">
          Pusat agenda, hasil pertandingan, bracket, venue, dan ranking wilayah. Logo full-color tampil bersih di atas glass chip, maskot memberi energi arena yang tetap resmi.
        </p>
        <div class="hero-actions">
          <a class="primary-button" href="#agenda">Lihat Agenda</a>
          <a class="secondary-button" href="#hasil">Hasil Terbaru</a>
          <a class="secondary-button" href="#ranking">Ranking Wilayah</a>
        </div>
      </div>

      <div class="mascot-stage" aria-hidden="true">
        <img class="mascot mascot-beru" :src="assets.beru" alt="" />
        <img class="mascot mascot-ganga" :src="assets.ganga" alt="" />
      </div>
    </section>

    <section class="insight-grid" aria-label="Ringkasan event">
      <article id="hasil" class="glass-card results-card">
        <div class="section-heading">
          <p>Hasil</p>
          <h2>Terbaru</h2>
        </div>
        <div v-for="result in results" :key="`${result.sport}-${result.team_a}`" class="result-row">
          <div>
            <span class="sport-label">{{ result.sport }}</span>
            <strong>{{ result.team_a }}</strong>
            <small>vs {{ result.team_b }}</small>
          </div>
          <div class="score-box">
            <b>{{ result.score }}</b>
            <span>{{ result.status }}</span>
          </div>
        </div>
      </article>

      <article id="agenda" class="glass-card agenda-card">
        <div class="section-heading">
          <p>Agenda</p>
          <h2>Hari Ini</h2>
        </div>
        <div v-for="item in todayAgenda" :key="`${item.date}-${item.title}-${item.start_time}`" class="agenda-row">
          <span :class="['agenda-type', item.type]">{{ item.type }}</span>
          <strong>{{ item.title }}</strong>
          <small>{{ item.start_time }}<template v-if="item.end_time">–{{ item.end_time }}</template> WITA · {{ item.venue }}</small>
        </div>
      </article>

      <article id="ranking" class="glass-card ranking-card">
        <div class="section-heading">
          <p>Ranking</p>
          <h2>Provinsi</h2>
        </div>
        <div v-for="(rank, index) in provinceRankings" :key="rank.name" class="ranking-row">
          <span>{{ index + 1 }}</span>
          <strong>{{ rank.name }}</strong>
          <small>{{ rank.gold }} emas · {{ rank.silver }} perak · {{ rank.bronze }} perunggu</small>
        </div>
      </article>
    </section>

    <section class="sports-section" aria-label="Cabor dan maskot">
      <div class="section-heading wide">
        <p>Cabor</p>
        <h2>Maskot per Arena</h2>
      </div>
      <div class="sport-grid">
        <article v-for="sport in sportCards" :key="sport.code" class="sport-card">
          <img v-if="assets.mascots[sport.code]" :src="assets.mascots[sport.code]" alt="" />
          <div>
            <span>{{ sport.type }}</span>
            <h3>{{ sport.name }}</h3>
            <p>{{ sport.default_format.replaceAll('_', ' ') }}</p>
          </div>
        </article>
      </div>
    </section>
  </main>
</template>

<style scoped>
.page-shell {
  min-height: 100vh;
  padding: 28px max(20px, calc((100vw - 1180px) / 2)) 64px;
  background:
    radial-gradient(circle at 82% 8%, rgba(54, 194, 240, .28), transparent 32rem),
    radial-gradient(circle at 10% 80%, rgba(240, 90, 40, .18), transparent 28rem),
    linear-gradient(135deg, #071126 0%, #0B1F4D 54%, #10275C 100%);
  overflow: hidden;
}

.topbar { display: flex; align-items: center; justify-content: space-between; gap: 20px; }
.brand-chip { display: inline-flex; align-items: center; gap: 16px; padding: 10px 16px; border-radius: 24px; background: rgba(255,255,255,.92); box-shadow: 0 20px 60px rgba(0,0,0,.2); backdrop-filter: blur(18px); }
.brand-mark { object-fit: contain; }
.brand-mark.porpamnas { width: 74px; height: 52px; }
.brand-mark.ptmb { width: 184px; height: 48px; }
.brand-divider { width: 1px; align-self: stretch; background: rgba(7, 17, 38, .18); }
.ghost-link { color: white; text-decoration: none; border: 1px solid rgba(255,255,255,.22); border-radius: 999px; padding: 12px 18px; font-weight: 800; }

.hero-section { display: grid; grid-template-columns: 1.04fr .96fr; gap: 40px; align-items: center; padding: 72px 0 38px; }
.eyebrow { margin: 0; color: #F6C64A; font-weight: 900; letter-spacing: .15em; text-transform: uppercase; }
h1 { margin: 14px 0 20px; max-width: 720px; font-size: clamp(54px, 8vw, 112px); line-height: .86; letter-spacing: -.07em; }
.hero-lead { max-width: 620px; color: rgba(255,255,255,.76); font-size: 18px; line-height: 1.75; }
.hero-actions { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 30px; }
.primary-button, .secondary-button { border-radius: 999px; padding: 14px 22px; text-decoration: none; font-weight: 900; }
.primary-button { color: #161006; background: linear-gradient(135deg, #F05A28, #F6C64A); }
.secondary-button { color: white; border: 1px solid rgba(255,255,255,.2); }

.mascot-stage { position: relative; min-height: 510px; isolation: isolate; }
.mascot-stage::before { content: ""; position: absolute; inset: 12% 2% 4% 10%; border-radius: 999px; background: linear-gradient(135deg, rgba(54,194,240,.34), rgba(246,198,74,.2)); filter: blur(28px); z-index: -1; }
.mascot { position: absolute; bottom: 0; max-height: 480px; object-fit: contain; filter: drop-shadow(0 28px 42px rgba(0,0,0,.4)); }
.mascot-beru { left: 4%; transform: rotate(-3deg); }
.mascot-ganga { right: 0; transform: rotate(4deg); }

.insight-grid { display: grid; grid-template-columns: 1.18fr .9fr .92fr; gap: 18px; margin-top: 26px; }
.glass-card, .sport-card { background: linear-gradient(160deg, rgba(255,255,255,.13), rgba(255,255,255,.05)); border: 1px solid rgba(255,255,255,.16); border-radius: 30px; box-shadow: 0 24px 80px rgba(0,0,0,.28); backdrop-filter: blur(18px); }
.glass-card { padding: 24px; }
.section-heading p { margin: 0; color: #36C2F0; font-size: 12px; font-weight: 900; letter-spacing: .16em; text-transform: uppercase; }
.section-heading h2 { margin: 4px 0 18px; font-size: 28px; letter-spacing: -.04em; }
.result-row, .agenda-row, .ranking-row { border-top: 1px solid rgba(255,255,255,.14); padding: 16px 0; }
.result-row:first-of-type, .agenda-row:first-of-type, .ranking-row:first-of-type { border-top: 0; padding-top: 0; }
.result-row { display: flex; justify-content: space-between; gap: 16px; }
.result-row strong, .agenda-row strong { display: block; margin: 7px 0 4px; }
.result-row small, .agenda-row small, .ranking-row small { color: rgba(255,255,255,.66); }
.sport-label, .agenda-type { display: inline-flex; width: fit-content; border-radius: 999px; padding: 5px 9px; font-size: 11px; font-weight: 900; text-transform: uppercase; }
.sport-label, .agenda-type.sport { color: #BFEFFF; background: rgba(54,194,240,.16); }
.agenda-type.exhibition { color: #FFE6A3; background: rgba(246,198,74,.16); }
.agenda-type.official { color: #FFD4C2; background: rgba(240,90,40,.16); }
.score-box { text-align: right; }
.score-box b { display: block; color: #F6C64A; font-size: 34px; line-height: 1; }
.score-box span { color: rgba(255,255,255,.68); font-size: 12px; font-weight: 900; text-transform: uppercase; }
.ranking-row { display: grid; grid-template-columns: 34px 1fr; gap: 8px 12px; align-items: center; }
.ranking-row span { display: grid; width: 28px; height: 28px; place-items: center; border-radius: 50%; background: rgba(246,198,74,.18); color: #F6C64A; font-weight: 900; }
.ranking-row small { grid-column: 2; }

.sports-section { margin-top: 28px; }
.section-heading.wide { margin-bottom: 16px; }
.sport-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
.sport-card { position: relative; min-height: 210px; padding: 24px; overflow: hidden; }
.sport-card img { position: absolute; right: -16px; bottom: -18px; max-height: 178px; filter: drop-shadow(0 18px 32px rgba(0,0,0,.35)); transition: transform .25s ease; }
.sport-card:hover img { transform: translateY(-8px) rotate(3deg); }
.sport-card span { color: #F6C64A; font-size: 12px; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
.sport-card h3 { max-width: 62%; margin: 8px 0; font-size: 26px; line-height: 1; }
.sport-card p { max-width: 60%; color: rgba(255,255,255,.68); text-transform: capitalize; }

@media (max-width: 900px) {
  .hero-section, .insight-grid, .sport-grid { grid-template-columns: 1fr; }
  .hero-section { padding-top: 44px; }
  .mascot-stage { min-height: 320px; }
  .mascot { max-height: 310px; }
  .mascot-ganga { display: none; }
  .brand-chip { max-width: 100%; }
  .brand-mark.ptmb { width: 132px; }
}
</style>
