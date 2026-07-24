<script setup>
import { onUnmounted, watch } from 'vue';

const props = defineProps({ open: Boolean, title: String, description: String });
const emit = defineEmits(['close']);
const onKey = (event) => { if (event.key === 'Escape') emit('close'); };

watch(() => props.open, (open) => {
  document.body.style.overflow = open ? 'hidden' : '';
  open ? window.addEventListener('keydown', onKey) : window.removeEventListener('keydown', onKey);
});
onUnmounted(() => { document.body.style.overflow = ''; window.removeEventListener('keydown', onKey); });
</script>

<template>
  <Teleport to="body">
    <Transition name="sheet">
      <div v-if="open" class="sheet-layer" @click.self="emit('close')">
        <section class="sheet-content" role="dialog" aria-modal="true" aria-labelledby="sheet-title" aria-describedby="sheet-description">
          <header class="sheet-header"><div><h2 id="sheet-title">{{ title }}</h2><p id="sheet-description">{{ description }}</p></div><button type="button" aria-label="Tutup panel" @click="emit('close')">×</button></header>
          <div class="sheet-body"><slot /></div>
          <footer v-if="$slots.footer" class="sheet-footer"><slot name="footer" /></footer>
        </section>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.sheet-layer{position:fixed;inset:0;z-index:120;display:flex;justify-content:flex-end;background:rgba(9,24,42,.48);backdrop-filter:blur(3px)}.sheet-content{display:grid;grid-template-rows:auto 1fr auto;width:min(760px,94vw);height:100%;color:#203747;background:#f7f9fb;border-left:1px solid #d4e0e7;box-shadow:-24px 0 70px rgba(9,24,42,.22)}.sheet-header{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding:22px 24px;background:#fff;border-bottom:1px solid #dce6eb}.sheet-header h2{margin:0;color:#102f59;font-size:22px;letter-spacing:-.02em}.sheet-header p{margin:5px 0 0;color:#6b7d89;font-size:12px;line-height:1.5}.sheet-header button{display:grid;flex:0 0 36px;place-items:center;width:36px;height:36px;color:#536571;background:#fff;border:1px solid #cbd8df;border-radius:9px;font-size:22px;cursor:pointer}.sheet-header button:hover{color:#1946a3;background:#eef4fb}.sheet-body{min-height:0;padding:20px 24px;overflow:auto}.sheet-footer{display:flex;align-items:center;justify-content:flex-end;gap:8px;padding:15px 24px;background:#fff;border-top:1px solid #dce6eb}.sheet-enter-active,.sheet-leave-active{transition:opacity .22s ease}.sheet-enter-active .sheet-content,.sheet-leave-active .sheet-content{transition:transform .28s cubic-bezier(.22,1,.36,1)}.sheet-enter-from,.sheet-leave-to{opacity:0}.sheet-enter-from .sheet-content,.sheet-leave-to .sheet-content{transform:translateX(100%)}@media(max-width:640px){.sheet-content{width:100%}.sheet-header,.sheet-body,.sheet-footer{padding-right:16px;padding-left:16px}}
</style>
