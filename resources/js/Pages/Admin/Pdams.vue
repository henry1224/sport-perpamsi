<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ActionIconButton from '../../Components/ActionIconButton.vue';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import Modal from '../../Components/Modal.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

const props = defineProps({ pdams: Object, provinces: Array, filters: Object });
const open = ref(false);
const editing = ref(null);
const form = useForm({ code: '', name: '', province_id: '', city: '', address: '' });
const showForm = (pdam = null) => { editing.value = pdam; form.clearErrors(); Object.assign(form, pdam ? { code: pdam.code, name: pdam.name, province_id: pdam.province_id, city: pdam.city || '', address: pdam.address || '' } : { code: '', name: '', province_id: '', city: '', address: '' }); open.value = true; };
const save = () => { const options = { preserveScroll: true, onSuccess: () => { open.value = false; form.reset(); } }; editing.value ? form.put(`/admin/pdams/${editing.value.id}`, options) : form.post('/admin/pdams', options); };
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head"><SectionTitle eyebrow="Master Data" title="Master PDAM" :meta="`${pdams.total} PDAM`" /></div>
    <section class="overview-card"><div><span>Direktori PDAM</span><h2>Sumber asal seluruh pemain yang didaftarkan.</h2><p>Data provinsi, kota, dan identitas PDAM dipakai pada registrasi peserta.</p></div><dl><div><dt>Total PDAM</dt><dd>{{ pdams.total }}</dd></div><div><dt>Provinsi</dt><dd>{{ provinces.length }}</dd></div><div><dt>Data Tampil</dt><dd>{{ pdams.data.length }}</dd></div></dl></section>
    <div class="section-actions"><div><strong>Daftar Master PDAM</strong><span>Kelola identitas dan wilayah asal pemain.</span></div><button type="button" @click="showForm()">Tambah PDAM</button></div>

    <AdminDataTable :paginator="pdams" :filters="filters" item-label="PDAM" search-placeholder="Cari nama, kode, atau kota" :filter-options="provinces.map(item => ({ value: String(item.id), label: item.name }))" v-slot="{ rows }">
      <table><thead><tr><th>PDAM</th><th>Provinsi</th><th>Kota</th><th>Pemain</th><th class="actions-heading">Aksi</th></tr></thead><tbody>
        <tr v-for="pdam in rows" :key="pdam.id"><td><div class="identity"><span>{{ pdam.name.slice(0, 2).toUpperCase() }}</span><div><strong>{{ pdam.name }}</strong><small>{{ pdam.code }}</small></div></div></td><td>{{ pdam.province?.name || '—' }}</td><td>{{ pdam.city || '—' }}</td><td><strong class="count">{{ pdam.members_count }}</strong></td><td><div class="row-actions"><ActionIconButton icon="edit" label="Edit PDAM" @click="showForm(pdam)" /></div></td></tr>
        <tr v-if="!rows.length" class="empty-row"><td colspan="5">Data PDAM tidak ditemukan.</td></tr>
      </tbody></table>
    </AdminDataTable>

    <Modal :open="open" :title="editing ? 'Edit Master PDAM' : 'Tambah Master PDAM'" theme="light" @close="open = false">
      <form class="modal-form" @submit.prevent="save">
        <section class="form-section"><header><span>01</span><div><strong>Identitas PDAM</strong><small>Kode dan nama dipakai sebagai referensi asal pemain.</small></div></header><div class="fields"><label>Kode PDAM<input v-model="form.code" required /></label><label>Nama PDAM<input v-model="form.name" required /></label></div></section>
        <section class="form-section location"><header><span>02</span><div><strong>Wilayah & Alamat</strong><small>Tetapkan provinsi, kota, dan alamat kantor PDAM.</small></div></header><div class="fields"><label>Provinsi<select v-model="form.province_id" required><option value="">Pilih provinsi</option><option v-for="province in provinces" :key="province.id" :value="province.id">{{ province.name }}</option></select></label><label>Kota<input v-model="form.city" /></label><label class="wide">Alamat<textarea v-model="form.address" rows="3" /></label></div></section>
        <small v-if="Object.keys(form.errors).length" class="error">Periksa kembali data wajib dan kode unik.</small><footer><button type="button" class="outline" @click="open = false">Batal</button><button class="primary" :disabled="form.processing">{{ form.processing ? 'Menyimpan…' : 'Simpan PDAM' }}</button></footer>
      </form>
    </Modal>
  </PortalLayout>
