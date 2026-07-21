<script setup>
import { router } from '@inertiajs/vue3';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

defineProps({ applications: Object, filters: Object });
const labels = { pending: 'Menunggu', revision_required: 'Perlu Perbaikan', verified: 'Terverifikasi', rejected: 'Ditolak' };
const verify = (id) => router.post(`/admin/committee-applications/${id}/verify`, {}, { preserveScroll: true });
const review = (id, action) => {
  const note = prompt(action === 'revision' ? 'Catatan perbaikan:' : 'Alasan penolakan:');
  if (note) router.post(`/admin/committee-applications/${id}/${action}`, { note }, { preserveScroll: true });
};
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head"><SectionTitle eyebrow="Akses Daerah" title="Verifikasi Pengurus Daerah" :meta="`${applications.total} pengajuan`" /></div>
    <AdminDataTable :paginator="applications" :filters="filters" item-label="pengajuan" search-placeholder="Cari PD, nama, atau email" :filter-options="[
      { value: 'pending', label: 'Menunggu' }, { value: 'revision_required', label: 'Perlu Perbaikan' },
      { value: 'verified', label: 'Terverifikasi' }, { value: 'rejected', label: 'Ditolak' },
    ]" v-slot="{ rows }">
      <table>
        <thead><tr><th>Pengurus Daerah</th><th>Penanggung Jawab</th><th>Kontak</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
        <tbody>
          <tr v-for="application in rows" :key="application.id">
            <td><div class="primary-cell"><strong>{{ application.committee }}</strong><small v-if="application.review_note">Catatan: {{ application.review_note }}</small></div></td>
            <td><div class="primary-cell"><strong>{{ application.name }}</strong><small>{{ application.position }}</small></div></td>
            <td><div class="primary-cell"><span>{{ application.email }}</span><small>{{ application.phone || '—' }}</small></div></td>
            <td><span :class="['status-badge', { success: application.status === 'verified', danger: application.status === 'rejected', info: application.status === 'revision_required' }]">{{ labels[application.status] }}</span></td>
            <td><div v-if="application.status !== 'verified'" class="row-actions"><button class="primary" @click="verify(application.id)">Verifikasi</button><button @click="review(application.id, 'revision')">Perbaikan</button><button class="danger" @click="review(application.id, 'reject')">Tolak</button></div></td>
          </tr>
          <tr v-if="!rows.length" class="empty-row"><td colspan="5">Tidak ada pengajuan sesuai filter.</td></tr>
        </tbody>
      </table>
    </AdminDataTable>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
</style>
