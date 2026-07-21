<script setup>
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

const props = defineProps({
  committee: Object,
  events: Array,
});

const q = ref('');
const filtered = computed(() => props.events.filter((e) =>
  [e.name, e.sport, e.category].filter(Boolean).join(' ').toLowerCase().includes(q.value.toLowerCase())
));
const totalEntries = computed(() => props.events.reduce((a, e) => a + e.entries.verified + e.entries.pending, 0));
const statusLabel = (status) => ({
  registration_open: 'Pendaftaran Dibuka',
  registration_closed: 'Pendaftaran Ditutup',
  bracket_locked: 'Bracket Dikunci',
  ongoing: 'Sedang Berlangsung',
  completed: 'Selesai',
}[status] || status);
</script>

<template>
  <PortalLayout portal="pd">
    <div class="page-head">
      <SectionTitle eyebrow="Panel Pengurus Daerah" :title="committee.name" :meta="`${totalEntries} pendaftaran cabor`" />
    </div>

    <section class="dash-panel">
      <div class="summary">
        <div class="pill">
          <span>Provinsi</span>
          <b>{{ committee.province || '—' }}</b>
        </div>
        <div class="pill">
          <span>Cabor Tersedia</span>
          <b>{{ events.length }}</b>
        </div>
        <div class="pill">
          <span>Total Pendaftaran</span>
          <b>{{ totalEntries }}</b>
        </div>
      </div>

      <div class="filters">
        <label>
          <span>Cari Event</span>
          <input v-model="q" type="search" placeholder="Cabor, kategori, atau nama event" />
        </label>
      </div>

      <div class="event-list">
        <div v-for="ev in filtered" :key="ev.code" class="event-row">
          <div class="event-main">
            <strong>{{ ev.name }}</strong>
            <small>{{ ev.sport }} · {{ ev.category || 'Umum' }} · {{ ev.member_limit }} · <em>{{ statusLabel(ev.status) }}</em></small>
          </div>
          <div class="event-counts">
            <span class="tag verified">Terverifikasi: {{ ev.entries.verified }}</span>
            <span class="tag pending">Menunggu: {{ ev.entries.pending }}</span>
            <span v-if="ev.entries.rejected" class="tag rejected">Ditolak: {{ ev.entries.rejected }}</span>
          </div>
          <Link :href="`/pd/events/${ev.code}`" class="event-cta">{{ ev.registration_open ? 'Daftar' : 'Lihat' }}</Link>
        </div>
        <p v-if="!filtered.length" class="empty">Tidak ada event sesuai filter.</p>
      </div>
    </section>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.dash-panel { display: grid; gap: 20px; padding: 20px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 10px 10px 0 rgba(54,194,240,.13); }
.summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
.pill { display: grid; gap: 6px; padding: 14px 18px; background: rgba(5,11,28,.6); border: 1px solid rgba(255,255,255,.12); }
.pill span { color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .14em; text-transform: uppercase; }
.pill b { font-size: 22px; color: #F6C64A; }
.filters label { display: grid; gap: 8px; }
.filters span { color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .14em; text-transform: uppercase; }
input, select { width: 100%; padding: 12px 14px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.16); font: inherit; }
.event-list { display: grid; gap: 0; border-top: 1px solid rgba(255,255,255,.12); }
.event-row { display: grid; grid-template-columns: 1.6fr auto auto; gap: 18px; align-items: center; min-height: 78px; padding: 14px 18px; background: linear-gradient(90deg, rgba(255,255,255,.045), rgba(255,255,255,.02)); border-bottom: 1px solid rgba(255,255,255,.1); border-left: 5px solid #36C2F0; }
.event-main { display: grid; gap: 4px; }
.event-main small { color: rgba(255,255,255,.62); font-weight: 700; }
.event-counts { display: flex; gap: 8px; flex-wrap: wrap; }
.tag { padding: 5px 10px; font-size: 11px; font-weight: 1000; letter-spacing: .1em; text-transform: uppercase; clip-path: polygon(8px 0,100% 0,calc(100% - 8px) 100%,0 100%); }
.tag.verified { background: rgba(54,194,240,.18); color: #36C2F0; }
.tag.pending { background: rgba(246,198,74,.22); color: #F6C64A; }
.tag.rejected { background: rgba(240,90,40,.22); color: #F05A28; }
.event-cta { padding: 10px 16px; background: #F6C64A; color: #071126; text-decoration: none; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; font-size: 12px; box-shadow: 5px 5px 0 rgba(240,90,40,.35); clip-path: polygon(10px 0,100% 0,calc(100% - 10px) 100%,0 100%); }
.empty { text-align: center; padding: 30px; color: rgba(255,255,255,.5); }
@media (max-width: 900px) { .event-row { grid-template-columns: 1fr; } }
</style>
