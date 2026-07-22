<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import Modal from '../../Components/Modal.vue';
import ActionIconButton from '../../Components/ActionIconButton.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import { formatDate } from '../../lib/date';

const props = defineProps({ mode: String, venues: { type: [Array, Object], default: () => [] }, sports: { type: Array, default: () => [] }, events: { type: Array, default: () => [] }, matches: { type: Array, default: () => [] }, agendas: Object, filters: Object });
const venueId = ref(null);
const agendaId = ref(null);
const venueModal = ref(false);
const venueOptions = computed(() => Array.isArray(props.venues) ? props.venues : props.venues.data || []);
const venue = useForm({ code: '', name: '', address: '', city: '', facilities: '', map_url: '', contact_name: '', contact_phone: '', latitude: '', longitude: '', is_active: true });
const agenda = useForm({ date: '', title: '', type: 'sport', sport_id: '', tournament_event_id: '', venue_id: '', start_time: '', end_time: '', time_note: '', description: '', change_note: '' });
const saveVenue = () => venue.submit(venueId.value ? 'put' : 'post', venueId.value ? `/admin/venues/${venueId.value}` : '/admin/venues', { preserveScroll: true, onSuccess: () => { venue.reset(); venueId.value = null; venueModal.value = false; } });
const saveAgenda = () => agenda.submit(agendaId.value ? 'put' : 'post', agendaId.value ? `/admin/agendas/${agendaId.value}` : '/admin/agendas', { preserveScroll: true, onSuccess: () => { agenda.reset(); agendaId.value = null; } });
const openVenue = (item = null) => { venue.reset(); venue.clearErrors(); venueId.value = item?.id || null; if (item) Object.keys(venue.data()).forEach((key) => { venue[key] = item[key] ?? (key === 'is_active' ? false : ''); }); venueModal.value = true; };
const deleteVenue = (item) => { if (confirm(`Hapus venue ${item.name}?`)) router.delete(`/admin/venues/${item.id}`, { preserveScroll: true }); };
const mapsUrl = (item) => item.map_url || (item.latitude && item.longitude ? `https://www.google.com/maps/search/?api=1&query=${item.latitude},${item.longitude}` : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent([item.name, item.address, item.city].filter(Boolean).join(', '))}`);
const showVenue = (item) => window.open(mapsUrl(item), '_blank', 'noopener');
const toggleVenue = (item) => router.put(`/admin/venues/${item.id}`, { ...item, is_active: !item.is_active }, { preserveScroll: true });
const editAgenda = (item) => { agendaId.value = item.id; Object.keys(agenda.data()).forEach((key) => { agenda[key] = item[key] ?? ''; }); agenda.date = item.date.slice(0, 10); agenda.start_time = item.start_time.slice(0, 5); agenda.end_time = item.end_time.slice(0, 5); window.scrollTo({ top: 0, behavior: 'smooth' }); };
const publish = (id) => router.post(`/admin/agendas/${id}/publish`, {}, { preserveScroll: true });
const schedule = useForm({ match_id: '', event_agenda_id: '' });
const saveSchedule = () => schedule.post(`/admin/matches/${schedule.match_id}/schedule`, { preserveScroll: true, onSuccess: () => schedule.reset() });
const typeLabel = { sport: 'Pertandingan', exhibition: 'Eksibisi', official: 'Acara Resmi' };
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head"><SectionTitle :eyebrow="mode === 'venues' ? 'Persiapan Lomba' : 'Operasional'" :title="mode === 'venues' ? 'Master Venue' : 'Agenda & Jadwal'" :meta="mode === 'venues' ? 'Lokasi resmi yang dapat digunakan lomba' : 'Publikasi agenda, jadwal pertandingan, dan konflik waktu'" /></div>
    <div v-if="mode === 'venues'" class="venue-heading"><div><b>Direktori Lokasi</b><span>Kelola venue, koordinat, kontak, dan status penggunaan.</span></div><button @click="openVenue()">Tambah Venue</button></div>
    <AdminDataTable v-if="mode === 'venues'" :paginator="venues" :filters="filters" item-label="venue" search-placeholder="Cari nama, kode, kota, atau alamat…">
      <template #default="{ rows }"><table><thead><tr><th>Venue</th><th>Lokasi</th><th>Koordinat</th><th>Agenda</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead><tbody>
        <tr v-for="item in rows" :key="item.id"><td><div class="primary"><strong>{{ item.name }}</strong><small>{{ item.code }}</small></div></td><td><strong>{{ item.city || '—' }}</strong><small>{{ item.address || 'Alamat belum diisi' }}</small></td><td><a class="map-link" :href="mapsUrl(item)" target="_blank" rel="noopener">Buka Google Maps ↗</a><small v-if="item.latitude && item.longitude">{{ item.latitude }}, {{ item.longitude }}</small></td><td>{{ item.agendas_count }}</td><td><span :class="['badge', item.is_active ? 'published' : 'draft']">{{ item.is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td><div class="row-actions"><ActionIconButton icon="show" label="Lihat lokasi" @click="showVenue(item)" /><ActionIconButton icon="edit" label="Edit venue" @click="openVenue(item)" /><ActionIconButton icon="power" :label="item.is_active ? 'Nonaktifkan venue' : 'Aktifkan venue'" :tone="item.is_active ? 'inactive' : 'active'" @click="toggleVenue(item)" /><ActionIconButton icon="delete" label="Hapus venue" tone="danger" :disabled="item.is_active || item.agendas_count > 0" @click="deleteVenue(item)" /></div></td></tr>
        <tr v-if="!rows.length"><td colspan="6" class="empty">Belum ada venue sesuai pencarian.</td></tr>
      </tbody></table></template>
    </AdminDataTable>
    <Modal :open="venueModal" :title="venueId ? 'Edit Venue' : 'Tambah Venue'" theme="light" @close="venueModal = false">
      <form class="modal-form" @submit.prevent="saveVenue"><div class="fields">
        <label>Kode<input v-model="venue.code" required /></label><label>Nama<input v-model="venue.name" required /></label>
        <label>Kota<input v-model="venue.city" /></label><label>Kontak<input v-model="venue.contact_name" /></label>
        <label>Telepon<input v-model="venue.contact_phone" /></label><label>URL Google Maps<input v-model="venue.map_url" type="url" placeholder="https://maps.google.com/…" /></label>
        <label>Latitude<input v-model="venue.latitude" type="number" min="-90" max="90" step="0.0000001" placeholder="-1.2379270" /></label><label>Longitude<input v-model="venue.longitude" type="number" min="-180" max="180" step="0.0000001" placeholder="116.8528520" /></label>
        <label class="wide">Alamat<textarea v-model="venue.address" /></label><label class="wide">Fasilitas<textarea v-model="venue.facilities" /></label><label class="wide check"><input v-model="venue.is_active" type="checkbox" /> Venue aktif</label>
        <p v-for="message in venue.errors" :key="message" class="error wide">{{ message }}</p>
      </div><footer><button type="button" class="outline" @click="venueModal = false">Batal</button><button :disabled="venue.processing">{{ venue.processing ? 'Menyimpan…' : 'Simpan Venue' }}</button></footer></form>
    </Modal>
    <div v-if="mode === 'agendas'" class="forms single">
      <form class="card" @submit.prevent="saveAgenda"><header><b>{{ agendaId ? 'Edit Agenda' : 'Agenda Baru' }}</b><span>Venue sama tidak boleh memiliki jadwal bertabrakan.</span></header><div class="fields">
        <label class="wide">Judul<input v-model="agenda.title" required /></label><label>Tanggal<input v-model="agenda.date" type="date" required /></label>
        <label>Tipe<select v-model="agenda.type"><option value="sport">Pertandingan</option><option value="exhibition">Eksibisi</option><option value="official">Acara Resmi</option></select></label>
        <label>Venue<select v-model="agenda.venue_id" required><option value="">Pilih venue</option><option v-for="item in venueOptions.filter(v => v.is_active)" :key="item.id" :value="item.id">{{ item.name }}</option></select></label>
        <label>Cabor<select v-model="agenda.sport_id"><option value="">Tanpa cabor</option><option v-for="item in sports" :key="item.id" :value="item.id">{{ item.name }}</option></select></label>
        <label>Mulai<input v-model="agenda.start_time" type="time" required /></label><label>Selesai<input v-model="agenda.end_time" type="time" required /></label>
        <label class="wide">Kompetisi<select v-model="agenda.tournament_event_id"><option value="">Tanpa kompetisi</option><option v-for="item in events" :key="item.id" :value="item.id">{{ item.name }}</option></select></label>
        <label v-if="agendaId" class="wide">Alasan Perubahan<input v-model="agenda.change_note" placeholder="Wajib untuk agenda terpublikasi" /></label>
        <p v-if="agenda.errors.start_time" class="error wide">{{ agenda.errors.start_time }}</p>
      </div><footer><button :disabled="agenda.processing">Simpan Agenda</button></footer></form>
    </div>
    <form v-if="mode === 'agendas'" class="card schedule" @submit.prevent="saveSchedule"><header><b>Jadwalkan Pertandingan</b><span>Venue dan waktu mengikuti agenda terpilih.</span></header><div class="fields">
      <label>Pertandingan<select v-model="schedule.match_id" required><option value="">Pilih pertandingan</option><option v-for="item in matches" :key="item.id" :value="item.id">{{ item.code }} — {{ item.tournament_event.name }}</option></select></label>
      <label>Agenda<select v-model="schedule.event_agenda_id" required><option value="">Pilih agenda</option><option v-for="item in agendas.data" :key="item.id" :value="item.id">{{ formatDate(item.date) }} · {{ item.title }} · {{ item.venue_name }}</option></select></label>
    </div><footer><button :disabled="schedule.processing">Tetapkan Jadwal</button></footer></form>
    <AdminDataTable v-if="mode === 'agendas'" :paginator="agendas" :filters="filters" item-label="agenda" search-placeholder="Cari agenda, cabor, atau venue…">
      <template #default="{ rows }"><table><thead><tr><th>Agenda</th><th>Waktu</th><th>Venue</th><th>Tipe</th><th>Status</th><th></th></tr></thead><tbody>
        <tr v-for="row in rows" :key="row.id"><td><div class="primary"><strong>{{ row.title }}</strong><small>{{ row.sport_name || 'Umum' }}</small></div></td>
          <td>{{ formatDate(row.date) }}<br><small>{{ row.start_time.slice(0,5) }}–{{ row.end_time.slice(0,5) }}</small></td><td>{{ row.venue_name }}</td><td>{{ typeLabel[row.type] }}</td>
          <td><span :class="['badge', row.published_at ? 'published' : 'draft']">{{ row.published_at ? 'Terpublikasi' : 'Draft' }}</span></td>
          <td><button class="outline" @click="editAgenda(row)">Edit</button> <button v-if="!row.published_at" class="outline" @click="publish(row.id)">Publikasikan</button></td></tr>
      </tbody></table></template>
    </AdminDataTable>
  </PortalLayout>
</template>

<style scoped>
.page-head{padding:8px 0 24px}.venue-heading{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:16px;padding:18px 20px;color:#fff;background:#102f59;border-radius:14px}.venue-heading div{display:grid;gap:4px}.venue-heading b{font-size:18px}.venue-heading span{color:#b9d9f1;font-size:12px}.forms{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px}.card{overflow:hidden;background:#fff;border:1px solid #d9e3e9;border-radius:14px;box-shadow:0 8px 24px rgba(25,53,76,.06)}.schedule{margin-bottom:24px}header{display:grid;gap:5px;padding:18px 20px;background:#fbfcfd;border-bottom:1px solid #e2e9ed}header b{font-size:18px}header span,small{color:#71808b;font-size:12px}.modal-form{min-width:min(720px,80vw)}.fields{display:grid;grid-template-columns:1fr 1fr;gap:13px;padding:20px}.wide{grid-column:1/-1}.check{display:flex;align-items:center}.check input{min-height:auto}label{display:grid;gap:6px;color:#60717f;font-size:11px;font-weight:750}input,select,textarea{min-height:42px;padding:10px 12px;border:1px solid #cbd8df;border-radius:8px;font:inherit}textarea{min-height:64px;resize:vertical}footer{display:flex;justify-content:flex-end;gap:8px;padding:0 20px 20px}button{min-height:40px;padding:9px 14px;color:#fff;background:#1946a3;border:0;border-radius:8px;font-weight:800;cursor:pointer}.outline{color:#1946a3;background:#fff;border:1px solid #bfd0dc}.danger{color:#a13d24;background:#fff4f0;border:1px solid #ffd0c1}.danger:disabled{opacity:.45;cursor:not-allowed}.primary{display:grid;gap:4px}.map-link{color:#1946a3;font-weight:800;text-decoration:none}.map-link:hover{text-decoration:underline}.row-actions{display:flex;justify-content:flex-end;gap:7px}.badge{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:10px;font-weight:800}.published{color:#087365;background:#eefaf6}.draft{color:#536571;background:#edf2f5}.error{margin:0;color:#a1432e;font-size:11px;font-weight:700}.empty{padding:36px!important;color:#71808b;text-align:center}@media(max-width:900px){.venue-heading{align-items:stretch;flex-direction:column}.forms{grid-template-columns:1fr}.fields{grid-template-columns:1fr}.wide{grid-column:auto}.modal-form{min-width:0}}
</style>
