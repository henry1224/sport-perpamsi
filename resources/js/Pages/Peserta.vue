<script setup>
import { computed, ref } from 'vue';
import PublicLayout from '../Layouts/PublicLayout.vue';
import SectionTitle from '../Components/SectionTitle.vue';

const props = defineProps({ regionalCommittees: Array });

const q = ref('');
const participants = computed(() => props.regionalCommittees
  .filter((participant) => participant.name.toLowerCase().includes(q.value.toLowerCase()))
  .sort((a, b) => a.name.localeCompare(b.name)));
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Participant Directory" title="Kontingen Provinsi" :meta="`${participants.length} provinsi`" />
    </div>

    <section class="participant-board">
      <div class="filters">
        <label>
          <span>Cari</span>
          <input v-model="q" type="search" placeholder="Cari nama provinsi" />
        </label>
      </div>

      <div class="pd-list">
        <article v-for="participant in participants" :key="participant.province_code" class="pd-card">
          <header>
            <span>{{ participant.province_code }}</span>
            <strong>{{ participant.name }}</strong>
          </header>
          <small>Kontingen PORPAMNAS IX</small>
        </article>
        <p v-if="!participants.length" class="empty">Tidak ada kontingen sesuai pencarian.</p>
      </div>
    </section>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.participant-board { position: relative; overflow: hidden; }
.participant-board::before { content: "DAERAH"; position: absolute; right: 18px; top: 8px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.055); font-size: clamp(80px, 13vw, 170px); font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
.filters { position: relative; z-index: 1; max-width: 560px; margin-bottom: 30px; padding: 16px; background: rgba(5,11,28,.56); border: 1px solid rgba(255,255,255,.12); }
.filters label { display: grid; gap: 10px; }
.filters span { color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
input { width: 100%; padding: 12px 14px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.16); font: inherit; }
.empty { text-align: center; padding: 40px; color: rgba(255,255,255,.5); }
.pd-list { position: relative; z-index: 1; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 18px; }
.pd-card { padding: 16px; background: #071126; border: 1px solid rgba(255,255,255,.12); border-left: 5px solid #F6C64A; box-shadow: 6px 6px 0 rgba(54,194,240,.13); }
.pd-card header { display: grid; grid-template-columns: auto 1fr; align-items: center; gap: 12px; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 1px solid rgba(255,255,255,.1); }
.pd-card header span { color: #071126; background: #F6C64A; padding: 6px 9px; font-size: 12px; font-weight: 1000; box-shadow: 4px 4px 0 rgba(240,90,40,.35); }
.pd-card header strong { color: #F6C64A; letter-spacing: .06em; }
.pd-card > small { color: rgba(255,255,255,.6); font-weight: 800; }
</style>
