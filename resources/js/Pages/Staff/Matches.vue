<script setup>
import { Link } from '@inertiajs/vue3';
import PortalLayout from '../../Layouts/PortalLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';
import { formatDateTime } from '../../lib/date';
defineProps({ matches: Array });
</script>
<template><PortalLayout portal="staff"><div class="page-head"><SectionTitle eyebrow="Portal Panitia" title="Pertandingan Tugas Saya" meta="Hanya cabor dan venue assignment aktif" /></div><section class="table-card"><table><thead><tr><th>Waktu</th><th>Pertandingan</th><th>Venue</th><th>Status</th><th></th></tr></thead><tbody><tr v-for="row in matches" :key="row.id"><td>{{ formatDateTime(row.scheduled_at) }}</td><td><strong>{{ row.team_a }} vs {{ row.team_b }}</strong><small>{{ row.event }} · {{ row.code }}</small></td><td>{{ row.venue }}</td><td>{{ row.status }}</td><td><Link :href="`/panitia/pertandingan/${row.id}`">Detail</Link></td></tr><tr v-if="!matches.length"><td colspan="5" class="empty">Belum ada pertandingan sesuai assignment.</td></tr></tbody></table></section></PortalLayout></template>
<style scoped>.page-head{padding:8px 0 24px}.table-card{overflow:auto;background:#fff;border:1px solid #d9e3e9;border-radius:14px;box-shadow:0 8px 24px rgba(25,53,76,.06)}table{width:100%;border-collapse:collapse}th,td{padding:14px 16px;border-bottom:1px solid #e5ecef;text-align:left}th{color:#60717f;background:#f7f9fa;font-size:10px;text-transform:uppercase}td strong,td small{display:block}td small{margin-top:4px;color:#71808b}a{color:#1946a3;font-weight:800;text-decoration:none}.empty{text-align:center;color:#71808b}</style>
