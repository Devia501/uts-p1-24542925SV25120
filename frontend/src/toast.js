import { ref } from 'vue'

export const toast = ref('')
let timer

export function notify(message) {
  toast.value = message
  clearTimeout(timer)
  timer = setTimeout(() => (toast.value = ''), 3200)
}