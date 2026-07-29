<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  options: { type: Array, default: () => [] },
  placeholder: { type: String, default: 'Pilih data' },
  disabled: Boolean,
});
const emit = defineEmits(['update:modelValue']);
const root = ref(null);
const open = ref(false);
const query = ref('');
const selected = computed(() => props.options.find((item) => String(item.value) === String(props.modelValue)));
const filtered = computed(() => props.options.filter((item) => `${item.label} ${item.meta || ''}`.toLowerCase().includes(query.value.toLowerCase())));
const resetQuery = () => { query.value = selected.value?.label || ''; };
const choose = (item) => { emit('update:modelValue', item.value); query.value = item.label; open.value = false; };
const closeOutside = (event) => { if (!root.value?.contains(event.target)) { open.value = false; resetQuery(); } };

watch(() => props.modelValue, resetQuery, { immediate: true });
onMounted(() => document.addEventListener('pointerdown', closeOutside));
onBeforeUnmount(() => document.removeEventListener('pointerdown', closeOutside));
</script>

<template>
  <div ref="root" :class="['search-select', { open, disabled }]">
    <input v-model="query" type="search" autocomplete="off" :disabled="disabled" :placeholder="placeholder" role="combobox" :aria-expanded="open" @focus="open = true" @input="open = true" @keydown.esc="open = false; resetQuery()" />
    <button type="button" tabindex="-1" :disabled="disabled" aria-label="Buka pilihan" @click="open = !open"><span aria-hidden="true"></span></button>
    <div v-if="open" class="search-select-menu" role="listbox">
      <button v-for="item in filtered" :key="item.value" type="button" :class="{ selected: String(item.value) === String(modelValue) }" @click="choose(item)">
        <strong>{{ item.label }}</strong><small v-if="item.meta">{{ item.meta }}</small>
      </button>
      <p v-if="!filtered.length">Data tidak ditemukan.</p>
    </div>
  </div>
</template>

<style scoped>
.search-select{position:relative}.search-select>input{width:100%;min-height:42px;padding:9px 40px 9px 11px;color:#172535;background:#fff;border:1px solid #cbd8df;border-radius:8px;font:inherit}.search-select>input:focus{border-color:#1946a3;outline:0;box-shadow:0 0 0 3px rgba(25,70,163,.1)}.search-select>button{position:absolute;top:1px;right:1px;display:grid;width:38px;height:40px;place-items:center;padding:0;background:transparent;border:0;cursor:pointer}.search-select>button span{width:7px;height:7px;border-right:2px solid #526875;border-bottom:2px solid #526875;transform:translateY(-2px) rotate(45deg);transition:transform .2s}.search-select.open>button span{transform:translateY(2px) rotate(225deg)}.search-select.disabled{opacity:.65}.search-select-menu{position:absolute;z-index:30;top:calc(100% + 6px);left:0;width:100%;max-height:250px;overflow:auto;padding:6px;background:#fff;border:1px solid #cbd8df;border-radius:8px;box-shadow:0 14px 30px rgba(17,40,58,.16)}.search-select-menu button{display:grid;width:100%;gap:3px;padding:10px 11px;color:#263d4d;background:transparent;border:0;border-radius:6px;text-align:left;cursor:pointer}.search-select-menu button:hover,.search-select-menu button.selected{background:#edf4fc}.search-select-menu strong{font-size:12px}.search-select-menu small{color:#71808b;font-size:9px}.search-select-menu p{margin:0;padding:12px;color:#71808b;font-size:11px;text-align:center}
</style>
