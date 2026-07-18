<script setup>
import AppHeader from '../Components/AppHeader.vue';
</script>

<template>
  <main class="arena-shell">
    <AppHeader />
    <slot />
    <footer class="site-footer">
      <small>© 2026 PORPAMNAS IX · Perumda Tirta Manuntung Balikpapan</small>
      <small>Balikpapan · 6–10 Oktober 2026</small>
    </footer>
  </main>
</template>

<style scoped>
.arena-shell {
  min-height: 100vh;
  color: #fff;
  padding: 24px max(24px, calc((100vw - 1560px) / 2)) 70px;
  background:
    linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px) 0 0 / 72px 72px,
    linear-gradient(0deg, rgba(255,255,255,.035) 1px, transparent 1px) 0 0 / 72px 72px,
    radial-gradient(circle at 78% 12%, rgba(54,194,240,.28), transparent 34rem),
    radial-gradient(circle at 12% 72%, rgba(240,90,40,.18), transparent 28rem),
    linear-gradient(135deg, #050B1C 0%, #0B1F4D 52%, #10275C 100%);
}
.site-footer { display: flex; justify-content: space-between; gap: 12px; margin-top: 60px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,.12); color: rgba(255,255,255,.55); font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
@media (max-width: 640px) { .arena-shell { padding-inline: 14px; } .site-footer { flex-direction: column; } }
</style>
