<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ portal: { type: String, required: true } });
const page = usePage();
const open = ref(false);
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash || {});
const isAdmin = computed(() => props.portal === 'admin');
const isStaff = computed(() => props.portal === 'staff');
const identity = computed(() => isAdmin.value
  ? { eyebrow: 'Pusat Kendali', title: 'Admin PORPAMNAS', subtitle: 'Seluruh data event dan operasional' }
  : isStaff.value
    ? { eyebrow: 'Portal Panitia', title: 'Operasional Pertandingan', subtitle: 'Tugas sesuai cabor dan venue' }
    : { eyebrow: 'Portal Delegasi', title: user.value?.committee?.name || 'Pengurus Daerah', subtitle: 'Registrasi atlet dan cabang olahraga' });

const menuGroups = computed(() => isAdmin.value ? [
  { label: 'Umum', items: [
    { label: 'Dashboard', href: '/admin/dashboard', icon: 'dashboard' },
  ] },
  { label: 'Persiapan Lomba', items: [
    { label: 'Master Cabor', href: '/admin/master-data?tab=sports', icon: 'database' },
    { label: 'Kategori', href: '/admin/master-data?tab=categories', icon: 'clipboard' },
    { label: 'Regulasi', href: '/admin/master-data?tab=regulations', icon: 'report' },
    { label: 'Master Venue', href: '/admin/venues', icon: 'venue' },
    { label: 'Master PDAM', href: '/admin/pdams', icon: 'building' },
    { label: 'Data Lomba', href: '/admin/events', icon: 'trophy' },
  ] },
  { label: 'Registrasi', items: [
    { label: 'Verifikasi Pengurus Daerah', href: '/admin/committee-applications', icon: 'building' },
    { label: 'Verifikasi Peserta', href: '/admin/entries', icon: 'users' },
  ] },
  { label: 'Operasional', items: [
    { label: 'Agenda & Jadwal', href: '/admin/agenda', icon: 'calendar' },
    { label: 'Panitia & Akses', href: '/admin/assignments', icon: 'shield' },
    { label: 'Pertandingan & Skor', href: '/admin/skor', icon: 'scoreboard' },
  ] },
  { label: 'Pelaporan', items: [
    { label: 'Laporan & Audit', icon: 'report', planned: true },
  ] },
] : isStaff.value ? [
  { label: 'Operasional', items: [{ label: 'Pertandingan Tugas', href: '/panitia/pertandingan', icon: 'scoreboard' }] },
] : [
  { label: 'Umum', items: [
    { label: 'Dashboard', href: '/pd/dashboard', icon: 'dashboard', exact: true },
  ] },
  { label: 'Pendaftaran', items: [
    { label: 'Registrasi Cabor', href: '/pd/dashboard?section=cabor#cabor', icon: 'clipboard', activeOn: ['/pd/events/'] },
  ] },
]);

const active = (item) => {
  if (!item.href) return false;
  const target = item.href.split('#')[0];
  if (item.exact) return page.url === target;
  return page.url.startsWith(target) || item.activeOn?.some((path) => page.url.startsWith(path));
};
const logout = () => router.post('/logout');
</script>

