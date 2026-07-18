<script setup>
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';

const props = defineProps({ sports: Array, assets: Object });
const cards = props.sports.filter((s) => s.type !== 'official');
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Game Roster" title="Cabor & Eksibisi" :meta="`${cards.length} aktivitas`" />
    </div>
    <div class="game-grid">
      <div v-for="sport in cards" :key="sport.code" class="game-card">
        <img v-if="assets.mascots[sport.code]" :src="assets.mascots[sport.code]" alt="" />
        <span>{{ sport.type }}</span>
        <h3>{{ sport.name }}</h3>
        <p><strong>Format:</strong> {{ sport.default_format.replaceAll('_', ' ') }}</p>
        <p><strong>Skor:</strong> {{ sport.score_template.replaceAll('_', ' ') }}</p>
      </div>
    </div>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.game-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 22px 18px; }
.game-card { position: relative; min-height: 260px; overflow: hidden; padding: 26px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 8px 8px 0 rgba(54,194,240,.13); color: inherit; clip-path: polygon(18px 0,100% 0,100% calc(100% - 18px),calc(100% - 18px) 100%,0 100%,0 18px); }
.game-card::before { content: ""; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(54,194,240,.12), transparent 45%); opacity: 0; transition: opacity .2s ease; }
.game-card::after { content: ""; position: absolute; inset: auto -20% -40% auto; width: 230px; height: 230px; background: radial-gradient(circle, rgba(246,198,74,.28), transparent 62%); }
.game-card:hover::before { opacity: 1; }
.game-card img { position: absolute; right: -18px; bottom: -24px; z-index: 1; max-height: 204px; filter: drop-shadow(0 18px 32px rgba(0,0,0,.38)); transition: transform .25s ease; }
.game-card:hover img { transform: translateY(-10px) rotate(3deg); }
.game-card span { position: relative; z-index: 2; color: #F6C64A; font-size: 12px; font-weight: 1000; letter-spacing: .14em; text-transform: uppercase; }
.game-card h3 { position: relative; z-index: 2; max-width: 62%; margin: 8px 0 18px; font-size: 32px; line-height: 1; letter-spacing: -.04em; }
.game-card p { position: relative; z-index: 2; max-width: 58%; margin: 8px 0; color: rgba(255,255,255,.68); font-size: 13px; text-transform: capitalize; }
.game-card p strong { display: block; color: #36C2F0; font-size: 10px; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; }
@media (max-width: 640px) { .game-grid { grid-template-columns: 1fr; } }
</style>
