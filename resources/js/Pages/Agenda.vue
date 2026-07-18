<script setup>
import { ref, computed } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';

const props = defineProps({ agenda: Array });
const dates = [...new Set(props.agenda.map((i) => i.date))];
const types = ['all', 'sport', 'exhibition', 'official'];
const selectedDate = ref('all');
const selectedType = ref('all');

const filtered = computed(() => props.agenda.filter((i) =>
  (selectedDate.value === 'all' || i.date === selectedDate.value) &&
  (selectedType.value === 'all' || i.type === selectedType.value)
));
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Full Schedule" title="Agenda Kegiatan" :meta="`${filtered.length} acara`" />
    </div>
    <section class="agenda-board">
      <div class="filters">
        <div class="filter-group">
          <label>Tanggal</label>
          <div class="chip-row">
            <button :class="{active: selectedDate==='all'}" @click="selectedDate='all'">Semua</button>
            <button v-for="d in dates" :key="d" :class="{active: selectedDate===d}" @click="selectedDate=d">{{ d.slice(8,10) }} Okt</button>
          </div>
        </div>
        <div class="filter-group">
          <label>Tipe</label>
          <div class="chip-row">
            <button v-for="t in types" :key="t" :class="{active: selectedType===t}" @click="selectedType=t">{{ t === 'all' ? 'Semua' : t }}</button>
          </div>
        </div>
      </div>
      <div class="agenda-list">
        <div v-for="item in filtered" :key="`${item.date}-${item.title}-${item.start_time}`" :class="['agenda-row', item.type]">
          <div class="time-col">
            <strong>{{ item.start_time }}</strong>
            <small>{{ item.day }} {{ item.date.slice(8,10) }}/{{ item.date.slice(5,7) }}</small>
          </div>
          <div class="event-col">
            <span :class="['type-pill', item.type]">{{ item.type }}</span>
            <strong>{{ item.title }}</strong>
            <small>{{ item.venue }}</small>
          </div>
          <time v-if="item.end_time">{{ item.start_time }}–{{ item.end_time }}</time>
        </div>
        <p v-if="!filtered.length" class="empty">Tidak ada acara sesuai filter.</p>
      </div>
    </section>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.agenda-board { position: relative; overflow: hidden; }
.agenda-board::before { content: "SCHEDULE"; position: absolute; right: 18px; top: 8px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.055); font-size: clamp(70px, 11vw, 150px); font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
.filters { position: relative; z-index: 1; display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; margin-bottom: 30px; padding: 16px; background: rgba(5,11,28,.56); border: 1px solid rgba(255,255,255,.12); }
.filter-group label { display: block; margin-bottom: 10px; color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
.chip-row { display: flex; flex-wrap: wrap; gap: 8px; }
.chip-row button { padding: 10px 14px; border: 1px solid rgba(255,255,255,.16); background: #08142d; color: white; font-weight: 900; font-size: 12px; letter-spacing: .08em; text-transform: uppercase; cursor: pointer; clip-path: polygon(9px 0,100% 0,calc(100% - 9px) 100%,0 100%); }
.chip-row button.active { background: #F6C64A; color: #071126; border-color: #F6C64A; box-shadow: 6px 6px 0 rgba(240,90,40,.35); }
.agenda-list { position: relative; z-index: 1; display: grid; gap: 0; border-top: 1px solid rgba(255,255,255,.12); }
.agenda-row { --accent: #36C2F0; display: grid; grid-template-columns: 150px 1fr 140px; gap: 20px; align-items: center; min-height: 86px; padding: 18px 20px; background: linear-gradient(90deg, rgba(255,255,255,.045), rgba(255,255,255,.02)); border-bottom: 1px solid rgba(255,255,255,.1); border-left: 5px solid var(--accent); }
.agenda-row:hover { background: linear-gradient(90deg, rgba(54,194,240,.11), rgba(255,255,255,.035)); }
.agenda-row.exhibition { --accent: #F6C64A; }
.agenda-row.official { --accent: #F05A28; }
.time-col { display: grid; gap: 4px; }
.time-col strong { display: block; color: #F6C64A; font-size: 28px; line-height: 1; font-variant-numeric: tabular-nums; }
.time-col small, .agenda-row small { color: rgba(255,255,255,.62); font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
.event-col { display: grid; align-content: center; gap: 6px; }
.event-col strong { display: block; margin: 0; font-size: 18px; letter-spacing: -.01em; }
.type-pill { display: inline-block; width: fit-content; padding: 4px 9px; color: #071126; background: var(--accent); font-size: 10px; font-weight: 1000; letter-spacing: .08em; text-transform: uppercase; }
.agenda-row time { justify-self: end; color: #F6C64A; font-size: 13px; font-weight: 1000; font-variant-numeric: tabular-nums; }
.empty { text-align: center; padding: 40px; color: rgba(255,255,255,.5); }
@media (max-width: 900px) { .filters, .agenda-row { grid-template-columns: 1fr; } .agenda-row time { justify-self: start; } }
</style>