<template>
  <div class="portal-shell">
    <svg class="portal-icon-sprite" aria-hidden="true">
      <symbol id="portal-icon-dashboard" viewBox="0 0 24 24"><path d="M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6v-9h-6v9Zm0-16v5h6V4h-6Z" /></symbol>
      <symbol id="portal-icon-database" viewBox="0 0 24 24"><path d="M12 3c-4.4 0-8 1.3-8 3s3.6 3 8 3 8-1.3 8-3-3.6-3-8-3Zm-8 7v4c0 1.7 3.6 3 8 3s8-1.3 8-3v-4c-1.8 1.2-4.8 1.8-8 1.8S5.8 11.2 4 10Zm0 8v1c0 1.7 3.6 3 8 3s8-1.3 8-3v-1c-1.8 1.2-4.8 1.8-8 1.8S5.8 19.2 4 18Z" /></symbol>
      <symbol id="portal-icon-trophy" viewBox="0 0 24 24"><path d="M18 4V2H6v2H3v4c0 2.2 1.8 4 4 4h.2A5 5 0 0 0 11 15.9V19H7v3h10v-3h-4v-3.1a5 5 0 0 0 3.8-3.9h.2c2.2 0 4-1.8 4-4V4h-3ZM7 10a2 2 0 0 1-2-2V6h1v2c0 .7.1 1.4.4 2H7Zm12-2a2 2 0 0 1-2 2h-.4c.3-.6.4-1.3.4-2V6h2v2Z" /></symbol>
      <symbol id="portal-icon-building" viewBox="0 0 24 24"><path d="M4 21V3h11v5h5v13h-7v-4h-2v4H4Zm3-14h2V5H7v2Zm4 0h2V5h-2v2ZM7 11h2V9H7v2Zm4 0h2V9h-2v2Zm5 1h2v-2h-2v2Zm0 4h2v-2h-2v2Z" /></symbol>
      <symbol id="portal-icon-users" viewBox="0 0 24 24"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm6-1a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM2 21v-3c0-3 3.1-5 7-5s7 2 7 5v3H2Zm15.5 0v-3c0-1.4-.5-2.7-1.5-3.7 3.3.2 6 1.9 6 4.7v2h-4.5Z" /></symbol>
      <symbol id="portal-icon-shield" viewBox="0 0 24 24"><path d="m12 2 8 3v6c0 5.1-3.4 9.8-8 11-4.6-1.2-8-5.9-8-11V5l8-3Zm0 4.2L8 7.7V11c0 3.2 1.9 6.3 4 7.4 2.1-1.1 4-4.2 4-7.4V7.7l-4-1.5Z" /></symbol>
      <symbol id="portal-icon-calendar" viewBox="0 0 24 24"><path d="M7 2h2v3h6V2h2v3h3v17H4V5h3V2Zm11 9H6v9h12v-9ZM6 7v2h12V7H6Zm2 6h3v3H8v-3Zm5 0h3v3h-3v-3Z" /></symbol>
      <symbol id="portal-icon-venue" viewBox="0 0 24 24"><path d="M12 2a8 8 0 0 0-8 8c0 5.6 8 12 8 12s8-6.4 8-12a8 8 0 0 0-8-8Zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z" /></symbol>
      <symbol id="portal-icon-scoreboard" viewBox="0 0 24 24"><path d="M3 4h18v16H3V4Zm3 3v3h4V7H6Zm8 0v3h4V7h-4ZM6 14v3h4v-3H6Zm8 0v3h4v-3h-4Zm-2-7h1v10h-1V7Z" /></symbol>
      <symbol id="portal-icon-report" viewBox="0 0 24 24"><path d="M5 2h10l4 4v16H5V2Zm9 2v4h4l-4-4ZM8 12h8v-2H8v2Zm0 4h8v-2H8v2Zm0 4h6v-2H8v2Z" /></symbol>
      <symbol id="portal-icon-clipboard" viewBox="0 0 24 24"><path d="M9 3h6l1 2h3v17H5V5h3l1-2Zm1.2 2-.5 1h4.6l-.5-1h-3.6ZM8 10v2h8v-2H8Zm0 4v2h8v-2H8Zm0 4v2h5v-2H8Z" /></symbol>
    </svg>
    <div v-if="open" class="portal-overlay" @click="open = false" />
    <aside :class="['portal-sidebar', { open }]">
      <Link href="/" class="portal-brand">
        <img :src="'/assets/brand/logos/porpamnas/vertical-porpamnas-ix.png'" alt="Logo PORPAMNAS IX" />
      </Link>

      <div class="portal-identity">
        <span>{{ identity.eyebrow }}</span>
        <strong>{{ identity.title }}</strong>
        <small>{{ identity.subtitle }}</small>
      </div>

      <nav class="portal-nav" aria-label="Menu portal">
        <section v-for="group in menuGroups" :key="group.label" class="portal-group">
          <p>{{ group.label }}</p>
          <component
            :is="item.planned ? 'div' : Link"
            v-for="item in group.items"
            :key="item.label"
            :href="item.href"
            :class="['portal-link', { active: active(item), planned: item.planned }]"
            @click="open = false"
          >
            <span><svg aria-hidden="true"><use :href="`#portal-icon-${item.icon}`" /></svg></span>
            <b>{{ item.label }}</b>
            <small v-if="item.planned">Segera</small>
          </component>
        </section>
      </nav>

      <div class="portal-sidebar-foot">
        <Link href="/" class="public-link">Lihat situs publik</Link>
        <button type="button" @click="logout">Keluar</button>
      </div>
    </aside>

    <main class="portal-main">
      <header class="portal-topbar">
        <button type="button" class="menu-button" aria-label="Buka menu" @click="open = true">Menu</button>
        <div>
          <span>{{ isAdmin ? 'Administrator' : isStaff ? 'Panitia' : 'Pengurus Daerah' }}</span>
          <strong>{{ user?.name }}</strong>
        </div>
      </header>

      <div v-if="flash.success" class="portal-flash success">{{ flash.success }}</div>
      <div v-if="flash.error" class="portal-flash error">{{ flash.error }}</div>
      <div class="portal-content"><slot /></div>
    </main>
  </div>
