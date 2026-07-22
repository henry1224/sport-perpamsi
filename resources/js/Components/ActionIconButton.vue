<script setup>
defineProps({ icon: { type: String, required: true }, label: { type: String, required: true }, tone: { type: String, default: 'default' }, disabled: Boolean });
</script>

<template>
  <button type="button" :class="['action-icon', tone]" :title="label" :aria-label="label" :disabled="disabled"><svg viewBox="0 0 24 24" aria-hidden="true"><path v-if="icon === 'show'" d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Zm10 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path v-else-if="icon === 'edit'" d="m4 16.5-.5 4 4-.5L19 8.5 15.5 5 4 16.5ZM14 6.5 17.5 10"/><path v-else-if="icon === 'delete'" d="M4 7h16M9 7V4h6v3m3 0-1 14H7L6 7m4 4v6m4-6v6"/><path v-else d="M12 2v10m6.4-6.4a9 9 0 1 1-12.8 0"/></svg></button>
</template>

<style scoped>
.action-icon{display:inline-grid;place-items:center;width:38px;height:38px;padding:0;color:#1946a3;background:#fff;border:1px solid #bfd0dc;border-radius:8px;cursor:pointer}.action-icon svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.9;stroke-linecap:round;stroke-linejoin:round}.action-icon.active{color:#087365;background:#eefaf6;border-color:#b9e3d6}.action-icon.inactive,.action-icon.danger{color:#a1432e;background:#fff4ef;border-color:#efcfc4}.action-icon:disabled{color:#9caab4;background:#edf2f4;border-color:#d7e0e5;cursor:not-allowed}
</style>
