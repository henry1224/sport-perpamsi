<script setup>
import { computed, ref } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';

const props = defineProps({ pdams: Array, provinces: Array });
const q = ref('');
const province = ref('all');
const filtered = computed(() => props.pdams.filter((p) =>
  (province.value === 'all' || p.province_code === province.value) &&
  [p.name, p.city, p.province].join(' ').toLowerCase().includes(q.value.toLowerCase())
));
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Participant Directory" title="Data PDAM Seluruh Indonesia" :meta="`${filtered.length} peserta`" />
    </div>

    <section class="participant-board">
      <div class="filters">
        <label>
          <span>Cari</span>
          <input v-model="q" type="search" placeholder="Cari PDAM, kota, provinsi" />
        </label>
        <label>
          <span>Provinsi</span>
          <select v-model="province">
            <option value="all">Semua Provinsi</option>
            <option v-for="p in provinces" :key="p.code" :value="p.code">{{ p.name }}</option>
          </select>
        </label>
      </div>

      <div class="participant-list">
        <div v-for="p in filtered" :key="p.code" class="participant-row">
          <div class="code">{{ p.province_code }}</div>
          <div class="main">
            <strong>{{ p.name }}</strong>
            <small>{{ p.city }} · {{ p.province }}</small>
          </div>
          <div class="area">{{ p.regency_code }}</div>
          <div class="addr">{{ p.address }}</div>
        </div>
        <p v-if="!filtered.length" class="empty">Tidak ada data sesuai filter.</p>
      </div>
    </section>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.participant-board { position: relative; overflow: hidden; }
.participant-board::before { content: "PDAM"; position: absolute; right: 18px; top: 8px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.055); font-size: clamp(80px, 13vw, 170px); font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
.filters { position: relative; z-index: 1; display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; margin-bottom: 30px; padding: 16px; background: rgba(5,11,28,.56); border: 1px solid rgba(255,255,255,.12); }
.filters label { display: grid; gap: 10px; }
.filters span { color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
input, select { width: 100%; padding: 12px 14px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.16); font: inherit; }
.participant-list { position: relative; z-index: 1; display: grid; gap: 0; border-top: 1px solid rgba(255,255,255,.12); }
.participant-row { display: grid; grid-template-columns: 70px 1.2fr 110px 1fr; gap: 18px; align-items: center; min-height: 78px; padding: 16px 20px; background: linear-gradient(90deg, rgba(255,255,255,.045), rgba(255,255,255,.02)); border-bottom: 1px solid rgba(255,255,255,.1); border-left: 5px solid #36C2F0; }
.code { color: #071126; background: #F6C64A; width: fit-content; padding: 6px 9px; font-size: 12px; font-weight: 1000; box-shadow: 5px 5px 0 rgba(240,90,40,.35); }
.main { display: grid; gap: 6px; }
.main strong { font-size: 16px; }
.main small, .addr, .area { color: rgba(255,255,255,.62); font-size: 12px; font-weight: 800; line-height: 1.5; }
.area { color: #F6C64A; font-weight: 1000; }
.empty { text-align: center; padding: 40px; color: rgba(255,255,255,.5); }
@media (max-width: 900px) { .filters, .participant-row { grid-template-columns: 1fr; } }
</style>
