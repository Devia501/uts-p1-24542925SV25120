<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../api'
import Icon from '../components/Icon.vue'
import { formatRupiah, stockStatus, LOW_STOCK, currentUser } from '../format'

const items = ref([])
const loading = ref(true)

const hour = new Date().getHours()
const greet = hour < 11 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 18 ? 'Selamat sore' : 'Selamat malam'
const firstName = (currentUser().name || 'Admin').split(' ')[0]

const totalUnits = computed(() => items.value.reduce((s, i) => s + Number(i.quantity), 0))
const totalValue = computed(() => items.value.reduce((s, i) => s + Number(i.quantity) * Number(i.price), 0))
const needRestock = computed(() => items.value.filter((i) => Number(i.quantity) <= LOW_STOCK).sort((a, b) => a.quantity - b.quantity))
const topStock = computed(() => [...items.value].sort((a, b) => b.quantity - a.quantity).slice(0, 4))
const share = (q) => (totalUnits.value ? Math.round((Number(q) / totalUnits.value) * 100) : 0)
const dots = ['#2563eb', '#16a37a', '#f59e0b', '#8b5cf6']

onMounted(async () => {
  try {
    const { data } = await api.get('/items')
    items.value = data.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section>
    <div class="page-head">
      <div>
        <div class="eyebrow">Ringkasan inventaris</div>
        <h1>{{ greet }}, {{ firstName }}</h1>
        <p class="sub">Berikut kondisi inventaris Anda hari ini.</p>
      </div>
      <RouterLink to="/items/new" class="btn btn-primary"><Icon name="plus" :size="18" />Tambah barang</RouterLink>
    </div>

    <div class="stats">
      <div class="card stat">
        <span class="ico blue"><Icon name="box" /></span>
        <div><small>Total barang</small><strong>{{ items.length }}</strong><em>Jenis barang terdaftar</em></div>
      </div>
      <div class="card stat">
        <span class="ico green"><Icon name="layers" /></span>
        <div><small>Jumlah stok</small><strong>{{ totalUnits }}</strong><em>Unit tersedia saat ini</em></div>
      </div>
      <div class="card stat">
        <span class="ico orange"><Icon name="alert" /></span>
        <div><small>Perlu ditambah</small><strong>{{ needRestock.length }}</strong><em>Stok menipis atau habis</em></div>
      </div>
      <div class="card stat">
        <span class="ico purple"><Icon name="wallet" /></span>
        <div><small>Nilai stok</small><strong class="money">{{ formatRupiah(totalValue) }}</strong><em>Jumlah x harga satuan</em></div>
      </div>
    </div>

    <div class="panels">
      <div class="card panel">
        <header>
          <div>
            <h2>Barang perlu ditambah</h2>
            <p class="sub">Stok menipis atau sudah habis.</p>
          </div>
          <RouterLink to="/items?status=perlu" class="link">Lihat semua<Icon name="right" :size="16" /></RouterLink>
        </header>
        <ul>
          <li v-for="i in needRestock.slice(0, 5)" :key="i.id">
            <span class="thumb"><Icon name="box" :size="18" /></span>
            <div class="grow">
              <strong>{{ i.name }}</strong>
              <small>{{ i.sku }}</small>
            </div>
            <div class="right">
              <strong>{{ i.quantity }} unit</strong>
              <span class="badge" :class="stockStatus(i.quantity).cls">{{ stockStatus(i.quantity).label }}</span>
            </div>
          </li>
          <li v-if="loading" class="empty">Memuat data...</li>
          <li v-else-if="!needRestock.length" class="empty">Semua stok aman. Tidak ada barang yang perlu ditambah.</li>
        </ul>
      </div>

      <div class="card panel">
        <header>
          <div>
            <h2>Stok terbanyak</h2>
            <p class="sub">Porsi dari total unit.</p>
          </div>
        </header>
        <ul>
          <li v-for="(i, n) in topStock" :key="i.id">
            <span class="dot" :style="{ background: dots[n] }"></span>
            <span class="grow">{{ i.name }}</span>
            <strong>{{ i.quantity }}</strong>
            <small class="pct">{{ share(i.quantity) }}%</small>
          </li>
          <li v-if="!loading && !items.length" class="empty">Belum ada barang.</li>
        </ul>
      </div>
    </div>
  </section>
</template>

<style scoped>
.stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
.stat { display: flex; align-items: center; gap: 16px; padding: 24px; }
.stat small { display: block; color: var(--muted); font-size: 14px; }
.stat strong { display: block; font-size: 32px; font-weight: 800; line-height: 1.2; letter-spacing: -0.02em; }
.stat strong.money { font-size: 22px; line-height: 1.55; }
.stat em { display: block; font-style: normal; color: var(--muted); font-size: 13.5px; }
.ico { width: 54px; height: 54px; border-radius: 14px; display: grid; place-items: center; flex-shrink: 0; }
.ico.blue { background: var(--primary-soft); color: var(--primary); }
.ico.green { background: var(--green-soft); color: var(--green); }
.ico.orange { background: var(--orange-soft); color: #e08a1e; }
.ico.purple { background: var(--purple-soft); color: var(--purple); }

.panels { display: grid; grid-template-columns: 1.7fr 1fr; gap: 24px; align-items: start; }
.panel header { display: flex; justify-content: space-between; align-items: flex-start; padding: 24px 28px 18px; border-bottom: 1px solid var(--line); }
.link { color: var(--primary); font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
.panel ul { list-style: none; margin: 0; padding: 0; }
.panel li { display: flex; align-items: center; gap: 14px; padding: 16px 28px; border-bottom: 1px solid var(--line); }
.panel li:last-child { border-bottom: 0; }
.grow { flex: 1; min-width: 0; }
.grow strong, .grow small { display: block; }
.grow small, .right small { color: var(--muted); font-size: 13px; }
.thumb { width: 44px; height: 44px; border-radius: 10px; background: #f0f3f9; color: var(--muted); display: grid; place-items: center; flex-shrink: 0; }
.right { display: flex; align-items: center; gap: 12px; }
.dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.pct { color: var(--muted); width: 36px; text-align: right; }
.empty { justify-content: center; color: var(--muted); padding: 40px 28px !important; text-align: center; }

@media (max-width: 1100px) { .stats { grid-template-columns: repeat(2, 1fr); } .panels { grid-template-columns: 1fr; } }
@media (max-width: 560px) { .stats { grid-template-columns: 1fr; } }
</style>