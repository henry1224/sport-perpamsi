<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import Modal from '../../Components/Modal.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import { formatDateTime } from '../../lib/date';
import { statusLabel } from '../../lib/status';

const props = defineProps({ events: Object, filters: Object, audits: Array, sportFormats: Object, sports: Array });
const rows = ref(props.events.data.map((event) => ({ ...event, max_teams_per_pd: event.rules?.max_teams_per_pd || 1 })));
const busy = ref(null);
const previewing = ref(null);
watch(() => props.events.data, (events) => { rows.value = events.map((event) => ({ ...event, max_teams_per_pd: event.rules?.max_teams_per_pd || 1 })); });
const statusTone = (status) => ({ registration_open: 'success', registration_closed: 'danger', registration_draft: '', bracket_locked: 'info' }[status] || 'info');
const formatOptions = (event) => [...new Set([event.default_format, event.format, ...Object.keys(props.sportFormats || {})].filter(Boolean))];
const formatLabel = (format) => props.sportFormats?.[format] || format?.replaceAll('_', ' ') || 'Belum ditetapkan';
const eventModal = ref(false);
const editingCode = ref(null);
const eventForm = useForm({ sport_id: '', sport_category_id: '', sport_regulation_id: '', code: '', name: '', format: 'knockout', max_teams_per_pd: 1, max_officials_per_pd: 0, official_roles: '', allow_member_cross_category: false, max_categories_per_member: '', official_can_compete: false });
const selectedSport = computed(() => props.sports.find((sport) => sport.id === Number(eventForm.sport_id)));
const selectedCategory = computed(() => selectedSport.value?.categories.find((category) => category.id === Number(eventForm.sport_category_id)));
const openEvent = (event = null) => {
  eventForm.reset(); eventForm.clearErrors(); editingCode.value = event?.code || null;
  if (event) Object.assign(eventForm, { sport_id: event.sport_id, sport_category_id: event.category_id, sport_regulation_id: event.regulation_id, code: event.code, name: event.name, format: event.format, max_teams_per_pd: event.rules?.max_teams_per_pd ?? 1, max_officials_per_pd: event.rules?.max_officials_per_pd ?? 0, official_roles: (event.rules?.official_roles || []).join(', '), allow_member_cross_category: event.rules?.allow_member_cross_category ?? false, max_categories_per_member: event.rules?.max_categories_per_member ?? '', official_can_compete: event.rules?.official_can_compete ?? false });
  eventModal.value = true;
};
const changeSport = () => { eventForm.sport_category_id = ''; eventForm.sport_regulation_id = ''; eventForm.format = selectedSport.value?.default_format || 'knockout'; eventForm.max_officials_per_pd = selectedSport.value?.default_max_officials_per_pd ?? 0; eventForm.official_roles = (selectedSport.value?.official_roles || []).join(', '); eventForm.allow_member_cross_category = selectedSport.value?.allow_member_cross_category ?? false; eventForm.max_categories_per_member = selectedSport.value?.max_categories_per_member ?? ''; eventForm.official_can_compete = selectedSport.value?.official_can_compete ?? false; };
const changeCategory = () => { eventForm.max_teams_per_pd = selectedCategory.value?.default_max_teams_per_pd ?? 1; };
const saveEvent = () => eventForm.transform((data) => ({ ...data, official_roles: data.official_roles.split(',').map((role) => role.trim()).filter(Boolean) })).submit(editingCode.value ? 'put' : 'post', editingCode.value ? `/admin/events/${editingCode.value}` : '/admin/events', { preserveScroll: true, onSuccess: () => { eventModal.value = false; editingCode.value = null; eventForm.reset(); } });
const deleteEvent = (event) => { if (confirm(`Hapus data lomba ${event.name}?`)) router.delete(`/admin/events/${event.code}`, { preserveScroll: true }); };

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
    max_teams_per_pd: event.max_teams_per_pd,
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
    <div class="page-head page-title-row">
      <SectionTitle eyebrow="Master Kompetisi" title="Publikasi Registrasi" :meta="`${events.total} paket kompetisi`" />
      <button type="button" class="primary create-button" @click="openEvent()">Tambah Data Lomba</button>
    </div>
    <p class="notice">PD hanya melihat kompetisi terpublikasi. Pilih versi regulasi, periksa preview snapshot, lalu publish.</p>

    <Modal :open="eventModal" :title="editingCode ? 'Edit Data Lomba' : 'Tambah Data Lomba'" theme="light" @close="eventModal = false">
      <form class="event-form" @submit.prevent="saveEvent"><div class="event-fields">
        <label>Cabor<select v-model="eventForm.sport_id" required @change="changeSport"><option value="">Pilih cabor</option><option v-for="sport in sports" :key="sport.id" :value="sport.id">{{ sport.name }}</option></select></label>
        <label>Kategori<select v-model="eventForm.sport_category_id" required @change="changeCategory"><option value="">Pilih kategori</option><option v-for="category in selectedSport?.categories || []" :key="category.id" :value="category.id">{{ category.name }}</option></select></label>
        <label>Kode<input v-model="eventForm.code" required placeholder="volleyball-mens-team" /></label>
        <label>Nama<input v-model="eventForm.name" required placeholder="Bola Voli Putra" /></label>
        <label>Format<select v-model="eventForm.format" required><option v-for="(label, value) in sportFormats" :key="value" :value="value">{{ label }}</option></select></label>
        <label>Regulasi<select v-model="eventForm.sport_regulation_id" required><option value="">Pilih regulasi</option><option v-for="regulation in selectedSport?.regulations || []" :key="regulation.id" :value="regulation.id">v{{ regulation.version }} · {{ regulation.title }}</option></select></label>
        <label>Maksimal Team per PD<input v-model.number="eventForm.max_teams_per_pd" type="number" min="1" max="16" required /></label>
        <label>Maksimal Official per PD<input v-model.number="eventForm.max_officials_per_pd" type="number" min="0" max="20" required /></label>
        <label>Peran Official<input v-model="eventForm.official_roles" placeholder="team_manager, coach" /></label>
        <label>Atlet Rangkap Kategori<select v-model="eventForm.allow_member_cross_category"><option :value="true">Boleh</option><option :value="false">Tidak boleh</option></select></label>
        <label>Maksimal Kategori per Atlet<input v-model.number="eventForm.max_categories_per_member" type="number" min="1" max="20" placeholder="Kosong = tanpa batas" /></label>
        <label>Official Boleh Bertanding<select v-model="eventForm.official_can_compete"><option :value="false">Tidak boleh</option><option :value="true">Boleh</option></select></label>
        <p v-for="message in eventForm.errors" :key="message" class="form-error">{{ message }}</p>
      </div><footer><button type="button" @click="eventModal = false">Batal</button><button class="primary" :disabled="eventForm.processing">{{ eventForm.processing ? 'Menyimpan…' : 'Simpan Data Lomba' }}</button></footer></form>
    </Modal>

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
            <td><div class="count-cell"><strong>{{ event.entries_count }}</strong><small>pendaftaran</small><label v-if="event.entries_count === 0" class="team-limit">Maks. tim/PD<input v-model.number="event.max_teams_per_pd" type="number" min="1" max="16" /></label><small v-else>{{ event.rules?.max_teams_per_pd || 1 }} tim/PD</small></div></td>
            <td><div class="row-actions"><button v-if="!event.published && event.entries_count === 0" type="button" @click="openEvent(event)">Edit</button><button type="button" :disabled="!event.regulation_id" @click="previewing = previewing === event.code ? null : event.code">{{ previewing === event.code ? 'Tutup Preview' : 'Preview' }}</button><button v-if="event.published && event.status === 'registration_open'" class="danger" type="button" :disabled="busy === event.code" @click="close(event)">Tutup</button><button v-if="event.published && event.entries_count === 0" class="danger" type="button" :disabled="busy === event.code" @click="unpublish(event)">Tarik</button><button v-if="!event.published && event.entries_count === 0" class="danger" type="button" @click="deleteEvent(event)">Hapus</button></div></td>
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
.page-head { padding: 8px 0 24px; }.page-title-row{display:flex;align-items:center;justify-content:space-between;gap:20px}.create-button{min-height:40px;padding:9px 14px;border:0;border-radius:8px;font-weight:800;cursor:pointer}
.notice { margin: 0 0 16px; padding: 14px 16px; color: #655000; background: #fff9e8; border: 1px solid #eedb94; border-radius: 10px; font-size: 13px; font-weight: 750; line-height:1.55; }
.period-fields { display: grid; grid-template-columns: minmax(165px,1fr) auto minmax(165px,1fr); gap: 8px; align-items: center; }
.period-fields span { color: #82909a; font-size: 11px; }
.period-fields input { min-height: 36px; padding: 7px 8px; color: #334553; background: #fff; border: 1px solid #cbd8df; border-radius:7px; color-scheme: light; outline:none; }.period-fields input:focus { border-color:#2a68b7; box-shadow:0 0 0 3px rgba(42,104,183,.11); }
.count-cell { display: grid; gap: 2px; }
.count-cell strong { color: #1946a3; font-size: 22px; }
.count-cell small { color: #738390; }
.team-limit{display:grid;gap:4px;margin-top:6px;color:#60717f;font-size:9px;font-weight:800;text-transform:uppercase}.team-limit input{width:76px;min-height:34px;padding:6px 8px;border:1px solid #cbd8df;border-radius:7px}
.regulation-select { min-width:220px; min-height:36px; padding:7px 9px; color:#334553; background:#fff; border:1px solid #cbd8df; border-radius:7px; }.preview-row td { padding:0 !important; background:#f7fafc; }.preview-card { margin:16px; padding:20px; background:#fff; border:1px solid #cad9e2; border-radius:12px; box-shadow:0 8px 24px rgba(25,53,76,.07); }.preview-card header,.preview-card footer { display:flex; justify-content:space-between; align-items:center; gap:16px; }.preview-card header span:first-child,.audit-card header span { color:#1946a3; font-size:10px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; }.preview-card h3,.audit-card h2 { margin:4px 0 0; color:#142536; }.preview-card dl { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; margin:18px 0; }.preview-card dl div { padding:12px; background:#f7f9fa; border-radius:8px; }.preview-card dt { color:#71808b; font-size:10px; text-transform:uppercase; }.preview-card dd { margin:5px 0 0; color:#243747; font-size:12px; font-weight:750; }.preview-card p { color:#536571; line-height:1.6; }.preview-card footer { padding-top:16px; border-top:1px solid #e5ecef; }.preview-card footer a { color:#1946a3; font-size:12px; font-weight:800; text-decoration:none; }.audit-card { margin-top:22px; overflow:hidden; background:#fff; border:1px solid #d9e3e9; border-radius:14px; box-shadow:0 8px 24px rgba(25,53,76,.07); }.audit-card > header { display:flex; justify-content:space-between; align-items:center; padding:18px 20px; background:#fbfcfd; border-bottom:1px solid #e2e9ed; }.audit-card header > b { padding:6px 9px; color:#536571; background:#edf2f5; border-radius:999px; font-size:10px; }.audit-list article { display:flex; justify-content:space-between; gap:16px; padding:13px 20px; border-bottom:1px solid #e7edf0; }.audit-list article div { display:grid; gap:3px; }.audit-list small { color:#7a8993; }.audit-list article > span { color:#1946a3; font-size:11px; font-weight:800; }.audit-list > p { padding:20px; color:#7a8993; }@media(max-width:1000px){.preview-card dl{grid-template-columns:repeat(2,1fr)}}
.format-editor,.locked-format { display:grid; gap:5px; min-width:170px; }.format-editor select { min-height:36px; padding:7px 9px; color:#334553; background:#fff; border:1px solid #cbd8df; border-radius:7px; text-transform:capitalize; }.format-editor small,.locked-format small { color:#7a8993; font-size:10px; }.format-editor button { width:fit-content; padding:6px 9px; color:#1946a3; background:#eff5fb; border:1px solid #bfd0dc; border-radius:6px; font-size:10px; font-weight:800; cursor:pointer; }.locked-format strong { color:#334553; font-size:12px; text-transform:capitalize; }
.event-form{min-width:min(720px,80vw)}.event-fields{display:grid;grid-template-columns:1fr 1fr;gap:14px;padding:20px}.event-fields label{display:grid;gap:6px;color:#60717f;font-size:11px;font-weight:800}.event-fields input,.event-fields select{min-height:42px;padding:10px 12px;border:1px solid #cbd8df;border-radius:8px}.event-form footer{display:flex;justify-content:flex-end;gap:9px;padding:0 20px 20px}.event-form footer button{min-height:40px;padding:9px 14px;border:1px solid #bfd0dc;border-radius:8px;cursor:pointer}.form-error{grid-column:1/-1;margin:0;color:#a1432e;font-size:11px;font-weight:700}@media(max-width:700px){.page-title-row{align-items:stretch;flex-direction:column}.event-fields{grid-template-columns:1fr}.event-form{min-width:0}}
</style>
