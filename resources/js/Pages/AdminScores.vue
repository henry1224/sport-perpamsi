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
        <strong>Alur panitia</strong>
        <span>Input setelah pertandingan selesai. Simpan otomatis membuat audit log.</span>
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
      <SectionTitle eyebrow="Audit Trail" title="Riwayat Perubahan" :meta="`${audit.length} log`" />
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
.page-head { padding: 44px 0 24px; }
.score-board, .audit-board { position: relative; overflow: hidden; padding: 20px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 10px 10px 0 rgba(54,194,240,.13); }
.score-board::before { content: "ADMIN"; position: absolute; right: 18px; top: -14px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.055); font-size: clamp(80px, 13vw, 170px); font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
.board-note { position: relative; z-index: 1; display: grid; gap: 4px; margin-bottom: 18px; padding: 14px 16px; background: rgba(5,11,28,.6); border-left: 5px solid #F6C64A; }
.board-note strong { color: #F6C64A; font-size: 12px; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; }
.board-note span { color: rgba(255,255,255,.66); font-size: 13px; }
.score-list, .audit-list { position: relative; z-index: 1; display: grid; gap: 0; border-top: 1px solid rgba(255,255,255,.12); }
.score-row { display: grid; grid-template-columns: 190px 1fr 110px 1fr 150px 110px; gap: 10px; align-items: center; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,.1); }
.meta-col { display: grid; gap: 4px; }
.meta-col span { color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; }
.meta-col small, .audit-row small { color: rgba(255,255,255,.54); font-size: 11px; }
input, select { width: 100%; padding: 11px 12px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.14); font: inherit; }
input[readonly] { color: rgba(255,255,255,.72); background: rgba(8,20,45,.58); }
.score-input { color: #F6C64A; font-size: 20px; font-weight: 1000; text-align: center; }
button { padding: 11px 14px; color: #071126; background: #F6C64A; border: 0; font-size: 12px; font-weight: 1000; letter-spacing: .1em; text-transform: uppercase; cursor: pointer; box-shadow: 5px 5px 0 rgba(240,90,40,.35); }
button:disabled { opacity: .55; cursor: wait; }
.audit-board { margin-top: 34px; }
.audit-row { display: grid; grid-template-columns: 90px 1fr auto; gap: 12px; align-items: center; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,.08); }
.audit-row strong { color: #F6C64A; }
.audit-row span { color: rgba(255,255,255,.78); }
@media (max-width: 1100px) { .score-row, .audit-row { grid-template-columns: 1fr; } }
</style>
