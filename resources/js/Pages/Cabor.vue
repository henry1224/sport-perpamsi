<script setup>
import { computed, ref } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';
import Modal from '../Components/Modal.vue';

const props = defineProps({ sports: Array, sportCategories: Array, sportTechnicalGuides: Array, sportRegulations: Array, agenda: Array, assets: Object });
const cards = props.sports.filter((sport) => sport.type !== 'official');
const selectedSport = ref(null);
const sportIcon = (code) => props.assets?.mascots?.[code?.toLowerCase()] || null;
const categories = (code) => props.sportCategories.filter((category) => category.sport_code === code);
const guide = (code) => props.sportTechnicalGuides.find((item) => item.sport_code === code);
const regulation = (code) => props.sportRegulations.find((item) => item.sport_code === code);
const venues = (code) => [...new Map(props.agenda.filter((item) => item.sport_code === code).map((item) => [item.venue, item])).values()];
const selectedGuide = computed(() => guide(selectedSport.value?.code));
const selectedRegulation = computed(() => regulation(selectedSport.value?.code));
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
      <article v-for="sport in cards" :key="sport.code" class="game-card">
        <header>
          <div><span>{{ sport.type }}</span><h3>{{ sport.name }}</h3></div>
          <img v-if="sportIcon(sport.code)" :src="sportIcon(sport.code)" alt="" />
        </header>
        <div class="summary-grid">
          <div><strong>Jadwal</strong><p>{{ guide(sport.code)?.schedule || 'Belum ditetapkan' }}</p></div>
          <div><strong>Kategori</strong><p>{{ categories(sport.code).length }} kategori tersedia</p></div>
        </div>
        <footer>
          <span>{{ venues(sport.code)[0]?.venue || guide(sport.code)?.venue || 'Venue belum ditetapkan' }}</span>
          <button type="button" :aria-label="`Lihat detail ${sport.name}`" @click="selectedSport = sport">Lihat Detail</button>
        </footer>
      </article>
    </div>

    <Modal :open="!!selectedSport" :title="selectedSport?.name || 'Detail Cabor'" @close="selectedSport = null">
      <div v-if="selectedSport" class="sport-detail">
        <section class="detail-lead">
          <div><span>{{ selectedSport.type }}</span><strong>{{ selectedRegulation?.title || 'Panduan teknis' }}</strong><small>Versi {{ selectedRegulation?.version || '—' }} · Sumber slide {{ selectedGuide?.source_slides || '—' }}</small></div>
          <img v-if="sportIcon(selectedSport.code)" :src="sportIcon(selectedSport.code)" alt="" />
        </section>

        <div class="detail-grid">
          <section><h4>Jadwal</h4><p>{{ selectedGuide?.schedule || 'Belum ditetapkan' }}</p></section>
          <section><h4>Lokasi</h4><p>{{ selectedGuide?.venue || venues(selectedSport.code)[0]?.venue || 'Belum ditetapkan' }}<small>{{ selectedGuide?.address || venues(selectedSport.code)[0]?.venue_address }}</small></p></section>
        </div>

        <section class="detail-section">
          <h4>Kategori dan Kuota</h4>
          <div class="category-list"><article v-for="category in categories(selectedSport.code)" :key="category.code"><strong>{{ category.name }}</strong><span>{{ memberLimit(category) }}</span></article></div>
        </section>

        <section v-if="selectedGuide?.system?.length" class="detail-section">
          <h4>Sistem Pertandingan</h4>
          <ul><li v-for="item in selectedGuide.system" :key="item">{{ item }}</li></ul>
        </section>

        <section class="detail-section">
          <h4>Persyaratan Peserta</h4>
          <ul v-if="selectedGuide?.eligibility?.length"><li v-for="item in selectedGuide.eligibility" :key="item">{{ item }}</li></ul>
          <p v-else>Belum ada persyaratan khusus yang ditetapkan.</p>
        </section>

        <div v-if="selectedGuide?.official_note || selectedGuide?.fee_note" class="detail-grid">
          <section v-if="selectedGuide?.official_note"><h4>Kontingen dan Official</h4><p>{{ selectedGuide.official_note }}</p></section>
          <section v-if="selectedGuide?.fee_note"><h4>Biaya</h4><p>{{ selectedGuide.fee_note }}</p></section>
        </div>

        <aside v-if="selectedGuide?.source_note">{{ selectedGuide.source_note }}</aside>
      </div>
    </Modal>
  </PublicLayout>
