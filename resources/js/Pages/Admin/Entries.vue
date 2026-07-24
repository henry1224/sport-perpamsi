<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import Modal from '../../Components/Modal.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import Sheet from '../../Components/Sheet.vue';
import { statusLabel } from '../../lib/status';

const props = defineProps({ entries: Object, filters: Object });
const page = usePage();
const flash = computed(() => page.props.flash || {});
const selectedId = ref(null);
const teamAction = ref(null);
const teamReason = ref('');
const noteAction = ref(null);
const actionNote = ref('');
const selected = computed(() => props.entries.data.find((entry) => entry.id === selectedId.value) || null);
const remaining = (entry) => Math.max(0, entry.players_count - entry.verified_players_count);
const approvalBlocker = (entry) => remaining(entry) ? `${remaining(entry)} pemain belum selesai` : !entry.teams_ready ? 'Semua tim aktif harus disetujui' : '';
const progress = (entry) => entry.players_count ? Math.round((entry.verified_players_count / entry.players_count) * 100) : 0;
const documentLabel = (key) => ({ photo: 'Foto 3×4', registration_form: 'Form Pendaftaran', identity_card: 'KTP', pension_card: 'Kartu Pensiun', employee_decree: 'SK Karyawan Tetap' }[key] || key);

const approve = (id) => router.post(`/admin/entries/${id}/verify`, {}, { preserveScroll: true });
const openNoteAction = (config) => { noteAction.value = config; actionNote.value = ''; };
const closeNoteAction = () => { noteAction.value = null; actionNote.value = ''; };
const submitNoteAction = () => { if (noteAction.value && actionNote.value.trim()) router.post(noteAction.value.url, { note: actionNote.value.trim() }, { preserveScroll: true, onSuccess: closeNoteAction }); };
const reject = (entry) => openNoteAction({ title: 'Tolak Pendaftaran', eyebrow: 'Keputusan Pendaftaran', name: entry.display_name, description: 'Penolakan bersifat final sampai Admin membuka kembali alur pemeriksaan.', placeholder: 'Jelaskan alasan penolakan pendaftaran', submit: 'Tolak Pendaftaran', danger: true, url: `/admin/entries/${entry.id}/reject` });
const requestRevision = (entry) => openNoteAction({ title: 'Minta Perbaikan Roster', eyebrow: 'Perbaikan Pendaftaran', name: entry.display_name, description: 'Catatan berlaku untuk seluruh pendaftaran dan akan tampil pada portal Pengurus Daerah.', placeholder: 'Jelaskan bagian roster yang perlu diperbaiki', submit: 'Kirim Permintaan Perbaikan', url: `/admin/entries/${entry.id}/revision` });
const openTeamAction = (team, status) => { teamAction.value = { id: team.id, label: team.label, status }; teamReason.value = ''; };
const closeTeamAction = () => { teamAction.value = null; teamReason.value = ''; };
const submitTeamAction = () => {
  if (!teamAction.value || !teamReason.value.trim()) return;
  if (teamAction.value.status === 'reset') {
    router.delete(`/admin/entry-teams/${teamAction.value.id}/override`, { data: { reason: teamReason.value.trim() }, preserveScroll: true, onSuccess: closeTeamAction });
    return;
  }
  router.post(`/admin/entry-teams/${teamAction.value.id}/override`, { status: teamAction.value.status, reason: teamReason.value.trim() }, { preserveScroll: true, onSuccess: closeTeamAction });
};
const verifyMember = (id) => router.post(`/admin/entry-members/${id}/verify`, {}, { preserveScroll: true });
const openPlayerRevision = (member) => openNoteAction({ title: 'Minta Perbaikan Pemain', eyebrow: 'Data Pemain', name: member.name, description: 'Jelaskan data atau dokumen yang perlu diperbaiki. Catatan tampil pada roster PD.', placeholder: 'Contoh: Foto kurang jelas atau nomor identitas tidak sesuai', submit: 'Kirim Permintaan Perbaikan', url: `/admin/entry-members/${member.id}/revision` });
const rejectMember = (member) => openNoteAction({ title: 'Tolak Pemain', eyebrow: 'Keputusan Pemain', name: member.name, description: 'Pemain ditolak dan tidak dapat disetujui sebelum keputusan diperbarui oleh Admin.', placeholder: 'Jelaskan alasan penolakan pemain', submit: 'Tolak Pemain', danger: true, url: `/admin/entry-members/${member.id}/reject` });
</script>

