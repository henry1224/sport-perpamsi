<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

const props = defineProps({
  entries: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const q = ref('');
const filtered = computed(() => props.entries.filter((e) =>
  [e.display_name, e.pdam, e.committee, e.event].join(' ').toLowerCase().includes(q.value.toLowerCase())
));

const approve = (id) => router.post(`/admin/entries/${id}/verify`, {}, { preserveScroll: true });
const reject = (id) => {
  const note = prompt('Alasan penolakan (opsional):') ?? '';
  router.post(`/admin/entries/${id}/reject`, { note }, { preserveScroll: true });
};
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head">
      <SectionTitle eyebrow="Verifikasi Pendaftaran" title="Antrian Entry Menunggu" :meta="`${entries.length} pending`" />
    </div>

    <div v-if="flash.success" class="flash ok">{{ flash.success }}</div>

    <section class="verif-panel">
      <div class="filters">
        <label>
          <span>Cari</span>
          <input v-model="q" type="search" placeholder="PD, PDAM, event, nama peserta" />
        </label>
      </div>

      <div class="entry-list">
        <div v-for="e in filtered" :key="e.id" class="entry-row">
          <div class="entry-main">
            <strong>{{ e.display_name }}</strong>
            <small><b>{{ e.event }}</b> · {{ e.event_status }}</small>
            <small>{{ e.committee }}</small>
            <small>PDAM: {{ e.pdam }}<span v-if="e.city"> · {{ e.city }}</span></small>
            <small v-if="e.athlete_1">Atlet: {{ e.athlete_1 }}<span v-if="e.athlete_2"> & {{ e.athlete_2 }}</span></small>
            <small v-if="e.team_name">Tim: {{ e.team_name }}</small>
          </div>
          <div class="actions">
            <button class="ok" @click="approve(e.id)">Setujui</button>
            <button class="no" @click="reject(e.id)">Tolak</button>
          </div>
        </div>
        <p v-if="!filtered.length" class="empty">Tidak ada antrian pendaftaran.</p>
      </div>
    </section>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.flash { padding: 12px 16px; margin-bottom: 12px; font-weight: 800; background: rgba(54,194,240,.18); color: #36C2F0; border-left: 4px solid #36C2F0; }
.verif-panel { display: grid; gap: 18px; padding: 20px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 10px 10px 0 rgba(54,194,240,.13); }
.filters label { display: grid; gap: 8px; color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
input { width: 100%; padding: 12px 14px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.16); font: inherit; }
.entry-list { display: grid; gap: 10px; }
.entry-row { display: grid; grid-template-columns: 1fr auto; gap: 14px; align-items: center; padding: 14px 16px; background: #08142d; border: 1px solid rgba(255,255,255,.1); border-left: 5px solid #F6C64A; }
.entry-main { display: grid; gap: 4px; }
.entry-main small { color: rgba(255,255,255,.6); font-weight: 700; }
.actions { display: flex; gap: 8px; }
.actions button { padding: 10px 14px; border: 0; font-weight: 1000; letter-spacing: .1em; text-transform: uppercase; font-size: 11px; cursor: pointer; }
.actions .ok { background: #36C2F0; color: #071126; box-shadow: 5px 5px 0 rgba(54,194,240,.35); }
.actions .no { background: rgba(240,90,40,.2); color: #F05A28; }
.empty { text-align: center; padding: 30px; color: rgba(255,255,255,.5); }
@media (max-width: 700px) { .entry-row { grid-template-columns: 1fr; } }
</style>
