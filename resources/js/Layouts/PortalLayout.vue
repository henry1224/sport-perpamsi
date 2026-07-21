<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ portal: { type: String, required: true } });
const page = usePage();
const open = ref(false);
const user = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash || {});
const isAdmin = computed(() => props.portal === 'admin');
const identity = computed(() => isAdmin.value
  ? { eyebrow: 'Pusat Kendali', title: 'Admin PORPAMNAS', subtitle: 'Seluruh data event dan operasional' }
  : { eyebrow: 'Portal Delegasi', title: user.value?.committee?.name || 'Pengurus Daerah', subtitle: 'Registrasi atlet dan cabang olahraga' });

const menuGroups = computed(() => isAdmin.value ? [
  { label: 'Umum', items: [
    { label: 'Ringkasan', href: '/admin/dashboard', code: '01' },
  ] },
  { label: 'Persiapan Lomba', items: [
    { label: 'Master Data', href: '/admin/master-data', code: '02' },
    { label: 'Data Lomba', href: '/admin/events', code: '03' },
  ] },
  { label: 'Registrasi', items: [
    { label: 'Verifikasi Pengurus Daerah', href: '/admin/committee-applications', code: '04' },
    { label: 'Verifikasi Peserta', href: '/admin/entries', code: '05' },
  ] },
  { label: 'Operasional', items: [
    { label: 'Panitia & Akses', href: '/admin/assignments', code: '06' },
    { label: 'Pertandingan & Skor', href: '/admin/skor', code: '07' },
  ] },
  { label: 'Pelaporan', items: [
    { label: 'Laporan & Audit', code: '08', planned: true },
  ] },
] : [
  { label: 'Umum', items: [
    { label: 'Ringkasan', href: '/pd/dashboard', code: '01' },
  ] },
  { label: 'Pendaftaran', items: [
    { label: 'Registrasi Cabor', href: '/pd/dashboard#cabor', code: '02' },
  ] },
]);

const active = (href) => href && page.url.split('#')[0] === href.split('#')[0];
const logout = () => router.post('/logout');
</script>

<template>
  <div class="portal-shell">
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
            :class="['portal-link', { active: active(item.href), planned: item.planned }]"
            @click="open = false"
          >
            <span>{{ item.code }}</span>
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
          <span>{{ isAdmin ? 'Administrator' : 'Pengurus Daerah' }}</span>
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
.portal-shell { min-height: 100vh; background: #eef3f6; color: #152331; }
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
.portal-content .page-head { padding-top: 8px !important; }
.portal-content .section-title h1, .portal-content .section-title h2 { color: #071126; }
.portal-content .section-title p { color: #63727e; }
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
