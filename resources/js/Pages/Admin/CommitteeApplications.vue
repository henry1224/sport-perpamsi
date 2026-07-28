<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import ActionIconButton from '../../Components/ActionIconButton.vue';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import Modal from '../../Components/Modal.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import { formatDateTime } from '../../lib/date';

const props = defineProps({ applications: Object, filters: Object, stats: Object });
const labels = { pending: 'Menunggu', revision_required: 'Perlu Perbaikan', verified: 'Terverifikasi', rejected: 'Ditolak' };
const decisionTones = { rejected: 'ditolak', revision_required: 'perlu-perbaikan', verified: 'terverifikasi' };
const detailTarget = ref(null);
const reviewTarget = ref(null);
const reviewAction = ref('');
const reviewForm = useForm({ note: '' });
const reviewMeta = computed(() => ({
  verify: { title: 'Verifikasi Pengurus Daerah', heading: 'Aktifkan akses portal daerah?', description: 'Akun akan berstatus terverifikasi dan dapat masuk ke portal Pengurus Daerah.', button: 'Verifikasi Pengurus', tone: 'verify' },
  revision: { title: 'Minta Perbaikan Pengajuan', heading: 'Kembalikan pengajuan untuk diperbaiki?', description: 'Pengurus Daerah akan melihat catatan dan dapat mengajukan kembali data setelah diperbarui.', button: 'Kirim Catatan Perbaikan', tone: 'revision' },
  reject: { title: 'Tolak Pengajuan', heading: 'Tolak akses Pengurus Daerah?', description: 'Pengajuan ditutup dan wilayah dapat digunakan oleh pendaftar lain. Alasan penolakan wajib dicatat.', button: 'Tolak Pengajuan', tone: 'reject' },
}[reviewAction.value]));
const openReview = (application, action) => { reviewTarget.value = application; reviewAction.value = action; reviewForm.reset(); reviewForm.clearErrors(); };
const resetReview = () => { reviewTarget.value = null; reviewAction.value = ''; reviewForm.reset(); };
const closeReview = () => { if (!reviewForm.processing) resetReview(); };
const submitReview = () => reviewForm.post(`/admin/committee-applications/${reviewTarget.value.id}/${reviewAction.value}`, { preserveScroll: true, onSuccess: resetReview });
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head"><SectionTitle eyebrow="Akses Daerah" title="Verifikasi Pengurus Daerah" /></div>
    <section class="overview-card"><div><span>Verifikasi Akses</span><h2>Pastikan pengurus daerah memenuhi persyaratan akses.</h2><p>Periksa identitas, mandat, dan kontak sebelum akun dapat memakai portal daerah.</p></div><dl><div><dt>Menunggu Verifikasi</dt><dd>{{ stats.pending }}</dd></div><div><dt>Terverifikasi</dt><dd>{{ stats.verified }}</dd></div><div><dt>Ditolak</dt><dd>{{ stats.rejected }}</dd></div></dl></section>
    <div class="section-actions"><div><strong>Daftar Pengajuan Pengurus Daerah</strong><span>Periksa pengajuan dan tetapkan status sesuai hasil verifikasi.</span></div></div>
    <AdminDataTable :paginator="applications" :filters="filters" item-label="pengajuan" search-placeholder="Cari PD, nama, atau email" :filter-options="[
      { value: 'pending', label: 'Menunggu' }, { value: 'revision_required', label: 'Perlu Perbaikan' },
      { value: 'verified', label: 'Terverifikasi' }, { value: 'rejected', label: 'Ditolak' },
    ]" v-slot="{ rows }">
      <table>
        <thead><tr><th>Pengurus Daerah</th><th>Penanggung Jawab</th><th>Kontak</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead>
        <tbody>
          <tr v-for="application in rows" :key="application.id">
            <td><div class="primary-cell"><strong>{{ application.committee }}</strong></div></td>
            <td><div class="primary-cell"><strong>{{ application.name }}</strong><small>{{ application.position }}</small></div></td>
            <td><div class="primary-cell"><span>{{ application.email }}</span><small>{{ application.phone || '—' }}</small></div></td>
            <td><span :class="['status-badge', { success: application.status === 'verified', danger: application.status === 'rejected', info: application.status === 'revision_required' }]">{{ labels[application.status] }}</span></td>
            <td><div class="row-actions"><ActionIconButton icon="show" label="Lihat pengajuan" @click="detailTarget = application" /><template v-if="['pending', 'revision_required'].includes(application.status)"><button class="verify-action" type="button" @click="openReview(application, 'verify')">Verifikasi</button><button type="button" @click="openReview(application, 'revision')">Perbaikan</button><button class="danger" type="button" @click="openReview(application, 'reject')">Tolak</button></template></div></td>
          </tr>
          <tr v-if="!rows.length" class="empty-row"><td colspan="5">Tidak ada pengajuan sesuai filter.</td></tr>
        </tbody>
      </table>
    </AdminDataTable>

    <Modal :open="!!detailTarget" title="Detail Pengajuan Pengurus Daerah" theme="light" @close="detailTarget = null">
      <section v-if="detailTarget" class="detail-modal">
        <header><div><span>Pengajuan Akses</span><h3>{{ detailTarget.committee }}</h3><p>{{ detailTarget.name }} · {{ detailTarget.position }}</p></div><span :class="['status-badge', { success: detailTarget.status === 'verified', danger: detailTarget.status === 'rejected', info: detailTarget.status === 'revision_required' }]">{{ labels[detailTarget.status] }}</span></header>
        <dl><div><dt>Penanggung Jawab</dt><dd>{{ detailTarget.name }}</dd></div><div><dt>Jabatan</dt><dd>{{ detailTarget.position }}</dd></div><div><dt>Email</dt><dd>{{ detailTarget.email }}</dd></div><div><dt>Telepon</dt><dd>{{ detailTarget.phone || '—' }}</dd></div></dl>
        <section v-if="detailTarget.review_note || detailTarget.reviewed_at" :class="['decision-history', decisionTones[detailTarget.status]]"><span>{{ detailTarget.status === 'rejected' ? 'Alasan Penolakan' : detailTarget.status === 'revision_required' ? 'Catatan Perbaikan' : 'Keputusan Verifikasi' }}</span><strong>{{ detailTarget.review_note || (detailTarget.status === 'verified' ? 'Pengajuan disetujui.' : 'Catatan tidak tersedia.') }}</strong><small>{{ detailTarget.status === 'rejected' ? 'Ditolak' : detailTarget.status === 'revision_required' ? 'Diminta perbaikan' : 'Diverifikasi' }} {{ formatDateTime(detailTarget.reviewed_at) }}</small></section>
        <footer><button type="button" @click="detailTarget = null">Tutup</button></footer>
      </section>
    </Modal>

    <Modal :open="!!reviewTarget" :title="reviewMeta?.title || 'Konfirmasi'" theme="light" @close="closeReview">
      <form v-if="reviewTarget && reviewMeta" class="review-modal" @submit.prevent="submitReview">
        <section :class="['review-summary', reviewMeta.tone]"><span aria-hidden="true">{{ reviewAction === 'verify' ? '✓' : reviewAction === 'revision' ? '!' : '×' }}</span><div><strong>{{ reviewMeta.heading }}</strong><p>{{ reviewMeta.description }}</p></div></section>
        <dl><div><dt>Pengurus Daerah</dt><dd>{{ reviewTarget.committee }}</dd></div><div><dt>Penanggung Jawab</dt><dd>{{ reviewTarget.name }}</dd></div><div><dt>Email</dt><dd>{{ reviewTarget.email }}</dd></div><div><dt>Status Saat Ini</dt><dd>{{ labels[reviewTarget.status] }}</dd></div></dl>
        <label v-if="reviewAction !== 'verify'">{{ reviewAction === 'revision' ? 'Catatan Perbaikan' : 'Alasan Penolakan' }}<textarea v-model.trim="reviewForm.note" rows="4" maxlength="255" required :placeholder="reviewAction === 'revision' ? 'Jelaskan data yang harus diperbaiki…' : 'Jelaskan alasan pengajuan ditolak…'" /><small>{{ reviewForm.note.length }}/255 karakter</small><em v-if="reviewForm.errors.note">{{ reviewForm.errors.note }}</em></label>
        <footer><button type="button" class="outline" :disabled="reviewForm.processing" @click="closeReview">Batal</button><button type="submit" :class="['confirm', reviewMeta.tone]" :disabled="reviewForm.processing || (reviewAction !== 'verify' && !reviewForm.note)">{{ reviewForm.processing ? 'Memproses…' : reviewMeta.button }}</button></footer>
      </form>
    </Modal>
  </PortalLayout>
