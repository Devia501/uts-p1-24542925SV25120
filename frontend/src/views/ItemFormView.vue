<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'
import Icon from '../components/Icon.vue'
import { notify } from '../toast'
import { idError } from '../format'

const route = useRoute()
const router = useRouter()
const id = route.params.id
const isEdit = !!id

const form = reactive({ name: '', sku: '', quantity: 0, price: 0, description: '' })
const errors = ref({})
const saving = ref(false)
const loading = ref(isEdit)
const notFound = ref(false)

const err = (field) => (errors.value[field] ? idError(errors.value[field][0]) : '')

onMounted(async () => {
  if (!isEdit) return
  try {
    const { data } = await api.get(`/items/${id}`)
    const i = data.data
    Object.assign(form, { name: i.name, sku: i.sku, quantity: i.quantity, price: Number(i.price), description: i.description ?? '' })
  } catch {
    notFound.value = true
  } finally {
    loading.value = false
  }
})

async function save() {
  errors.value = {}
  saving.value = true
  try {
    if (isEdit) await api.put(`/items/${id}`, form)
    else await api.post('/items', form)
    notify(isEdit ? 'Perubahan tersimpan.' : 'Barang ditambahkan.')
    router.push('/items')
  } catch (e) {
    if (e.response?.status === 422) errors.value = e.response.data.errors
    else notify('Terjadi kesalahan. Coba lagi.')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <section>
    <RouterLink to="/items" class="back"><Icon name="back" :size="18" />Kembali ke daftar barang</RouterLink>
    <div class="page-head">
      <div>
        <div class="eyebrow">Inventaris</div>
        <h1>{{ isEdit ? 'Edit Barang' : 'Tambah Barang' }}</h1>
        <p class="sub">{{ isEdit ? 'Perbarui informasi barang yang dipilih.' : 'Masukkan informasi barang baru ke inventaris.' }}</p>
      </div>
    </div>

    <div v-if="loading" class="card note">Memuat data...</div>
    <div v-else-if="notFound" class="card note">Barang tidak ditemukan. Mungkin sudah dihapus.</div>

    <form v-else class="card form" @submit.prevent="save" novalidate>
      <div class="sec">
        <span class="num">01</span>
        <div><h3>Informasi dasar</h3><p class="sub">Identitas utama barang inventaris.</p></div>
      </div>
      <div class="row">
        <div class="field">
          <label for="name">Nama barang <span class="req">*</span></label>
          <input id="name" v-model="form.name" class="input" :class="{ invalid: err('name') }" placeholder="Contoh: Monitor LED 24 inci" />
          <div v-if="err('name')" class="field-error">{{ err('name') }}</div>
        </div>
        <div class="field">
          <label for="sku">Kode unik <span class="req">*</span></label>
          <input id="sku" v-model="form.sku" class="input" :class="{ invalid: err('sku') }" placeholder="Contoh: ELK-025" />
          <div v-if="err('sku')" class="field-error">{{ err('sku') }}</div>
        </div>
      </div>

      <hr />

      <div class="sec">
        <span class="num">02</span>
        <div><h3>Stok &amp; harga</h3><p class="sub">Jumlah dan harga satuan barang saat ini.</p></div>
      </div>
      <div class="row">
        <div class="field">
          <label for="qty">Jumlah stok <span class="req">*</span></label>
          <input id="qty" v-model.number="form.quantity" class="input" :class="{ invalid: err('quantity') }" type="number" min="0" />
          <div v-if="err('quantity')" class="field-error">{{ err('quantity') }}</div>
        </div>
        <div class="field">
          <label for="price">Harga satuan (Rp) <span class="req">*</span></label>
          <input id="price" v-model.number="form.price" class="input" :class="{ invalid: err('price') }" type="number" min="0" step="0.01" />
          <div v-if="err('price')" class="field-error">{{ err('price') }}</div>
        </div>
      </div>
      <div class="field">
        <label for="desc">Deskripsi</label>
        <textarea id="desc" v-model="form.description" class="input" rows="4" placeholder="Tambahkan catatan atau keterangan barang..."></textarea>
        <div v-if="err('description')" class="field-error">{{ err('description') }}</div>
      </div>

      <div class="actions">
        <button type="button" class="btn btn-outline" @click="router.push('/items')">Batal</button>
        <button class="btn btn-primary" :disabled="saving"><Icon name="check" :size="18" />{{ saving ? 'Menyimpan...' : 'Simpan barang' }}</button>
      </div>
    </form>
  </section>
</template>

<style scoped>
.back { display: inline-flex; align-items: center; gap: 8px; color: var(--muted); font-size: 14px; margin-bottom: 18px; }
.back:hover { color: var(--primary); }
.note { padding: 40px; text-align: center; color: var(--muted); }
.form { padding: 36px; }
.sec { display: flex; align-items: center; gap: 14px; margin-bottom: 24px; }
.num { width: 42px; height: 42px; border-radius: 10px; background: var(--primary-soft); color: var(--primary); font-weight: 800; display: grid; place-items: center; font-size: 14px; flex-shrink: 0; }
.sec h3 { font-size: 17px; font-weight: 700; }
.sec .sub { font-size: 13.5px; margin-top: 0; }
.row { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; }
hr { border: 0; border-top: 1px solid var(--line); margin: 14px 0 28px; }
.actions { display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--line); padding-top: 24px; margin-top: 8px; }
@media (max-width: 700px) { .row { grid-template-columns: 1fr; gap: 0; } .form { padding: 22px; } }
</style>