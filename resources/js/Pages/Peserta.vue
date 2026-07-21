<script setup>
import { computed, ref } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';

const props = defineProps({ pdams: Array, provinces: Array, regionalCommittees: Array });

const q = ref('');
const province = ref('all');
const viewMode = ref('pdam');

const matchesQuery = (p) => {
  const needle = q.value.toLowerCase();
  return [p.name, p.city, p.province, p.regional_committee_name].filter(Boolean).join(' ').toLowerCase().includes(needle);
};
const filtered = computed(() => props.pdams.filter((p) =>
  (province.value === 'all' || p.province_code === province.value) && matchesQuery(p)
));

const grouped = computed(() => {
  const map = new Map();
  for (const p of filtered.value) {
    const key = p.regional_committee_name || `PD PERPAMSI ${(p.province || '').toUpperCase()}`;
    if (!map.has(key)) map.set(key, { name: key, province: p.province, province_code: p.province_code, members: [] });
    map.get(key).members.push(p);
  }
  return [...map.values()].sort((a, b) => a.name.localeCompare(b.name));
});
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Participant Directory" title="Data PDAM & Pimpinan Daerah" :meta="`${filtered.length} peserta · ${grouped.length} PD`" />
    </div>

    <section class="participant-board">
      <div class="filters">
        <label>
          <span>Cari</span>
          <input v-model="q" type="search" placeholder="Cari PDAM, kota, PD PERPAMSI" />
        </label>
        <label>
          <span>Provinsi</span>
          <select v-model="province">
            <option value="all">Semua Provinsi</option>
            <option v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}</option>
          </select>
        </label>
        <div class="view-toggle" role="tablist" aria-label="Mode tampilan">
          <button :class="{ active: viewMode === 'pdam' }" @click="viewMode = 'pdam'">Per PDAM</button>
          <button :class="{ active: viewMode === 'pd' }" @click="viewMode = 'pd'">Per PD PERPAMSI</button>
        </div>
      </div>

      <div v-if="viewMode === 'pdam'" class="participant-list">
        <div v-for="p in filtered" :key="p.code" class="participant-row">
          <div class="code">{{ p.province_code }}</div>
          <div class="main">
            <strong>{{ p.name }}</strong>
            <small>{{ p.city }} · {{ p.province }}</small>
            <small class="pd">{{ p.regional_committee_name }}</small>
          </div>
          <div class="area">{{ p.regency_code }}</div>
          <div class="addr">{{ p.address }}</div>
        </div>
        <p v-if="!filtered.length" class="empty">Tidak ada data sesuai filter.</p>
      </div>

      <div v-else class="pd-list">
        <article v-for="g in grouped" :key="g.name" class="pd-card">
          <header>
            <strong>{{ g.name }}</strong>
            <small>{{ g.province }} · {{ g.members.length }} PDAM</small>
          </header>
          <ul>
            <li v-for="m in g.members" :key="m.code">
              <b>{{ m.name }}</b>
              <span>{{ m.city || '—' }}</span>
            </li>
          </ul>
        </article>
        <p v-if="!grouped.length" class="empty">Tidak ada PD sesuai filter.</p>
      </div>
    </section>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.participant-board { position: relative; overflow: hidden; }
.participant-board::before { content: "PDAM"; position: absolute; right: 18px; top: 8px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.055); font-size: clamp(80px, 13vw, 170px); font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
.filters { position: relative; z-index: 1; display: grid; grid-template-columns: 1.2fr 1fr auto; gap: 20px; margin-bottom: 30px; padding: 16px; background: rgba(5,11,28,.56); border: 1px solid rgba(255,255,255,.12); align-items: end; }
.filters label { display: grid; gap: 10px; }
.filters span { color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
input, select { width: 100%; padding: 12px 14px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.16); font: inherit; }
.view-toggle { display: flex; gap: 0; }
.view-toggle button { padding: 12px 16px; background: #08142d; color: rgba(255,255,255,.7); border: 1px solid rgba(255,255,255,.16); font: inherit; font-weight: 1000; letter-spacing: .1em; text-transform: uppercase; font-size: 11px; cursor: pointer; }
.view-toggle button.active { background: #F6C64A; color: #071126; box-shadow: 5px 5px 0 rgba(240,90,40,.35); }
.participant-list { position: relative; z-index: 1; display: grid; gap: 0; border-top: 1px solid rgba(255,255,255,.12); }
.participant-row { display: grid; grid-template-columns: 70px 1.2fr 110px 1fr; gap: 18px; align-items: center; min-height: 78px; padding: 16px 20px; background: linear-gradient(90deg, rgba(255,255,255,.045), rgba(255,255,255,.02)); border-bottom: 1px solid rgba(255,255,255,.1); border-left: 5px solid #36C2F0; }
.code { color: #071126; background: #F6C64A; width: fit-content; padding: 6px 9px; font-size: 12px; font-weight: 1000; box-shadow: 5px 5px 0 rgba(240,90,40,.35); }
.main { display: grid; gap: 6px; }
.main strong { font-size: 16px; }
.main small, .addr, .area { color: rgba(255,255,255,.62); font-size: 12px; font-weight: 800; line-height: 1.5; }
.main small.pd { color: #36C2F0; letter-spacing: .06em; }
.area { color: #F6C64A; font-weight: 1000; }
.empty { text-align: center; padding: 40px; color: rgba(255,255,255,.5); }
.pd-list { position: relative; z-index: 1; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; }
.pd-card { padding: 16px; background: #071126; border: 1px solid rgba(255,255,255,.12); border-left: 5px solid #F6C64A; box-shadow: 6px 6px 0 rgba(54,194,240,.13); }
.pd-card header { display: grid; gap: 4px; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,.1); }
.pd-card header strong { color: #F6C64A; letter-spacing: .06em; }
.pd-card header small { color: rgba(255,255,255,.6); font-weight: 800; }
.pd-card ul { list-style: none; padding: 0; margin: 0; display: grid; gap: 6px; }
.pd-card li { display: grid; grid-template-columns: 1fr auto; gap: 8px; padding: 8px 10px; background: #08142d; border: 1px solid rgba(255,255,255,.08); font-size: 13px; }
.pd-card li span { color: rgba(255,255,255,.55); font-weight: 700; font-size: 12px; }
@media (max-width: 900px) { .filters, .participant-row { grid-template-columns: 1fr; } }
</style>
