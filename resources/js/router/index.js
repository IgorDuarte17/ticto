import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import AppLayout from '../components/layouts/AppLayout.vue'
import AuthLayout from '../components/layouts/AuthLayout.vue'

import Login from '../pages/auth/Login.vue'
import Dashboard from '../pages/Dashboard.vue'
import Employees from '../pages/Employees.vue'
import TimeRecords from '../pages/TimeRecords.vue'
import Profile from '../pages/Profile.vue'

const routes = [
  {
    path: '/login',
    component: AuthLayout,
    children: [
      {
        path: '',
        name: 'login',
        component: Login,
        meta: { guest: true }
      }
    ]
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: Dashboard
      },
      {
        path: '/employees',
        name: 'employees',
        component: Employees,
        meta: { requiresRole: ['admin'] }
      },
      {
        path: '/time-records',
        name: 'time-records',
        component: TimeRecords
      },
      {
        path: '/profile',
        name: 'profile',
        component: Profile
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  if (authStore.token && !authStore.user) {
    console.log('Aguardando inicialização da autenticação...')
    await authStore.initialize()
  }
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    console.log('Redirecionando para login - não autenticado')
    next('/login')
  } else if (to.meta.guest && authStore.isAuthenticated) {
    console.log('Redirecionando para dashboard - já autenticado')
    next('/')
  } else if (to.meta.requiresRole && !to.meta.requiresRole.includes(authStore.user?.role)) {
    console.log('Redirecionando para dashboard - sem permissão para', to.meta.requiresRole)
    next('/')
  } else {
    next()
  }
})

export default router
