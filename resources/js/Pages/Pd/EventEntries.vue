<script setup>
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

const props = defineProps({
  event: Object,
  category: Object,
  pdams: Array,
  entries: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
const type = computed(() => props.category?.competition_type || 'individual');
const canRegister = computed(() => props.event.status === 'registration_open');

const form = useForm({
  pdam_id: '',
  athlete_1: '',
  athlete_2: '',
  team_name: '',
});

const submit = () => {
  form.post(`/pd/events/${props.event.code}/entries`, {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
};

const cancel = (id) => {
  if (!confirm('Batalkan pendaftaran ini?')) return;
  router.delete(`/pd/entries/${id}`, { preserveScroll: true });
};

const statusLabel = (s) => ({ verified: 'Terverifikasi', pending: 'Menunggu', rejected: 'Ditolak' }[s] || s);
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
      <form @submit.prevent="submit" class="entry-form" v-if="canRegister">
        <h3>Daftarkan Peserta Baru</h3>
        <label>
          <span>PDAM Peserta</span>
          <select v-model="form.pdam_id" required>
            <option value="" disabled>— Pilih PDAM —</option>
            <option v-for="p in pdams" :key="p.id" :value="p.id">{{ p.name }} · {{ p.city }}</option>
          </select>
          <small v-if="form.errors.pdam_id" class="err">{{ form.errors.pdam_id }}</small>
        </label>

        <template v-if="type === 'individual' || type === 'doubles'">
          <label>
            <span>Nama Atlet {{ type === 'doubles' ? '(1)' : '' }}</span>
            <input v-model="form.athlete_1" type="text" maxlength="120" required />
            <small v-if="form.errors.athlete_1" class="err">{{ form.errors.athlete_1 }}</small>
          </label>
        </template>
        <template v-if="type === 'doubles'">
          <label>
            <span>Nama Atlet (2)</span>
            <input v-model="form.athlete_2" type="text" maxlength="120" required />
            <small v-if="form.errors.athlete_2" class="err">{{ form.errors.athlete_2 }}</small>
          </label>
        </template>
        <template v-if="type === 'team'">
          <label>
            <span>Nama Tim</span>
            <input v-model="form.team_name" type="text" maxlength="160" required />
            <small v-if="form.errors.team_name" class="err">{{ form.errors.team_name }}</small>
          </label>
        </template>

        <button type="submit" :disabled="form.processing">
          {{ form.processing ? 'Mengirim…' : 'Ajukan Pendaftaran' }}
        </button>
        <p class="hint">Status awal: <b>Menunggu</b>. Super admin akan memverifikasi.</p>
      </form>
      <div v-else class="locked-notice">Pendaftaran ditutup untuk event ini.</div>

      <div class="entry-list">
        <h3>Entry PD Anda ({{ entries.length }})</h3>
        <div v-for="e in entries" :key="e.id" class="entry-row" :class="e.verification_status">
          <div class="entry-main">
            <strong>{{ e.display_name }}</strong>
            <small>{{ e.pdam }}</small>
            <small v-if="e.athlete_1">Atlet: {{ e.athlete_1 }}<span v-if="e.athlete_2"> & {{ e.athlete_2 }}</span></small>
            <small v-if="e.team_name">Tim: {{ e.team_name }}</small>
            <small v-if="e.verification_note" class="note">Catatan: {{ e.verification_note }}</small>
          </div>
          <span class="tag" :class="e.verification_status">{{ statusLabel(e.verification_status) }}</span>
          <button v-if="e.verification_status === 'pending'" class="del" @click="cancel(e.id)">Batalkan</button>
        </div>
        <p v-if="!entries.length" class="empty">Belum ada pendaftaran untuk event ini.</p>
      </div>
    </section>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 12px; }
.back { display: inline-block; margin-top: 8px; color: #36C2F0; text-decoration: none; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; font-size: 12px; }
.flash { padding: 12px 16px; margin-bottom: 12px; font-weight: 800; }
.flash.ok { background: rgba(54,194,240,.18); color: #36C2F0; border-left: 4px solid #36C2F0; }
.flash.err { background: rgba(240,90,40,.18); color: #F05A28; border-left: 4px solid #F05A28; }
.entry-panel { display: grid; grid-template-columns: 1fr 1.4fr; gap: 20px; padding: 20px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 10px 10px 0 rgba(54,194,240,.13); }
.entry-form, .entry-list { padding: 18px; background: rgba(5,11,28,.56); border: 1px solid rgba(255,255,255,.12); display: grid; gap: 14px; }
h3 { margin: 0 0 4px; color: #F6C64A; letter-spacing: .1em; text-transform: uppercase; font-size: 12px; }
label { display: grid; gap: 8px; color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
input, select { width: 100%; padding: 12px 14px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.16); font: inherit; }
button { padding: 12px 16px; background: #F6C64A; color: #071126; border: 0; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; box-shadow: 6px 6px 0 rgba(240,90,40,.45); cursor: pointer; }
button:disabled { opacity: .6; cursor: not-allowed; }
.hint { color: rgba(255,255,255,.6); font-size: 12px; font-weight: 700; }
.locked-notice { padding: 18px; background: rgba(240,90,40,.14); color: #F05A28; border-left: 4px solid #F05A28; font-weight: 800; }
.entry-row { display: grid; grid-template-columns: 1fr auto auto; gap: 14px; align-items: center; padding: 12px; background: #08142d; border: 1px solid rgba(255,255,255,.1); border-left: 4px solid #36C2F0; }
.entry-row.pending { border-left-color: #F6C64A; }
.entry-row.rejected { border-left-color: #F05A28; }
.entry-main { display: grid; gap: 4px; }
.entry-main small { color: rgba(255,255,255,.6); font-weight: 700; }
.entry-main small.note { color: #F05A28; }
.tag { padding: 5px 10px; font-size: 11px; font-weight: 1000; letter-spacing: .1em; text-transform: uppercase; clip-path: polygon(8px 0,100% 0,calc(100% - 8px) 100%,0 100%); }
.tag.verified { background: rgba(54,194,240,.18); color: #36C2F0; }
.tag.pending { background: rgba(246,198,74,.22); color: #F6C64A; }
.tag.rejected { background: rgba(240,90,40,.22); color: #F05A28; }
.del { padding: 6px 10px; background: rgba(240,90,40,.2); color: #F05A28; box-shadow: none; font-size: 11px; }
.err { color: #F05A28; text-transform: none; letter-spacing: 0; font-weight: 700; }
.empty { text-align: center; padding: 20px; color: rgba(255,255,255,.5); }
@media (max-width: 900px) { .entry-panel { grid-template-columns: 1fr; } .entry-row { grid-template-columns: 1fr; } }
</style>
