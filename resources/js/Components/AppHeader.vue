<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
const page = usePage();
const { assets } = page.props;
const currentPath = computed(() => page.url.split('?')[0]);
const isActive = (href) => href === '/' ? currentPath.value === '/' : currentPath.value.startsWith(href);
const nav = [
  { href: '/', label: 'Home' },
  { href: '/agenda', label: 'Agenda' },
  { href: '/hasil', label: 'Hasil' },
  { href: '/cabor', label: 'Cabor' },
  { href: '/bracket', label: 'Bracket' },
  { href: '/ranking', label: 'Ranking' },
  { href: '/venue', label: 'Venue' },
  { href: '/peserta', label: 'Peserta' },
];
</script>

<template>
  <header class="site-header">
    <Link href="/" class="brand-chip">
      <img class="brand-mark porpamnas" :src="assets.porpamnas" alt="PORPAMNAS IX" />
      <span class="brand-divider" aria-hidden="true" />
      <img class="brand-mark ptmb" :src="assets.ptmb" alt="PTMB dan TRUST" />
    </Link>
    <nav aria-label="Menu public">
      <Link v-for="item in nav" :key="item.href" :href="item.href" :class="{ active: isActive(item.href) }">{{ item.label }}</Link>
    </nav>
    <Link class="header-cta" href="/agenda">Live Board</Link>
  </header>
</template>

<style scoped>
.site-header { position: sticky; top: 16px; z-index: 20; display: grid; grid-template-columns: auto 1fr auto; gap: 18px; align-items: center; padding: 10px 12px; background: #071126; border: 1px solid rgba(255,255,255,.14); box-shadow: 10px 10px 0 rgba(54,194,240,.13); clip-path: polygon(18px 0,100% 0,100% calc(100% - 18px),calc(100% - 18px) 100%,0 100%,0 18px); }
.brand-chip { display: inline-flex; align-items: center; gap: 14px; padding: 8px 13px; background: #fff; text-decoration: none; clip-path: polygon(12px 0,100% 0,calc(100% - 12px) 100%,0 100%); }
.brand-mark { object-fit: contain; }
.brand-mark.porpamnas { width: 64px; height: 46px; }
.brand-mark.ptmb { width: 156px; height: 42px; }
.brand-divider { width: 1px; align-self: stretch; background: rgba(7,17,38,.18); }
.site-header nav { display: flex; justify-content: center; gap: 8px; }
.site-header nav a { padding: 10px 12px; color: rgba(255,255,255,.78); text-decoration: none; font-size: 12px; font-weight: 950; letter-spacing: .14em; text-transform: uppercase; clip-path: polygon(8px 0,100% 0,calc(100% - 8px) 100%,0 100%); }
.site-header nav a:hover, .site-header nav a.active { color: #F6C64A; }
.site-header nav a.active { position: relative; }
.site-header nav a.active { color: #071126; background: #F6C64A; box-shadow: 5px 5px 0 rgba(240,90,40,.38); }
.site-header nav a.active::after { display: none; }
.header-cta { justify-self: end; padding: 13px 18px; background: #F6C64A; color: #071126 !important; text-decoration: none; font-weight: 950; letter-spacing: .12em; text-transform: uppercase; font-size: 12px; box-shadow: 6px 6px 0 rgba(240,90,40,.45); clip-path: polygon(10px 0,100% 0,calc(100% - 10px) 100%,0 100%); }
@media (max-width: 1080px) {
  .site-header { grid-template-columns: 1fr; }
  .site-header nav { justify-content: flex-start; overflow-x: auto; }
  .header-cta { justify-self: start; }
}
</style>
