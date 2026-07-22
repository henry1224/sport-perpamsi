<script setup>
import { watch, onUnmounted } from 'vue';
const props = defineProps({ open: Boolean, title: String, theme: { type: String, default: 'dark' } });
const emit = defineEmits(['close']);

const onKey = (e) => { if (e.key === 'Escape') emit('close'); };
watch(() => props.open, (v) => {
  if (v) { document.body.style.overflow = 'hidden'; window.addEventListener('keydown', onKey); }
  else { document.body.style.overflow = ''; window.removeEventListener('keydown', onKey); }
});
onUnmounted(() => { document.body.style.overflow = ''; window.removeEventListener('keydown', onKey); });
</script>

<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="open" :class="['modal-backdrop', theme]" @click.self="emit('close')">
        <div class="modal-shell" role="dialog" aria-modal="true" aria-labelledby="modal-title">
          <header>
            <h3 id="modal-title">{{ title }}</h3>
            <button type="button" class="close" @click="emit('close')" aria-label="Tutup">×</button>
          </header>
          <div class="body"><slot /></div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-backdrop { position: fixed; inset: 0; z-index: 100; display: grid; place-items: center; padding: 24px; background: rgba(5,11,28,.78); backdrop-filter: blur(6px); }
.modal-shell { position: relative; width: min(760px, 100%); max-height: 88vh; display: grid; grid-template-rows: auto 1fr; overflow: hidden; color:#fff; background:#071126; border:1px solid rgba(255,255,255,.14); box-shadow:14px 14px 0 rgba(54,194,240,.16),0 40px 120px rgba(0,0,0,.5); clip-path:polygon(22px 0,100% 0,100% calc(100% - 22px),calc(100% - 22px) 100%,0 100%,0 22px); }
.modal-shell::before { content:"DETAIL"; position:absolute; right:18px; top:-14px; color:transparent; -webkit-text-stroke:1px rgba(255,255,255,.055); font-size:110px; font-weight:1000; letter-spacing:-.08em; pointer-events:none; }
header { position:relative; z-index:1; display:flex; justify-content:space-between; align-items:center; gap:12px; padding:22px 24px; background:linear-gradient(90deg,rgba(54,194,240,.11),transparent 62%); border-bottom:1px dashed rgba(255,255,255,.16); }
h3 { margin:0; max-width:86%; color:inherit; font-size:28px; line-height:1.05; letter-spacing:-.04em; text-transform:uppercase; }
.close { width:38px; height:38px; color:#071126; background:#f6c64a; border:1px solid rgba(255,255,255,.18); font-size:24px; font-weight:1000; line-height:1; cursor:pointer; clip-path:polygon(8px 0,100% 0,calc(100% - 8px) 100%,0 100%); }
.close:hover { background:#36c2f0; }
.body { position:relative; z-index:1; padding:22px 24px; overflow-y:auto; }
.modal-backdrop.light { background:rgba(7,17,38,.68); backdrop-filter:blur(5px); }
.light .modal-shell { color:#172535; background:#fff; border:1px solid #d4e0e7; border-radius:16px; box-shadow:0 28px 90px rgba(7,17,38,.28); clip-path:none; }
.light .modal-shell::before { content:"PORPAMNAS IX"; top:-8px; -webkit-text-stroke-color:rgba(25,70,163,.045); font-size:72px; }
.light header { padding:20px 22px; background:#f7f9fb; border-bottom:1px solid #dfe8ed; }
.light h3 { color:#142536; font-size:22px; line-height:1.1; letter-spacing:-.02em; text-transform:none; }
.light .close { color:#536571; background:#fff; border:1px solid #cbd8df; border-radius:8px; font-weight:900; clip-path:none; }
.light .close:hover { color:#1946a3; background:#eef4fb; }
.light .body { padding:0; background:#fff; }
.light, .light * { scrollbar-color:#1946a3 #eef3f6; }
.light::-webkit-scrollbar-track, .light *::-webkit-scrollbar-track { background:#eef3f6; }
.light::-webkit-scrollbar-thumb, .light *::-webkit-scrollbar-thumb { background:#1946a3; border-color:#eef3f6; }
.light::-webkit-scrollbar-thumb:hover, .light *::-webkit-scrollbar-thumb:hover { background:#2a68b7; }
.fade-enter-active, .fade-leave-active { transition: opacity .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
