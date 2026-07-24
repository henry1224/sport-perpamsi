<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import Modal from '../../Components/Modal.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import { formatDateTime } from '../../lib/date';
import { statusLabel } from '../../lib/status';

const props = defineProps({ events: Object, filters: Object, audits: Array, sportFormats: Object, sports: Array });
const busy = ref(null);
const collapsedSports = ref(new Set());
const openActionMenu = ref(null);
const statusTone = (status) => ({ registration_open: 'success', registration_closed: 'danger', registration_draft: '', bracket_locked: 'info' }[status] || 'info');
const formatLabel = (format) => props.sportFormats?.[format] || format?.replaceAll('_', ' ') || 'Belum ditetapkan';
const normalizeLabel = (value) => value?.toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim() || '';
const displayName = (event) => normalizeLabel(event.name) === normalizeLabel(`${event.sport} ${event.category}`) ? event.sport : event.name;
const categoryTypeLabel = (type) => ({ individual: 'Individual', doubles: 'Ganda', team: 'Beregu' }[type] || type || 'Kategori');
const memberLimitLabel = (event) => event.max_members === null ? `Minimal ${event.min_members} pemain/team` : event.min_members === event.max_members ? `${event.min_members} pemain/team` : `${event.min_members}–${event.max_members} pemain/team`;
const groupEvents = (events) => Object.values(events.reduce((groups, event) => {
  const key = event.sport || 'Tanpa Cabor';
  groups[key] ||= { name: key, defaultFormat: event.default_format, events: [], entries: 0, open: 0 };
  groups[key].events.push(event);
  groups[key].entries += event.teams_count;
  groups[key].open += event.status === 'registration_open' ? 1 : 0;
  return groups;
}, {}));
const statusFlow = [
  ['registration_draft', 'Draft', 'Lengkapi kategori dan aturan'],
  ['registration_open', 'Dibuka', 'PD mulai mendaftar'],
  ['registration_closed', 'Ditutup', 'Verifikasi peserta final'],
  ['bracket_locked', 'Bracket Dikunci', 'Susunan pertandingan tetap'],
  ['ongoing', 'Berlangsung', 'Skor pertandingan diisi'],
  ['completed', 'Selesai', 'Hasil dan medali final'],
];
const isExpanded = (sport) => !collapsedSports.value.has(sport);
const toggleSport = (sport) => {
  const collapsed = new Set(collapsedSports.value);
  collapsed.has(sport) ? collapsed.delete(sport) : collapsed.add(sport);
  collapsedSports.value = collapsed;
};
const allExpanded = (events) => groupEvents(events).every((group) => isExpanded(group.name));
const toggleAll = (events) => { collapsedSports.value = allExpanded(events) ? new Set(groupEvents(events).map((group) => group.name)) : new Set(); };
const toggleActionMenu = (code) => { openActionMenu.value = openActionMenu.value === code ? null : code; };
const closeActionMenu = () => { openActionMenu.value = null; };
onMounted(() => document.addEventListener('click', closeActionMenu));
onBeforeUnmount(() => document.removeEventListener('click', closeActionMenu));
const eventModal = ref(false);
const editingCode = ref(null);
const eventForm = useForm({ sport_id: '', sport_category_id: '' });
const selectedSport = computed(() => props.sports.find((sport) => sport.id === Number(eventForm.sport_id)));
const selectedCategory = computed(() => selectedSport.value?.categories.find((category) => category.id === Number(eventForm.sport_category_id)));
const openEvent = (event = null) => {
  eventForm.reset(); eventForm.clearErrors(); editingCode.value = event?.code || null;
  if (event) Object.assign(eventForm, { sport_id: event.sport_id, sport_category_id: event.category_id });
  eventModal.value = true;
};
const changeSport = () => { eventForm.sport_category_id = ''; };
const saveEvent = () => eventForm.submit(editingCode.value ? 'put' : 'post', editingCode.value ? `/admin/events/${editingCode.value}` : '/admin/events', { preserveScroll: true, onSuccess: () => { eventModal.value = false; editingCode.value = null; eventForm.reset(); } });
const deleteEvent = (event) => { if (confirm(`Hapus data lomba ${event.name}?`)) router.delete(`/admin/events/${event.code}`, { preserveScroll: true }); };
const publishModal = ref(false);
const publishingEvent = ref(null);
const publishForm = useForm({ registration_open_at: '', registration_close_at: '' });
const openPublish = (event) => {
  publishingEvent.value = event;
  publishForm.clearErrors();
  Object.assign(publishForm, { registration_open_at: event.open_at || '', registration_close_at: event.close_at || '' });
  publishModal.value = true;
};
const selectedRegulation = computed(() => publishingEvent.value?.regulations[0]);
const publish = () => publishForm.post(`/admin/events/${publishingEvent.value.code}/publish`, { preserveScroll: true, onSuccess: () => { publishModal.value = false; publishingEvent.value = null; } });
const unpublish = (event) => {
  if (!confirm(`Tarik publikasi ${event.name}? Kompetisi kembali menjadi draft.`)) return;
  busy.value = event.code;
  router.post(`/admin/events/${event.code}/unpublish`, {}, { preserveScroll: true, onFinish: () => { busy.value = null; } });
};
const close = (event) => {
  if (!confirm(`Tutup registrasi ${event.name}?`)) return;
  busy.value = event.code;
  router.post(`/admin/events/${event.code}/close`, {}, { preserveScroll: true, onFinish: () => { busy.value = null; } });
};
const lockBracket = (event) => {
  if (!confirm(`Kunci bracket ${event.name} dengan ${event.verified_teams_count} team terverifikasi?`)) return;
  busy.value = event.code;
  router.post(`/admin/events/${event.code}/lock-bracket`, {}, { preserveScroll: true, onFinish: () => { busy.value = null; } });
};
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head"><SectionTitle eyebrow="Penyusunan Lomba" title="Data Lomba" :meta="`${events.total} kompetisi`" /></div>
    <section class="overview-card"><div><span>Data Kompetisi</span><h2>Susun paket lomba sebelum registrasi dibuka.</h2><p>Hubungkan cabor, kategori, regulasi, format, serta aturan peserta dalam satu data resmi.</p></div><dl><div><dt>Total Data</dt><dd>{{ events.total }}</dd></div><div><dt>Cabor Aktif</dt><dd>{{ sports.length }}</dd></div><div><dt>Audit Terbaru</dt><dd>{{ audits.length }}</dd></div></dl></section>
    <div class="section-actions"><div><strong>Daftar Data Lomba</strong><span>Buat draft, periksa aturan, lalu publikasikan untuk Pengurus Daerah.</span></div><button type="button" class="primary create-button" @click="openEvent()">Tambah Data Lomba</button></div>
    <section class="status-flow" aria-label="Alur status Data Lomba"><div v-for="([value, label, note], index) in statusFlow" :key="value" :class="['flow-step', value]"><span>{{ index + 1 }}</span><div><strong>{{ label }}</strong><small>{{ note }}</small></div></div></section>

    <Modal :open="eventModal" :title="editingCode ? 'Edit Data Lomba' : 'Tambah Data Lomba'" theme="light" @close="eventModal = false">
      <form class="event-form event-modal-form" @submit.prevent="saveEvent">
        <section class="form-section"><header><span>01</span><div><strong>Pilih Master Kompetisi</strong><small>Data Lomba mengambil seluruh aturan dari Master Cabor, Kategori, dan Regulasi aktif.</small></div></header><div class="event-fields"><label>Cabor<select v-model="eventForm.sport_id" required @change="changeSport"><option value="">Pilih cabor</option><option v-for="sport in sports" :key="sport.id" :value="sport.id">{{ sport.name }}</option></select></label><label>Kategori<select v-model="eventForm.sport_category_id" required><option value="">Pilih kategori</option><option v-for="category in selectedSport?.categories || []" :key="category.id" :value="category.id">{{ category.name }}</option></select></label></div></section>
        <section class="form-section registration-section"><header><span>02</span><div><strong>Ringkasan Otomatis</strong><small>Perbaiki nilai pada master bila ringkasan belum sesuai; Data Lomba tidak menyimpan override draft.</small></div></header><div class="registration-summary"><div><span>Format</span><strong>{{ formatLabel(selectedSport?.default_format) }}</strong></div><div><span>Tim per PD</span><strong>{{ selectedCategory?.default_max_teams_per_pd || '—' }}</strong></div><div><span>Official per PD</span><strong>{{ selectedSport?.default_max_officials_per_pd ?? '—' }}</strong></div></div><div class="inherited-rules"><div><span>Regulasi Aktif</span><strong>{{ selectedSport?.regulations?.[0] ? `v${selectedSport.regulations[0].version} · ${selectedSport.regulations[0].title}` : 'Belum tersedia' }}</strong></div><div><span>Pemain per Tim</span><strong>{{ selectedCategory ? `${selectedCategory.min_members}–${selectedCategory.max_members ?? 'Tidak dibatasi'}` : '—' }}</strong></div><div><span>Official Bertanding</span><strong>{{ selectedSport?.official_can_compete ? 'Boleh' : 'Tidak boleh' }}</strong></div></div></section>
        <p v-for="message in eventForm.errors" :key="message" class="form-error modal-error">{{ message }}</p><footer><button type="button" @click="eventModal = false">Batal</button><button class="primary" :disabled="eventForm.processing">{{ eventForm.processing ? 'Menyimpan…' : 'Simpan Data Lomba' }}</button></footer>
      </form>
    </Modal>

    <Modal :open="publishModal" title="Publikasi Data Lomba" theme="light" @close="publishModal = false">
      <form v-if="publishingEvent" class="event-form publish-form" @submit.prevent="publish">
        <div class="publish-summary">
          <span>{{ publishingEvent.sport }} · {{ publishingEvent.category }}</span>
          <h3>{{ publishingEvent.name }}</h3>
          <p>{{ formatLabel(publishingEvent.default_format) }} · Maksimal {{ publishingEvent.rules?.max_teams_per_pd || 1 }} tim per PD</p>
        </div>
        <div class="event-fields">
          <label>Registrasi Dibuka<input v-model="publishForm.registration_open_at" type="datetime-local" required /></label>
          <label>Registrasi Ditutup<input v-model="publishForm.registration_close_at" type="datetime-local" required /></label>
          <div class="regulation-preview"><span>Ringkasan Regulasi</span><strong>{{ selectedRegulation?.label || 'Pilih regulasi terlebih dahulu' }}</strong><p>{{ selectedRegulation?.content || 'Isi regulasi akan tampil di sini sebagai pemeriksaan sebelum publikasi.' }}</p><a v-if="selectedRegulation?.document_url" :href="selectedRegulation.document_url" target="_blank" rel="noopener">Buka dokumen regulasi</a></div>
          <p v-for="message in publishForm.errors" :key="message" class="form-error">{{ message }}</p>
        </div>
        <footer><button type="button" @click="publishModal = false">Batal</button><button class="primary" :disabled="publishForm.processing">{{ publishForm.processing ? 'Memublikasikan…' : 'Publikasikan' }}</button></footer>
      </form>
    </Modal>

    <AdminDataTable :paginator="events" :filters="filters" item-label="kompetisi" search-placeholder="Cari cabor, kategori, kode, atau kompetisi" :filter-options="[
      { value: 'registration_draft', label: 'Draft' }, { value: 'registration_open', label: 'Pendaftaran Dibuka' },
      { value: 'registration_closed', label: 'Pendaftaran Ditutup' }, { value: 'bracket_locked', label: 'Bracket Dikunci' },
      { value: 'ongoing', label: 'Sedang Berlangsung' }, { value: 'completed', label: 'Selesai' },
    ]">
      <template #toolbar-actions="{ rows: pageRows }"><button class="toggle-all" type="button" @click="toggleAll(pageRows)"><span :class="['toggle-arrow', { open: allExpanded(pageRows) }]" aria-hidden="true"></span><span>{{ allExpanded(pageRows) ? 'Tutup Semua' : 'Buka Semua' }}</span></button></template>
      <template #default="{ rows: pageRows }">
      <table>
        <thead><tr><th>Cabang / Kategori</th><th>Aturan Lomba</th><th>Pendaftaran</th><th>Peserta</th><th class="actions-heading">Aksi</th></tr></thead>
        <tbody>
          <template v-for="group in groupEvents(pageRows)" :key="group.name">
          <tr class="sport-row"><td colspan="5"><button class="sport-summary" type="button" :aria-expanded="isExpanded(group.name)" @click="toggleSport(group.name)"><span class="event-mark">{{ group.name.slice(0, 2).toUpperCase() }}</span><div><strong>{{ group.name }}</strong><small>{{ group.events.length }} kategori · {{ formatLabel(group.defaultFormat) }}</small></div><dl><div><dt>Total Peserta</dt><dd>{{ group.entries }}</dd></div><div><dt>Pendaftaran Dibuka</dt><dd>{{ group.open }}</dd></div></dl><span :class="['sport-chevron', { open: isExpanded(group.name) }]" aria-hidden="true"></span></button></td></tr>
          <tr v-for="event in group.events" v-show="isExpanded(group.name)" :key="event.code" class="event-row">
            <td><div class="event-identity child"><span class="tree-line" aria-hidden="true"></span><div class="primary-cell"><strong :title="event.name">{{ event.category || displayName(event) }}</strong><small>{{ categoryTypeLabel(event.competition_type) }} · {{ memberLimitLabel(event) }} · Maks. {{ event.rules?.max_teams_per_pd || 1 }} team/PD</small></div></div></td>
            <td><div class="competition-rules"><strong>{{ formatLabel(event.format) }}</strong><small>{{ event.regulation || 'Regulasi belum dipilih' }}</small></div></td>
            <td><div class="registration-cell"><span :class="['status-badge', statusTone(event.status)]">{{ statusLabel(event.status) }}</span><small v-if="event.open_at">{{ formatDateTime(event.open_at) }}<template v-if="event.close_at"> — {{ formatDateTime(event.close_at) }}</template></small><small v-else>Belum dijadwalkan</small></div></td>
            <td><a :href="`/admin/entries?event=${event.code}`" class="verification-count verification-link"><span><strong>{{ event.verified_players_count }}</strong><small>/ {{ event.players_count }} pemain</small></span><div class="verification-track"><i :style="{ width: `${event.players_count ? (event.verified_players_count / event.players_count) * 100 : 0}%` }"></i></div><small>{{ event.entries_count }} PD · {{ event.verified_teams_count }} / {{ event.teams_count }} tim terverifikasi</small><small :class="{ complete: event.verification_complete }">{{ event.verification_complete ? 'Verifikasi lengkap' : `${event.players_count - event.verified_players_count} pemain belum diverifikasi` }}</small></a></td>
            <td><details class="action-menu" :open="openActionMenu === event.code" @click.stop><summary aria-label="Buka menu aksi" @click.prevent="toggleActionMenu(event.code)">•••</summary><div><button v-if="!event.published && event.entries_count === 0" type="button" @click="closeActionMenu(); openEvent(event)">Edit Data</button><button v-if="!event.published && event.entries_count === 0" class="primary-action" type="button" @click="closeActionMenu(); openPublish(event)">Buka Pendaftaran</button><button v-if="event.published && event.status === 'registration_open'" class="danger-action" type="button" :disabled="busy === event.code" @click="closeActionMenu(); close(event)">Tutup Pendaftaran</button><button v-if="event.status === 'registration_closed'" class="primary-action" type="button" :disabled="busy === event.code || !event.verification_complete" @click="closeActionMenu(); lockBracket(event)">Kunci Bracket</button><button v-if="event.published && event.entries_count === 0" type="button" :disabled="busy === event.code" @click="closeActionMenu(); unpublish(event)">Kembalikan ke Draft</button><button v-if="!event.published && event.entries_count === 0" class="danger-action" type="button" @click="closeActionMenu(); deleteEvent(event)">Hapus Data</button><small v-if="event.status === 'registration_closed'">{{ event.verification_complete ? 'Seluruh team dan pemain siap dikunci.' : `${event.players_count - event.verified_players_count} pemain dan ${event.teams_count - event.verified_teams_count} team belum selesai.` }}</small><small v-else-if="event.status === 'bracket_locked'">Bracket {{ event.bracket_size }} slot terkunci. Lanjutkan ke Agenda & Jadwal.</small><small v-else-if="event.status === 'ongoing'">Kelola pertandingan melalui menu Skor.</small><small v-else-if="event.status === 'completed'">Kompetisi selesai dan siap dilaporkan.</small></div></details></td>
          </tr>
          </template>
          <tr v-if="!pageRows.length" class="empty-row"><td colspan="5">Tidak ada kompetisi sesuai filter.</td></tr>
        </tbody>
      </table>
      </template>
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
.actions-heading{text-align:right}.event-row{background:#fff}.event-identity{display:flex;align-items:flex-start;gap:12px;min-width:230px}.event-mark{display:grid;flex:0 0 38px;place-items:center;height:38px;color:#1946a3;background:#edf4ff;border:1px solid #cedcf3;border-radius:10px;font-size:11px;font-weight:900;letter-spacing:.08em}.event-identity .primary-cell{min-width:0}.event-identity strong{max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.event-identity code{width:fit-content;padding:2px 6px;color:#536571;background:#f1f5f7;border-radius:4px;font-family:inherit;font-size:10px;font-weight:750}.regulation-field{display:grid;gap:6px}.regulation-field small{color:#7a8993;font-size:10px}.regulation-select{min-width:190px;max-width:230px}.period-fields{grid-template-columns:minmax(154px,1fr);min-width:170px}.period-fields input:disabled,.regulation-select:disabled{color:#71808b;background:#f2f5f7;border-color:#dce4e8}.count-cell{gap:8px;min-width:90px}.count-cell>span{display:flex;align-items:baseline;gap:6px}.count-cell strong{font-size:24px;line-height:1}.team-limit{display:flex;align-items:center;gap:6px;margin-top:0}.team-limit input{width:54px;min-height:32px}.team-cap{padding-top:7px;border-top:1px solid #e5ecef}.format-editor,.locked-format{min-width:150px}.row-actions{min-width:118px}.row-actions button{min-width:54px}.row-actions button.primary{box-shadow:0 4px 10px rgba(54,194,240,.16)}
</style>

<style scoped>
.wide,.regulation-preview{grid-column:1/-1}.publish-summary{padding:20px 20px 0}.publish-summary span,.regulation-preview span{color:#1946a3;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.publish-summary h3{margin:5px 0;color:#142536}.publish-summary p,.regulation-preview p{margin:0;color:#60717f;font-size:12px;line-height:1.55}.regulation-preview{display:grid;gap:7px;padding:14px;background:#f5f8fa;border:1px solid #dde6ea;border-radius:10px}.regulation-preview strong{color:#243747}.regulation-preview a{width:fit-content;color:#1946a3;font-size:11px;font-weight:800;text-decoration:none}.locked-format,.regulation-field,.period-display{display:grid;gap:5px;min-width:150px}.locked-format strong,.regulation-field strong,.period-display strong{color:#334553;font-size:12px}.locked-format small,.regulation-field small,.period-display small{color:#7a8993;font-size:10px}
.event-hero{display:grid;grid-template-columns:1fr auto;gap:20px;margin:8px 0 24px;padding:24px;color:#fff;background:linear-gradient(135deg,#0b1d3d 0%,#12356c 68%,#1946a3 100%);border:1px solid rgba(54,194,240,.28);border-radius:16px;box-shadow:0 14px 34px rgba(7,17,38,.18)}.event-hero :deep(.section-title){margin:0}.event-hero-main>p{max-width:680px;margin:10px 0 0;color:#c7d8eb;font-size:13px;line-height:1.6}.create-button{align-self:start;display:flex;align-items:center;gap:8px;padding:11px 15px;color:#071126;background:#36c2f0;border:0;border-radius:9px;box-shadow:0 8px 20px rgba(54,194,240,.22)}.create-button span{font-size:18px;line-height:1}.event-summary{grid-column:1/-1;display:grid;grid-template-columns:repeat(3,minmax(110px,150px)) 1fr;gap:10px;padding-top:18px;border-top:1px solid rgba(255,255,255,.14)}.event-summary>div{display:grid;gap:3px;padding:10px 12px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:9px}.event-summary small{color:#9eb7d2;font-size:9px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.event-summary strong{font-size:20px}.event-summary>p{align-self:center;margin:0;color:#c7d8eb;font-size:11px;line-height:1.5}.event-meta{display:flex;flex-wrap:wrap;gap:6px;margin-top:3px}.event-meta span,.event-meta code{display:inline-flex;align-items:center;min-height:22px;padding:3px 7px;border-radius:999px;font-size:9px;font-weight:800}.event-meta span{color:#1946a3;background:#eaf1fb}.event-meta code{color:#60717f;background:#f1f5f7}.event-identity strong{max-width:280px}.event-row>td{vertical-align:middle}@media(max-width:800px){.event-hero{grid-template-columns:1fr;padding:18px}.create-button{width:100%;justify-content:center}.event-summary{grid-template-columns:repeat(3,1fr)}.event-summary>p{grid-column:1/-1}}@media(max-width:520px){.event-summary{grid-template-columns:1fr}.event-summary>p{grid-column:auto}}
.page-head{padding:8px 0 6px}.overview-card{display:grid;grid-template-columns:1.2fr 1fr;gap:28px;margin-bottom:18px;padding:26px 28px;color:#fff;background:linear-gradient(135deg,#0b1d3d,#1946a3);border:1px solid rgba(54,194,240,.28);border-radius:16px;box-shadow:0 14px 34px rgba(7,17,38,.16)}.overview-card>div>span{color:#36c2f0;font-size:10px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}.overview-card h2{margin:6px 0;color:#fff;font-size:24px}.overview-card p{max-width:620px;margin:0;color:#c7d8eb;font-size:12px;line-height:1.6}.overview-card dl{display:grid;grid-template-columns:repeat(3,1fr);margin:0;overflow:hidden;border:1px solid rgba(255,255,255,.13);border-radius:11px}.overview-card dl div{display:grid;place-content:center;padding:14px;border-right:1px solid rgba(255,255,255,.13);text-align:center}.overview-card dl div:last-child{border-right:0}.overview-card dt{color:#aac1d8;font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.overview-card dd{margin:5px 0 0;color:#fff;font-size:21px;font-weight:900}.section-actions{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:14px;padding:18px 20px;background:#fff;border:1px solid #d9e3e9;border-radius:14px}.section-actions div{display:grid;gap:4px}.section-actions strong{color:#142536;font-size:17px}.section-actions span{color:#71808b;font-size:12px}.section-actions .create-button{align-self:center;color:#fff;background:#1946a3;box-shadow:none}.event-modal-form{display:grid;overflow:hidden;background:#f6f8fa}.event-modal-form .form-section{display:grid;gap:16px;padding:20px 22px;background:#fff;border-bottom:1px solid #e4ebef}.event-modal-form .form-section>header{display:flex;align-items:flex-start;gap:12px}.event-modal-form .form-section>header>span{display:grid;place-items:center;width:30px;height:30px;flex:0 0 auto;color:#1946a3;background:#eaf1fb;border:1px solid #cfdded;border-radius:8px;font-size:10px;font-weight:900}.event-modal-form .form-section>header>div{display:grid;gap:3px}.event-modal-form .form-section>header strong{color:#172535;font-size:14px}.event-modal-form .form-section>header small{color:#71808b;font-size:11px}.event-modal-form .event-fields{padding:0}.event-modal-form label>small{color:#71808b;font-weight:600}.registration-section{background:#f5f8fa!important}.registration-section .registration-summary{display:grid;grid-template-columns:repeat(3,1fr);overflow:hidden;background:#102f59;border:1px solid #102f59;border-radius:11px}.registration-section .registration-summary div{display:grid;gap:4px;padding:13px 15px;border-right:1px solid rgba(255,255,255,.14)}.registration-section .registration-summary div:last-child{border-right:0}.registration-section .registration-summary span{color:#b9d9f1;font-size:9px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.registration-section .registration-summary strong{color:#fff;font-size:12px}.event-modal-form footer{padding:16px 22px;background:#f7f9fa;border-top:1px solid #e2e9ed}.modal-error{margin:14px 22px 0}@media(max-width:900px){.overview-card{grid-template-columns:1fr}.overview-card dl{min-height:92px}}@media(max-width:700px){.section-actions{align-items:stretch;flex-direction:column}.overview-card{padding:20px}.overview-card dl,.registration-section .registration-summary{grid-template-columns:1fr}.overview-card dl div,.registration-section .registration-summary div{border-right:0;border-bottom:1px solid rgba(255,255,255,.13)}.overview-card dl div:last-child,.registration-section .registration-summary div:last-child{border-bottom:0}.event-modal-form .form-section{padding:18px}.wide{grid-column:auto}}
.inherited-rules{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.inherited-rules>div{display:grid;gap:5px;padding:13px;background:#fff;border:1px solid #d9e3e9;border-radius:9px}.inherited-rules span{color:#71808b;font-size:9px;font-weight:800;letter-spacing:.07em;text-transform:uppercase}.inherited-rules strong{color:#243747;font-size:11px;line-height:1.45}@media(max-width:700px){.inherited-rules{grid-template-columns:1fr}}
.status-flow{display:grid;grid-template-columns:repeat(6,1fr);overflow:hidden;margin:0 0 14px;background:#fff;border:1px solid #d9e3e9;border-radius:14px}.flow-step{position:relative;display:flex;align-items:center;gap:9px;min-height:70px;padding:12px;border-right:1px solid #e3eaee}.flow-step:last-child{border-right:0}.flow-step>span{display:grid;flex:0 0 26px;place-items:center;height:26px;color:#1946a3;background:#eaf1fb;border-radius:8px;font-size:10px;font-weight:900}.flow-step div{display:grid;gap:3px}.flow-step strong{color:#243747;font-size:11px}.flow-step small{color:#71808b;font-size:9px;line-height:1.35}.sport-row td{padding:0!important;background:#f3f7fa!important}.sport-summary{display:flex;align-items:center;gap:12px;padding:14px 16px;border-top:1px solid #d9e3e9;border-bottom:1px solid #d9e3e9}.sport-row:first-child .sport-summary{border-top:0}.sport-summary>div{display:grid;gap:3px}.sport-summary>div strong{color:#102f59;font-size:14px}.sport-summary>div small{color:#71808b;font-size:10px}.sport-summary dl{display:flex;gap:24px;margin:0 0 0 auto}.sport-summary dl div{display:grid;gap:2px}.sport-summary dt{color:#7a8993;font-size:8px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.sport-summary dd{margin:0;color:#1946a3;font-size:13px;font-weight:900}.event-identity.child{align-items:center;padding-left:12px}.tree-line{width:18px;height:24px;border-bottom:1px solid #b9c8d2;border-left:1px solid #b9c8d2;border-radius:0 0 0 8px}.action-menu{position:relative;margin-left:auto;width:38px}.action-menu summary{display:grid;place-items:center;width:38px;height:34px;color:#1946a3;background:#fff;border:1px solid #bfd0dc;border-radius:8px;font-size:18px;font-weight:900;line-height:1;cursor:pointer;list-style:none}.action-menu summary::-webkit-details-marker{display:none}.action-menu[open] summary{background:#edf4ff;border-color:#9fb7d6}.action-menu>div{position:absolute;z-index:12;top:40px;right:0;display:grid;width:190px;padding:6px;background:#fff;border:1px solid #d5e0e6;border-radius:10px;box-shadow:0 14px 32px rgba(25,53,76,.16)}.action-menu button{padding:9px 10px;color:#334553;background:transparent;border:0;border-radius:6px;font-size:11px;font-weight:750;text-align:left;cursor:pointer}.action-menu button:hover{background:#f0f5f8}.action-menu .primary-action{color:#087365}.action-menu .danger-action{color:#a1432e}.action-menu button:disabled{opacity:.5;cursor:not-allowed}.action-menu small{padding:9px 10px;color:#71808b;font-size:9px;line-height:1.45;border-top:1px solid #e5ecef}@media(max-width:1100px){.status-flow{grid-template-columns:repeat(3,1fr)}.flow-step:nth-child(3){border-right:0}.flow-step:nth-child(-n+3){border-bottom:1px solid #e3eaee}}@media(max-width:700px){.status-flow{grid-template-columns:1fr}.flow-step{border-right:0;border-bottom:1px solid #e3eaee}.sport-summary dl{display:none}}
.sport-summary{width:100%;color:inherit;background:transparent;text-align:left;cursor:pointer;transition:background .28s ease,box-shadow .28s ease}.sport-summary:hover{background:#eaf1f6;box-shadow:inset 3px 0 #1946a3}.sport-chevron,.toggle-arrow{display:inline-grid;flex:0 0 20px;place-items:center;width:20px;height:20px}.sport-chevron{margin-left:6px}.sport-chevron::before,.toggle-arrow::before{content:"";width:7px;height:7px;border-right:2px solid #1946a3;border-bottom:2px solid #1946a3;transform:translateY(-2px) rotate(45deg);transition:transform .34s cubic-bezier(.22,1,.36,1)}.sport-chevron.open::before,.toggle-arrow.open::before{transform:translateY(2px) rotate(225deg)}.toggle-all{display:inline-flex!important;align-items:center;justify-content:center;gap:7px;line-height:1}.toggle-all>span:last-child{display:inline-flex;align-items:center;min-height:18px}.event-row{animation:child-row-in .3s cubic-bezier(.22,1,.36,1)}.event-row td{transition:background .24s ease,border-color .24s ease}@keyframes child-row-in{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
.competition-rules,.registration-cell{display:grid;gap:6px;min-width:190px}.competition-rules strong{color:#243747;font-size:12px}.competition-rules small,.registration-cell>small{color:#71808b;font-size:10px;line-height:1.45}.registration-cell .status-badge{margin-bottom:1px}
.verification-link{padding:7px;border-radius:8px;color:inherit;text-decoration:none;transition:background .2s ease,transform .2s ease}.verification-link:hover{background:#edf4ff;transform:translateY(-1px)}
.verification-count{display:grid;gap:6px;min-width:120px}.verification-count>span{display:flex;align-items:baseline;gap:4px}.verification-count strong{color:#1946a3;font-size:20px}.verification-count small{color:#71808b;font-size:9px}.verification-count small.complete{color:#087365;font-weight:800}.verification-track{width:100%;height:5px;overflow:hidden;background:#e5ecef;border-radius:999px}.verification-track i{display:block;height:100%;background:#20a995;border-radius:inherit;transition:width .3s ease}
@media(prefers-reduced-motion:reduce){.sport-summary,.sport-chevron::before,.toggle-arrow::before,.event-row,.event-row td{animation:none;transition:none}}
</style>
