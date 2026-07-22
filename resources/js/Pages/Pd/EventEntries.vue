<script setup>
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import { statusLabel } from '../../lib/status';

const props = defineProps({
  event: Object,
  category: Object,
  entries: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const canRegister = computed(() => props.event.registration_open);
const minMembers = computed(() => props.category?.min_members || 1);
const maxMembers = computed(() => props.category?.max_members ?? null);
const maxTeams = computed(() => props.category?.max_teams_per_pd || 1);
const editableEntry = computed(() => props.entries.find((entry) => ['draft', 'revision_required', 'rejected', 'cancelled'].includes(entry.verification_status)));
const lockedEntry = computed(() => props.entries.find((entry) => ['pending', 'verified'].includes(entry.verification_status)));
const blankMembers = () => Array.from({ length: minMembers.value }, () => ({ name: '' }));
const initialTeams = editableEntry.value?.teams?.length ? editableEntry.value.teams.map((team) => ({ members: team.members.map((name) => ({ name })) })) : [{ members: blankMembers() }];

const form = useForm({
  intent: 'draft',
  teams: initialTeams,
});

const addMember = (team) => {
  if (maxMembers.value === null || team.members.length < maxMembers.value) team.members.push({ name: '' });
};

const removeMember = (team, index) => {
  if (team.members.length > minMembers.value) team.members.splice(index, 1);
};
const addTeam = () => { if (form.teams.length < maxTeams.value) form.teams.push({ members: blankMembers() }); };
const removeTeam = (index) => { if (form.teams.length > 1) form.teams.splice(index, 1); };

const submit = (intent) => {
  form.intent = intent;
  form.post(`/pd/events/${props.event.code}/entries`, {
    preserveScroll: true,
  });
};

const cancel = (id) => {
  if (!confirm('Batalkan pendaftaran ini?')) return;
  router.delete(`/pd/entries/${id}`, { preserveScroll: true });
};

</script>

<template>
  <PortalLayout portal="pd">
    <div class="page-head">
      <SectionTitle eyebrow="Pendaftaran Peserta" :title="event.name" :meta="`${event.sport} · ${category?.name || 'Umum'}`" />
      <Link href="/pd/dashboard" class="back">← Kembali ke Dashboard</Link>
    </div>

    <div v-if="flash.success" class="flash ok">{{ flash.success }}</div>
    <div v-if="flash.error" class="flash err">{{ flash.error }}</div>

    <section class="entry-panel">
      <form class="entry-form portal-card" v-if="(canRegister || editableEntry?.verification_status === 'revision_required') && !lockedEntry">
        <div class="portal-card-head">
          <h3>{{ editableEntry ? 'Perbarui Roster' : 'Daftarkan Pemain' }}</h3>
          <p class="hint">Maksimal {{ maxTeams }} tim. {{ maxMembers === null ? `Minimal ${minMembers} pemain per tim` : minMembers === maxMembers ? `${minMembers} pemain per tim` : `${minMembers}–${maxMembers} pemain per tim` }}.</p>
        </div>
        <fieldset v-for="(team, teamIndex) in form.teams" :key="teamIndex" class="team-block"><legend>Tim #{{ teamIndex + 1 }}</legend><div v-for="(member, index) in team.members" :key="index" class="member-row">
          <label class="portal-field">
            <span>Nama Pemain {{ index + 1 }}</span>
            <input v-model="member.name" type="text" maxlength="120" required />
            <small v-if="form.errors[`teams.${teamIndex}.members.${index}.name`]" class="err">{{ form.errors[`teams.${teamIndex}.members.${index}.name`] }}</small>
          </label>
          <button v-if="team.members.length > minMembers" type="button" class="remove portal-button danger" @click="removeMember(team, index)">Hapus</button>
        </div>
        <button v-if="maxMembers === null || team.members.length < maxMembers" type="button" class="add portal-button" @click="addMember(team)">+ Tambah Pemain</button><button v-if="form.teams.length > 1" type="button" class="portal-button danger" @click="removeTeam(teamIndex)">Hapus Tim</button></fieldset>
        <small v-if="form.errors.teams" class="err">{{ form.errors.teams }}</small><button v-if="form.teams.length < maxTeams" type="button" class="add portal-button" @click="addTeam">+ Tambah Tim</button>

        <div class="form-actions"><button type="button" class="draft portal-button" :disabled="form.processing" @click="submit('draft')">Simpan Draft</button><button type="button" class="submit portal-button primary" :disabled="form.processing" @click="submit('submit')">{{ form.processing ? 'Mengirim…' : editableEntry?.verification_status === 'revision_required' ? 'Kirim Ulang' : 'Ajukan Pendaftaran' }}</button></div>
        <p class="hint">Draft dapat diubah. Setelah diajukan, roster terkunci sampai Admin meminta perbaikan.</p>
      </form>
      <div v-else class="locked-notice"><strong>{{ lockedEntry ? statusLabel(lockedEntry.verification_status) : 'Pendaftaran Ditutup' }}</strong><span v-if="lockedEntry">Roster sudah dikirim. Form terbuka kembali bila Admin meminta perbaikan.</span><span v-else>Periode registrasi untuk kompetisi ini sudah berakhir.</span></div>

      <div class="entry-list portal-card">
        <h3 class="portal-card-head">Pendaftaran PD Anda ({{ entries.length }})</h3>
        <div class="roster-table"><table><thead><tr><th>Kontingen</th><th>Tim dan Pemain</th><th>Status</th><th style="text-align:right">Aksi</th></tr></thead><tbody><tr v-for="e in entries" :key="e.id"><td><div class="entry-main"><strong>{{ e.display_name }}</strong><small v-if="e.verification_note" class="note">Catatan: {{ e.verification_note }}</small></div></td><td><div v-for="team in e.teams" :key="team.id" class="team-summary"><strong>{{ team.label }}</strong><span>{{ team.members.join(', ') }}</span><small>{{ statusLabel(team.status) }}</small></div></td><td><span class="tag" :class="e.verification_status">{{ statusLabel(e.verification_status) }}</span></td><td><div class="row-actions"><button v-if="['draft', 'pending', 'revision_required', 'rejected'].includes(e.verification_status)" class="del" @click="cancel(e.id)">Batalkan</button></div></td></tr><tr v-if="!entries.length"><td colspan="4" class="empty">Belum ada pendaftaran untuk event ini.</td></tr></tbody></table></div>
      </div>
    </section>
  </PortalLayout>
</template>

<style scoped>
.team-block{display:grid;gap:10px;margin:0;padding:14px;border:1px solid var(--portal-border);border-radius:10px}.team-block legend{padding:0 6px;color:var(--portal-primary);font-weight:800}.team-summary{display:grid;gap:3px}.team-summary+.team-summary{margin-top:10px;padding-top:10px;border-top:1px solid var(--portal-border)}.team-summary span,.team-summary small{color:var(--portal-muted);font-size:11px}
.page-head { padding:8px 0 18px; }.back { display:inline-flex; margin-top:8px; color:var(--portal-primary); text-decoration:none; font-size:12px; font-weight:800; }.flash { margin-bottom:14px; padding:12px 16px; border:1px solid; border-radius:var(--portal-control-radius); font-weight:750; }.flash.ok { color:var(--portal-success); background:var(--portal-success-soft); border-color:#b9e3d6; }.flash.err { color:var(--portal-danger); background:var(--portal-danger-soft); border-color:#efcfc4; }.entry-panel { display:grid; grid-template-columns:minmax(330px,.8fr) minmax(0,1.2fr); gap:18px; align-items:start; }.entry-form,.entry-list { display:grid; gap:14px; padding:20px; }.entry-form > div:first-child,.entry-list > h3 { margin:-20px -20px 4px; }.entry-form h3,.entry-list h3 { margin:0; color:var(--portal-text); font-size:17px; }.entry-form > div:first-child .hint { margin-top:6px; }.hint { margin:0; color:var(--portal-muted); font-size:12px; line-height:1.5; }.member-row { display:grid; grid-template-columns:1fr auto; gap:10px; align-items:end; }label span { letter-spacing:.04em; }button:disabled { opacity:.55; cursor:wait; }.form-actions { display:grid; grid-template-columns:1fr 1fr; gap:10px; }.members { margin:0; padding-left:20px; color:#536571; font-size:13px; line-height:1.65; }.locked-notice { display:grid; gap:6px; padding:20px; color:#655000; background:#fff9e8; border:1px solid #eedb94; border-radius:var(--portal-radius); }.locked-notice strong { color:#4d3f09; }.locked-notice span { font-size:12px; line-height:1.5; }.roster-table { overflow:auto; margin:0 -20px -20px; }.roster-table table { width:100%; min-width:680px; border-collapse:collapse; }.roster-table th,.roster-table td { padding:13px 16px; border-bottom:1px solid #e5ecef; color:#334553; text-align:left; vertical-align:middle; }.roster-table th { color:#60717f; background:var(--portal-surface-soft); font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }.roster-table tbody tr:hover { background:#f8fbfd; }.entry-main { display:grid; gap:4px; }.entry-main strong { color:#203444; }.entry-main small { color:var(--portal-muted); font-weight:650; }.entry-main small.note { color:var(--portal-danger); }.tag { display:inline-flex; padding:5px 9px; border:1px solid; border-radius:999px; font-size:10px; font-weight:800; text-transform:uppercase; }.tag.verified { color:var(--portal-success); background:var(--portal-success-soft); border-color:#b9e3d6; }.tag.pending { color:#745b00; background:#fff5cf; border-color:#eedb94; }.tag.rejected,.tag.revision_required { color:var(--portal-danger); background:var(--portal-danger-soft); border-color:#efcfc4; }.tag.draft,.tag.cancelled { color:#536571; background:#edf2f5; border-color:#d3dde3; }.row-actions { display:flex; justify-content:flex-end; }.del { min-height:34px; padding:7px 10px; color:var(--portal-danger); background:var(--portal-danger-soft); border:1px solid #efcfc4; border-radius:var(--portal-control-radius); font-size:10px; font-weight:800; cursor:pointer; }.err { color:var(--portal-danger); font-size:11px; font-weight:700; }.empty { margin:0; padding:24px !important; color:#7a8993 !important; text-align:center !important; }@media(max-width:900px){.entry-panel{grid-template-columns:1fr}.form-actions{grid-template-columns:1fr}}
</style>
