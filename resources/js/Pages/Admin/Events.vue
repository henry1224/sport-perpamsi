<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import { formatDateTime } from '../../lib/date';

const props = defineProps({ events: Object, filters: Object, audits: Array });
const rows = ref(props.events.data.map((event) => ({ ...event })));
const busy = ref(null);
const previewing = ref(null);
watch(() => props.events.data, (events) => { rows.value = events.map((event) => ({ ...event })); });
const statusLabel = (status) => ({
  registration_draft: 'Draft', registration_open: 'Pendaftaran Dibuka', registration_closed: 'Pendaftaran Ditutup',
  bracket_locked: 'Bracket Dikunci', ongoing: 'Sedang Berlangsung', completed: 'Selesai', archived: 'Diarsipkan',
}[status] || status);
const statusTone = (status) => ({ registration_open: 'success', registration_closed: 'danger', registration_draft: '', bracket_locked: 'info' }[status] || 'info');
const formatOptions = (event) => [...new Set([event.default_format, event.format, 'knockout', 'group', 'group_then_knockout', 'round_robin', 'ranking'].filter(Boolean))];
const formatLabel = (format) => format?.replaceAll('_', ' ') || 'Belum ditetapkan';

const updateFormat = (event) => {
  busy.value = event.code;
  router.put(`/admin/events/${event.code}/format`, { format: event.format }, { preserveScroll: true, onFinish: () => { busy.value = null; } });
};

