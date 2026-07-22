<script setup>
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';

const props = defineProps({ sports: Array, sportCategories: Array, sportTechnicalGuides: Array, assets: Object });
const cards = props.sports.filter((s) => s.type !== 'official');
const sportIcon = (code) => props.assets?.mascots?.[code?.toLowerCase()] || null;
const categories = (code) => props.sportCategories.filter((category) => category.sport_code === code);
const guide = (code) => props.sportTechnicalGuides.find((item) => item.sport_code === code);
const memberLimit = (category) => category.max_members === null || category.max_members === ''
  ? `Minimal ${category.min_members} pemain`
  : Number(category.min_members) === Number(category.max_members)
    ? `${category.min_members} pemain`
    : `${category.min_members}–${category.max_members} pemain`;
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Game Roster" title="Cabor & Eksibisi" :meta="`${cards.length} aktivitas`" />
    </div>
    <div class="game-grid">
      <div v-for="sport in cards" :key="sport.code" class="game-card">
        <header><div><span>{{ sport.type }}</span><h3>{{ sport.name }}</h3></div><img v-if="sportIcon(sport.code)" :src="sportIcon(sport.code)" alt="" /></header>
        <div v-if="guide(sport.code)" class="technical-grid">
          <div><strong>Jadwal</strong><p>{{ guide(sport.code).schedule }}</p></div>
          <div><strong>Lokasi</strong><p>{{ guide(sport.code).venue }}</p><small>{{ guide(sport.code).address }}</small></div>
        </div>
        <section class="category-list"><strong>Kategori</strong><div><article v-for="category in categories(sport.code)" :key="category.code"><b>{{ category.name }}</b><small>{{ memberLimit(category) }}</small></article></div></section>
        <section v-if="guide(sport.code)?.system?.length" class="technical-list"><strong>Sistem Pertandingan</strong><ol><li v-for="item in guide(sport.code).system" :key="item">{{ item }}</li></ol></section>
        <section v-if="guide(sport.code)?.eligibility?.length" class="technical-list"><strong>Syarat Peserta</strong><ul><li v-for="item in guide(sport.code).eligibility" :key="item">{{ item }}</li></ul></section>
        <p v-if="guide(sport.code)?.official_note" class="note"><strong>Kontingen</strong>{{ guide(sport.code).official_note }}</p>
        <p v-if="guide(sport.code)?.fee_note" class="note"><strong>Biaya</strong>{{ guide(sport.code).fee_note }}</p>
        <footer><span>Format bawaan: {{ sport.default_format.replaceAll('_', ' ') }}</span><small>Sumber slide {{ guide(sport.code)?.source_slides || '—' }}</small></footer>
      </div>
    </div>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.game-grid { display: grid; gap: 22px; }
.game-card { position: relative; overflow: hidden; padding: 26px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 8px 8px 0 rgba(54,194,240,.13); color: inherit; clip-path: polygon(18px 0,100% 0,100% calc(100% - 18px),calc(100% - 18px) 100%,0 100%,0 18px); }
.game-card::before { content: ""; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(54,194,240,.12), transparent 45%); opacity: 0; transition: opacity .2s ease; }
.game-card::after { content: ""; position: absolute; inset: auto -20% -40% auto; width: 230px; height: 230px; background: radial-gradient(circle, rgba(246,198,74,.28), transparent 62%); }
.game-card:hover::before { opacity: 1; }
.game-card > header { position:relative; z-index:2; display:flex; min-height:90px; align-items:center; justify-content:space-between; gap:20px; border-bottom:1px solid rgba(255,255,255,.12); }.game-card header img { width:120px; height:100px; object-fit:contain; filter:drop-shadow(0 12px 22px rgba(0,0,0,.35)); }.game-card header span { color:#F6C64A; font-size:11px; font-weight:900; letter-spacing:.14em; text-transform:uppercase; }.game-card h3 { margin:7px 0 0; font-size:32px; line-height:1; }.technical-grid { position:relative; z-index:2; display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:18px; }.technical-grid div,.category-list article { padding:13px 15px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); }.technical-grid strong,.category-list > strong,.technical-list > strong,.note strong { display:block; margin-bottom:6px; color:#36C2F0; font-size:10px; letter-spacing:.12em; text-transform:uppercase; }.technical-grid p,.technical-grid small { margin:0; color:rgba(255,255,255,.74); font-size:13px; line-height:1.45; }.technical-grid small { color:rgba(255,255,255,.5); }.category-list,.technical-list { position:relative; z-index:2; margin-top:18px; }.category-list > div { display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:8px; }.category-list article { display:grid; gap:4px; }.category-list article b { font-size:13px; }.category-list article small { color:rgba(255,255,255,.55); }.technical-list ol,.technical-list ul { margin:0; padding-left:20px; color:rgba(255,255,255,.7); font-size:13px; line-height:1.65; }.note { position:relative; z-index:2; margin:14px 0 0; padding:12px 14px; color:rgba(255,255,255,.72); background:rgba(246,198,74,.08); border-left:3px solid #F6C64A; font-size:13px; }.game-card footer { position:relative; z-index:2; display:flex; justify-content:space-between; gap:12px; margin-top:20px; padding-top:14px; color:rgba(255,255,255,.45); border-top:1px solid rgba(255,255,255,.1); font-size:10px; text-transform:capitalize; }
@media (max-width: 640px) { .technical-grid { grid-template-columns:1fr; }.game-card > header { align-items:flex-start; }.game-card header img { width:88px; height:78px; }.game-card footer { flex-direction:column; } }
</style>
