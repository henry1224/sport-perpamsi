<script setup>
import { router, useForm } from '@inertiajs/vue3';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';

defineProps({ assignments: Object, filters: Object, staff: Array, sports: Array, venues: Array });

const userForm = useForm({ name: '', email: '', password: '', password_confirmation: '', role: 'scorekeeper' });
const assignmentForm = useForm({ user_id: '', sport_id: '', venue_id: '' });

const createUser = () => userForm.post('/admin/assignments/users', { onSuccess: () => userForm.reset() });
const assign = () => assignmentForm.post('/admin/assignments', { onSuccess: () => assignmentForm.reset() });
const revoke = (id) => router.post(`/admin/assignments/${id}/revoke`, {}, { preserveScroll: true });
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head"><SectionTitle eyebrow="Operasional" title="Panitia & Akses Venue" meta="Assignment cabor dan venue" /></div>

    <div class="forms-grid">
      <form class="form-card" @submit.prevent="createUser">
        <h2>Buat Akun Panitia</h2>
        <label>Nama<input v-model="userForm.name" required /></label>
        <label>Email<input v-model="userForm.email" type="email" required /></label>
        <label>Peran<select v-model="userForm.role"><option value="scorekeeper">Scorekeeper</option><option value="sport_coordinator">Koordinator Cabor</option></select></label>
        <label>Password<input v-model="userForm.password" type="password" minlength="8" required /></label>
        <label>Ulangi Password<input v-model="userForm.password_confirmation" type="password" minlength="8" required /></label>
        <button :disabled="userForm.processing">{{ userForm.processing ? 'Menyimpan' : 'Buat Akun' }}</button>
      </form>

      <form class="form-card" @submit.prevent="assign">
        <h2>Tetapkan Panitia</h2>
        <label>Panitia<select v-model="assignmentForm.user_id" required><option value="">Pilih panitia</option><option v-for="item in staff" :key="item.id" :value="item.id">{{ item.name }} — {{ item.email }}</option></select></label>
        <label>Cabor<select v-model="assignmentForm.sport_id" required><option value="">Pilih cabor</option><option v-for="item in sports" :key="item.id" :value="item.id">{{ item.name }}</option></select></label>
        <label>Venue<select v-model="assignmentForm.venue_id" required><option value="">Pilih venue</option><option v-for="item in venues" :key="item.id" :value="item.id">{{ item.name }}{{ item.city ? ` — ${item.city}` : '' }}</option></select></label>
        <button :disabled="assignmentForm.processing">{{ assignmentForm.processing ? 'Menyimpan' : 'Tetapkan' }}</button>
      </form>
    </div>

    <AdminDataTable :paginator="assignments" :filters="filters" item-label="assignment" search-placeholder="Cari panitia, cabor, atau venue…" :filter-options="[{ value: 'active', label: 'Aktif' }, { value: 'inactive', label: 'Tidak Aktif' }]">
      <template #default="{ rows }">
        <table>
          <thead><tr><th>Panitia</th><th>Cabor</th><th>Venue</th><th>Tugas</th><th>Status</th><th></th></tr></thead>
          <tbody>
            <tr v-for="row in rows" :key="row.id">
              <td><div class="primary-cell"><strong>{{ row.name }}</strong><small>{{ row.email }}</small></div></td>
              <td>{{ row.sport }}</td>
              <td><div class="primary-cell"><strong>{{ row.venue }}</strong><small>{{ row.city || '—' }}</small></div></td>
              <td>{{ row.role === 'scorekeeper' ? 'Scorekeeper' : 'Koordinator Cabor' }}</td>
              <td><span :class="['status-badge', row.is_active ? 'success' : 'danger']">{{ row.is_active ? 'Aktif' : 'Tidak Aktif' }}</span></td>
              <td><div class="row-actions"><button v-if="row.is_active" class="danger" type="button" @click="revoke(row.id)">Nonaktifkan</button></div></td>
            </tr>
          </tbody>
        </table>
      </template>
    </AdminDataTable>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 8px 0 24px; }
.forms-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-bottom: 24px; }
.form-card { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; padding: 20px; background: #fff; border: 1px solid #dbe5ea; }
.form-card h2 { grid-column: 1 / -1; margin: 0; color: #102132; font-size: 18px; }
label { display: grid; gap: 7px; color: #60717f; font-size: 11px; font-weight: 800; }
input, select { min-height: 42px; padding: 10px 12px; color: #172535; background: #fff; border: 1px solid #cbd8df; }
button { min-height: 42px; padding: 10px 14px; color: #fff; background: #1946a3; border: 0; font-weight: 800; cursor: pointer; }
button:disabled { opacity: .6; cursor: wait; }
@media (max-width: 900px) { .forms-grid, .form-card { grid-template-columns: 1fr; } }
</style>
