<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'
import Icon from '../components/Icon.vue'
import { notify } from '../toast'
import { formatRupiah, stockStatus, LOW_STOCK } from '../format'

const route = useRoute()
const items = ref([])
const loading = ref(true)
const search = ref('')
const status = ref(route.query.status || 'all')
const page = ref(1)
const perPage = 8
const target = ref(null)

const filtered = computed(() => {
  const q = search.value.trim().toLowerCase()
  return items.value.filter((i) => {
    const text = !q || i.name.toLowerCase().includes(q) || i.sku.toLowerCase().includes(q)
    const qty = Number(i.quantity)
    const st = status.value === 'all' ? true : status.value === 'perlu' ? qty <= LOW_STOCK : stockStatus(qty).key === status.value
    return text && st
  })
})
const pages = computed(() => Math.max(1, Math.ceil(filtered.value.length / perPage)))
const paged = computed(() => filtered.value.slice((page.value - 1) * perPage, page.value * perPage))

watch([search, status], () => (page.value = 1))
watch(pages, (p) => { if (page.value > p) page.value = p })

async function load() {
  try {
    const { data } = await api.get('/items')
    items.value = data.data
  } finally {
    loading.value = false
  }
}

async function confirmDelete() {
  const item = target.value
  try {
    await api.delete(`/items/${item.id}`)
    notify('Barang dihapus.')
    await load()
  } catch {
    notify('Gagal menghapus barang. Coba lagi.')
  } finally {
    target.value = null
  }
}

onMounted(load)
</script>

<template>
  <section>
    <div class="page-head">
      <div>
        <div class="eyebrow">Inventaris</div>
        <h1>Daftar Barang</h1>
        <p class="sub">Kelola seluruh barang inventaris dalam satu tempat.</p>
      </div>
      <RouterLink to="/items/new" class="btn btn-primary"><Icon name="plus" :size="18" />Tambah barang</RouterLink>
    </div>

    <div class="card">
      <div class="toolbar">
        <div class="search">
          <Icon name="search" :size="18" />
          <input v-model="search" type="search" placeholder="Cari nama atau kode barang..." aria-label="Cari barang" />
        </div>
        <select v-model="status" class="input select" aria-label="Filter status">
          <option value="all">Semua status</option>
          <option value="tersedia">Tersedia</option>
          <option value="menipis">Menipis</option>
          <option value="habis">Habis</option>
          <option value="perlu">Perlu ditambah</option>
        </select>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama barang</th>
              <th>Stok</th>
              <th>Harga</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="i in paged" :key="i.id">
              <td><span class="code">{{ i.sku }}</span></td>
              <td class="name">{{ i.name }}</td>
              <td><strong>{{ i.quantity }}</strong> <span class="unit">unit</span></td>
              <td>{{ formatRupiah(i.price) }}</td>
              <td><span class="badge" :class="stockStatus(i.quantity).cls">{{ stockStatus(i.quantity).label }}</span></td>
              <td>
                <div class="actions">
                  <RouterLink :to="`/items/${i.id}`" title="Lihat detail" aria-label="Lihat detail"><Icon name="eye" :size="19" /></RouterLink>
                  <RouterLink :to="`/items/${i.id}/edit`" title="Edit" aria-label="Edit"><Icon name="edit" :size="19" /></RouterLink>
                  <button title="Hapus" aria-label="Hapus" @click="target = i"><Icon name="trash" :size="19" /></button>
                </div>
              </td>
            </tr>
            <tr v-if="loading"><td colspan="6" class="empty">Memuat data...</td></tr>
            <tr v-else-if="!items.length"><td colspan="6" class="empty">Belum ada barang. Klik "Tambah barang" untuk menambahkan yang pertama.</td></tr>
            <tr v-else-if="!filtered.length"><td colspan="6" class="empty">Tidak ada barang yang cocok dengan pencarian atau filter.</td></tr>
          </tbody>
        </table>
      </div>

      <div class="foot">
        <span class="count">Menampilkan {{ paged.length }} dari {{ filtered.length }} barang</span>
        <div class="pager" v-if="pages > 1">
          <button :disabled="page === 1" aria-label="Halaman sebelumnya" @click="page--"><Icon name="left" :size="16" /></button>
          <button v-for="p in pages" :key="p" :class="{ on: p === page }" @click="page = p">{{ p }}</button>
          <button :disabled="page === pages" aria-label="Halaman berikutnya" @click="page++"><Icon name="right" :size="16" /></button>
        </div>
      </div>
    </div>

    <div v-if="target" class="overlay" @click.self="target = null">
      <div class="modal" role="dialog" aria-modal="true" aria-labelledby="del-title">
        <span class="del-ico"><Icon name="trash" :size="22" /></span>
        <h3 id="del-title">Hapus barang?</h3>
        <p><strong>{{ target.name }}</strong> akan dihapus dari inventaris. Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-actions">
          <button class="btn btn-outline" @click="target = null">Batal</button>
          <button class="btn btn-danger" @click="confirmDelete">Ya, hapus</button>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.toolbar { display: flex; gap: 12px; padding: 22px 24px; flex-wrap: wrap; }
.search { flex: 1; min-width: 220px; max-width: 540px; display: flex; align-items: center; gap: 10px; border: 1px solid var(--line); border-radius: 10px; padding: 0 14px; color: var(--muted); background: #fff; }
.search:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); }
.search input { flex: 1; border: 0; outline: 0; padding: 13px 0; font: inherit; color: var(--ink); background: transparent; }
.select { width: auto; min-width: 190px; }

.table-wrap { overflow-x: auto; border-top: 1px solid var(--line); }
table { width: 100%; border-collapse: collapse; }
th { text-align: left; font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); font-weight: 700; padding: 14px 24px; background: #f9fafd; border-bottom: 1px solid var(--line); white-space: nowrap; }
td { padding: 18px 24px; border-bottom: 1px solid var(--line); white-space: nowrap; }
tbody tr:last-child td { border-bottom: 0; }
tbody tr:hover td { background: #fafbff; }
.name { font-weight: 700; }
.unit { color: var(--muted); }
.empty { text-align: center; color: var(--muted); padding: 48px 24px; white-space: normal; }

.actions { display: flex; gap: 14px; align-items: center; }
.actions a, .actions button { color: var(--muted); background: none; border: 0; padding: 4px; cursor: pointer; display: grid; place-items: center; border-radius: 6px; }
.actions a:hover, .actions button:hover { color: var(--primary); }
.actions button:hover { color: #dc2626; }

.foot { display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-top: 1px solid var(--line); flex-wrap: wrap; gap: 12px; }
.count { color: var(--muted); font-size: 14px; }
.pager { display: flex; gap: 6px; }
.pager button { min-width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--line); background: #fff; color: var(--ink); font: inherit; font-weight: 600; cursor: pointer; display: grid; place-items: center; }
.pager button.on { background: var(--primary); border-color: var(--primary); color: #fff; }
.pager button:disabled { opacity: 0.45; cursor: default; }

.overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); display: grid; place-items: center; padding: 20px; z-index: 50; }
.modal { background: #fff; border-radius: 16px; padding: 32px; width: 100%; max-width: 440px; text-align: center; }
.del-ico { width: 52px; height: 52px; border-radius: 50%; background: var(--red-soft); color: #dc3b3b; display: grid; place-items: center; margin: 0 auto 16px; }
.modal h3 { font-size: 22px; font-weight: 800; margin-bottom: 8px; }
.modal p { color: var(--muted); }
.modal p strong { color: var(--ink); }
.modal-actions { display: flex; justify-content: center; gap: 12px; margin-top: 24px; }
</style>