<template>
  <PortalLayout portal="admin">
    <div class="page-head"><SectionTitle eyebrow="Verifikasi Pendaftaran" title="Antrian Peserta" :meta="`${entries.total} PD menunggu pemeriksaan`" /></div>
    <div v-if="flash.success" class="flash">{{ flash.success }}</div><div v-if="flash.error" class="flash error">{{ flash.error }}</div>

    <AdminDataTable :paginator="entries" :filters="filters" item-label="pendaftaran" search-placeholder="Cari PD, kompetisi, atau pemain" :filter-options="[{ value: 'registration_open', label: 'Pendaftaran Dibuka' }, { value: 'registration_closed', label: 'Pendaftaran Ditutup' }, { value: 'bracket_locked', label: 'Bracket Dikunci' }]" v-slot="{ rows }">
      <table class="verification-table">
        <thead><tr><th>Pengurus Daerah</th><th>Kompetisi</th><th>Progres Pemeriksaan</th><th>Status</th><th class="actions-heading">Aksi</th></tr></thead>
        <tbody>
          <tr v-for="entry in rows" :key="entry.id" class="entry-row" @click="selectedId = entry.id">
            <td><div class="committee-cell"><span class="committee-mark">PD</span><div><strong>{{ entry.display_name }}</strong><small>{{ entry.committee }}</small></div></div></td>
            <td><div class="primary-cell"><strong>{{ entry.event }}</strong><small>{{ entry.event_code }} · {{ statusLabel(entry.event_status) }}</small></div></td>
            <td><div class="progress-cell"><div><strong>{{ entry.verified_players_count }}/{{ entry.players_count }}</strong><span>pemain terverifikasi</span><b>{{ progress(entry) }}%</b></div><div class="progress-track"><i :style="{ width: `${progress(entry)}%` }"></i></div><small :class="{ complete: !remaining(entry) }">{{ remaining(entry) ? `${remaining(entry)} pemain perlu diperiksa` : 'Seluruh pemain selesai diperiksa' }}</small></div></td>
            <td><span :class="['review-status', entry.verification_status === 'rejected' ? 'rejected' : remaining(entry) ? 'pending' : 'ready']"><i></i>{{ entry.verification_status === 'rejected' ? 'Ditolak' : remaining(entry) ? 'Belum lengkap' : 'Siap disetujui' }}</span></td>
            <td><button type="button" class="inspect-button" @click.stop="selectedId = entry.id">Periksa <span>›</span></button></td>
          </tr>
          <tr v-if="!rows.length" class="empty-row"><td colspan="5">Tidak ada pendaftaran sesuai filter.</td></tr>
        </tbody>
      </table>
    </AdminDataTable>

    <Sheet :open="!!selected" :title="selected?.display_name || ''" :description="selected ? `${selected.event} · ${selected.event_code}` : ''" @close="selectedId = null">
      <template v-if="selected">
        <section class="review-overview">
          <div class="review-score"><span>Progres Verifikasi</span><strong>{{ selected.verified_players_count }}/{{ selected.players_count }}</strong><small>pemain selesai</small></div>
          <div class="review-meta"><div><span>Status Pendaftaran</span><strong>{{ statusLabel(selected.verification_status) }}</strong></div><div><span>Diajukan</span><strong>{{ selected.submitted_at || '—' }}</strong></div><div><span>Official</span><strong>{{ selected.officials.length }} orang</strong></div></div>
        </section>

        <div v-if="selected.verification_status === 'rejected'" class="approval-blocker"><span>!</span><div><strong>Pendaftaran ditolak</strong><p>{{ selected.verification_note }} Buka kembali sebagai perbaikan bila PD diizinkan mengajukan ulang.</p></div></div><div v-else-if="remaining(selected)" class="approval-blocker"><span>!</span><div><strong>Persetujuan belum tersedia</strong><p>{{ remaining(selected) }} pemain masih belum terverifikasi. Periksa pemain bertanda kuning atau merah.</p></div></div>
        <div v-else class="approval-ready"><span>✓</span><div><strong>Siap disetujui</strong><p>Seluruh pemain telah diverifikasi. Pendaftaran dapat disetujui.</p></div></div>

        <section v-for="team in selected.teams" :key="team.id" class="team-section">
          <header><div><span>Unit Peserta</span><h3>{{ team.label }}</h3></div><span :class="['team-state', team.effective_status]">{{ statusLabel(team.effective_status) }}<template v-if="team.override"> · override</template></span></header>
          <div class="player-list">
            <details v-for="(member, index) in team.members" :key="member.id" class="player-card" :open="member.verification_status !== 'verified'">
              <summary><span class="player-number">{{ String(index + 1).padStart(2, '0') }}</span><span class="player-name"><strong>{{ member.name }}</strong><small>{{ member.pdam }} · {{ member.identity }}</small></span><span :class="['member-status', member.verification_status]">{{ statusLabel(member.verification_status) }}</span><i></i></summary>
              <div class="player-detail">
                <div class="identity-facts"><span>Jenis Identitas<strong>{{ member.identity_type?.toUpperCase() || '—' }}</strong></span><span>Nomor Identitas<strong>{{ member.identity_number || '—' }}</strong></span><span>Pembaruan<strong>{{ member.updated_at || '—' }}</strong></span></div>
                <div class="document-grid"><a v-for="document in member.documents" :key="document.key" :href="document.url" target="_blank" rel="noopener"><img v-if="document.key === 'photo'" :src="document.url" alt="Foto pemain"><span>{{ documentLabel(document.key) }}</span><small>Buka dokumen ↗</small></a></div>
                <p v-if="member.verification_note" class="member-note"><strong>Catatan pemeriksaan</strong>{{ member.verification_note }}</p>
                <div v-if="member.audits.length" class="audit-list"><span v-for="audit in member.audits" :key="`${audit.action}-${audit.created_at}`"><b>{{ statusLabel(audit.action) }}</b>{{ audit.reason || 'Tanpa catatan' }} · {{ audit.created_at }}</span></div>
                <div v-if="team.effective_status === 'pending'" class="member-actions"><button v-if="member.verification_status !== 'verified'" class="primary" @click="verifyMember(member.id)">Verifikasi Pemain</button><button @click="openPlayerRevision(member)">Minta Perbaikan</button><button class="danger" @click="rejectMember(member)">Tolak</button></div>
              </div>
            </details>
          </div>
          <div v-if="selected.verification_status === 'pending'" class="team-actions"><span v-if="team.verified_players_count !== team.players_count" class="team-blocker">{{ team.players_count - team.verified_players_count }} pemain belum verified</span><button :disabled="team.players_count === 0 || team.verified_players_count !== team.players_count" :title="team.verified_players_count !== team.players_count ? 'Verifikasi seluruh pemain sebelum menyetujui tim' : 'Setujui tim'" @click="openTeamAction(team, 'verified')">Setujui Tim</button><button @click="openTeamAction(team, 'revision_required')">Perbaikan Tim</button><button class="danger" @click="openTeamAction(team, 'rejected')">Tolak Tim</button><button v-if="team.override" @click="openTeamAction(team, 'reset')">Reset Override</button></div>
        </section>

        <section v-if="selected.officials.length" class="official-section"><header><div><span>Kelengkapan Pendamping</span><h3>Official</h3></div><small>Official tidak masuk gate verifikasi pemain</small></header><details v-for="official in selected.officials" :key="official.id"><summary><strong>{{ official.name }}</strong><span>{{ official.role.replaceAll('_', ' ') }} · {{ official.document_count }}/2 dokumen</span></summary><nav><a v-for="document in official.documents" :key="document.key" :href="document.url" target="_blank" rel="noopener">{{ documentLabel(document.key) }} ↗</a></nav></details></section>
      </template>

      <template #footer><template v-if="selected"><template v-if="selected.verification_status === 'rejected'"><span class="footer-blocker">PD belum dapat mengajukan ulang</span><button type="button" class="approve" @click="requestRevision(selected)">Buka untuk Perbaikan</button></template><template v-else><span v-if="approvalBlocker(selected)" class="footer-blocker">{{ approvalBlocker(selected) }}</span><button type="button" @click="requestRevision(selected)">Perbaikan Roster</button><button type="button" class="danger" @click="reject(selected)">Tolak</button><button type="button" class="approve" :disabled="!!approvalBlocker(selected)" :title="approvalBlocker(selected) || 'Setujui pendaftaran'" @click="approve(selected.id)">Setujui Pendaftaran</button></template></template></template>
    </Sheet>

    <Modal :open="!!teamAction" title="Perbarui Status Tim" theme="light" stacked @close="closeTeamAction">
      <form v-if="teamAction" class="team-action-modal" @submit.prevent="submitTeamAction">
        <header><span>Unit Peserta</span><strong>{{ teamAction.label }}</strong><p>{{ teamAction.status === 'reset' ? 'Status khusus tim akan dihapus dan kembali mengikuti status pendaftaran utama.' : `Status tim akan diubah menjadi ${statusLabel(teamAction.status)}.` }} Berikan alasan agar keputusan tercatat jelas pada audit.</p></header>
        <label><span>Alasan Perubahan</span><textarea v-model="teamReason" rows="4" maxlength="255" autofocus required placeholder="Jelaskan dasar keputusan untuk tim ini"></textarea><small>{{ teamReason.length }}/255 karakter</small></label>
        <footer><button type="button" @click="closeTeamAction">Batal</button><button :class="['submit-team-action', { danger: teamAction.status === 'rejected' }]" :disabled="!teamReason.trim()">{{ teamAction.status === 'verified' ? 'Setujui Tim' : teamAction.status === 'revision_required' ? 'Minta Perbaikan' : teamAction.status === 'reset' ? 'Reset Override' : 'Tolak Tim' }}</button></footer>
      </form>
    </Modal>

    <Modal :open="!!noteAction" :title="noteAction?.title || ''" theme="light" stacked @close="closeNoteAction">
      <form v-if="noteAction" class="team-action-modal" @submit.prevent="submitNoteAction">
        <header><span>{{ noteAction.eyebrow }}</span><strong>{{ noteAction.name }}</strong><p>{{ noteAction.description }}</p></header>
        <label><span>Catatan Keputusan</span><textarea v-model="actionNote" rows="4" maxlength="255" autofocus required :placeholder="noteAction.placeholder"></textarea><small>{{ actionNote.length }}/255 karakter</small></label>
        <footer><button type="button" @click="closeNoteAction">Batal</button><button :class="['submit-team-action', { danger: noteAction.danger }]" :disabled="!actionNote.trim()">{{ noteAction.submit }}</button></footer>
      </form>
    </Modal>
  </PortalLayout>
