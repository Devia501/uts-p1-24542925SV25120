const rupiah = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 })
export const formatRupiah = (v) => rupiah.format(Number(v))

export const LOW_STOCK = 5

export function stockStatus(q) {
  q = Number(q)
  if (q === 0) return { key: 'habis', label: 'Habis', cls: 'badge-red' }
  if (q <= LOW_STOCK) return { key: 'menipis', label: 'Menipis', cls: 'badge-orange' }
  return { key: 'tersedia', label: 'Tersedia', cls: 'badge-green' }
}

export function currentUser() {
  try {
    return JSON.parse(localStorage.getItem('user')) || {}
  } catch {
    return {}
  }
}

export function idError(msg = '') {
  const m = msg.toLowerCase()
  if (m.includes('required')) return 'Wajib diisi.'
  if (m.includes('already been taken')) return 'Sudah dipakai barang lain.'
  if (m.includes('must be an integer')) return 'Harus berupa bilangan bulat.'
  if (m.includes('must be a number')) return 'Harus berupa angka.'
  if (m.includes('at least 0')) return 'Tidak boleh kurang dari 0.'
  return msg
}