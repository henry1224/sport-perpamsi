<script setup>
import { Link } from '@inertiajs/vue3';
import PortalLayout from '../../Layouts/PortalLayout.vue';

defineProps({ stats: Object, recentEntries: Array });

const cards = [
  ['Kontingen Provinsi', 'committees', 'Peserta daerah'],
  ['PDAM', 'pdams', 'Instansi peserta'],
  ['Data Lomba', 'events', 'Cabor dan kategori'],
  ['Registrasi', 'entries', 'Seluruh entry'],
  ['Menunggu Verifikasi', 'pending', 'Perlu tindakan'],
  ['Pertandingan', 'matches', 'Match tersedia'],
];
</script>

<template>
  <PortalLayout portal="admin">
    <section class="admin-hero">
      <div>
        <span>Command Center</span>
        <h1>Dashboard Admin</h1>
        <p>Kelola data lomba, registrasi daerah, pertandingan, panitia, dan laporan dari satu portal.</p>
      </div>
      <Link href="/admin/entries">Periksa Registrasi</Link>
    </section>

    <section class="metric-grid">
      <article v-for="([label, key, note], index) in cards" :key="key">
        <small>0{{ index + 1 }}</small><span>{{ label }}</span><strong>{{ stats[key] }}</strong><p>{{ note }}</p>
      </article>
    </section>

    <div class="admin-columns">
      <section class="admin-panel">
        <header><div><span>Registrasi Terbaru</span><h2>Aktivitas Daerah</h2></div><Link href="/admin/entries">Lihat Semua</Link></header>
        <div class="entry-list">
          <article v-for="entry in recentEntries" :key="entry.id">
            <div><strong>{{ entry.name }}</strong><span>{{ entry.pdam }} · {{ entry.event }}</span></div>
            <small :class="entry.status">{{ entry.status }}</small>
          </article>
          <p v-if="!recentEntries.length">Belum ada registrasi.</p>
        </div>
      </section>
      <section class="admin-panel access-panel">
        <header><div><span>Kontrol Akses</span><h2>Panitia</h2></div></header>
        <p>Admin akan menentukan menu, cabor, dan pertandingan yang boleh dikelola setiap panitia.</p>
        <ul><li>Koordinator cabor</li><li>Verifikator peserta</li><li>Scorekeeper pertandingan</li><li>Auditor read-only</li></ul>
        <b>Menu pengaturan panitia disiapkan pada fase berikutnya.</b>
      </section>
    </div>
  </PortalLayout>
</template>

<style scoped>
.admin-hero { display: flex; align-items: end; justify-content: space-between; gap: 24px; padding: 30px; color: #fff; background: linear-gradient(120deg, #071126 0 68%, #1946a3 68%); box-shadow: 10px 10px 0 rgba(25,70,163,.13); }
.admin-hero span, .admin-panel header span { color: #36c2f0; font-size: 10px; font-weight: 1000; letter-spacing: .18em; text-transform: uppercase; }
.admin-hero h1 { margin: 7px 0; font-size: clamp(30px, 5vw, 58px); line-height: .95; text-transform: uppercase; }
.admin-hero p { max-width: 650px; margin: 0; color: rgba(255,255,255,.65); }
.admin-hero a { flex: none; padding: 13px 18px; color: #071126; background: #f6c64a; font-size: 12px; font-weight: 1000; text-decoration: none; text-transform: uppercase; box-shadow: 6px 6px 0 #f05a28; }
.metric-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin: 28px 0; }
.metric-grid article { position: relative; display: grid; min-height: 145px; padding: 20px; overflow: hidden; background: #fff; border: 1px solid #dbe5ea; }
.metric-grid small { position: absolute; right: 12px; top: 4px; color: #edf2f4; font-size: 58px; font-weight: 1000; }
.metric-grid span { position: relative; color: #63727e; font-size: 10px; font-weight: 1000; letter-spacing: .13em; text-transform: uppercase; }
.metric-grid strong { position: relative; align-self: end; color: #071126; font-size: 38px; }
.metric-grid p { position: relative; margin: 2px 0 0; color: #7b8992; font-size: 12px; }
.admin-columns { display: grid; grid-template-columns: 1.5fr .8fr; gap: 18px; }
.admin-panel { padding: 22px; background: #fff; border: 1px solid #dbe5ea; }
.admin-panel header { display: flex; align-items: center; justify-content: space-between; padding-bottom: 16px; border-bottom: 1px solid #e5ecef; }
.admin-panel h2 { margin: 4px 0 0; color: #071126; }
.admin-panel header a { color: #1946a3; font-size: 12px; font-weight: 900; text-decoration: none; }
.entry-list article { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 15px 0; border-bottom: 1px solid #edf1f3; }
.entry-list article div { display: grid; gap: 4px; }
.entry-list article span { color: #71808a; font-size: 12px; }
.entry-list article small { padding: 5px 8px; font-weight: 900; text-transform: uppercase; }
.entry-list article small.pending { color: #8a6200; background: #fff2c8; }
.entry-list article small.verified { color: #087365; background: #dff7f2; }
.entry-list article small.rejected { color: #a13d24; background: #ffe7df; }
.access-panel { color: #fff; background: #1946a3; border: 0; }
.access-panel h2 { color: #fff; }
.access-panel p, .access-panel li { color: rgba(255,255,255,.72); line-height: 1.55; }
.access-panel ul { padding-left: 18px; }
.access-panel b { display: block; margin-top: 22px; padding: 12px; color: #071126; background: #f6c64a; font-size: 12px; }
@media (max-width: 1050px) { .metric-grid { grid-template-columns: repeat(2, 1fr); } .admin-columns { grid-template-columns: 1fr; } }
@media (max-width: 620px) { .admin-hero { align-items: stretch; flex-direction: column; } .metric-grid { grid-template-columns: 1fr; } }
</style>
