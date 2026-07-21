<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '../../Layouts/PublicLayout.vue';
import SectionTitle from '../../Components/SectionTitle.vue';

defineProps({ committees: Array });

const form = useForm({
  regional_committee_id: '',
  name: '',
  position: '',
  phone: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => form.post('/register');
</script>

<template>
  <PublicLayout>
    <div class="page-head">
      <SectionTitle eyebrow="Pengajuan Akses" title="Daftar Pengurus Daerah" meta="Verifikasi dilakukan Admin PORPAMNAS" />
    </div>

    <section class="register-shell">
      <aside>
        <span>Alur Pengajuan</span>
        <h2>Satu akses resmi untuk setiap daerah.</h2>
        <ol>
          <li><b>01</b>Pilih PD PERPAMSI provinsi.</li>
          <li><b>02</b>Lengkapi penanggung jawab.</li>
          <li><b>03</b>Tunggu verifikasi Admin.</li>
          <li><b>04</b>Masuk dan daftarkan cabor.</li>
        </ol>
        <p>Sudah memiliki akun? <Link href="/login">Masuk di sini</Link>.</p>
      </aside>

      <form @submit.prevent="submit">
        <label class="wide">
          <span>PD PERPAMSI</span>
          <select v-model="form.regional_committee_id" required autofocus>
            <option value="" disabled>Pilih provinsi</option>
            <option v-for="committee in committees" :key="committee.id" :value="committee.id">{{ committee.name }}</option>
          </select>
          <small v-if="form.errors.regional_committee_id">{{ form.errors.regional_committee_id }}</small>
        </label>
        <label><span>Nama Penanggung Jawab</span><input v-model="form.name" required /><small v-if="form.errors.name">{{ form.errors.name }}</small></label>
        <label><span>Jabatan</span><input v-model="form.position" required /><small v-if="form.errors.position">{{ form.errors.position }}</small></label>
        <label><span>Nomor Telepon</span><input v-model="form.phone" type="tel" required /><small v-if="form.errors.phone">{{ form.errors.phone }}</small></label>
        <label><span>Email</span><input v-model="form.email" type="email" autocomplete="username" required /><small v-if="form.errors.email">{{ form.errors.email }}</small></label>
        <label><span>Kata Sandi</span><input v-model="form.password" type="password" autocomplete="new-password" required /><small v-if="form.errors.password">{{ form.errors.password }}</small></label>
        <label><span>Ulangi Kata Sandi</span><input v-model="form.password_confirmation" type="password" autocomplete="new-password" required /></label>
        <button class="wide" type="submit" :disabled="form.processing">{{ form.processing ? 'Mengirim…' : 'Kirim Pengajuan' }}</button>
      </form>
    </section>
  </PublicLayout>
</template>

<style scoped>
.page-head { padding: 44px 0 24px; }
.register-shell { display: grid; grid-template-columns: .8fr 1.4fr; border: 1px solid rgba(255,255,255,.12); background: #071126; box-shadow: 12px 12px 0 rgba(54,194,240,.13); }
aside { padding: 30px; background: linear-gradient(150deg, #1946a3, #071126 72%); }
aside > span, form span { color: #36c2f0; font-size: 10px; font-weight: 1000; letter-spacing: .16em; text-transform: uppercase; }
aside h2 { margin: 10px 0 28px; max-width: 360px; font-size: clamp(28px, 4vw, 48px); line-height: .96; text-transform: uppercase; }
ol { list-style: none; display: grid; gap: 14px; padding: 0; }
li { display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,.74); font-weight: 800; }
li b { display: grid; place-items: center; width: 34px; height: 34px; color: #071126; background: #f6c64a; box-shadow: 4px 4px 0 #f05a28; }
aside p { margin-top: 30px; color: rgba(255,255,255,.58); }
aside a { color: #f6c64a; font-weight: 900; }
form { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 30px; }
label { display: grid; gap: 8px; }
.wide { grid-column: 1 / -1; }
input, select { width: 100%; padding: 13px 14px; color: #fff; background: #08142d; border: 1px solid rgba(255,255,255,.16); font: inherit; }
label small { color: #f05a28; font-weight: 800; }
button { padding: 15px; border: 0; background: #f6c64a; color: #071126; font-weight: 1000; letter-spacing: .12em; text-transform: uppercase; box-shadow: 6px 6px 0 #f05a28; cursor: pointer; }
button:disabled { opacity: .55; }
@media (max-width: 850px) { .register-shell, form { grid-template-columns: 1fr; } .wide { grid-column: auto; } }
</style>
