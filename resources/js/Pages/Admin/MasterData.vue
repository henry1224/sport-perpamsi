<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

const props = defineProps({ sports: Array, categories: Array, regulations: Array, audits: Array });
const tab = ref('sports');
const sportForm = useForm({ code: '', name: '', type: 'sport', default_format: 'knockout', score_template: '' });
const categoryForm = useForm({ sport_id: '', code: '', name: '', competition_type: 'single', min_members: 1, max_members: 1, scoring_type: 'points', bracket_enabled: true, sort_order: 0, is_active: true });
const regulationForm = useForm({ sport_id: '', title: '', content: '', document_url: '' });

const addSport = () => sportForm.post('/admin/master-data/sports', { onSuccess: () => sportForm.reset() });
const addCategory = () => categoryForm.post('/admin/master-data/categories', { onSuccess: () => categoryForm.reset() });
const addRegulation = () => regulationForm.post('/admin/master-data/regulations', { onSuccess: () => regulationForm.reset() });
const updateSport = (sport) => router.put(`/admin/master-data/sports/${sport.id}`, sport, { preserveScroll: true });
const updateCategory = (category) => router.put(`/admin/master-data/categories/${category.id}`, category, { preserveScroll: true });
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head"><SectionTitle eyebrow="Phase 2" title="Master Data Lomba" meta="Cabor, kategori, dan regulasi" /></div>
    <nav class="tabs" aria-label="Master data"><button v-for="item in ['sports', 'categories', 'regulations', 'audit']" :key="item" :class="{ active: tab === item }" @click="tab = item">{{ { sports: 'Cabor', categories: 'Kategori', regulations: 'Regulasi', audit: 'Audit' }[item] }}</button></nav>

    <section v-if="tab === 'sports'" class="master-grid">
      <form class="form-card" @submit.prevent="addSport"><h2>Tambah Cabor</h2><label>Kode<input v-model="sportForm.code" required /></label><label>Nama<input v-model="sportForm.name" required /></label><label>Tipe<select v-model="sportForm.type"><option value="sport">Olahraga</option><option value="seminar">Seminar</option></select></label><label>Format<select v-model="sportForm.default_format"><option value="knockout">Knockout</option><option value="group">Grup</option><option value="round_robin">Round Robin</option><option value="ranking">Ranking</option></select></label><label>Template Skor<input v-model="sportForm.score_template" /></label><button :disabled="sportForm.processing">Tambah</button></form>
      <div class="table-card"><table><thead><tr><th>Cabor</th><th>Format</th><th>Status</th><th></th></tr></thead><tbody><tr v-for="sport in sports" :key="sport.id"><td><input v-model="sport.name" /><small>{{ sport.code }} · {{ sport.categories_count }} kategori · {{ sport.events_count }} kompetisi</small></td><td><select v-model="sport.default_format"><option value="knockout">Knockout</option><option value="group">Grup</option><option value="round_robin">Round Robin</option><option value="ranking">Ranking</option></select></td><td><select v-model="sport.is_active"><option :value="true">Aktif</option><option :value="false">Nonaktif</option></select></td><td><button @click="updateSport(sport)">Simpan</button></td></tr></tbody></table></div>
    </section>

    <section v-else-if="tab === 'categories'" class="master-grid">
      <form class="form-card" @submit.prevent="addCategory"><h2>Tambah Kategori</h2><label>Cabor<select v-model="categoryForm.sport_id" required><option value="">Pilih</option><option v-for="sport in sports" :key="sport.id" :value="sport.id">{{ sport.name }}</option></select></label><label>Kode<input v-model="categoryForm.code" required /></label><label>Nama<input v-model="categoryForm.name" required /></label><label>Tipe<select v-model="categoryForm.competition_type"><option value="single">Tunggal</option><option value="doubles">Ganda</option><option value="team">Tim</option></select></label><label>Minimum Pemain<input v-model="categoryForm.min_members" type="number" min="1" /></label><label>Maksimum Pemain<input v-model="categoryForm.max_members" type="number" min="1" /></label><label>Tipe Skor<input v-model="categoryForm.scoring_type" required /></label><button :disabled="categoryForm.processing">Tambah</button></form>
      <div class="table-card"><table><thead><tr><th>Kategori</th><th>Pemain</th><th>Status</th><th></th></tr></thead><tbody><tr v-for="category in categories" :key="category.id"><td><input v-model="category.name" /><small>{{ category.sport.name }} · {{ category.code }}</small></td><td><div class="range"><input v-model="category.min_members" type="number" min="1" /><span>–</span><input v-model="category.max_members" type="number" min="1" /></div></td><td><select v-model="category.is_active"><option :value="true">Aktif</option><option :value="false">Nonaktif</option></select></td><td><button @click="updateCategory(category)">Simpan</button></td></tr></tbody></table></div>
    </section>

    <section v-else-if="tab === 'regulations'" class="master-grid">
      <form class="form-card" @submit.prevent="addRegulation"><h2>Terbitkan Versi Regulasi</h2><label>Cabor<select v-model="regulationForm.sport_id" required><option value="">Pilih</option><option v-for="sport in sports" :key="sport.id" :value="sport.id">{{ sport.name }}</option></select></label><label>Judul<input v-model="regulationForm.title" required /></label><label class="wide">Isi Regulasi<textarea v-model="regulationForm.content" rows="8" required /></label><label class="wide">URL Dokumen Technical Meeting<input v-model="regulationForm.document_url" type="url" /></label><button :disabled="regulationForm.processing">Terbitkan Versi Baru</button></form>
      <div class="table-card"><table><thead><tr><th>Regulasi</th><th>Versi</th><th>Dokumen</th></tr></thead><tbody><tr v-for="item in regulations" :key="item.id"><td><strong>{{ item.title }}</strong><small>{{ item.sport.name }}</small></td><td>v{{ item.version }}</td><td><a v-if="item.document_url" :href="item.document_url" target="_blank" rel="noopener">Buka</a><span v-else>—</span></td></tr></tbody></table></div>
    </section>

    <section v-else class="table-card"><table><thead><tr><th>Entitas</th><th>Aksi</th><th>Waktu</th></tr></thead><tbody><tr v-for="item in audits" :key="item.id"><td>{{ item.entity_type }} #{{ item.entity_id }}</td><td>{{ item.action }}</td><td>{{ item.created_at }}</td></tr></tbody></table></section>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 8px 0 24px; }.tabs { display:flex; gap:8px; margin-bottom:18px; }.tabs button { padding:10px 14px; color:#1946a3; background:#fff; border:1px solid #cbd8df; font-weight:800; }.tabs button.active { color:#fff; background:#1946a3; }.master-grid { display:grid; grid-template-columns:340px minmax(0,1fr); gap:18px; }.form-card,.table-card { padding:20px; background:#fff; border:1px solid #dbe5ea; }.form-card { display:grid; gap:12px; align-content:start; }.form-card h2 { margin:0 0 4px; font-size:18px; }.form-card label { display:grid; gap:6px; color:#60717f; font-size:11px; font-weight:800; }.form-card input,.form-card select,.form-card textarea,td input,td select { width:100%; min-height:40px; padding:9px 10px; border:1px solid #cbd8df; background:#fff; }.form-card button,td button { min-height:38px; padding:9px 12px; color:#fff; background:#1946a3; border:0; font-weight:800; }.table-card { overflow:auto; padding:0; }table { width:100%; border-collapse:collapse; }th,td { padding:13px 15px; border-bottom:1px solid #e5ecef; text-align:left; vertical-align:middle; }th { color:#60717f; background:#f8fafb; font-size:10px; text-transform:uppercase; }td small { display:block; margin-top:4px; color:#738390; }.range { display:grid; grid-template-columns:70px auto 70px; align-items:center; gap:6px; }.wide { grid-column:1/-1; }a { color:#1946a3; font-weight:800; }@media(max-width:900px){.master-grid{grid-template-columns:1fr}.tabs{overflow:auto}}
</style>