const publish = (event) => {
  busy.value = event.code;
  router.post(`/admin/events/${event.code}/publish`, {
    registration_open_at: event.open_at,
    registration_close_at: event.close_at,
    sport_regulation_id: event.regulation_id,
  }, { preserveScroll: true, onFinish: () => { busy.value = null; } });
};
const unpublish = (event) => {
  if (!confirm(`Tarik publikasi ${event.name}? Kompetisi kembali menjadi draft.`)) return;
  busy.value = event.code;
  router.post(`/admin/events/${event.code}/unpublish`, {}, { preserveScroll: true, onFinish: () => { busy.value = null; } });
};
const selectedRegulation = (event) => event.regulations.find((item) => item.id === Number(event.regulation_id));
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
    <p class="notice">PD hanya melihat kompetisi terpublikasi. Pilih versi regulasi, periksa preview snapshot, lalu publish.</p>

    <AdminDataTable :paginator="{ ...events, data: rows }" :filters="filters" item-label="kompetisi" search-placeholder="Cari cabor, kategori, kode, atau kompetisi" :filter-options="[
      { value: 'registration_draft', label: 'Draft' }, { value: 'registration_open', label: 'Pendaftaran Dibuka' },
      { value: 'registration_closed', label: 'Pendaftaran Ditutup' }, { value: 'bracket_locked', label: 'Bracket Dikunci' },
      { value: 'ongoing', label: 'Sedang Berlangsung' }, { value: 'completed', label: 'Selesai' },
    ]" v-slot="{ rows: pageRows }">
      <table>
        <thead><tr><th>Kompetisi</th><th>Format Kompetisi</th><th>Regulasi</th><th>Status</th><th>Periode Registrasi</th><th>Peserta</th><th style="text-align:right">Aksi</th></tr></thead>
        <tbody>
          <template v-for="event in pageRows" :key="event.code">
          <tr>
            <td><div class="primary-cell"><strong>{{ event.name }}</strong><small>{{ event.sport }} · {{ event.category || 'Kategori belum ditetapkan' }} · {{ event.code }}</small></div></td>
            <td><div v-if="!event.published && event.entries_count === 0" class="format-editor"><select v-model="event.format"><option v-for="format in formatOptions(event)" :key="format" :value="format">{{ formatLabel(format) }}</option></select><small>Bawaan: {{ formatLabel(event.default_format) }}</small><button type="button" :disabled="busy === event.code" @click="updateFormat(event)">Simpan</button></div><div v-else class="locked-format"><strong>{{ formatLabel(event.format) }}</strong><small>Terkunci setelah publikasi</small></div></td>
            <td><select v-model="event.regulation_id" class="regulation-select" :disabled="event.entries_count > 0"><option :value="null">Pilih regulasi</option><option v-for="item in event.regulations" :key="item.id" :value="item.id">{{ item.label }}</option></select></td>
            <td><span :class="['status-badge', statusTone(event.status)]">{{ statusLabel(event.status) }}</span></td>
            <td><div class="period-fields"><input v-model="event.open_at" type="datetime-local" aria-label="Waktu buka" :disabled="event.entries_count > 0" /><span>hingga</span><input v-model="event.close_at" type="datetime-local" aria-label="Waktu tutup" :disabled="event.entries_count > 0" /></div></td>
            <td><div class="count-cell"><strong>{{ event.entries_count }}</strong><small>pendaftaran</small></div></td>
            <td><div class="row-actions"><button type="button" :disabled="!event.regulation_id" @click="previewing = previewing === event.code ? null : event.code">{{ previewing === event.code ? 'Tutup Preview' : 'Preview' }}</button><button v-if="event.published && event.status === 'registration_open'" class="danger" type="button" :disabled="busy === event.code" @click="close(event)">Tutup</button><button v-if="event.published && event.entries_count === 0" class="danger" type="button" :disabled="busy === event.code" @click="unpublish(event)">Tarik</button></div></td>
          </tr>
          <tr v-if="previewing === event.code" class="preview-row"><td colspan="7"><div class="preview-card"><header><div><span>Preview Paket Registrasi</span><h3>{{ event.name }}</h3></div><span class="status-badge info">Belum dipublikasikan</span></header><dl><div><dt>Cabor</dt><dd>{{ event.sport }}</dd></div><div><dt>Kategori</dt><dd>{{ event.category }}</dd></div><div><dt>Format Kompetisi</dt><dd>{{ formatLabel(event.format) }}</dd></div><div><dt>Regulasi</dt><dd>{{ selectedRegulation(event)?.label }}</dd></div><div><dt>Periode</dt><dd>{{ event.open_at }} — {{ event.close_at }}</dd></div></dl><p>{{ selectedRegulation(event)?.content }}</p><footer><a v-if="selectedRegulation(event)?.document_url" :href="selectedRegulation(event).document_url" target="_blank" rel="noopener">Buka Dokumen Regulasi ↗</a><button class="primary" type="button" :disabled="busy === event.code || !event.open_at || !event.close_at" @click="publish(event)">{{ event.published ? 'Publish Ulang Paket' : 'Publish Paket' }}</button></footer></div></td></tr>
          </template>
          <tr v-if="!pageRows.length" class="empty-row"><td colspan="7">Tidak ada kompetisi sesuai filter.</td></tr>
        </tbody>
      </table>
    </AdminDataTable>

    <section class="audit-card"><header><div><span>Audit Publikasi</span><h2>Aktivitas Kompetisi</h2></div><b>{{ audits.length }} aktivitas terbaru</b></header><div class="audit-list"><article v-for="item in audits" :key="item.id"><div><strong>{{ item.event }}</strong><small>{{ formatDateTime(item.created_at) }}</small></div><span>{{ { published: 'Dipublikasikan', republished: 'Dipublikasikan Ulang', closed: 'Ditutup', unpublished: 'Ditarik' }[item.action] || item.action }}</span></article><p v-if="!audits.length">Belum ada aktivitas publikasi.</p></div></section>
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
.regulation-select { min-width:220px; min-height:36px; padding:7px 9px; color:#334553; background:#fff; border:1px solid #cbd8df; border-radius:7px; }.preview-row td { padding:0 !important; background:#f7fafc; }.preview-card { margin:16px; padding:20px; background:#fff; border:1px solid #cad9e2; border-radius:12px; box-shadow:0 8px 24px rgba(25,53,76,.07); }.preview-card header,.preview-card footer { display:flex; justify-content:space-between; align-items:center; gap:16px; }.preview-card header span:first-child,.audit-card header span { color:#1946a3; font-size:10px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }.preview-card h3,.audit-card h2 { margin:4px 0 0; color:#142536; }.preview-card dl { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; margin:18px 0; }.preview-card dl div { padding:12px; background:#f7f9fa; border-radius:8px; }.preview-card dt { color:#71808b; font-size:10px; text-transform:uppercase; }.preview-card dd { margin:5px 0 0; color:#243747; font-size:12px; font-weight:750; }.preview-card p { color:#536571; line-height:1.6; }.preview-card footer { padding-top:16px; border-top:1px solid #e5ecef; }.preview-card footer a { color:#1946a3; font-size:12px; font-weight:800; text-decoration:none; }.audit-card { margin-top:22px; overflow:hidden; background:#fff; border:1px solid #d9e3e9; border-radius:14px; box-shadow:0 8px 24px rgba(25,53,76,.07); }.audit-card > header { display:flex; justify-content:space-between; align-items:center; padding:18px 20px; background:#fbfcfd; border-bottom:1px solid #e2e9ed; }.audit-card header > b { padding:6px 9px; color:#536571; background:#edf2f5; border-radius:999px; font-size:10px; }.audit-list article { display:flex; justify-content:space-between; gap:16px; padding:13px 20px; border-bottom:1px solid #e7edf0; }.audit-list article div { display:grid; gap:3px; }.audit-list small { color:#7a8993; }.audit-list article > span { color:#1946a3; font-size:11px; font-weight:800; }.audit-list > p { padding:20px; color:#7a8993; }@media(max-width:1000px){.preview-card dl{grid-template-columns:repeat(2,1fr)}}
.format-editor,.locked-format { display:grid; gap:5px; min-width:170px; }.format-editor select { min-height:36px; padding:7px 9px; color:#334553; background:#fff; border:1px solid #cbd8df; border-radius:7px; text-transform:capitalize; }.format-editor small,.locked-format small { color:#7a8993; font-size:10px; }.format-editor button { width:fit-content; padding:6px 9px; color:#1946a3; background:#eff5fb; border:1px solid #bfd0dc; border-radius:6px; font-size:10px; font-weight:800; cursor:pointer; }.locked-format strong { color:#334553; font-size:12px; text-transform:capitalize; }
</style>
