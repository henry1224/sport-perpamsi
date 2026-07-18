<script setup>
import { ref, computed } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';

const props = defineProps({ results: Array });
const sports = computed(() => ['all', ...new Set(props.results.map((r) => r.sport))]);
const statuses = ['all', 'live', 'final', 'scheduled'];
const selSport = ref('all');
const selStatus = ref('all');
const filtered = computed(() => props.results.filter((r) =>
  (selSport.value === 'all' || r.sport === selSport.value) &&
  (selStatus.value === 'all' || r.status === selStatus.value)
));
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Match Result" title="Hasil Pertandingan" :meta="`${filtered.length} match`" />
    </div>
    <section class="result-board">
      <div class="filters">
      <div class="filter-group">
        <label>Cabor</label>
        <div class="chip-row">
          <button v-for="s in sports" :key="s" :class="{active: selSport===s}" @click="selSport=s">{{ s === 'all' ? 'Semua Cabor' : s }}</button>
        </div>
      </div>
      <div class="filter-group">
        <label>Status</label>
        <div class="chip-row">
          <button v-for="s in statuses" :key="s" :class="{active: selStatus===s}" @click="selStatus=s">{{ s === 'all' ? 'Semua Status' : s }}</button>
        </div>
      </div>
    </div>
    <div class="result-list">
      <div v-for="r in filtered" :key="`${r.sport}-${r.team_a}`" :class="['result-row', r.status]">
        <div class="time-col">
          <strong>{{ r.time || '—' }}</strong>
          <small>{{ r.sport }}</small>
        </div>
        <div class="event-col">
          <span :class="['status', r.status]">{{ r.status }}</span>
          <strong>{{ r.team_a }} <b>{{ r.score }}</b> {{ r.team_b }}</strong>
          <small>{{ r.venue }}</small>
        </div>
        <time>{{ r.score }}</time>
      </div>
      <p v-if="!filtered.length" class="empty">Tidak ada hasil sesuai filter.</p>
    </div>
    </section>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.result-board { position: relative; overflow: hidden; }
.result-board::before { content: "RESULT"; position: absolute; right: 18px; top: 8px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.055); font-size: clamp(70px, 11vw, 150px); font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
.filters { position: relative; z-index: 1; display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; margin-bottom: 30px; padding: 16px; background: rgba(5,11,28,.56); border: 1px solid rgba(255,255,255,.12); }
.filter-group label { display: block; margin-bottom: 10px; color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
.chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
.chip-row button { padding: 10px 14px; border: 1px solid rgba(255,255,255,.16); background: #08142d; color: white; font-weight: 900; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; cursor: pointer; clip-path: polygon(9px 0,100% 0,calc(100% - 9px) 100%,0 100%); }
.chip-row button.active { background: #F6C64A; color: #071126; border-color: #F6C64A; box-shadow: 6px 6px 0 rgba(240,90,40,.35); }
.result-list { position: relative; z-index: 1; display: grid; gap: 0; border-top: 1px solid rgba(255,255,255,.12); }
.result-row { --accent: #36C2F0; display: grid; grid-template-columns: 150px 1fr 140px; gap: 20px; align-items: center; min-height: 86px; padding: 18px 20px; background: linear-gradient(90deg, rgba(255,255,255,.045), rgba(255,255,255,.02)); border-bottom: 1px solid rgba(255,255,255,.1); border-left: 5px solid var(--accent); }
.result-row:hover { background: linear-gradient(90deg, rgba(54,194,240,.11), rgba(255,255,255,.035)); }
.result-row.final { --accent: #20C6B7; }
.result-row.scheduled { --accent: #F6C64A; }
.time-col { display: grid; gap: 4px; }
.time-col strong { display: block; color: #F6C64A; font-size: 20px; line-height: 1; font-variant-numeric: tabular-nums; }
.time-col small, .result-row small { color: rgba(255,255,255,.62); font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
.event-col { display: grid; align-content: center; gap: 6px; }
.event-col strong { display: block; margin: 0; font-size: 18px; letter-spacing: -.01em; }
.event-col b { color: #F6C64A; font-size: 24px; font-variant-numeric: tabular-nums; }
.status { display: inline-block; width: fit-content; padding: 4px 9px; color: #071126; background: var(--accent); font-size: 10px; font-weight: 1000; letter-spacing: .08em; text-transform: uppercase; }
.status.final { background: #20C6B7; }
.status.scheduled { background: #F6C64A; }
.result-row time { justify-self: end; color: #F6C64A; font-size: 24px; font-weight: 1000; font-variant-numeric: tabular-nums; }
.empty { grid-column: 1/-1; text-align: center; padding: 40px; color: rgba(255,255,255,.5); }
@media (max-width: 900px) { .filters, .result-row { grid-template-columns: 1fr; } .result-row time { justify-self: start; } }
</style>