</template>

<style scoped>
.page-head{padding:8px 0 6px}.overview-card{display:grid;grid-template-columns:1.2fr 1fr;gap:28px;margin-bottom:18px;padding:26px 28px;color:#fff;background:linear-gradient(135deg,#0b1d3d,#1946a3);border:1px solid rgba(54,194,240,.28);border-radius:16px;box-shadow:0 14px 34px rgba(7,17,38,.16)}.overview-card>div>span{color:#36c2f0;font-size:10px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}.overview-card h2{margin:6px 0;color:#fff;font-size:24px}.overview-card p{margin:0;color:#c7d8eb;font-size:12px;line-height:1.6}.overview-card dl{display:grid;grid-template-columns:repeat(3,1fr);margin:0;overflow:hidden;border:1px solid rgba(255,255,255,.13);border-radius:11px}.overview-card dl div{display:grid;place-content:center;padding:14px;border-right:1px solid rgba(255,255,255,.13);text-align:center}.overview-card dl div:last-child{border-right:0}.overview-card dt{color:#aac1d8;font-size:9px;font-weight:800;text-transform:uppercase}.overview-card dd{margin:5px 0 0;color:#fff;font-size:21px;font-weight:900}.section-actions{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:14px;padding:18px 20px;background:#fff;border:1px solid #d9e3e9;border-radius:14px}.section-actions div{display:grid;gap:4px}.section-actions strong{color:#142536;font-size:17px}.section-actions span{color:#71808b;font-size:12px}:deep(.row-actions .verify-action){color:#fff;background:#1946a3;border-color:#1946a3;box-shadow:0 4px 10px rgba(25,70,163,.18)}:deep(.row-actions .verify-action:hover){background:#123b8a;border-color:#123b8a}.detail-modal{display:grid}.detail-modal>header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding:20px 22px;background:#f7f9fb;border-bottom:1px solid #e2e9ed}.detail-modal>header div{display:grid;gap:4px}.detail-modal>header div>span{color:#1946a3;font-size:9px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.detail-modal h3{margin:0;color:#172535;font-size:18px}.detail-modal header p{margin:0;color:#71808b;font-size:11px}.detail-modal dl,.review-modal dl{display:grid;grid-template-columns:repeat(2,1fr);gap:1px;margin:0;background:#dfe7eb;border-bottom:1px solid #dfe7eb}.detail-modal dl div,.review-modal dl div{display:grid;gap:4px;padding:14px 18px;background:#fff}.detail-modal dt,.review-modal dt{color:#71808b;font-size:9px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.detail-modal dd,.review-modal dd{margin:0;color:#263d4d;font-size:12px;font-weight:750}.decision-history{display:grid;gap:6px;margin:18px 22px;padding:16px 18px;background:#f4f7f9;border:1px solid #d9e3e9;border-left:4px solid #1946a3;border-radius:10px}.decision-history.ditolak{background:#fff6f2;border-color:#f0d3c8;border-left-color:#a1432e}.decision-history.perlu-perbaikan{background:#fff9e8;border-color:#ecdca6;border-left-color:#d3a925}.decision-history span{color:#71808b;font-size:9px;font-weight:900;letter-spacing:.09em;text-transform:uppercase}.decision-history strong{color:#263d4d;font-size:13px;line-height:1.5}.decision-history small{color:#71808b;font-size:10px}.detail-modal footer{display:flex;justify-content:flex-end;padding:16px 22px;background:#f7f9fa;border-top:1px solid #e2e9ed}.detail-modal footer button{min-height:40px;padding:9px 14px;color:#fff;background:#1946a3;border:1px solid #1946a3;border-radius:8px;font-weight:800;cursor:pointer}.review-modal{display:grid}.review-summary{display:grid;grid-template-columns:42px 1fr;gap:14px;padding:20px 22px;background:#f7f9fb;border-bottom:1px solid #e2e9ed}.review-summary>span{display:grid;place-items:center;width:42px;height:42px;color:#fff;background:#1946a3;border-radius:10px;font-size:22px;font-weight:900}.review-summary.revision>span{color:#745b00;background:#f6c64a}.review-summary.reject>span{background:#a1432e}.review-summary div{display:grid;gap:5px}.review-summary strong{color:#172535;font-size:15px}.review-summary p{margin:0;color:#60717f;font-size:11px;line-height:1.55}.review-modal>label{display:grid;gap:7px;padding:18px 22px;color:#526875;font-size:10px;font-weight:850;letter-spacing:.06em;text-transform:uppercase}.review-modal textarea{width:100%;padding:11px 12px;color:#172535;background:#fff;border:1px solid #cbd8df;border-radius:8px;font:inherit;line-height:1.5;resize:vertical;text-transform:none}.review-modal textarea:focus{border-color:#1946a3;outline:0;box-shadow:0 0 0 3px rgba(25,70,163,.1)}.review-modal label small{color:#84929c;font-size:9px;font-weight:650;text-align:right;text-transform:none}.review-modal label em{color:#a1432e;font-size:10px;font-style:normal;text-transform:none}.review-modal footer{display:flex;justify-content:flex-end;gap:9px;padding:16px 22px;background:#f7f9fa;border-top:1px solid #e2e9ed}.review-modal footer button{min-height:40px;padding:9px 14px;border-radius:8px;font-weight:800;cursor:pointer}.review-modal .outline{color:#1946a3;background:#fff;border:1px solid #bfd0da}.review-modal .confirm{color:#fff;background:#1946a3;border:1px solid #1946a3}.review-modal .confirm.revision{color:#5d4700;background:#f6c64a;border-color:#e4b936}.review-modal .confirm.reject{background:#a1432e;border-color:#a1432e}.review-modal button:disabled{opacity:.55;cursor:wait}@media(max-width:800px){.overview-card{grid-template-columns:1fr}.overview-card dl,.detail-modal dl,.review-modal dl{grid-template-columns:1fr}.overview-card dl div{border-right:0;border-bottom:1px solid rgba(255,255,255,.13)}.overview-card dl div:last-child{border-bottom:0}}
</style>