</template>

<style scoped>
.page-head{padding:8px 0 24px}.flash{margin-bottom:14px;padding:12px 16px;color:#087365;background:#eefaf6;border:1px solid #b9e3d6;border-radius:10px;font-weight:750}.verification-table{min-width:900px}.actions-heading{text-align:right}.entry-row{cursor:pointer;transition:background .18s ease}.entry-row:hover{background:#f4f8fc}.committee-cell{display:flex;align-items:center;gap:11px}.committee-mark{display:grid;flex:0 0 38px;place-items:center;height:38px;color:#1946a3;background:#eaf1fb;border:1px solid #cbdaf0;border-radius:10px;font-size:10px;font-weight:900}.committee-cell div{display:grid;gap:3px}.committee-cell small{color:#71808b;font-size:10px}.progress-cell{display:grid;gap:7px;min-width:235px}.progress-cell>div:first-child{display:flex;align-items:baseline;gap:5px}.progress-cell strong{color:#102f59;font-size:18px}.progress-cell span,.progress-cell small{color:#71808b;font-size:10px}.progress-cell b{margin-left:auto;color:#1946a3;font-size:10px}.progress-track{height:6px;overflow:hidden;background:#e4ebef;border-radius:999px}.progress-track i{display:block;height:100%;background:linear-gradient(90deg,#1946a3,#36a8d7);border-radius:inherit;transition:width .35s ease}.progress-cell small.complete{color:#087365}.review-status{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;font-size:9px;font-weight:850;text-transform:uppercase}.review-status i{width:6px;height:6px;border-radius:50%}.review-status.pending{color:#755b00;background:#fff5cf}.review-status.pending i{background:#d5a600}.review-status.ready{color:#087365;background:#dff7f2}.review-status.ready i{background:#0d9b7f}.inspect-button{display:inline-flex;align-items:center;gap:8px;float:right;padding:8px 11px;color:#1946a3;background:#fff;border:1px solid #bfd0dc;border-radius:8px;font-size:10px;font-weight:850;cursor:pointer}.inspect-button:hover{background:#edf4ff}.inspect-button span{font-size:17px;line-height:.7}.review-overview{display:grid;grid-template-columns:150px 1fr;gap:12px;margin-bottom:14px}.review-score{display:grid;align-content:center;padding:16px;color:#fff;background:linear-gradient(145deg,#102f59,#1946a3);border-radius:13px}.review-score span,.review-score small{color:#d8e6f7;font-size:9px;text-transform:uppercase}.review-score strong{font-size:30px}.review-meta{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;overflow:hidden;background:#dce6eb;border:1px solid #dce6eb;border-radius:13px}.review-meta div{display:grid;align-content:center;gap:5px;padding:14px;background:#fff}.review-meta span{color:#71808b;font-size:9px;text-transform:uppercase}.review-meta strong{font-size:11px}.approval-blocker,.approval-ready{display:flex;gap:11px;margin-bottom:16px;padding:13px 14px;border-radius:11px}.approval-blocker{color:#755000;background:#fff7dc;border:1px solid #ead58a}.approval-ready{color:#087365;background:#e9f8f3;border:1px solid #b9e3d6}.approval-blocker>span,.approval-ready>span{display:grid;flex:0 0 28px;place-items:center;height:28px;background:rgba(255,255,255,.7);border-radius:8px;font-weight:900}.approval-blocker div,.approval-ready div{display:grid;gap:3px}.approval-blocker p,.approval-ready p{margin:0;font-size:11px}.team-section,.official-section{margin-top:14px;padding:15px;background:#fff;border:1px solid #d8e3e9;border-radius:13px}.team-section>header,.official-section>header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:11px}.team-section header div,.official-section header div{display:grid;gap:2px}.team-section header span:first-child,.official-section header span{color:#71808b;font-size:9px;font-weight:800;text-transform:uppercase}.team-section h3,.official-section h3{margin:0;color:#173346;font-size:15px}.team-state{padding:5px 8px;border-radius:999px;background:#edf2f5;font-size:8px!important;font-weight:850}.player-list{display:grid;gap:8px}.player-card{overflow:hidden;border:1px solid #dce6eb;border-radius:10px;background:#fff}.player-card[open]{border-color:#b9cde0;box-shadow:0 7px 20px rgba(25,53,76,.07)}.player-card summary{display:flex;align-items:center;gap:10px;padding:11px 12px;cursor:pointer;list-style:none}.player-card summary::-webkit-details-marker{display:none}.player-number{display:grid;flex:0 0 30px;place-items:center;height:30px;color:#1946a3;background:#edf4ff;border-radius:8px;font-size:9px;font-weight:900}.player-name{display:grid;flex:1;gap:2px}.player-name strong{font-size:12px}.player-name small{color:#71808b;font-size:9px}.player-card summary>i{width:7px;height:7px;border-right:2px solid #71808b;border-bottom:2px solid #71808b;transform:rotate(45deg);transition:transform .2s}.player-card[open] summary>i{transform:rotate(225deg)}.member-status{padding:4px 7px;border-radius:999px;font-size:8px;font-weight:850;text-transform:uppercase}.member-status.verified{color:#087365;background:#dff7f2}.member-status.pending{color:#755000;background:#fff2c8}.member-status.revision_required,.member-status.rejected{color:#a13d24;background:#ffe7df}.player-detail{display:grid;gap:12px;padding:13px;background:#f7f9fb;border-top:1px solid #dce6eb}.identity-facts{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.identity-facts span{display:grid;gap:3px;color:#71808b;font-size:9px;text-transform:uppercase}.identity-facts strong{color:#263d4d;font-size:11px;text-transform:none}.document-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(115px,1fr));gap:8px}.document-grid a{display:grid;gap:4px;padding:9px;color:#1946a3;background:#fff;border:1px solid #d7e2e8;border-radius:8px;text-decoration:none}.document-grid img{width:100%;height:76px;object-fit:cover;border-radius:5px}.document-grid span{font-size:10px;font-weight:850}.document-grid small{color:#71808b;font-size:9px}.member-note{display:grid;gap:3px;margin:0;padding:10px;color:#a1432e;background:#fff0e9;border-radius:8px;font-size:11px}.audit-list{display:grid;gap:4px}.audit-list span{color:#71808b;font-size:9px}.audit-list b{margin-right:5px;color:#263d4d}.member-actions,.team-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:6px}.team-actions{margin-top:16px;padding-top:14px;border-top:1px solid #e2e9ed}.member-actions button,.team-actions button,:deep(.sheet-footer button){min-height:34px;padding:7px 10px;color:#344a59;background:#fff;border:1px solid #c8d6de;border-radius:8px;font-size:9px;font-weight:850;cursor:pointer}.member-actions button.primary,:deep(.sheet-footer .approve){color:#fff;background:#1946a3;border-color:#1946a3}.danger,:deep(.sheet-footer .danger){color:#a1432e!important}.official-section header small{color:#71808b;font-size:9px}.official-section details{padding:9px 0;border-top:1px solid #e2e9ed}.official-section summary{display:flex;justify-content:space-between;gap:12px;cursor:pointer;font-size:10px;text-transform:capitalize}.official-section nav{display:flex;flex-wrap:wrap;gap:6px;padding-top:8px}.official-section a{padding:6px 8px;color:#1946a3;background:#edf4ff;border-radius:7px;font-size:9px;font-weight:800;text-decoration:none}.footer-blocker{margin-right:auto;color:#9a6b00;font-size:10px;font-weight:800}:deep(.sheet-footer button:disabled){opacity:.48;cursor:not-allowed}@media(max-width:700px){.review-overview{grid-template-columns:1fr}.review-meta,.identity-facts{grid-template-columns:1fr}.review-meta{gap:0}.review-meta div+div{border-top:1px solid #dce6eb}.member-status{display:none}}
.flash.error{color:#a1432e;background:#fff0e9;border-color:#efc4b5}.team-blocker{margin-right:auto;align-self:center;color:#9a6b00;font-size:9px;font-weight:850}.team-actions button:disabled{opacity:.45;cursor:not-allowed}.team-action-modal{display:grid}.team-action-modal>header{display:grid;gap:5px;padding:20px 22px;background:#f7f9fb;border-bottom:1px solid #dfe8ed}.team-action-modal>header span,.team-action-modal label>span{color:#71808b;font-size:9px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.team-action-modal>header strong{color:#173346;font-size:17px}.team-action-modal>header p{margin:3px 0 0;color:#60717f;font-size:11px;line-height:1.55}.team-action-modal label{display:grid;gap:7px;padding:20px 22px}.team-action-modal textarea{width:100%;resize:vertical;padding:11px 12px;color:#243747;background:#fff;border:1px solid #cbd8df;border-radius:9px;font:inherit;font-size:12px;line-height:1.5;outline:none}.team-action-modal textarea:focus{border-color:#1946a3;box-shadow:0 0 0 3px rgba(25,70,163,.1)}.team-action-modal label small{color:#82909a;font-size:9px;text-align:right}.team-action-modal footer{display:flex;justify-content:flex-end;gap:8px;padding:14px 22px;background:#f7f9fb;border-top:1px solid #dfe8ed}.team-action-modal footer button{min-height:38px;padding:8px 13px;color:#405361;background:#fff;border:1px solid #c8d6de;border-radius:8px;font-size:10px;font-weight:850;cursor:pointer}.team-action-modal footer .submit-team-action{color:#fff;background:#1946a3;border-color:#1946a3}.team-action-modal footer .submit-team-action.danger{color:#fff!important;background:#a1432e;border-color:#a1432e}.team-action-modal footer button:disabled{opacity:.48;cursor:not-allowed}
.review-status.rejected{color:#a1432e;background:#ffe7df}.review-status.rejected i{background:#c65332}
</style>
