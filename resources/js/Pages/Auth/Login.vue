<script setup>
import { useForm } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => form.post('/login');
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Akun Panitia" title="Masuk Admin PD PERPAMSI" meta="Pengelolaan pendaftaran peserta" />
    </div>
    <section class="login-panel">
      <form @submit.prevent="submit" class="login-form">
        <label>
          <span>Email</span>
          <input v-model="form.email" type="email" autocomplete="username" required autofocus />
          <small v-if="form.errors.email" class="err">{{ form.errors.email }}</small>
        </label>
        <label>
          <span>Kata sandi</span>
          <input v-model="form.password" type="password" autocomplete="current-password" required />
          <small v-if="form.errors.password" class="err">{{ form.errors.password }}</small>
        </label>
        <label class="remember">
          <input type="checkbox" v-model="form.remember" />
          <span>Ingat saya di perangkat ini</span>
        </label>
        <button type="submit" :disabled="form.processing">
          {{ form.processing ? 'Memproses…' : 'Masuk' }}
        </button>
      </form>
      <aside class="login-help">
        <h3>Akun demo</h3>
        <p><b>Super Admin</b><br /><code>super@perpamsi.local</code></p>
        <p><b>Admin PD (contoh)</b><br /><code>pd-kalimantan-timur@perpamsi.local</code></p>
        <p>Kata sandi seluruh akun demo: <code>password</code></p>
      </aside>
    </section>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.login-panel { display: grid; grid-template-columns: 1.4fr 1fr; gap: 24px; }
.login-form { display: grid; gap: 16px; padding: 24px; background: #071126; border: 1px solid rgba(255,255,255,.12); box-shadow: 10px 10px 0 rgba(54,194,240,.13); }
.login-form label { display: grid; gap: 8px; color: #36C2F0; font-size: 11px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
.login-form input[type=email], .login-form input[type=password] { padding: 12px 14px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.16); font: inherit; }
.login-form .remember { display: flex; flex-direction: row; align-items: center; gap: 10px; color: rgba(255,255,255,.7); text-transform: none; letter-spacing: 0; font-weight: 700; }
.login-form button { padding: 14px; background: #F6C64A; color: #071126; border: 0; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; box-shadow: 6px 6px 0 rgba(240,90,40,.45); cursor: pointer; }
.login-form button:disabled { opacity: .6; cursor: not-allowed; }
.err { color: #F05A28; text-transform: none; letter-spacing: 0; font-weight: 700; }
.login-help { padding: 24px; background: rgba(5,11,28,.56); border: 1px solid rgba(255,255,255,.12); color: rgba(255,255,255,.7); font-size: 13px; }
.login-help h3 { margin: 0 0 12px; color: #F6C64A; letter-spacing: .1em; text-transform: uppercase; font-size: 12px; }
.login-help code { display: inline-block; padding: 2px 6px; background: #08142d; color: #36C2F0; border: 1px solid rgba(255,255,255,.1); font-size: 12px; }
@media (max-width: 900px) { .login-panel { grid-template-columns: 1fr; } }
</style>
