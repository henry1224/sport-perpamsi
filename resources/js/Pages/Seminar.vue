<script setup>
import { Link } from '@inertiajs/vue3';
import PublicLayout from '../Layouts/PublicLayout.vue';

const sessions = [
  {
    number: '01',
    forum: 'Indonesia Water Forum',
    title: 'Strengthening Regional Water Security',
    subtitle: 'Challenges, Policy, and Future Investment',
    focus: [
      'Tantangan penyediaan air baku di daerah.',
      'Dampak kawasan strategis nasional terhadap layanan air.',
      'Dukungan regulasi dan pendanaan bagi PDAM.',
      'Rekomendasi kebijakan kepada pemerintah pusat.',
    ],
    speakers: [
      { name: 'Basuki Hadimuljono', role: 'Kepala Otorita Ibu Kota Nusantara', image: '/assets/brand/seminar/speakers/basuki-hadimuljono.jpg' },
      { name: "Rudy Mas'ud", role: 'Gubernur Kalimantan Timur', image: '/assets/brand/seminar/speakers/rudy-masud.jpg' },
      { name: "Rahmad Mas'ud", role: 'Wali Kota Balikpapan', image: '/assets/brand/seminar/speakers/rahmad-masud.jpg' },
      { name: 'Andi Harun', role: 'Wali Kota Samarinda', image: '/assets/brand/seminar/speakers/andi-harun.jpg' },
      { name: 'Aulia Rahman Basri', role: 'Bupati Kutai Kartanegara', image: '/assets/brand/seminar/speakers/aulia-rahman-basri.jpg' },
      { name: 'Mudyat Noor', role: 'Bupati Penajam Paser Utara', image: '/assets/brand/seminar/speakers/mudyat-noor.jpg' },
    ],
  },
  {
    number: '02',
    forum: 'Global Water Forum',
    title: 'Global Perspectives on Water Security',
    subtitle: 'Innovation, Climate, and Resilient Cities',
    focus: [
      'Ketahanan air dalam perspektif global.',
      'Adaptasi terhadap perubahan iklim.',
      'Teknologi dan digitalisasi sektor air.',
      'Studi kasus dari berbagai negara.',
      'Kolaborasi internasional untuk masa depan layanan air.',
    ],
    speakers: [
      { name: 'A. Teddy Setiabudi', role: 'Ketua Umum PP PERPAMSI', image: '/assets/brand/seminar/speakers/teddy-setiabudi.jpg' },
      { name: 'Retno Lestari Priansari Marsudi', role: 'Utusan Khusus Sekjen PBB untuk Air', image: '/assets/brand/seminar/speakers/retno-marsudi.jpg' },
      { name: 'Saroj Kumar Jha', role: 'Global Director for Water Global Practice, World Bank Group', image: '/assets/brand/seminar/speakers/saroj-kumar-jha.jpg' },
      { name: 'Harry Seah', role: 'PUB, Singapore', image: '/assets/brand/seminar/speakers/harry-seah.jpg' },
    ],
  },
];
</script>

<template>
  <PublicLayout>
    <section class="seminar-hero">
      <div class="conference-code"><span>NIWC</span><small>2026</small></div>
      <div class="seminar-copy">
        <p>Nusantara International Water Conference</p>
        <h1>Water for the Nation, Local Voices, Global Solutions</h1>
        <div class="seminar-lead">Forum PERPAMSI yang membawa tantangan nyata daerah ke percakapan nasional dan global—mencari kebijakan, investasi, teknologi, dan kolaborasi untuk masa depan layanan air.</div>
      </div>
      <div class="seminar-facts">
        <div><span>Tanggal</span><strong>08</strong><b>Oktober 2026</b></div>
        <div><span>Waktu</span><strong>09.00</strong><b>— 16.00 WITA</b></div>
        <div><span>Lokasi</span><strong>IKN</strong><b>Gedung Kemenko 2</b></div>
      </div>
    </section>

    <section class="session-section">
      <header class="section-head">
        <div><span>Conference Program</span><h2>Dua Forum, Satu Agenda Air</h2></div>
        <p>Diskusi bergerak dari kebutuhan daerah menuju praktik dan inovasi dunia.</p>
      </header>
      <div class="session-grid">
        <article v-for="session in sessions" :key="session.number" class="session-card">
          <div class="session-number">{{ session.number }}</div>
          <div class="session-intro">
            <div>
              <span>{{ session.forum }}</span>
              <h3>{{ session.title }}</h3>
              <h4>{{ session.subtitle }}</h4>
            </div>
            <div class="session-focus">
              <b>Fokus Seminar</b>
              <ul><li v-for="item in session.focus" :key="item">{{ item }}</li></ul>
            </div>
          </div>
          <div class="speaker-lineup">
            <div class="lineup-label"><span>Featured Speakers</span><b>{{ session.speakers.length }} pembicara</b></div>
            <div class="speaker-grid">
              <article v-for="speaker in session.speakers" :key="speaker.name" class="speaker-card">
                <div class="speaker-photo"><img :src="speaker.image" :alt="speaker.name" /></div>
                <strong>{{ speaker.name }}</strong>
                <small>{{ speaker.role }}</small>
              </article>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section class="registration-board">
      <div class="audience-block">
        <span>Untuk Siapa</span>
        <h2>Pemimpin sektor air daerah</h2>
        <div class="audience-list">
          <b>Kuasa Pemilik Modal (KPM)</b>
          <b>Dewan Pengawas</b>
          <b>Direksi BUMD Air Minum</b>
        </div>
      </div>
      <div class="benefit-block">
        <span>Participant Benefit</span>
        <h3>E-Sertifikat</h3><h3>Seminar Kit</h3>
      </div>
      <div class="fee-block">
        <span>Biaya Pendaftaran</span>
        <strong>Rp1.500.000</strong><small>per orang</small>
        <p>Transfer BRI<br><b>012101005013303</b><br>Panitia PORPAMNAS IX 2026</p>
      </div>
    </section>

    <section class="seminar-action">
      <div><span>PORPAMNAS IX · Kalimantan Timur</span><h2>Catat tanggalnya. Bawa suara daerah.</h2></div>
      <Link href="/agenda">Lihat Agenda Lengkap</Link>
    </section>
  </PublicLayout>