</template>

<style scoped>
.page-head{padding:8px 0 6px}.overview-card{display:grid;grid-template-columns:1.2fr 1fr;gap:28px;margin-bottom:18px;padding:26px 28px;color:#fff;background:linear-gradient(135deg,#0b1d3d,#1946a3);border:1px solid rgba(54,194,240,.28);border-radius:16px;box-shadow:0 14px 34px rgba(7,17,38,.16)}.overview-card>div>span{color:#36c2f0;font-size:10px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}.overview-card h2{margin:6px 0;color:#fff;font-size:24px}.overview-card p{margin:0;color:#c7d8eb;font-size:12px;line-height:1.6}.overview-card dl{display:grid;grid-template-columns:repeat(3,1fr);margin:0;overflow:hidden;border:1px solid rgba(255,255,255,.13);border-radius:11px}.overview-card dl div{display:grid;place-content:center;padding:14px;border-right:1px solid rgba(255,255,255,.13);text-align:center}.overview-card dl div:last-child{border-right:0}.overview-card dt{color:#aac1d8;font-size:9px;font-weight:800;text-transform:uppercase}.overview-card dd{margin:5px 0 0;color:#fff;font-size:21px;font-weight:900}.section-actions{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:14px;padding:18px 20px;background:#fff;border:1px solid #d9e3e9;border-radius:14px}.section-actions div{display:grid;gap:4px}.section-actions strong{color:#142536;font-size:17px}.section-actions span{color:#71808b;font-size:12px}.section-actions button,.modal-form footer button{min-height:40px;padding:9px 14px;border-radius:8px;font-weight:800;cursor:pointer}.section-actions button,.primary{color:#fff;background:#1946a3;border:1px solid #1946a3}.identity{display:flex;align-items:center;gap:11px;min-width:240px}.identity>span{display:grid;place-items:center;width:38px;height:38px;flex:0 0 auto;color:#1946a3;background:#edf4fc;border:1px solid #ceddef;border-radius:10px;font-size:11px;font-weight:900}.identity>div{display:grid;gap:3px}.identity small{color:#71808b}.count{color:#1946a3;font-size:18px}.actions-heading{text-align:right}.modal-form{display:grid;overflow:hidden;background:#f6f8fa}.form-section{display:grid;gap:16px;padding:20px 22px;background:#fff;border-bottom:1px solid #e4ebef}.form-section>header{display:flex;align-items:flex-start;gap:12px}.form-section>header>span{display:grid;place-items:center;width:30px;height:30px;flex:0 0 auto;color:#1946a3;background:#eaf1fb;border:1px solid #cfdded;border-radius:8px;font-size:10px;font-weight:900}.form-section header div{display:grid;gap:3px}.form-section header strong{color:#172535;font-size:14px}.form-section header small{color:#71808b;font-size:11px}.location{background:#f5f8fa}.fields{display:grid;grid-template-columns:1fr 1.5fr;gap:14px}.fields label{display:grid;gap:6px;color:#526875;font-size:10px;font-weight:800;text-transform:uppercase}.fields input,.fields select,.fields textarea{width:100%;padding:10px 12px;border:1px solid #cedbe1;border-radius:8px}.wide{grid-column:1/-1}.error{margin:14px 22px 0;color:#a1432e}.modal-form footer{display:flex;justify-content:flex-end;gap:9px;padding:16px 22px;background:#f7f9fa}.outline{color:#1946a3;background:#fff;border:1px solid #bfd0da}@media(max-width:800px){.overview-card{grid-template-columns:1fr}.section-actions{align-items:stretch;flex-direction:column}.fields{grid-template-columns:1fr}.wide{grid-column:auto}.overview-card dl{grid-template-columns:1fr}.overview-card dl div{border-right:0;border-bottom:1px solid rgba(255,255,255,.13)}}
</style>