</template>

<style scoped>
.page-head{padding:44px 0 24px}.game-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}.game-card{position:relative;overflow:hidden;display:grid;gap:18px;padding:24px;color:#fff;background:#071126;border:1px solid rgba(255,255,255,.12);box-shadow:8px 8px 0 rgba(54,194,240,.13);clip-path:polygon(16px 0,100% 0,100% calc(100% - 16px),calc(100% - 16px) 100%,0 100%,0 16px)}.game-card::before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,rgba(54,194,240,.12),transparent 48%);pointer-events:none}.game-card>header,.game-card>footer,.summary-grid{position:relative;z-index:1}.game-card>header{display:flex;min-height:82px;align-items:center;justify-content:space-between;gap:18px}.game-card header span,.detail-lead span{color:#f6c64a;font-size:10px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}.game-card h3{margin:7px 0 0;font-size:30px;line-height:1}.game-card header img{width:108px;height:86px;object-fit:contain;filter:drop-shadow(0 12px 22px rgba(0,0,0,.35))}.summary-grid{display:grid;grid-template-columns:1.3fr .7fr;gap:10px}.summary-grid div{padding:13px 14px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1)}.summary-grid strong,.detail-section h4,.detail-grid h4{display:block;margin:0 0 6px;color:#36c2f0;font-size:10px;letter-spacing:.12em;text-transform:uppercase}.summary-grid p{margin:0;color:rgba(255,255,255,.72);font-size:12px;line-height:1.45}.game-card>footer{display:flex;align-items:center;justify-content:space-between;gap:14px;padding-top:15px;border-top:1px solid rgba(255,255,255,.11)}.game-card>footer span{color:rgba(255,255,255,.56);font-size:11px}.game-card button{min-height:38px;padding:9px 14px;color:#071126;background:#f6c64a;border:0;font-size:10px;font-weight:900;letter-spacing:.07em;text-transform:uppercase;cursor:pointer;clip-path:polygon(7px 0,100% 0,calc(100% - 7px) 100%,0 100%)}.game-card button:hover,.game-card button:focus-visible{background:#36c2f0;outline:none}.sport-detail{display:grid;gap:18px}.detail-lead{display:flex;min-height:110px;align-items:center;justify-content:space-between;gap:20px;padding:18px 20px;background:rgba(54,194,240,.08);border:1px solid rgba(54,194,240,.2)}.detail-lead>div{display:grid;gap:5px}.detail-lead strong{font-size:18px}.detail-lead small{color:rgba(255,255,255,.55)}.detail-lead img{width:120px;height:100px;object-fit:contain}.detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.detail-grid section,.detail-section{padding:17px 18px;background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.1)}.detail-grid p,.detail-section p{margin:0;color:rgba(255,255,255,.74);font-size:13px;line-height:1.55}.detail-grid p small{display:block;margin-top:4px;color:rgba(255,255,255,.48)}.detail-section ul{display:grid;gap:8px;margin:0;padding-left:18px;color:rgba(255,255,255,.76);font-size:13px;line-height:1.5}.category-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.category-list article{display:grid;gap:3px;padding:11px 12px;background:rgba(255,255,255,.045);border-left:3px solid #f6c64a}.category-list strong{font-size:12px}.category-list span{color:rgba(255,255,255,.56);font-size:11px}.sport-detail aside{padding:13px 15px;color:#efd889;background:rgba(246,198,74,.08);border:1px solid rgba(246,198,74,.24);font-size:11px;line-height:1.5}@media(max-width:800px){.game-grid{grid-template-columns:1fr}.summary-grid,.detail-grid,.category-list{grid-template-columns:1fr}.game-card{padding:20px}.game-card h3{font-size:26px}.detail-lead img{width:92px;height:82px}}@media(max-width:480px){.game-card>footer{align-items:flex-start;flex-direction:column}.game-card button{width:100%}.detail-lead img{display:none}}
</style>
