import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
  const storedUser = localStorage.getItem('user')
  const storedToken = localStorage.getItem('token')
  
  const user = ref(storedUser ? JSON.parse(storedUser) : null)
  const token = ref(storedToken)
  const loading = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isEmployee = computed(() => user.value?.role === 'employee')

  const login = async (credentials) => {
    loading.value = true
    try {
      const response = await api.post('/auth/login', credentials)
      const { token: authToken, user: userData } = response.data
      
      token.value = authToken
      user.value = userData
      
      localStorage.setItem('token', authToken)
      localStorage.setItem('user', JSON.stringify(userData))
      
      api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
      
      return { success: true }
    } catch (error) {
      return { 
        success: false, 
        message: error.response?.data?.message || 'Erro ao fazer login' 
      }
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      await api.post('/auth/logout')
    } catch (error) {
    } finally {
      user.value = null
      token.value = null
      
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      
      delete api.defaults.headers.common['Authorization']
    }
  }

  const fetchUser = async () => {
    if (!token.value) return
    
    try {
      const response = await api.get('/auth/me')
      user.value = response.data
      
      localStorage.setItem('user', JSON.stringify(response.data))
    } catch (error) {
      console.error('Erro ao buscar usuário:', error)
      logout()
    }
  }

  const changePassword = async (passwords) => {
    try {
      await api.post('/auth/change-password', passwords)
      return { success: true }
    } catch (error) {
      return { 
        success: false, 
        message: error.response?.data?.message || 'Erro ao alterar senha' 
      }
    }
  }

  const initialize = async () => {
    console.log('Inicializando autenticação...')
    console.log('Token armazenado:', !!token.value)
    console.log('Usuário armazenado:', !!user.value)
    
    if (token.value) {
      api.defaults.headers.common['Authorization'] = `Bearer ${token.value}`
      
      if (!user.value) {
        console.log('Buscando dados do usuário da API...')
        await fetchUser()
      } else {
        console.log('Dados do usuário encontrados no localStorage')
      }
    } else {
      console.log('Nenhum token encontrado - usuário não autenticado')
    }
  }

  return {
    user,
    token,
    loading,
    isAuthenticated,
    isAdmin,
    isEmployee,
    login,
    logout,
    fetchUser,
    changePassword,
    initialize
  }
})
