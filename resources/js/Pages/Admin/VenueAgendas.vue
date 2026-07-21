<script setup>
import { router, useForm } from '@inertiajs/vue3';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

defineProps({ venues: Array, sports: Array, events: Array, agendas: Object, filters: Object });
const venue = useForm({ code: '', name: '', address: '', city: '', facilities: '', map_url: '', contact_name: '', contact_phone: '', is_active: true });
const agenda = useForm({ date: '', title: '', type: 'sport', sport_id: '', tournament_event_id: '', venue_id: '', start_time: '', end_time: '', time_note: '', description: '' });
const saveVenue = () => venue.post('/admin/venues', { preserveScroll: true, onSuccess: () => venue.reset() });
const saveAgenda = () => agenda.post('/admin/agendas', { preserveScroll: true, onSuccess: () => agenda.reset() });
const publish = (id) => router.post(`/admin/agendas/${id}/publish`, {}, { preserveScroll: true });
const typeLabel = { sport: 'Pertandingan', exhibition: 'Eksibisi', official: 'Acara Resmi' };
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head"><SectionTitle eyebrow="Operasional" title="Venue & Agenda" meta="Lokasi, jadwal, publikasi, dan konflik waktu" /></div>
    <div class="forms">
      <form class="card" @submit.prevent="saveVenue"><header><b>Venue Baru</b><span>Venue aktif dapat dipakai agenda.</span></header><div class="fields">
        <label>Kode<input v-model="venue.code" required /></label><label>Nama<input v-model="venue.name" required /></label>
        <label>Kota<input v-model="venue.city" /></label><label>Kontak<input v-model="venue.contact_name" /></label>
        <label>Telepon<input v-model="venue.contact_phone" /></label><label>URL Peta<input v-model="venue.map_url" type="url" /></label>
        <label class="wide">Alamat<textarea v-model="venue.address" /></label><label class="wide">Fasilitas<textarea v-model="venue.facilities" /></label>
      </div><footer><button :disabled="venue.processing">Simpan Venue</button></footer></form>
      <form class="card" @submit.prevent="saveAgenda"><header><b>Agenda Baru</b><span>Venue sama tidak boleh memiliki jadwal bertabrakan.</span></header><div class="fields">
        <label class="wide">Judul<input v-model="agenda.title" required /></label><label>Tanggal<input v-model="agenda.date" type="date" required /></label>
        <label>Tipe<select v-model="agenda.type"><option value="sport">Pertandingan</option><option value="exhibition">Eksibisi</option><option value="official">Acara Resmi</option></select></label>
        <label>Venue<select v-model="agenda.venue_id" required><option value="">Pilih venue</option><option v-for="item in venues.filter(v => v.is_active)" :key="item.id" :value="item.id">{{ item.name }}</option></select></label>
        <label>Cabor<select v-model="agenda.sport_id"><option value="">Tanpa cabor</option><option v-for="item in sports" :key="item.id" :value="item.id">{{ item.name }}</option></select></label>
        <label>Mulai<input v-model="agenda.start_time" type="time" required /></label><label>Selesai<input v-model="agenda.end_time" type="time" required /></label>
        <label class="wide">Kompetisi<select v-model="agenda.tournament_event_id"><option value="">Tanpa kompetisi</option><option v-for="item in events" :key="item.id" :value="item.id">{{ item.name }}</option></select></label>
        <p v-if="agenda.errors.start_time" class="error wide">{{ agenda.errors.start_time }}</p>
      </div><footer><button :disabled="agenda.processing">Simpan Agenda</button></footer></form>
    </div>
    <AdminDataTable :paginator="agendas" :filters="filters" item-label="agenda" search-placeholder="Cari agenda, cabor, atau venue…">
      <template #default="{ rows }"><table><thead><tr><th>Agenda</th><th>Waktu</th><th>Venue</th><th>Tipe</th><th>Status</th><th></th></tr></thead><tbody>
        <tr v-for="row in rows" :key="row.id"><td><div class="primary"><strong>{{ row.title }}</strong><small>{{ row.sport_name || 'Umum' }}</small></div></td>
          <td>{{ row.date }}<br><small>{{ row.start_time.slice(0,5) }}–{{ row.end_time.slice(0,5) }}</small></td><td>{{ row.venue_name }}</td><td>{{ typeLabel[row.type] }}</td>
          <td><span :class="['badge', row.published_at ? 'published' : 'draft']">{{ row.published_at ? 'Terpublikasi' : 'Draft' }}</span></td>
          <td><button v-if="!row.published_at" class="outline" @click="publish(row.id)">Publikasikan</button></td></tr>
      </tbody></table></template>
    </AdminDataTable>
  </PortalLayout>
</template>

<style scoped>
.page-head{padding:8px 0 24px}.forms{display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:24px}.card{overflow:hidden;background:#fff;border:1px solid #d9e3e9;border-radius:14px;box-shadow:0 8px 24px rgba(25,53,76,.06)}header{display:grid;gap:5px;padding:18px 20px;background:#fbfcfd;border-bottom:1px solid #e2e9ed}header b{font-size:18px}header span,small{color:#71808b;font-size:12px}.fields{display:grid;grid-template-columns:1fr 1fr;gap:13px;padding:20px}.wide{grid-column:1/-1}label{display:grid;gap:6px;color:#60717f;font-size:11px;font-weight:750}input,select,textarea{min-height:42px;padding:10px 12px;border:1px solid #cbd8df;border-radius:8px;font:inherit}textarea{min-height:64px;resize:vertical}footer{padding:0 20px 20px}button{min-height:40px;padding:9px 14px;color:#fff;background:#1946a3;border:0;border-radius:8px;font-weight:800;cursor:pointer}.outline{color:#1946a3;background:#fff;border:1px solid #bfd0dc}.primary{display:grid;gap:4px}.badge{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:10px;font-weight:800}.published{color:#087365;background:#eefaf6}.draft{color:#536571;background:#edf2f5}.error{margin:0;color:#a1432e;font-size:11px;font-weight:700}@media(max-width:900px){.forms{grid-template-columns:1fr}.fields{grid-template-columns:1fr}.wide{grid-column:auto}}
</style>
