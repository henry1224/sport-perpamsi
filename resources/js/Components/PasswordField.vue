<script setup>
import { computed, ref } from 'vue';

const model = defineModel({ type: String, default: '' });
const props = defineProps({
  label: { type: String, default: 'Kata Sandi' },
  autocomplete: { type: String, default: 'new-password' },
  required: Boolean,
  showStrength: Boolean,
  confirmation: Boolean,
  compareWith: { type: String, default: '' },
  error: { type: String, default: '' },
  theme: { type: String, default: 'light' },
  square: Boolean,
});

const visible = ref(false);
const checks = computed(() => [
  model.value.length >= 8,
  /[a-z]/.test(model.value),
  /[A-Z]/.test(model.value),
  /\d/.test(model.value),
  /[^A-Za-z0-9]/.test(model.value),
]);
const score = computed(() => checks.value.filter(Boolean).length);
const strength = computed(() => ['Sangat Lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat', 'Sangat Kuat'][score.value]);
const confirmationState = computed(() => !props.confirmation || !model.value ? '' : model.value === props.compareWith ? 'match' : 'mismatch');
</script>

<template>
  <label :class="['password-field', theme, { invalid: error || confirmationState === 'mismatch', square }]">
    <span class="field-label">{{ label }}</span>
    <span class="input-shell">
      <input v-model="model" :type="visible ? 'text' : 'password'" :autocomplete="autocomplete" :required="required" minlength="8" />
      <button type="button" :aria-label="visible ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'" :aria-pressed="visible" @click="visible = !visible">
        <svg v-if="visible" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 10.7a2 2 0 002.7 2.7M9.9 4.2A10.8 10.8 0 0112 4c5.5 0 9 5.5 9 5.5a15.8 15.8 0 01-3.2 3.8M6.6 6.6C4.3 8.1 3 9.5 3 9.5S6.5 15 12 15c1 0 1.9-.2 2.7-.5" /></svg>
        <svg v-else viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12s3.5-5.5 9-5.5 9 5.5 9 5.5-3.5 5.5-9 5.5S3 12 3 12z" /><circle cx="12" cy="12" r="2.5" /></svg>
      </button>
    </span>
    <template v-if="showStrength && model">
      <span class="strength-head"><b>Kekuatan Kata Sandi</b><em>{{ strength }}</em></span>
      <span class="strength-track" aria-hidden="true"><i :style="{ width: `${score * 20}%` }" :data-score="score" /></span>
      <small class="requirements">Minimal 8 karakter, huruf besar, huruf kecil, angka, dan karakter khusus.</small>
    </template>
    <small v-if="confirmationState" :class="['confirmation', confirmationState]">{{ confirmationState === 'match' ? 'Kata sandi cocok.' : 'Ulangi Kata Sandi belum cocok.' }}</small>
    <small v-if="error" class="field-error">{{ error }}</small>
  </label>
</template>

<style scoped>
.password-field{display:grid;gap:8px}.field-label{font-size:10px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}.input-shell{position:relative;display:block}.input-shell input{width:100%;min-height:44px;padding:12px 48px 12px 14px;font:inherit;border:1px solid #cfdbe2;outline:none;transition:border-color .16s,box-shadow .16s}.input-shell input:focus{border-color:#1946a3;box-shadow:0 0 0 3px rgba(25,70,163,.1)}.input-shell button{position:absolute;top:50%;right:7px;display:grid;place-items:center;width:34px;height:34px;padding:0;transform:translateY(-50%);color:#607482;background:transparent;border:0;box-shadow:none;cursor:pointer}.input-shell svg{width:19px;height:19px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}.strength-head{display:flex;justify-content:space-between;gap:12px;font-size:10px}.strength-head b{color:#607482}.strength-head em{color:#1946a3;font-style:normal;font-weight:900}.strength-track{height:6px;overflow:hidden;background:#e5ebef}.strength-track i{display:block;height:100%;background:#c0392b;transition:width .22s ease,background .22s ease}.strength-track i[data-score="3"]{background:#d99a18}.strength-track i[data-score="4"],.strength-track i[data-score="5"]{background:#12806f}.requirements{color:#71818b;font-size:10px;line-height:1.45}.confirmation,.field-error{font-size:10px;font-weight:800}.confirmation.match{color:#12806f}.confirmation.mismatch,.field-error{color:#b43c2c}.invalid .input-shell input{border-color:#d88476}.dark .field-label{color:#36c2f0}.dark .input-shell input{color:#fff;background:#08142d;border-color:rgba(255,255,255,.16)}.dark .input-shell input:focus{border-color:#36c2f0;box-shadow:0 0 0 3px rgba(54,194,240,.12)}.dark .input-shell button{color:#9fb5ca}.dark .strength-head b,.dark .requirements{color:rgba(255,255,255,.62)}.dark .strength-head em{color:#36c2f0}.dark .strength-track{background:rgba(255,255,255,.13)}
.requirements,.confirmation,.field-error{text-transform:none;letter-spacing:0}.input-shell input{border-radius:7px}.square .input-shell input{border-radius:0}
</style>
