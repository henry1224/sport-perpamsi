<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

const props = defineProps({ events: Array });
const rows = ref(props.events.map((event) => ({ ...event })));
const busy = ref(null);
const statusLabel = (status) => ({
  registration_draft: 'Draft',
  registration_open: 'Pendaftaran Dibuka',
  registration_closed: 'Pendaftaran Ditutup',
  bracket_locked: 'Bracket Dikunci',
  ongoing: 'Sedang Berlangsung',
  completed: 'Selesai',
  archived: 'Diarsipkan',
}[status] || status);

const publish = (event) => {
  busy.value = event.code;
  router.post(`/admin/events/${event.code}/publish`, {
    registration_open_at: event.open_at,
    registration_close_at: event.close_at,
  }, { preserveScroll: true, onFinish: () => { busy.value = null; } });
};

const close = (event) => {
  if (!confirm(`Tutup registrasi ${event.name}?`)) return;
  busy.value = event.code;
  router.post(`/admin/events/${event.code}/close`, {}, { preserveScroll: true, onFinish: () => { busy.value = null; } });
};
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head">
      <SectionTitle eyebrow="Master Kompetisi" title="Publikasi Registrasi" :meta="`${events.length} paket kompetisi`" />
    </div>

    <section class="event-panel">
      <p class="notice">PD hanya melihat kompetisi yang dipublikasikan. Publikasi mengunci kategori, format, tipe skor, dan batas pemain.</p>
      <div class="event-list">
        <article v-for="event in rows" :key="event.code">
          <div class="identity">
            <span>{{ event.sport }} · {{ event.category || 'Kategori belum ditetapkan' }}</span>
            <strong>{{ event.name }}</strong>
            <small>{{ event.format }} · {{ event.entries_count }} pendaftaran · {{ statusLabel(event.status) }}</small>
          </div>
          <label><span>Buka</span><input v-model="event.open_at" type="datetime-local" :disabled="event.entries_count > 0" /></label>
          <label><span>Tutup</span><input v-model="event.close_at" type="datetime-local" :disabled="event.entries_count > 0" /></label>
          <div class="actions">
            <button v-if="!event.published || event.entries_count === 0" type="button" :disabled="busy === event.code || !event.open_at || !event.close_at" @click="publish(event)">
              {{ event.published ? 'Publikasikan Ulang' : 'Publikasikan' }}
            </button>
            <button v-if="event.published && event.status === 'registration_open'" type="button" class="close" :disabled="busy === event.code" @click="close(event)">Tutup</button>
            <b v-if="event.published">Terpublikasi</b>
          </div>
        </article>
      </div>
    </section>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.event-panel { padding: 20px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 10px 10px 0 rgba(54,194,240,.13); }
.notice { margin: 0 0 18px; padding: 14px 16px; color: #f6c64a; background: rgba(246,198,74,.1); border-left: 5px solid #f6c64a; font-weight: 800; }
.event-list { display: grid; gap: 10px; }
article { display: grid; grid-template-columns: minmax(260px,1.5fr) 210px 210px 180px; gap: 14px; align-items: end; padding: 16px; background: #08142d; border: 1px solid rgba(255,255,255,.1); }
.identity { display: grid; gap: 4px; }
.identity span, label span { color: #36c2f0; font-size: 10px; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; }
.identity strong { color: #fff; }
.identity small { color: rgba(255,255,255,.55); }
label { display: grid; gap: 7px; }
input { width: 100%; padding: 10px; color: #fff; background: #071126; border: 1px solid rgba(255,255,255,.16); color-scheme: dark; }
.actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
button { padding: 10px 12px; color: #071126; background: #f6c64a; border: 0; font-weight: 1000; cursor: pointer; }
button.close { color: #f05a28; background: rgba(240,90,40,.15); border: 1px solid rgba(240,90,40,.4); }
button:disabled { opacity: .5; cursor: not-allowed; }
.actions b { color: #36c2f0; font-size: 11px; text-transform: uppercase; }
@media (max-width: 1100px) { article { grid-template-columns: 1fr; } }
</style>
