<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'
import Icon from '../components/Icon.vue'

const router = useRouter()
const email = ref('admin@example.com')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function login() {
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/login', { email: email.value, password: password.value })
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Server tidak dapat dihubungi. Pastikan backend sedang berjalan.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="login">
    <section class="hero">
      <div class="brand">
        <span class="brand-mark"><Icon name="box" :size="22" /></span>
        <span class="brand-name">Stokita</span>
      </div>

      <div class="pitch">
        <div class="eyebrow light">Manajemen inventaris</div>
        <h1>Kelola stok lebih rapi, keputusan jadi lebih pasti.</h1>
        <p>Pantau barang, perbarui stok, dan temukan informasi inventaris dalam satu ruang kerja yang sederhana.</p>
        <ul>
          <li><span class="tick"><Icon name="check" :size="14" /></span>Informasi stok selalu terorganisir</li>
          <li><span class="tick"><Icon name="check" :size="14" /></span>Akses cepat untuk seluruh data barang</li>
        </ul>
      </div>
      <div class="rings" aria-hidden="true"></div>
    </section>

    <section class="side">
      <form class="panel" @submit.prevent="login">
        <div class="eyebrow tag">Selamat datang</div>
        <h2>Masuk ke akun Anda</h2>
        <p class="sub">Gunakan akun pengelola inventaris Anda.</p>

        <div class="field first">
          <label for="email">Email</label>
          <input id="email" v-model="email" class="input" type="email" autocomplete="username" required />
        </div>
        <div class="field">
          <label for="password">Kata sandi</label>
          <input id="password" v-model="password" class="input" type="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required />
        </div>

        <div v-if="error" class="alert" role="alert">{{ error }}</div>

        <button class="btn btn-primary full" :disabled="loading">
          {{ loading ? 'Memproses...' : 'Masuk ke Stokita' }}
        </button>
      </form>
      <footer>© 2026 Stokita · Sistem Inventaris</footer>
    </section>
  </main>
</template>

<style scoped>
.login { display: grid; grid-template-columns: 1.2fr 1fr; min-height: 100vh; }

.hero { position: relative; overflow: hidden; background: var(--navy); color: #fff; padding: 40px 104px 40px 104px; display: flex; flex-direction: column; justify-content: center; gap: 56px; }
.brand { position: absolute; top: 40px; left: 104px; display: flex; align-items: center; gap: 12px; }
.brand-mark { width: 50px; height: 50px; border-radius: 13px; background: var(--primary); display: grid; place-items: center; }
.brand-name { font-size: 26px; font-weight: 800; }

.pitch { position: relative; z-index: 1; max-width: 640px; padding-top: 40px; }
.eyebrow.light { color: #6ea0ff; }
.pitch h1 { font-size: clamp(34px, 4.2vw, 58px); line-height: 1.08; font-weight: 800; letter-spacing: -0.035em; }
.pitch p { color: rgba(255, 255, 255, 0.72); font-size: 18px; line-height: 1.7; margin-top: 26px; max-width: 580px; }
.pitch ul { list-style: none; padding: 0; margin: 40px 0 0; display: flex; flex-direction: column; gap: 16px; }
.pitch li { display: flex; align-items: center; gap: 14px; font-size: 17px; }
.tick { width: 32px; height: 32px; border-radius: 50%; background: rgba(255, 255, 255, 0.1); color: #8db4ff; display: grid; place-items: center; }

.rings { position: absolute; right: -140px; bottom: -160px; width: 560px; height: 560px; border-radius: 50%; background: rgba(255, 255, 255, 0.035); }
.rings::after { content: ''; position: absolute; inset: 100px; border-radius: 50%; border: 1px solid rgba(255, 255, 255, 0.08); }

.side { position: relative; display: grid; place-items: center; padding: 40px 24px 72px; }
.panel { width: 100%; max-width: 548px; background: #fff; border: 1px solid var(--line); border-radius: 22px; padding: 52px 52px 44px; box-shadow: 0 24px 60px rgba(15, 34, 71, 0.08); }
.eyebrow.tag { display: inline-block; padding-top: 4px; border-top: 2px solid var(--primary); margin-bottom: 20px; font-size: 11.5px; }
.panel h2 { font-size: 32px; font-weight: 800; letter-spacing: -0.02em; }
.panel .sub { font-size: 17px; margin: 10px 0 34px; }
.field.first { margin-top: 0; }
.full { width: 100%; padding: 17px; font-size: 17px; margin-top: 12px; border-radius: 12px; }
.alert { background: var(--red-soft); color: var(--red); border-radius: 10px; padding: 11px 14px; font-size: 14px; margin-bottom: 14px; }
footer { position: absolute; bottom: 22px; left: 0; right: 0; text-align: center; color: var(--muted); font-size: 14px; }

@media (max-width: 900px) {
  .login { grid-template-columns: 1fr; }
  .hero { padding: 96px 28px 48px; gap: 0; }
  .brand { left: 28px; top: 28px; }
  .pitch { padding-top: 0; }
  .pitch p, .pitch ul { display: none; }
  .panel { padding: 32px 24px; }
  .side { padding-bottom: 64px; }
}
</style>