<script setup>
import { Link } from '@inertiajs/vue3';
import AdminDataTable from '../../Components/AdminDataTable.vue';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import { statusLabel } from '../../lib/status';

const props = defineProps({
  committee: Object,
  events: Object,
  filters: Object,
  summary: Object,
});
</script>

<template>
  <PortalLayout portal="pd">
    <div class="page-head">
      <SectionTitle eyebrow="Panel Pengurus Daerah" :title="committee.name" :meta="`${summary.entries} pendaftaran cabor`" />
    </div>

    <section class="summary">
        <div class="pill">
          <span>Provinsi</span>
          <b>{{ committee.province || '—' }}</b>
        </div>
        <div class="pill">
          <span>Cabor Tersedia</span>
          <b>{{ summary.events }}</b>
        </div>
        <div class="pill">
          <span>Total Pendaftaran</span>
          <b>{{ summary.entries }}</b>
        </div>
    </section>

    <AdminDataTable :paginator="events" :filters="filters" item-label="kompetisi" search-placeholder="Cari cabor, kategori, kode, atau kompetisi" :filter-options="[{ value: 'registration_open', label: 'Pendaftaran Dibuka' }, { value: 'registration_closed', label: 'Pendaftaran Ditutup' }, { value: 'bracket_locked', label: 'Bracket Dikunci' }, { value: 'ongoing', label: 'Sedang Berlangsung' }, { value: 'completed', label: 'Selesai' }]" v-slot="{ rows }">
      <table><thead><tr><th>Kompetisi</th><th>Status</th><th>Pendaftaran & Pemain</th><th>Batas Pemain</th><th style="text-align:right">Aksi</th></tr></thead><tbody><tr v-for="ev in rows" :key="ev.code"><td><div class="primary-cell"><strong>{{ ev.name }}</strong><small>{{ ev.sport }} · {{ ev.category || 'Umum' }} · {{ ev.code }}</small></div></td><td><span :class="['status-badge', ev.registration_open ? 'success' : 'info']">{{ statusLabel(ev.status) }}</span></td><td><div class="entry-counts"><small>Status pendaftaran PD</small><span class="status-badge success">{{ ev.entries.verified }} disetujui</span><span class="status-badge">{{ ev.entries.pending }} menunggu</span><span v-if="ev.entries.rejected" class="status-badge danger">{{ ev.entries.rejected }} ditolak</span></div><div v-if="ev.players.total" class="player-counts"><strong>{{ ev.players.verified }}/{{ ev.players.total }} pemain terverifikasi</strong><small>{{ ev.players.pending }} menunggu · {{ ev.players.revision_required }} perbaikan · {{ ev.players.rejected }} ditolak</small></div></td><td>{{ ev.member_limit || '—' }}</td><td><div class="row-actions"><Link :href="`/pd/events/${ev.code}`" class="table-link">{{ ev.registration_open ? 'Daftar Pemain' : 'Lihat Roster' }}</Link></div></td></tr><tr v-if="!rows.length" class="empty-row"><td colspan="5">Tidak ada kompetisi sesuai filter.</td></tr></tbody></table>
    </AdminDataTable>
  </PortalLayout>
</template>

<style scoped>
.page-head { padding: 8px 0 24px; }
.summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
.pill { display: grid; gap: 6px; margin-bottom:18px; padding: 16px 18px; background: #fff; border: 1px solid #d9e3e9; border-radius: 14px; box-shadow: 0 8px 24px rgba(25,53,76,.06); }.pill span { color:#60717f; font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; }.pill b { color:#1946a3; font-size:24px; }.entry-counts { display:flex; flex-wrap:wrap; gap:6px; }.entry-counts>small{flex-basis:100%;color:#71808b;font-size:9px;font-weight:800;text-transform:uppercase}.player-counts{display:grid;gap:3px;margin-top:8px}.player-counts strong{color:#1946a3;font-size:11px}.player-counts small{color:#71808b;font-size:9px}.table-link { display:inline-flex; min-height:34px; align-items:center; padding:7px 11px; color:#fff; background:#1946a3; border-radius:7px; font-size:10px; font-weight:800; letter-spacing:.04em; text-decoration:none; text-transform:uppercase; }
</style>
