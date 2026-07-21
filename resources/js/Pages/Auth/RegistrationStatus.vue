<script setup>
import { router, useForm } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

const props = defineProps({ application: Object, applicant: Object });
const form = useForm({ ...props.applicant });
const labels = {
  pending: 'Menunggu Verifikasi',
  revision_required: 'Perlu Perbaikan',
  verified: 'Terverifikasi',
  rejected: 'Ditolak',
};
const logout = () => router.post('/logout');
const resubmit = () => form.put('/registration-status');
</script>

<template>
  <PublicLayout>
    <div class="page-head"><SectionTitle eyebrow="Status Pengajuan" :title="application.committee" :meta="labels[application.status]" /></div>
    <section class="status-card" :class="application.status">
      <span>{{ labels[application.status] }}</span>
      <h2>{{ application.status === 'verified' ? 'Akses Pengurus Daerah sudah aktif.' : 'Pengajuan sedang diproses.' }}</h2>
      <p v-if="application.review_note"><b>Catatan Admin:</b> {{ application.review_note }}</p>
      <p>Terakhir diperbarui {{ application.updated_at }}.</p>
      <form v-if="application.status === 'revision_required'" @submit.prevent="resubmit">
        <label><span>Nama</span><input v-model="form.name" required /><small v-if="form.errors.name">{{ form.errors.name }}</small></label>
        <label><span>Jabatan</span><input v-model="form.position" required /><small v-if="form.errors.position">{{ form.errors.position }}</small></label>
        <label><span>Nomor Telepon</span><input v-model="form.phone" required /><small v-if="form.errors.phone">{{ form.errors.phone }}</small></label>
        <button type="submit" :disabled="form.processing">Kirim Ulang Perbaikan</button>
      </form>
      <a v-if="application.status === 'verified'" href="/pd/dashboard">Buka Portal Pengurus Daerah</a>
      <button v-else type="button" @click="logout">Keluar</button>
    </section>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.status-card { max-width: 760px; padding: 34px; background: #071126; border: 1px solid rgba(255,255,255,.12); border-left: 8px solid #f6c64a; box-shadow: 12px 12px 0 rgba(54,194,240,.13); }
.status-card > span { color: #f6c64a; font-size: 11px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
h2 { max-width: 600px; margin: 12px 0 22px; font-size: clamp(28px, 5vw, 54px); line-height: .98; text-transform: uppercase; }
p { color: rgba(255,255,255,.65); }
form { display: grid; gap: 12px; max-width: 520px; margin-top: 22px; }
label { display: grid; gap: 6px; color: #36c2f0; font-size: 11px; font-weight: 900; text-transform: uppercase; }
input { padding: 12px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.16); font: inherit; }
small { color: #f05a28; }
a, button { display: inline-block; margin-top: 18px; padding: 13px 18px; border: 0; background: #f6c64a; color: #071126; font-weight: 1000; text-decoration: none; text-transform: uppercase; box-shadow: 5px 5px 0 #f05a28; cursor: pointer; }
.verified { border-left-color: #20c6b7; }
.rejected { border-left-color: #f05a28; }
</style>
