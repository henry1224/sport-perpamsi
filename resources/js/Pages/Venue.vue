<script setup>
import { ref, computed } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';
import Modal from '../Components/Modal.vue';

const props = defineProps({ venues: Array, agenda: Array });

const sportTone = {
  'mini-football': 'lime', 'chess': 'amber', 'badminton': 'sky',
  'tennis': 'green', 'table-tennis': 'peach', 'volleyball': 'sky',
  'golf': 'lime', 'padel': 'sky', 'vocal': 'amber',
};
const typeTone = { official: 'navy', exhibition: 'amber', sport: 'sky' };
const toneOf = (item) => sportTone[item.sport_code] || typeTone[item.type] || 'sky';

const withCount = props.venues.map((v) => ({
  ...v,
  items: props.agenda
    .filter((a) => a.venue === v.name)
    .sort((a, b) => (a.date + a.start_time).localeCompare(b.date + b.start_time)),
})).sort((a, b) => b.items.length - a.items.length);

const active = ref(null);
const activeItems = computed(() => active.value?.items || []);
const mapsUrl = computed(() => active.value?.map_url || (active.value?.latitude && active.value?.longitude
  ? `https://www.google.com/maps/search/?api=1&query=${active.value.latitude},${active.value.longitude}`
  : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent([active.value?.name, active.value?.address, active.value?.city].filter(Boolean).join(', '))}`));

const formatDate = (d, day) => {
  const [, m, dd] = d.split('-');
  const bulan = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][parseInt(m)];
  return `${day}, ${parseInt(dd)} ${bulan}`;
};
const formatTime = (i) => `${i.start_time} – ${i.end_time || i.time_note || 'Selesai'} WITA`;
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Battle Ground" title="Venue PORPAMNAS IX" :meta="`${withCount.length} venue`" />
    </div>

    <div class="grid">
      <button v-for="v in withCount" :key="v.code" type="button" class="venue-card" @click="active = v">
        <span class="tag">Venue</span>
        <h3>{{ v.name }}</h3>
        <p class="addr">📍 {{ v.address }}</p>
        <div class="footer">
          <small>{{ v.city }}</small>
          <b>{{ v.items.length }} <span>agenda</span></b>
        </div>
        <span class="cta">Lihat detail →</span>
      </button>
    </div>

    <Modal :open="!!active" :title="active?.name || ''" @close="active = null">
      <p class="modal-addr">📍 {{ active?.address }}<br /><small>{{ active?.city }}</small></p>
      <a class="maps-button" :href="mapsUrl" target="_blank" rel="noopener">Buka panduan lokasi di Google Maps ↗</a>
      <div v-if="activeItems.length" class="items">
        <div v-for="(item, i) in activeItems" :key="i" :class="['item', toneOf(item)]">
          <div class="when">
            <strong>{{ formatDate(item.date, item.day) }}</strong>
            <small>{{ formatTime(item) }}</small>
          </div>
          <div class="what">
            <span class="type">{{ item.type }}</span>
            <strong>{{ item.title }}</strong>
            <small v-if="item.time_note">{{ item.time_note }}</small>
          </div>
        </div>
      </div>
      <p v-else class="empty">Belum ada agenda terdaftar di venue ini.</p>
    </Modal>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 22px 18px; }

.venue-card { position: relative; display: grid; gap: 12px; min-height: 230px; padding: 24px; overflow: hidden; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 8px 8px 0 rgba(54,194,240,.13); color: white; text-align: left; cursor: pointer; transition: transform .2s ease, border-color .2s ease; clip-path: polygon(18px 0,100% 0,100% calc(100% - 18px),calc(100% - 18px) 100%,0 100%,0 18px); }
.venue-card::before { content: "VENUE"; position: absolute; right: -8px; top: 12px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.07); font-size: 58px; font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
.venue-card::after { content: ""; position: absolute; inset: auto -20% -42% auto; width: 230px; height: 230px; background: radial-gradient(circle, rgba(246,198,74,.24), transparent 62%); }
.venue-card:hover { transform: translateY(-4px); border-color: rgba(246,198,74,.35); }
.tag { position: relative; z-index: 1; justify-self: start; padding: 4px 10px; background: #36C2F0; color: #071126; font-size: 10px; font-weight: 1000; letter-spacing: .14em; text-transform: uppercase; }
.venue-card h3 { position: relative; z-index: 1; max-width: 78%; margin: 0; font-size: 27px; line-height: 1.05; letter-spacing: -.04em; }
.addr { position: relative; z-index: 1; max-width: 82%; margin: 0; color: rgba(255,255,255,.7); font-size: 13px; line-height: 1.5; }
.footer { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 14px; border-top: 1px dashed rgba(255,255,255,.14); }
.footer small { color: rgba(255,255,255,.6); font-weight: 900; letter-spacing: .1em; text-transform: uppercase; font-size: 11px; }
.footer b { color: #F6C64A; font-size: 26px; }
.footer b span { font-size: 11px; color: rgba(255,255,255,.55); font-weight: 800; letter-spacing: .12em; text-transform: uppercase; margin-left: 4px; }
.cta { position: relative; z-index: 1; margin-top: 4px; color: #36C2F0; font-size: 12px; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; }

.modal-addr { margin: 0 0 18px; padding: 14px 16px; background: rgba(5,11,28,.6); border-left: 5px solid #36C2F0; color: rgba(255,255,255,.78); font-size: 13px; line-height: 1.55; }
.modal-addr small { color: rgba(255,255,255,.55); font-weight: 800; letter-spacing: .1em; text-transform: uppercase; font-size: 11px; }
.maps-button { display:flex; align-items:center; justify-content:center; min-height:44px; margin:-6px 0 18px; padding:10px 14px; color:#071126; background:#36C2F0; border:1px solid rgba(255,255,255,.18); font-size:12px; font-weight:900; text-decoration:none; }
.maps-button:hover { background:#F6C64A; }

.items { display: grid; gap: 0; border-top: 1px solid rgba(255,255,255,.12); }
.item { --tone: #36C2F0; display: grid; grid-template-columns: 160px 1fr; gap: 16px; padding: 16px; background: linear-gradient(90deg, rgba(255,255,255,.045), rgba(255,255,255,.02)); border-bottom: 1px solid rgba(255,255,255,.1); border-left: 5px solid var(--tone); }
.item .when strong { display: block; font-size: 13px; color: white; }
.item .when small { color: rgba(255,255,255,.7); font-size: 11px; font-weight: 900; letter-spacing: .06em; }
.item .what strong { display: block; margin-top: 4px; font-size: 15px; }
.item .what small { display: block; margin-top: 4px; color: rgba(255,255,255,.65); font-size: 11px; }
.item .type { display: inline-block; padding: 4px 9px; background: var(--tone); color: #071126; font-size: 9px; font-weight: 1000; letter-spacing: .14em; text-transform: uppercase; }
.item.lime  { --tone: #B8DC6E; }
.item.amber { --tone: #F6C64A; }
.item.sky   { --tone: #36C2F0; }
.item.green { --tone: #96C88C; }
.item.peach { --tone: #F4B496; }
.item.navy  { --tone: #36C2F0; }

.empty { text-align: center; padding: 24px; color: rgba(255,255,255,.55); }

@media (max-width: 640px) { .item { grid-template-columns: 1fr; } }
</style>
