<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'
import Icon from '../components/Icon.vue'
import { toast } from '../toast'
import { currentUser } from '../format'

const route = useRoute()
const router = useRouter()

const user = currentUser()
const name = user.name || 'Admin'
const email = user.email || ''
const initials = name.split(' ').map((w) => w[0]).slice(0, 2).join('').toUpperCase()

const nav = [
  { to: '/', label: 'Dashboard', icon: 'grid', names: ['dashboard'] },
  { to: '/items', label: 'Daftar Barang', icon: 'box', names: ['items', 'item-detail', 'item-edit'] },
  { to: '/items/new', label: 'Tambah Barang', icon: 'plus', names: ['item-new'] },
]
const isActive = (n) => n.names.includes(route.name)

async function logout() {
  try {
    await api.post('/logout')
  } finally {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
  }
}
</script>

<template>
  <div class="app">
    <aside class="sidebar">
      <RouterLink to="/" class="brand">
        <span class="brand-mark"><Icon name="box" :size="20" /></span>
        <span class="brand-name">Stokita</span>
      </RouterLink>

      <nav>
        <div class="nav-label">Menu utama</div>
        <RouterLink v-for="n in nav" :key="n.to" :to="n.to" class="nav-item" :class="{ active: isActive(n) }">
          <Icon :name="n.icon" :size="20" />
          <span>{{ n.label }}</span>
        </RouterLink>
      </nav>

      <button class="logout" @click="logout">
        <Icon name="logout" :size="18" />
        <span>Keluar</span>
      </button>
    </aside>

    <div class="main">
      <header class="topbar">
        <h2>{{ route.meta.title }}</h2>
        <div class="user">
          <span class="avatar">{{ initials }}</span>
          <div class="user-text">
            <strong>{{ name }}</strong>
            <small>{{ email }}</small>
          </div>
        </div>
      </header>
      <div class="content">
        <RouterView :key="route.fullPath" />
      </div>
    </div>

    <div v-if="toast" class="toast" role="status">{{ toast }}</div>
  </div>
</template>

<style scoped>
.app { display: grid; grid-template-columns: 280px 1fr; min-height: 100vh; }

.sidebar {
  position: sticky; top: 0; height: 100vh;
  background: var(--navy); color: #fff;
  display: flex; flex-direction: column; padding: 0 18px 20px;
}
.brand { display: flex; align-items: center; gap: 12px; padding: 22px 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); margin: 0 -18px 24px; padding-left: 30px; }
.brand-mark { width: 44px; height: 44px; border-radius: 12px; background: var(--primary); display: grid; place-items: center; }
.brand-name { font-size: 24px; font-weight: 800; letter-spacing: -0.01em; }

nav { display: flex; flex-direction: column; gap: 6px; flex: 1; }
.nav-label { font-size: 11.5px; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255, 255, 255, 0.45); padding: 0 14px 10px; }
.nav-item { display: flex; align-items: center; gap: 14px; padding: 15px 16px; border-radius: 12px; color: rgba(255, 255, 255, 0.78); font-weight: 600; font-size: 16px; transition: background 0.15s; }
.nav-item:hover { background: rgba(255, 255, 255, 0.07); }
.nav-item.active { background: var(--primary); color: #fff; }

.logout { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 12px; border: 0; background: transparent; color: rgba(255, 255, 255, 0.78); font: inherit; font-weight: 600; cursor: pointer; border-top: 1px solid rgba(255, 255, 255, 0.08); border-radius: 0; margin: 0 -18px; padding-left: 30px; }
.logout:hover { color: #fff; }

.main { min-width: 0; display: flex; flex-direction: column; }
.topbar { background: #fff; border-bottom: 1px solid var(--line); height: 72px; padding: 0 48px; display: flex; align-items: center; justify-content: space-between; }
.topbar h2 { font-size: 19px; font-weight: 700; }
.user { display: flex; align-items: center; gap: 12px; }
.avatar { width: 42px; height: 42px; border-radius: 50%; background: var(--primary-soft); color: var(--primary); font-weight: 700; display: grid; place-items: center; font-size: 14px; }
.user-text { display: flex; flex-direction: column; line-height: 1.25; }
.user-text strong { font-size: 14.5px; }
.user-text small { color: var(--muted); font-size: 12.5px; }

.content { padding: 40px 48px 64px; max-width: 1280px; width: 100%; }

.toast { position: fixed; right: 28px; bottom: 28px; background: var(--ink); color: #fff; padding: 13px 20px; border-radius: 12px; font-weight: 600; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.25); }

@media (max-width: 960px) {
  .app { grid-template-columns: 1fr; }
  .sidebar { position: static; height: auto; flex-direction: row; align-items: center; padding: 0 12px; overflow-x: auto; }
  .brand { margin: 0; padding: 12px; border: 0; }
  .brand-name { display: none; }
  nav { flex-direction: row; align-items: center; }
  .nav-label { display: none; }
  .nav-item { padding: 10px 12px; font-size: 14px; white-space: nowrap; }
  .nav-item span { display: none; }
  .logout { border: 0; margin: 0; padding: 10px 12px; }
  .logout span { display: none; }
  .topbar { padding: 0 20px; }
  .user-text { display: none; }
  .content { padding: 24px 16px 48px; }
}
</style>