<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

const props = defineProps({ applications: Array });
const q = ref('');
const filtered = computed(() => props.applications.filter((application) =>
  [application.committee, application.name, application.email, application.status].join(' ').toLowerCase().includes(q.value.toLowerCase())
));
const labels = { pending: 'Menunggu', revision_required: 'Perlu Perbaikan', verified: 'Terverifikasi', rejected: 'Ditolak' };
const verify = (id) => router.post(`/admin/committee-applications/${id}/verify`, {}, { preserveScroll: true });
const review = (id, action) => {
  const note = prompt(action === 'revision' ? 'Catatan perbaikan:' : 'Alasan penolakan:');
  if (note) router.post(`/admin/committee-applications/${id}/${action}`, { note }, { preserveScroll: true });
};
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head"><SectionTitle eyebrow="Akses Daerah" title="Verifikasi Pengurus Daerah" :meta="`${applications.length} pengajuan`" /></div>
    <section class="panel">
      <input v-model="q" type="search" placeholder="Cari PD, nama, email, atau status" />
      <article v-for="application in filtered" :key="application.id">
        <div>
          <span>{{ application.committee }}</span>
          <h3>{{ application.name }}</h3>
          <p>{{ application.position }} · {{ application.email }} · {{ application.phone }}</p>
          <p v-if="application.review_note">Catatan: {{ application.review_note }}</p>
        </div>
        <div class="review">
          <b :class="application.status">{{ labels[application.status] }}</b>
          <div v-if="application.status !== 'verified'" class="actions">
            <button class="yes" @click="verify(application.id)">Verifikasi</button>
            <button @click="review(application.id, 'revision')">Perbaikan</button>
            <button class="no" @click="review(application.id, 'reject')">Tolak</button>
          </div>
        </div>
      </article>
      <p v-if="!filtered.length" class="empty">Tidak ada pengajuan sesuai pencarian.</p>
    </section>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.panel { display: grid; gap: 12px; padding: 20px; background: #071126; box-shadow: 10px 10px 0 rgba(25,70,163,.14); }
input { padding: 13px 14px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.16); font: inherit; }
article { display: grid; grid-template-columns: 1fr auto; gap: 20px; padding: 18px; background: #fff; color: #152331; border-left: 6px solid #36c2f0; }
article span { color: #1946a3; font-size: 11px; font-weight: 1000; letter-spacing: .1em; text-transform: uppercase; }
h3 { margin: 5px 0; font-size: 20px; }
p { margin: 3px 0; color: #687985; font-size: 13px; }
.review { display: grid; justify-items: end; gap: 14px; }
.review > b { padding: 6px 9px; color: #8a6200; background: #fff2c8; font-size: 11px; text-transform: uppercase; }
.review > b.verified { color: #087365; background: #dff7f2; }
.review > b.rejected { color: #a13d24; background: #ffe7df; }
.actions { display: flex; gap: 7px; }
button { padding: 9px 11px; border: 1px solid #cbd8df; background: #eef3f6; color: #1946a3; font-weight: 900; cursor: pointer; }
button.yes { color: #071126; background: #36c2f0; border-color: #36c2f0; }
button.no { color: #a13d24; background: #ffe7df; border-color: #ffd0c1; }
.empty { padding: 30px; text-align: center; color: rgba(255,255,255,.55); }
@media (max-width: 800px) { article { grid-template-columns: 1fr; } .review { justify-items: start; } .actions { flex-wrap: wrap; } }
</style>