</template>

<style scoped>
.seminar-hero { position: relative; display: grid; grid-template-columns: 130px 1fr; gap: 28px; min-height: 650px; padding: 92px 0 190px; overflow: hidden; }
.seminar-hero::before { content: "WATER"; position: absolute; right: -18px; top: 75px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.07); font-size: clamp(110px, 20vw, 300px); font-weight: 1000; letter-spacing: -.1em; line-height: .8; }
.conference-code { position: relative; z-index: 2; display: grid; align-self: start; margin-top: 20px; padding: 22px 14px; color: #071126; text-align: center; background: #36c2f0; box-shadow: 8px 8px 0 #1946a3; transform: skewY(-5deg); }
.conference-code span { font-size: 32px; font-weight: 1000; letter-spacing: -.05em; }
.conference-code small { font-weight: 1000; letter-spacing: .18em; }
.seminar-copy { position: relative; z-index: 2; max-width: 1000px; }
.seminar-copy > p, .section-head span, .registration-board span, .seminar-action span { color: #36c2f0; font-size: 11px; font-weight: 1000; letter-spacing: .18em; text-transform: uppercase; }
.seminar-copy h1 { max-width: 980px; margin: 18px 0 34px; color: #fff; font-family: Georgia, Cambria, "Times New Roman", serif; font-size: clamp(44px, 6.7vw, 96px); font-weight: 700; line-height: .98; letter-spacing: -.045em; text-transform: none; text-wrap: balance; text-shadow: none; font-variant-ligatures: common-ligatures; }
.seminar-lead { max-width: 720px; padding-left: 22px; color: rgba(255,255,255,.74); font-size: 18px; line-height: 1.65; border-left: 5px solid #36c2f0; }
.seminar-facts { position: absolute; left: 0; right: 0; bottom: 20px; z-index: 3; display: grid; grid-template-columns: repeat(3, 1fr); background: #fff; color: #071126; box-shadow: 12px 12px 0 rgba(54,194,240,.2); }
.seminar-facts div { display: grid; gap: 4px; padding: 20px 24px; border-right: 1px solid #dfe7ea; }
.seminar-facts div:last-child { border-right: 0; }
.seminar-facts span { color: #1946a3; font-size: 10px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
.seminar-facts strong { font-size: 34px; line-height: 1; }
.seminar-facts b { color: #697780; font-size: 12px; text-transform: uppercase; }
.session-section { padding: 70px 0 100px; }
.section-head { display: flex; align-items: end; justify-content: space-between; gap: 30px; margin-bottom: 28px; }
.section-head h2 { max-width: 700px; margin: 6px 0 0; font-size: clamp(34px, 5vw, 70px); line-height: .95; text-transform: uppercase; }
.section-head p { max-width: 390px; color: rgba(255,255,255,.62); line-height: 1.6; }
.session-grid { display: grid; gap: 30px; }
.session-card { position: relative; padding: 34px; overflow: hidden; background: linear-gradient(145deg, #0b1f4d, #071126); border: 1px solid rgba(255,255,255,.13); box-shadow: 10px 10px 0 rgba(25,70,163,.25); }
.session-card:nth-child(2) { background: linear-gradient(145deg, #083d48, #071126); box-shadow-color: rgba(32,198,183,.23); }
.session-number { position: absolute; right: 14px; top: -26px; color: rgba(255,255,255,.045); font-size: 180px; font-weight: 1000; }
.session-intro { position: relative; display: grid; grid-template-columns: 1fr .8fr; gap: 50px; padding-bottom: 32px; border-bottom: 1px solid rgba(255,255,255,.12); }
.session-card span { color: #f6c64a; font-size: 11px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
.session-card h3 { position: relative; max-width: 700px; margin: 28px 0 8px; font-size: clamp(28px, 4vw, 55px); line-height: .98; text-transform: uppercase; }
.session-card h4 { position: relative; margin: 0 0 32px; color: #36c2f0; font-size: 16px; line-height: 1.4; }
.session-focus > b { display: block; width: fit-content; margin-bottom: 18px; padding: 7px 10px; color: #071126; background: #f6c64a; font-size: 10px; font-weight: 1000; letter-spacing: .15em; text-transform: uppercase; box-shadow: 4px 4px 0 rgba(240,90,40,.45); }
.session-card ul { position: relative; display: grid; gap: 14px; margin: 0; padding: 0; list-style: none; }
.session-card li { padding-left: 20px; color: rgba(255,255,255,.72); line-height: 1.5; }
.session-card li::before { content: "+"; position: absolute; margin-left: -20px; color: #f6c64a; font-weight: 1000; }
.speaker-lineup { position: relative; padding-top: 28px; }
.lineup-label { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
.lineup-label b { color: rgba(255,255,255,.5); font-size: 10px; letter-spacing: .12em; text-transform: uppercase; }
.speaker-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 14px; }
.speaker-card { display: grid; align-content: start; padding: 10px; background: rgba(255,255,255,.055); border: 1px solid rgba(255,255,255,.1); transition: transform .2s ease, border-color .2s ease; }
.speaker-card:hover { border-color: rgba(246,198,74,.58); transform: translateY(-4px); }
.speaker-photo { position: relative; aspect-ratio: 1; margin-bottom: 13px; overflow: hidden; background: #fff; clip-path: polygon(12px 0,100% 0,100% calc(100% - 12px),calc(100% - 12px) 100%,0 100%,0 12px); }
.speaker-photo::after { content: ""; position: absolute; inset: auto 0 0; height: 5px; background: linear-gradient(90deg, #36c2f0 0 60%, #f6c64a 60%); }
.speaker-photo img { width: 100%; height: 100%; object-fit: cover; }
.speaker-card strong { font-size: 13px; line-height: 1.25; }
.speaker-card small { margin-top: 6px; color: rgba(255,255,255,.56); font-size: 10px; line-height: 1.4; }
.registration-board { display: grid; grid-template-columns: 1.2fr .75fr .9fr; margin-bottom: 90px; background: #fff; color: #071126; box-shadow: 14px 14px 0 rgba(240,90,40,.2); }
.registration-board > div { padding: 30px; border-right: 1px solid #dfe7ea; }
.registration-board > div:last-child { border-right: 0; }
.registration-board span { color: #1946a3; }
.registration-board h2 { margin: 12px 0 24px; font-size: 34px; line-height: 1; text-transform: uppercase; }
.audience-list { display: grid; gap: 8px; }
.audience-list b { padding: 10px 12px; background: #eef4f6; border-left: 4px solid #36c2f0; }
.benefit-block { color: #fff; background: #1946a3; }
.benefit-block span { color: #8fe4ff; }
.benefit-block h3 { margin: 20px 0 0; font-size: 25px; text-transform: uppercase; }
.fee-block strong { display: block; margin-top: 15px; color: #f05a28; font-size: clamp(32px, 4vw, 52px); line-height: 1; }
.fee-block small { color: #6f7d86; text-transform: uppercase; }
.fee-block p { margin-top: 25px; padding: 13px; background: #fff5d7; line-height: 1.5; }
.seminar-action { display: flex; align-items: center; justify-content: space-between; gap: 30px; margin-bottom: 120px; padding: 28px 32px; background: #f6c64a; color: #071126; box-shadow: 10px 10px 0 #f05a28; }
.seminar-action span { color: #1946a3; }
.seminar-action h2 { margin: 6px 0 0; text-transform: uppercase; }
.seminar-action a { padding: 13px 18px; color: #fff; background: #071126; font-size: 12px; font-weight: 1000; text-decoration: none; text-transform: uppercase; }
@media (max-width: 850px) {
  .seminar-hero { grid-template-columns: 1fr; padding-top: 55px; }
  .conference-code { width: 105px; }
  .seminar-facts, .registration-board, .session-intro { grid-template-columns: 1fr; }
  .seminar-facts { position: relative; bottom: auto; margin-top: 45px; }
  .seminar-facts div, .registration-board > div { border-right: 0; border-bottom: 1px solid #dfe7ea; }
  .section-head, .seminar-action { align-items: stretch; flex-direction: column; }
  .speaker-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (min-width: 851px) and (max-width: 1150px) { .speaker-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

</style>
