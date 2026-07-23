<script setup>
import AppHeader from '../Components/AppHeader.vue';

const props = defineProps({ status: Number });
const stopIcon = '/assets/brand/mascots/STOP.png';
const errors = {
  403: { eyebrow: 'Akses Dibatasi', title: 'Anda tidak memiliki akses.', message: 'Halaman ini hanya tersedia untuk pengguna dengan kewenangan yang sesuai.' },
  404: { eyebrow: 'Halaman Tidak Ditemukan', title: 'Rute pertandingan terputus.', message: 'Alamat yang dibuka tidak tersedia atau sudah dipindahkan.' },
  419: { eyebrow: 'Sesi Berakhir', title: 'Waktu sesi sudah habis.', message: 'Muat ulang halaman, lalu masuk kembali bila diperlukan.' },
  500: { eyebrow: 'Gangguan Sistem', title: 'Pertandingan dihentikan sementara.', message: 'Terjadi kendala pada server. Tim teknis sudah menerima sinyal gangguan.' },
  503: { eyebrow: 'Pemeliharaan Sistem', title: 'Arena sedang disiapkan.', message: 'Layanan belum tersedia sementara. Silakan kembali beberapa saat lagi.' },
};
const detail = errors[props.status] || errors[500];
</script>

<template>
  <main class="error-shell">
    <AppHeader />
    <section class="error-stage">
      <div class="error-copy">
        <span>{{ detail.eyebrow }}</span>
        <strong>{{ status }}</strong>
        <h1>{{ detail.title }}</h1>
        <p>{{ detail.message }}</p>
        <div class="actions">
          <a href="/">Kembali ke Beranda</a>
          <button type="button" @click="history.back()">Kembali</button>
        </div>
      </div>
      <div class="mascot-wrap"><img :src="stopIcon" alt="Maskot PORPAMNAS membawa tanda berhenti" /></div>
    </section>
    <footer><small>PORPAMNAS IX · Balikpapan 2026</small><small>Kode status {{ status }}</small></footer>
  </main>
</template>

<style scoped>
.error-shell{min-height:100vh;padding:24px max(24px,calc((100vw - 1320px)/2)) 42px;color:#fff;background:linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px) 0 0/72px 72px,linear-gradient(0deg,rgba(255,255,255,.035) 1px,transparent 1px) 0 0/72px 72px,radial-gradient(circle at 78% 36%,rgba(240,90,40,.24),transparent 28rem),linear-gradient(135deg,#050b1c 0%,#0b1f4d 58%,#10275c 100%)}.error-stage{min-height:calc(100vh - 190px);display:grid;grid-template-columns:minmax(0,1fr) minmax(340px,.8fr);align-items:center;gap:48px;padding:50px 3vw}.error-copy{position:relative;z-index:1}.error-copy>span{color:#f6c64a;font-size:11px;font-weight:900;letter-spacing:.17em;text-transform:uppercase}.error-copy>strong{display:block;margin:12px 0 -12px;color:transparent;-webkit-text-stroke:2px rgba(54,194,240,.6);font-size:clamp(90px,16vw,220px);font-weight:1000;line-height:.8;letter-spacing:-.08em}.error-copy h1{max-width:700px;margin:30px 0 14px;font-size:clamp(38px,6vw,78px);line-height:.93;letter-spacing:-.055em}.error-copy p{max-width:610px;margin:0;color:rgba(255,255,255,.68);font-size:16px;line-height:1.65}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:28px}.actions a,.actions button{min-height:43px;padding:11px 17px;font:inherit;font-size:11px;font-weight:900;letter-spacing:.07em;text-decoration:none;text-transform:uppercase;cursor:pointer}.actions a{color:#071126;background:#f6c64a;border:1px solid #f6c64a}.actions button{color:#fff;background:transparent;border:1px solid rgba(255,255,255,.28)}.actions a:hover,.actions a:focus-visible{background:#36c2f0;border-color:#36c2f0}.actions button:hover,.actions button:focus-visible{border-color:#36c2f0}.mascot-wrap{position:relative;display:grid;place-items:center;align-self:end}.mascot-wrap::before{content:"";position:absolute;width:78%;aspect-ratio:1;background:radial-gradient(circle,rgba(240,90,40,.22),transparent 68%)}.mascot-wrap img{position:relative;width:min(100%,540px);max-height:68vh;object-fit:contain;filter:drop-shadow(0 30px 40px rgba(0,0,0,.34))}.error-shell>footer{display:flex;justify-content:space-between;gap:16px;padding-top:20px;color:rgba(255,255,255,.48);border-top:1px solid rgba(255,255,255,.12);font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}@media(max-width:850px){.error-stage{grid-template-columns:1fr;padding-top:56px;text-align:center}.error-copy p{margin-inline:auto}.actions{justify-content:center}.mascot-wrap{align-self:center}.mascot-wrap img{max-height:380px}.error-copy>strong{margin-bottom:-4px}}@media(max-width:560px){.error-shell{padding-inline:14px}.error-stage{gap:24px;padding-inline:0}.actions{display:grid}.actions a,.actions button{width:100%}.error-shell>footer{flex-direction:column;text-align:center}.mascot-wrap img{max-height:310px}}
</style>
