<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../api'
import Icon from '../components/Icon.vue'
import { formatRupiah, stockStatus } from '../format'

const route = useRoute()
const item = ref(null)
const loading = ref(true)
const notFound = ref(false)

const status = computed(() => stockStatus(item.value.quantity))
const value = computed(() => Number(item.value.quantity) * Number(item.value.price))
const updated = computed(() =>
  new Date(item.value.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }),
)

onMounted(async () => {
  try {
    const { data } = await api.get(`/items/${route.params.id}`)
    item.value = data.data
  } catch {
    notFound.value = true
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section>
    <RouterLink to="/items" class="back"><Icon name="back" :size="18" />Kembali ke daftar barang</RouterLink>

    <div v-if="loading" class="card note">Memuat data...</div>
    <div v-else-if="notFound" class="card note">Barang tidak ditemukan. Mungkin sudah dihapus.</div>

    <template v-else>
      <div class="page-head">
        <div>
          <div class="eyebrow">Detail barang</div>
          <h1>{{ item.name }}</h1>
          <p class="sub">Informasi lengkap barang inventaris.</p>
        </div>
        <RouterLink :to="`/items/${item.id}/edit`" class="btn btn-outline"><Icon name="edit" :size="18" />Edit barang</RouterLink>
      </div>

      <div class="card">
        <div class="top">
          <span class="big"><Icon name="box" :size="34" /></span>
          <div>
            <span class="code">{{ item.sku }}</span>
            <h2>{{ item.name }}</h2>
            <span class="badge" :class="status.cls">{{ status.label }}</span>
          </div>
        </div>
        <dl>
          <div><dt>Kode barang</dt><dd>{{ item.sku }}</dd></div>
          <div><dt>Jumlah stok</dt><dd>{{ item.quantity }} unit</dd></div>
          <div><dt>Harga satuan</dt><dd>{{ formatRupiah(item.price) }}</dd></div>
          <div><dt>Nilai stok</dt><dd>{{ formatRupiah(value) }}</dd></div>
          <div><dt>Status</dt><dd>{{ status.label }}</dd></div>
          <div><dt>Terakhir diperbarui</dt><dd>{{ updated }}</dd></div>
        </dl>
        <div class="desc">
          <dt>Deskripsi</dt>
          <p>{{ item.description || 'Belum ada deskripsi.' }}</p>
        </div>
      </div>
    </template>
  </section>
</template>

<style scoped>
.back { display: inline-flex; align-items: center; gap: 8px; color: var(--muted); font-size: 14px; margin-bottom: 18px; }
.back:hover { color: var(--primary); }
.note { padding: 40px; text-align: center; color: var(--muted); }
.top { display: flex; align-items: center; gap: 22px; padding: 32px 36px; border-bottom: 1px solid var(--line); }
.big { width: 80px; height: 80px; border-radius: 16px; background: var(--primary-soft); color: var(--primary); display: grid; place-items: center; flex-shrink: 0; }
.top h2 { font-size: 24px; font-weight: 800; margin: 8px 0; }
dl { display: grid; grid-template-columns: repeat(3, 1fr); margin: 0; }
dl > div { padding: 24px 36px; border-bottom: 1px solid var(--line); }
dt { color: var(--muted); font-size: 13.5px; margin-bottom: 4px; }
dd { margin: 0; font-weight: 700; }
.desc { padding: 24px 36px 32px; }
.desc p { color: #475569; margin-top: 4px; }
@media (max-width: 800px) { dl { grid-template-columns: 1fr 1fr; } dl > div, .top, .desc { padding-left: 20px; padding-right: 20px; } }
</style>