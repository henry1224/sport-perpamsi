<script setup>
defineProps({ status: { type: String, required: true } });
const map = {
  live: { label: 'LIVE', variant: 'live' },
  scheduled: { label: 'Terjadwal', variant: 'info' },
  finished: { label: 'Selesai', variant: 'ok' },
  final: { label: 'Final', variant: 'ok' },
  verified: { label: 'Terverifikasi', variant: 'ok' },
  disputed: { label: 'Sengketa', variant: 'warn' },
  postponed: { label: 'Ditunda', variant: 'warn' },
  walkover: { label: 'Walkover', variant: 'warn' },
  revision: { label: 'Revisi', variant: 'warn' },
};
</script>

<template>
  <span :class="['status-badge', (map[status.toLowerCase()] || {}).variant || 'info']">
    {{ (map[status.toLowerCase()] || {}).label || status }}
  </span>
</template>

<style scoped>
.status-badge { display: inline-flex; align-items: center; padding: 5px 10px; border-radius: 999px; font-size: 10px; font-weight: 950; letter-spacing: .14em; text-transform: uppercase; }
.status-badge.live { color: #fff; background: #E02D2D; box-shadow: 0 0 12px rgba(224,45,45,.5); }
.status-badge.info { color: #BFEFFF; background: rgba(54,194,240,.18); }
.status-badge.ok { color: #B7F5D8; background: rgba(32,198,183,.18); }
.status-badge.warn { color: #FFE6A3; background: rgba(246,198,74,.18); }
</style>
