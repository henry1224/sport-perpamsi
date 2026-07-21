<script setup>
import { computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
const page = usePage();
const { assets } = page.props;
const user = computed(() => page.props.auth?.user);
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
const dashHref = computed(() => user.value?.role === 'super_admin' ? '/admin/dashboard' : '/pd/dashboard');
const logout = () => router.post('/logout');
</script>

<template>
  <header class="site-header">
    <Link href="/" class="brand-chip">
      <img class="brand-mark ptmb" :src="assets.ptmbMark" alt="PTMB" />
      <span class="brand-divider" aria-hidden="true" />
      <img class="brand-mark pemkot" :src="assets.pemkot" alt="Pemerintah Kota Balikpapan" />
      <span class="brand-divider" aria-hidden="true" />
      <img class="brand-mark perpamsi" :src="assets.perpamsi" alt="PERPAMSI" />
      <span class="brand-divider" aria-hidden="true" />
      <img class="brand-mark porpamnas" :src="assets.porpamnas" alt="PORPAMNAS IX" />
    </Link>
    <nav aria-label="Menu public">
      <Link v-for="item in nav" :key="item.href" :href="item.href" :class="{ active: isActive(item.href) }">{{ item.label }}</Link>
    </nav>
    <div class="header-cta-group">
      <template v-if="user">
        <Link :href="dashHref" class="header-user" :title="user.committee?.name || user.role">
          <b>{{ user.committee?.name || 'SUPER ADMIN' }}</b>
          <small>{{ user.name }}</small>
        </Link>
        <button class="header-cta ghost" @click="logout">Keluar</button>
      </template>
      <template v-else>
        <Link class="seminar-cta" href="/seminar" aria-label="Agenda khusus Seminar NIWC, 8 Oktober 2026">
          <span class="seminar-date"><b>08</b><small>Okt</small></span>
          <span class="seminar-label"><small>Agenda Khusus</small><b>Seminar NIWC</b></span>
        </Link>
        <Link class="header-cta" href="/login">Masuk</Link>
      </template>
    </div>
  </header>
</template>

<style scoped>
.site-header { position: sticky; top: 16px; z-index: 20; display: grid; grid-template-columns: auto 1fr auto; gap: 18px; align-items: center; padding: 10px 12px; background: #071126; border: 1px solid rgba(255,255,255,.14); box-shadow: 10px 10px 0 rgba(54,194,240,.13); clip-path: polygon(18px 0,100% 0,100% calc(100% - 18px),calc(100% - 18px) 100%,0 100%,0 18px); }
.brand-chip { display: inline-flex; align-items: center; gap: 10px; padding: 7px 13px; background: #fff; text-decoration: none; clip-path: polygon(12px 0,100% 0,calc(100% - 12px) 100%,0 100%); }
.brand-mark { object-fit: contain; }
.brand-mark.ptmb { width: 43px; height: 42px; }
.brand-mark.pemkot { width: 40px; height: 44px; }
.brand-mark.perpamsi { width: 58px; height: 44px; }
.brand-mark.porpamnas { width: 58px; height: 44px; }
.brand-divider { width: 1px; align-self: stretch; background: rgba(7,17,38,.18); }
.site-header nav { display: flex; justify-content: center; gap: 8px; }
.site-header nav a { padding: 10px 12px; color: rgba(255,255,255,.78); text-decoration: none; font-size: 12px; font-weight: 950; letter-spacing: .14em; text-transform: uppercase; clip-path: polygon(8px 0,100% 0,calc(100% - 8px) 100%,0 100%); }
.site-header nav a:hover, .site-header nav a.active { color: #F6C64A; }
.site-header nav a.active { position: relative; color: #071126; background: #F6C64A; box-shadow: 5px 5px 0 rgba(240,90,40,.38); }
.header-cta-group { display: flex; align-items: center; gap: 10px; justify-self: end; }
.header-user { display: grid; gap: 2px; padding: 8px 14px; background: #08142d; color: #F6C64A; text-decoration: none; border: 1px solid rgba(255,255,255,.14); max-width: 240px; }
.header-user b { font-size: 11px; letter-spacing: .1em; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.header-user small { color: rgba(255,255,255,.62); font-weight: 700; font-size: 11px; }
.seminar-cta { display: flex; min-height: 48px; align-items: stretch; overflow: hidden; color: #fff; text-decoration: none; background: #0b1f4d; border: 1px solid rgba(54,194,240,.42); box-shadow: 6px 6px 0 rgba(54,194,240,.2); clip-path: polygon(10px 0,100% 0,calc(100% - 10px) 100%,0 100%); transition: border-color .18s ease, transform .18s ease, box-shadow .18s ease; }
.seminar-date { display: grid; min-width: 48px; place-content: center; text-align: center; color: #071126; background: #F6C64A; border-right: 3px solid #F05A28; }
.seminar-date b { font-size: 18px; line-height: .9; letter-spacing: -.04em; }
.seminar-date small { margin-top: 3px; font-size: 8px; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; }
.seminar-label { display: grid; align-content: center; gap: 3px; padding: 7px 14px 7px 12px; }
.seminar-label small { color: #8FE4FF; font-size: 8px; font-weight: 1000; letter-spacing: .14em; text-transform: uppercase; }
.seminar-label b { font-size: 11px; letter-spacing: .1em; line-height: 1; white-space: nowrap; text-transform: uppercase; }
.seminar-cta:hover { border-color: #F6C64A; box-shadow: 4px 4px 0 rgba(240,90,40,.38); transform: translateY(-1px); }
.seminar-cta:focus-visible { outline: 3px solid #36C2F0; outline-offset: 3px; }
.header-cta { padding: 13px 18px; background: #F6C64A; color: #071126; text-decoration: none; font-weight: 950; letter-spacing: .12em; text-transform: uppercase; font-size: 12px; box-shadow: 6px 6px 0 rgba(240,90,40,.45); clip-path: polygon(10px 0,100% 0,calc(100% - 10px) 100%,0 100%); border: 0; cursor: pointer; font-family: inherit; }
.header-cta.ghost { background: transparent; color: #F05A28; box-shadow: none; border: 1px solid #F05A28; }
@media (max-width: 1080px) {
  .site-header { grid-template-columns: 1fr; }
  .site-header nav { justify-content: flex-start; overflow-x: auto; }
  .header-cta-group { justify-self: start; flex-wrap: wrap; }
}
</style>
