<script setup>
import { watch, onUnmounted } from 'vue';
const props = defineProps({ open: Boolean, title: String });
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
      <div v-if="open" class="modal-backdrop" @click.self="emit('close')">
        <div class="modal-shell" role="dialog" aria-modal="true">
          <header>
            <h3>{{ title }}</h3>
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
.modal-shell { position: relative; width: min(760px, 100%); max-height: 88vh; display: grid; grid-template-rows: auto 1fr; overflow: hidden; background: #071126; border: 1px solid rgba(255,255,255,.14); box-shadow: 14px 14px 0 rgba(54,194,240,.16), 0 40px 120px rgba(0,0,0,.5); clip-path: polygon(22px 0,100% 0,100% calc(100% - 22px),calc(100% - 22px) 100%,0 100%,0 22px); }
.modal-shell::before { content: "DETAIL"; position: absolute; right: 18px; top: -14px; color: transparent; -webkit-text-stroke: 1px rgba(255,255,255,.055); font-size: 110px; font-weight: 1000; letter-spacing: -.08em; pointer-events: none; }
header { position: relative; z-index: 1; display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 22px 24px; border-bottom: 1px dashed rgba(255,255,255,.16); background: linear-gradient(90deg, rgba(54,194,240,.11), transparent 62%); }
h3 { margin: 0; max-width: 86%; font-size: 28px; line-height: 1.05; letter-spacing: -.04em; text-transform: uppercase; }
.close { width: 38px; height: 38px; border: 1px solid rgba(255,255,255,.18); background: #F6C64A; color: #071126; font-size: 24px; font-weight: 1000; line-height: 1; cursor: pointer; clip-path: polygon(8px 0,100% 0,calc(100% - 8px) 100%,0 100%); }
.close:hover { background: #36C2F0; }
.body { position: relative; z-index: 1; padding: 22px 24px; overflow-y: auto; }
.fade-enter-active, .fade-leave-active { transition: opacity .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
