<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

const props = defineProps({ events: Object, filters: Object });
const rows = ref(props.events.data.map((event) => ({ ...event })));
const busy = ref(null);
watch(() => props.events.data, (events) => { rows.value = events.map((event) => ({ ...event })); });
const statusLabel = (status) => ({
  registration_draft: 'Draft', registration_open: 'Pendaftaran Dibuka', registration_closed: 'Pendaftaran Ditutup',
  bracket_locked: 'Bracket Dikunci', ongoing: 'Sedang Berlangsung', completed: 'Selesai', archived: 'Diarsipkan',
}[status] || status);
const statusTone = (status) => ({ registration_open: 'success', registration_closed: 'danger', registration_draft: '', bracket_locked: 'info' }[status] || 'info');

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
      <SectionTitle eyebrow="Master Kompetisi" title="Publikasi Registrasi" :meta="`${events.total} paket kompetisi`" />
    </div>
    <p class="notice">PD hanya melihat kompetisi yang dipublikasikan. Publikasi mengunci kategori, format, tipe skor, dan batas pemain.</p>

    <AdminDataTable :paginator="{ ...events, data: rows }" :filters="filters" item-label="kompetisi" search-placeholder="Cari cabor, kategori, kode, atau kompetisi" :filter-options="[
      { value: 'registration_draft', label: 'Draft' }, { value: 'registration_open', label: 'Pendaftaran Dibuka' },
      { value: 'registration_closed', label: 'Pendaftaran Ditutup' }, { value: 'bracket_locked', label: 'Bracket Dikunci' },
      { value: 'ongoing', label: 'Sedang Berlangsung' }, { value: 'completed', label: 'Selesai' },
    ]" v-slot="{ rows: pageRows }">
      <table>
        <thead><tr><th>Kompetisi</th><th>Status</th><th>Periode Registrasi</th><th>Peserta</th><th style="text-align:right">Aksi</th></tr></thead>
        <tbody>
          <tr v-for="event in pageRows" :key="event.code">
            <td><div class="primary-cell"><strong>{{ event.name }}</strong><small>{{ event.sport }} · {{ event.category || 'Kategori belum ditetapkan' }} · {{ event.code }}</small></div></td>
            <td><span :class="['status-badge', statusTone(event.status)]">{{ statusLabel(event.status) }}</span></td>
            <td><div class="period-fields"><input v-model="event.open_at" type="datetime-local" aria-label="Waktu buka" :disabled="event.entries_count > 0" /><span>hingga</span><input v-model="event.close_at" type="datetime-local" aria-label="Waktu tutup" :disabled="event.entries_count > 0" /></div></td>
            <td><div class="count-cell"><strong>{{ event.entries_count }}</strong><small>pendaftaran</small></div></td>
            <td><div class="row-actions"><button v-if="!event.published || event.entries_count === 0" class="primary" type="button" :disabled="busy === event.code || !event.open_at || !event.close_at" @click="publish(event)">{{ event.published ? 'Publish Ulang' : 'Publish' }}</button><button v-if="event.published && event.status === 'registration_open'" class="danger" type="button" :disabled="busy === event.code" @click="close(event)">Tutup</button></div></td>
          </tr>
          <tr v-if="!pageRows.length" class="empty-row"><td colspan="5">Tidak ada kompetisi sesuai filter.</td></tr>
        </tbody>
      </table>
    </AdminDataTable>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 8px 0 24px; }
.notice { margin: 0 0 16px; padding: 14px 16px; color: #655000; background: #fff9e8; border: 1px solid #eedb94; border-radius: 10px; font-size: 13px; font-weight: 750; line-height:1.55; }
.period-fields { display: grid; grid-template-columns: minmax(165px,1fr) auto minmax(165px,1fr); gap: 8px; align-items: center; }
.period-fields span { color: #82909a; font-size: 11px; }
.period-fields input { min-height: 36px; padding: 7px 8px; color: #334553; background: #fff; border: 1px solid #cbd8df; border-radius:7px; color-scheme: light; outline:none; }.period-fields input:focus { border-color:#2a68b7; box-shadow:0 0 0 3px rgba(42,104,183,.11); }
.count-cell { display: grid; gap: 2px; }
.count-cell strong { color: #1946a3; font-size: 22px; }
.count-cell small { color: #738390; }
</style>
