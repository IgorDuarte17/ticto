import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useEmployeeStore = defineStore('employee', () => {
  const employees = ref([])
  const loading = ref(false)
  const pagination = ref({})

  const fetchEmployees = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/employees', { params })
      employees.value = response.data.data
      pagination.value = response.data.meta
    } catch (error) {
      console.error('Erro ao buscar funcionários:', error)
    } finally {
      loading.value = false
    }
  }

  const createEmployee = async (employeeData) => {
    
    try {
      const response = await api.post('/employees', employeeData)
      employees.value.push(response.data)
      return { success: true }
    } catch (error) {
      console.error('Erro na criação (store):', error)
      console.error('Error response:', error.response)
      console.error('Error response data:', error.response?.data)
      console.error('Error response status:', error.response?.status)
      
      return { 
        success: false, 
        message: error.response?.data?.message || 'Erro ao criar funcionário',
        errors: error.response?.data?.errors || {}
      }
    }
  }

  const updateEmployee = async (id, employeeData) => {
    
    try {
      const response = await api.put(`/employees/${id}`, employeeData)
      
      const index = employees.value.findIndex(emp => emp.id === id)
      if (index !== -1) {
        employees.value[index] = response.data
      }
      return { success: true }
    } catch (error) {
      console.error('Erro na atualização (store):', error)
      console.error('Error response:', error.response)
      console.error('Error response data:', error.response?.data)
      console.error('Error response status:', error.response?.status)
      console.error('Error config:', error.config)
      
      return { 
        success: false, 
        message: error.response?.data?.message || 'Erro ao atualizar funcionário',
        errors: error.response?.data?.errors || {}
      }
    }
  }

  const deleteEmployee = async (id) => {
    try {
      await api.delete(`/employees/${id}`)
      employees.value = employees.value.filter(emp => emp.id !== id)
      return { success: true }
    } catch (error) {
      return { 
        success: false, 
        message: error.response?.data?.message || 'Erro ao excluir funcionário' 
      }
    }
  }

  const getEmployee = async (id) => {
    try {
      const response = await api.get(`/employees/${id}`)
      return response.data
    } catch (error) {
      console.error('Erro ao buscar funcionário:', error)
      return null
    }
  }

  return {
    employees,
    loading,
    pagination,
    fetchEmployees,
    createEmployee,
    updateEmployee,
    deleteEmployee,
    getEmployee
  }
})
