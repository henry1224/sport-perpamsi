<script setup>
import { ref, computed } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';

const props = defineProps({ provinceRankings: Array });
const scope = ref('provinsi');
const scopes = [
  { key: 'provinsi', label: 'Provinsi' },
  { key: 'kabkota', label: 'Kabupaten / Kota' },
  { key: 'pdam', label: 'PDAM' },
];

// ponytail: only province data seeded; others share same list until real data lands.
const rows = computed(() => [...props.provinceRankings].sort((a, b) =>
  b.gold - a.gold || b.silver - a.silver || b.bronze - a.bronze
));
const total = (r) => r.gold + r.silver + r.bronze;
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Medal Table" title="Ranking Wilayah" meta="Perhitungan resmi" />
    </div>
    <div class="scope-row">
      <button v-for="s in scopes" :key="s.key" :class="{active: scope===s.key}" @click="scope=s.key">{{ s.label }}</button>
    </div>
    <section class="ranking-board">
      <table class="rank-table">
        <thead>
          <tr>
            <th>#</th><th>Nama</th>
            <th title="Emas">Emas</th><th title="Perak">Perak</th><th title="Perunggu">Perunggu</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(r, i) in rows" :key="r.name" :class="{top: i < 3}">
            <td><span class="pos">{{ i + 1 }}</span></td>
            <td><strong>{{ r.name }}</strong></td>
            <td>{{ r.gold }}</td>
            <td>{{ r.silver }}</td>
            <td>{{ r.bronze }}</td>
            <td><b>{{ total(r) }}</b></td>
          </tr>
        </tbody>
      </table>
      <p class="rule">Rumus: emas → perak → perunggu → total. Tie-breaker mengikuti regulasi cabor.</p>
    </section>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.scope-row { display: flex; gap: 8px; margin-bottom: 28px; padding: 16px; flex-wrap: wrap; background: rgba(5,11,28,.56); border: 1px solid rgba(255,255,255,.12); }
.scope-row button { padding: 10px 16px; border: 1px solid rgba(255,255,255,.16); background: #08142d; color: white; font-weight: 900; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; cursor: pointer; clip-path: polygon(9px 0,100% 0,calc(100% - 9px) 100%,0 100%); }
.scope-row button.active { background: #F6C64A; color: #071126; border-color: #F6C64A; box-shadow: 6px 6px 0 rgba(240,90,40,.35); }
.ranking-board { position: relative; overflow: hidden; padding: 20px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 10px 10px 0 rgba(54,194,240,.13); }
.ranking-board::before { content: "MEDAL"; position: absolute; right: 20px; top: -16px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.055); font-size: clamp(80px, 13vw, 170px); font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
.rank-table { width: 100%; border-collapse: collapse; }
.rank-table th { position: relative; z-index: 1; text-align: left; padding: 13px 12px; color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .14em; text-transform: uppercase; border-bottom: 1px solid rgba(255,255,255,.14); }
.rank-table td { position: relative; z-index: 1; padding: 18px 12px; border-bottom: 1px solid rgba(255,255,255,.08); }
.rank-table tr.top { background: linear-gradient(90deg, rgba(246,198,74,.12), transparent 62%); }
.rank-table tr.top .pos { background: #F6C64A; color: #071126; box-shadow: 5px 5px 0 rgba(240,90,40,.35); }
.pos { display: inline-grid; place-items: center; width: 32px; height: 32px; background: rgba(255,255,255,.1); color: white; font-weight: 1000; clip-path: polygon(8px 0,100% 0,calc(100% - 8px) 100%,0 100%); }
.rank-table b { color: #F6C64A; font-size: 20px; }
.rule { margin-top: 16px; color: rgba(255,255,255,.55); font-size: 12px; }
</style>
