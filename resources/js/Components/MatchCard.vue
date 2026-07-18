<script setup>
import StatusBadge from './StatusBadge.vue';
defineProps({
  sport: String,
  teamA: String,
  teamB: String,
  score: String,
  status: String,
  venue: String,
  time: String,
});
</script>

<template>
  <div class="match-card">
    <span class="sport-tag">{{ sport }}</span>
    <div class="versus">
      <strong>{{ teamA }}</strong>
      <b>{{ score }}</b>
      <strong>{{ teamB }}</strong>
    </div>
    <div class="meta">
      <StatusBadge :status="status" />
      <small v-if="venue || time">{{ [venue, time].filter(Boolean).join(' · ') }}</small>
    </div>
  </div>
</template>

<style scoped>
.match-card { position: relative; overflow: hidden; padding: 18px 20px; background: #071126; border: 1px solid rgba(255,255,255,.12); border-left: 5px solid #36C2F0; box-shadow: 8px 8px 0 rgba(54,194,240,.13); clip-path: polygon(16px 0,100% 0,100% calc(100% - 16px),calc(100% - 16px) 100%,0 100%,0 16px); }
.match-card::before { content: "RESULT"; position: absolute; right: -4px; top: 6px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.06); font-size: 52px; font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
.sport-tag { position: relative; z-index: 1; display: inline-block; width: fit-content; padding: 4px 9px; color: #071126; background: #36C2F0; font-size: 10px; font-weight: 1000; letter-spacing: .13em; text-transform: uppercase; }
.versus { display: grid; grid-template-columns: 1fr auto 1fr; gap: 14px; align-items: center; margin: 12px 0 10px; }
.versus strong { position: relative; z-index: 1; font-size: 15px; line-height: 1.25; }
.versus b { position: relative; z-index: 1; padding: 8px 12px; color: #071126; background: #F6C64A; font-size: 34px; line-height: 1; box-shadow: 5px 5px 0 rgba(240,90,40,.35); font-variant-numeric: tabular-nums; }
.versus strong:last-child { text-align: right; }
.meta { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; gap: 10px; padding-top: 10px; border-top: 1px dashed rgba(255,255,255,.14); }
.meta small { color: rgba(255,255,255,.65); font-weight: 900; text-transform: uppercase; font-size: 11px; letter-spacing: .1em; }
@media (max-width: 640px) { .versus { grid-template-columns: 1fr; } .versus strong:last-child { text-align: left; } }
</style>
