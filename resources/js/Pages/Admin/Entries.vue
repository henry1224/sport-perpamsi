<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

defineProps({ entries: Object, filters: Object });

const page = usePage();
const flash = computed(() => page.props.flash || {});
const approve = (id) => router.post(`/admin/entries/${id}/verify`, {}, { preserveScroll: true });
const reject = (id) => {
  const note = prompt('Alasan penolakan:');
  if (!note?.trim()) return;
  router.post(`/admin/entries/${id}/reject`, { note }, { preserveScroll: true });
};
const requestRevision = (id) => {
  const note = prompt('Catatan perbaikan roster:');
  if (!note?.trim()) return;
  router.post(`/admin/entries/${id}/revision`, { note }, { preserveScroll: true });
};
const eventStatus = (status) => ({
  registration_draft: 'Draft', registration_open: 'Pendaftaran Dibuka', registration_closed: 'Pendaftaran Ditutup',
  bracket_locked: 'Bracket Dikunci', ongoing: 'Sedang Berlangsung', completed: 'Selesai',
}[status] || status);
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head">
      <SectionTitle eyebrow="Verifikasi Pendaftaran" title="Antrian Peserta" :meta="`${entries.total} menunggu`" />
    </div>
    <div v-if="flash.success" class="flash">{{ flash.success }}</div>

    <AdminDataTable :paginator="entries" :filters="filters" item-label="pendaftaran" search-placeholder="Cari PD, kompetisi, atau pemain" :filter-options="[
      { value: 'registration_open', label: 'Pendaftaran Dibuka' },
      { value: 'registration_closed', label: 'Pendaftaran Ditutup' },
      { value: 'bracket_locked', label: 'Bracket Dikunci' },
    ]" v-slot="{ rows }">
      <table>
        <thead><tr><th>Kontingen</th><th>Kompetisi</th><th>Daftar Pemain</th><th>Status Event</th><th style="text-align:right">Aksi</th></tr></thead>
        <tbody>
          <tr v-for="entry in rows" :key="entry.id">
            <td><div class="primary-cell"><strong>{{ entry.display_name }}</strong><small>{{ entry.committee }}</small></div></td>
            <td><div class="primary-cell"><strong>{{ entry.event }}</strong><small>{{ entry.event_code }}</small></div></td>
            <td><ol class="members"><li v-for="member in entry.members" :key="member">{{ member }}</li></ol></td>
            <td><span class="status-badge info">{{ eventStatus(entry.event_status) }}</span></td>
            <td><div class="row-actions"><button class="primary" @click="approve(entry.id)">Setujui</button><button @click="requestRevision(entry.id)">Perbaikan</button><button class="danger" @click="reject(entry.id)">Tolak</button></div></td>
          </tr>
          <tr v-if="!rows.length" class="empty-row"><td colspan="5">Tidak ada pendaftaran sesuai filter.</td></tr>
        </tbody>
      </table>
    </AdminDataTable>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 8px 0 24px; }
.flash { margin-bottom: 14px; padding: 12px 16px; color: #087365; background: #eefaf6; border: 1px solid #b9e3d6; border-radius: 10px; font-weight: 750; }
.members { max-height: 104px; margin: 0; padding-left: 18px; overflow: auto; color: #526573; }
.members li + li { margin-top: 3px; }
</style>
