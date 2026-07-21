<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PortalLayout from '../Layouts/PortalLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';

const props = defineProps({ matches: Array, audit: Array });
const rows = ref(props.matches.map((m) => ({ ...m })));
const saving = ref(null);
const statusOptions = ['scheduled', 'live', 'final', 'verified', 'disputed'];
const complete = computed(() => rows.value.filter((r) => ['final', 'verified'].includes(r.status)).length);

const save = (row) => {
  saving.value = row.id;
  router.post('/admin/skor', row, {
    preserveScroll: true,
    onFinish: () => { saving.value = null; },
  });
};
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head">
      <SectionTitle eyebrow="Admin Score Desk" title="Input Hasil Pertandingan" :meta="`${complete} selesai`" />
    </div>

    <section class="score-board">
      <div class="board-note">
        <div><strong>Meja Skor</strong><span>Input setelah pertandingan selesai. Setiap penyimpanan otomatis masuk audit.</span></div>
        <b>{{ rows.length }} pertandingan</b>
      </div>

      <div class="score-list">
        <div v-for="row in rows" :key="row.id" class="score-row">
          <div class="meta-col">
            <span>{{ row.sport }}</span>
            <small>{{ [row.venue, row.time].filter(Boolean).join(' · ') }}</small>
          </div>
          <input v-model="row.team_a" aria-label="Tim A" readonly />
          <input v-model="row.score" class="score-input" aria-label="Skor" />
          <input v-model="row.team_b" aria-label="Tim B" readonly />
          <select v-model="row.status" aria-label="Status">
            <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
          </select>
          <button type="button" :disabled="saving === row.id" @click="save(row)">{{ saving === row.id ? 'Menyimpan' : 'Simpan' }}</button>
        </div>
      </div>
    </section>

    <section class="audit-board">
      <header><div><span>Audit Trail</span><h2>Riwayat Perubahan</h2></div><b>{{ audit.length }} log</b></header>
      <div class="audit-list">
        <div v-for="item in audit.slice(0, 12)" :key="`${item.match_id}-${item.at}`" class="audit-row">
          <strong>{{ item.match_id }}</strong>
          <span>{{ item.before?.score || '—' }} → {{ item.after?.score || '—' }}</span>
          <small>{{ item.at }}</small>
        </div>
      </div>
    </section>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 8px 0 24px; }
.score-board, .audit-board { overflow: hidden; background: #fff; border: 1px solid #d9e3e9; border-radius: 14px; box-shadow: 0 8px 24px rgba(25,53,76,.07); }
.board-note,.audit-board > header { display:flex; align-items:center; justify-content:space-between; gap:18px; padding:18px 20px; background:#fbfcfd; border-bottom:1px solid #e2e9ed; }
.board-note > div { display:grid; gap:5px; }.board-note strong,.audit-board header span { color:#1946a3; font-size:10px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }.board-note span { color:#687985; font-size:12px; }.board-note > b,.audit-board header > b { padding:6px 9px; color:#536571; background:#edf2f5; border-radius:999px; font-size:10px; }
.score-list, .audit-list { display: grid; gap: 0; }
.score-row { display: grid; grid-template-columns: 170px 1fr 100px 1fr 145px 100px; gap: 10px; align-items: center; padding: 13px 16px; border-bottom: 1px solid #e7edf0; transition:background .15s; }.score-row:hover { background:#fbfdfe; }
.meta-col { display: grid; gap: 4px; }
.meta-col span { color: #1946a3; font-size: 10px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
.meta-col small, .audit-row small { color: #7a8993; font-size: 11px; }
input, select { width: 100%; padding: 10px 11px; color: #243747; background: #fff; border: 1px solid #cbd7de; border-radius: 8px; font: inherit; outline:none; }
input:focus,select:focus { border-color:#2a68b7; box-shadow:0 0 0 3px rgba(42,104,183,.11); }
input[readonly] { color: #536571; background: #f7f9fa; }
.score-input { color: #1946a3; font-size: 18px; font-weight: 800; text-align: center; }
button { padding: 10px 13px; color: #fff; background: #1946a3; border: 0; border-radius:8px; font-size: 11px; font-weight: 800; cursor: pointer; }
button:disabled { opacity: .55; cursor: wait; }
.audit-board { margin-top: 24px; }.audit-board h2 { margin:4px 0 0; color:#142536; font-size:17px; }
.audit-row { display: grid; grid-template-columns: 90px 1fr auto; gap: 12px; align-items: center; padding: 13px 20px; border-bottom: 1px solid #e7edf0; }
.audit-row strong { color: #1946a3; }.audit-row span { color: #405361; }
@media (max-width: 1100px) { .score-row, .audit-row { grid-template-columns: 1fr; } }
</style>