</template>

<style>
.portal-shell { --portal-bg:#eef3f6; --portal-surface:#fff; --portal-surface-soft:#f7f9fa; --portal-border:#d9e3e9; --portal-border-strong:#c7d4dc; --portal-text:#172535; --portal-muted:#71808b; --portal-primary:#1946a3; --portal-primary-soft:#edf4fc; --portal-success:#087365; --portal-success-soft:#eefaf6; --portal-danger:#a1432e; --portal-danger-soft:#fff4ef; --portal-radius:14px; --portal-control-radius:8px; --portal-shadow:0 8px 24px rgba(25,53,76,.06); min-height: 100vh; background: var(--portal-bg); color: var(--portal-text); }
.portal-shell, .portal-shell *, html:has(.portal-shell), html:has(.portal-shell) body { scrollbar-color:var(--portal-primary) var(--portal-bg); }
.portal-shell::-webkit-scrollbar-track, .portal-shell *::-webkit-scrollbar-track, html:has(.portal-shell)::-webkit-scrollbar-track, html:has(.portal-shell) body::-webkit-scrollbar-track { background:var(--portal-bg); }
.portal-shell::-webkit-scrollbar-thumb, .portal-shell *::-webkit-scrollbar-thumb, html:has(.portal-shell)::-webkit-scrollbar-thumb, html:has(.portal-shell) body::-webkit-scrollbar-thumb { background:var(--portal-primary); border-color:var(--portal-bg); }
.portal-shell::-webkit-scrollbar-thumb:hover, .portal-shell *::-webkit-scrollbar-thumb:hover, html:has(.portal-shell)::-webkit-scrollbar-thumb:hover, html:has(.portal-shell) body::-webkit-scrollbar-thumb:hover { background:#2a68b7; }
.portal-icon-sprite { position: absolute; width: 0; height: 0; overflow: hidden; }
.portal-sidebar { position: fixed; inset: 0 auto 0 0; z-index: 50; display: flex; width: 286px; flex-direction: column; overflow: hidden; color: #fff; background: #071126; border-right: 1px solid rgba(54,194,240,.22); }
.portal-sidebar::after { content: ""; position: absolute; right: -90px; bottom: 80px; width: 220px; height: 220px; border: 34px solid rgba(54,194,240,.06); transform: rotate(18deg); pointer-events: none; }
.portal-brand { position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; min-height: 82px; padding: 10px 22px; color: #fff; text-decoration: none; border-bottom: 1px solid rgba(255,255,255,.1); }
.portal-brand img { width: 168px; height: 64px; object-fit: contain; }
.portal-brand span { display: grid; line-height: 1; }
.portal-brand b { font-size: 20px; letter-spacing: .08em; }
.portal-brand small { margin-top: 6px; color: #36c2f0; font-size: 10px; font-weight: 900; letter-spacing: .22em; }
.portal-identity { position: relative; z-index: 1; display: grid; gap: 5px; margin: 22px 16px 12px; padding: 18px; background: linear-gradient(135deg, rgba(25,70,163,.72), rgba(8,20,45,.92)); border: 1px solid rgba(54,194,240,.25); box-shadow: 6px 6px 0 rgba(240,90,40,.16); }
.portal-identity span, .portal-group > p { color: #f6c64a; font-size: 10px; font-weight: 900; letter-spacing: .16em; text-transform: uppercase; }
.portal-identity strong { font-size: 15px; line-height: 1.3; }
.portal-identity small { color: rgba(255,255,255,.6); line-height: 1.45; }
.portal-nav { position: relative; z-index: 1; display: grid; gap: 16px; padding: 12px 14px 22px; overflow-y: auto; }
.portal-group { display: grid; gap: 5px; }
.portal-group > p { margin: 0 0 3px; padding: 0 10px; }
.portal-link { display: grid; grid-template-columns: 34px 1fr auto; align-items: center; gap: 10px; min-height: 48px; padding: 8px 10px; color: rgba(255,255,255,.68); text-decoration: none; border: 1px solid transparent; transition: .18s ease; }
.portal-link > span { display: grid; place-items: center; width: 30px; height: 30px; color: #36c2f0; font-size: 10px; font-weight: 1000; background: rgba(54,194,240,.08); border: 1px solid rgba(54,194,240,.18); }
.portal-link svg { width: 16px; height: 16px; fill: currentColor; }
.portal-link b { font-size: 13px; }
.portal-link small { padding: 3px 6px; color: rgba(255,255,255,.38); font-size: 9px; text-transform: uppercase; border: 1px solid rgba(255,255,255,.1); }
.portal-link:hover:not(.planned), .portal-link.active { color: #071126; background: #f6c64a; border-color: #f6c64a; transform: translateX(3px); }
.portal-link:hover:not(.planned) > span, .portal-link.active > span { color: #071126; background: rgba(7,17,38,.1); border-color: rgba(7,17,38,.16); }
.portal-link.planned { cursor: not-allowed; opacity: .56; }
.portal-sidebar-foot { position: relative; z-index: 1; display: grid; gap: 8px; margin-top: auto; padding: 16px; border-top: 1px solid rgba(255,255,255,.09); }
.portal-sidebar-foot a, .portal-sidebar-foot button { padding: 11px 14px; color: rgba(255,255,255,.72); text-align: left; text-decoration: none; background: transparent; border: 1px solid rgba(255,255,255,.12); cursor: pointer; }
.portal-sidebar-foot a:hover, .portal-sidebar-foot button:hover { color: #fff; border-color: #36c2f0; }
.portal-main { min-height: 100vh; margin-left: 286px; }
.portal-topbar { display: flex; min-height: 82px; align-items: center; justify-content: flex-end; padding: 14px 34px; background: rgba(255,255,255,.94); border-bottom: 1px solid #dbe5ea; }
.portal-topbar > div { display: grid; text-align: right; }
.portal-topbar span { color: #6f7d87; font-size: 10px; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
.portal-topbar strong { margin-top: 4px; font-size: 14px; }
.menu-button { display: none; margin-right: auto; padding: 9px 13px; color: #071126; background: #f6c64a; border: 0; font-weight: 900; text-transform: uppercase; }
.portal-content { width: min(1440px, 100%); margin: 0 auto; padding: 30px 34px 64px; }
.portal-content :is(input,select,textarea):focus-visible { border-color:var(--portal-primary)!important; box-shadow:0 0 0 3px rgba(25,70,163,.1)!important; outline:0; }
.portal-content button:focus-visible,.portal-content a:focus-visible { outline:3px solid rgba(25,70,163,.24); outline-offset:2px; }
.portal-content .page-head { padding-top: 8px !important; }
.portal-content .section-title h1, .portal-content .section-title h2 { color: var(--portal-text); font-weight: 750; }
.portal-content .section-title p { color: var(--portal-primary); font-weight: 800; }
.portal-content .section-title span { color: var(--portal-muted); font-weight: 800; }
.portal-content .portal-card { overflow:hidden; color:var(--portal-text); background:var(--portal-surface); border:1px solid var(--portal-border); border-radius:var(--portal-radius); box-shadow:var(--portal-shadow); }
.portal-content .portal-card-head { padding:18px 20px; background:var(--portal-surface-soft); border-bottom:1px solid var(--portal-border); }
.portal-content .portal-field { display:grid; gap:7px; color:#536571; font-size:11px; font-weight:750; }
.portal-content .portal-field input, .portal-content .portal-field select, .portal-content .portal-field textarea { width:100%; min-height:42px; padding:10px 12px; color:var(--portal-text); background:var(--portal-surface); border:1px solid var(--portal-border-strong); border-radius:var(--portal-control-radius); font:inherit; }
.portal-content .portal-button { min-height:40px; padding:9px 13px; color:var(--portal-primary); background:var(--portal-surface); border:1px solid var(--portal-border-strong); border-radius:var(--portal-control-radius); font-weight:800; cursor:pointer; }
.portal-content .portal-button.primary { color:#fff; background:var(--portal-primary); border-color:var(--portal-primary); }
.portal-content .portal-button.danger { color:var(--portal-danger); background:var(--portal-danger-soft); border-color:#efcfc4; }
.portal-flash { margin: 18px 34px 0; padding: 12px 16px; font-weight: 800; border-left: 5px solid; }
.portal-flash.success { color: #0b5f54; background: #dcf7f1; border-color: #20c6b7; }
.portal-flash.error { color: #8b2c18; background: #ffebe4; border-color: #f05a28; }
.portal-overlay { position: fixed; inset: 0; z-index: 40; background: rgba(7,17,38,.62); }
@media (max-width: 900px) {
  .portal-sidebar { transform: translateX(-100%); transition: transform .22s ease; }
  .portal-sidebar.open { transform: translateX(0); }
  .portal-main { margin-left: 0; }
  .portal-topbar { justify-content: space-between; padding: 12px 18px; }
  .menu-button { display: inline-block; }
  .portal-content { padding: 22px 16px 48px; }
}
</style>
