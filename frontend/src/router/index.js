import { createRouter, createWebHistory } from 'vue-router'
import AppLayout from '../layouts/AppLayout.vue'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import ItemsView from '../views/ItemsView.vue'
import ItemFormView from '../views/ItemFormView.vue'
import ItemDetailView from '../views/ItemDetailView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/login', name: 'login', component: LoginView },
    {
      path: '/',
      component: AppLayout,
      meta: { auth: true },
      children: [
        { path: '', name: 'dashboard', component: DashboardView, meta: { title: 'Dashboard' } },
        { path: 'items', name: 'items', component: ItemsView, meta: { title: 'Daftar Barang' } },
        { path: 'items/new', name: 'item-new', component: ItemFormView, meta: { title: 'Tambah Barang' } },
        { path: 'items/:id', name: 'item-detail', component: ItemDetailView, meta: { title: 'Detail Barang' } },
        { path: 'items/:id/edit', name: 'item-edit', component: ItemFormView, meta: { title: 'Edit Barang' } },
      ],
    },
    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  if (to.matched.some((r) => r.meta.auth) && !token) return '/login'
  if (to.name === 'login' && token) return '/'
})

export default router