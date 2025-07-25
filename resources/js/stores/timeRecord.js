import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useTimeRecordStore = defineStore('timeRecord', () => {
  const records = ref([])
  const todayRecords = ref([])
  const loading = ref(false)
  const pagination = ref({})

  const fetchRecords = async (params = {}) => {
    loading.value = true
    try {
      const response = await api.get('/time-records', { params })
      console.log('fetchRecords response:', response.data)
      
      if (response.data.data) {
        records.value = response.data.data
        pagination.value = response.data.pagination || response.data.meta || {}
      } else if (Array.isArray(response.data)) {
        records.value = response.data
        pagination.value = {}
      } else {
        records.value = []
        pagination.value = {}
      }
      
      console.log('Records atualizados:', records.value)
    } catch (error) {
      console.error('Erro ao buscar registros:', error)
      records.value = []
      pagination.value = {}
    } finally {
      loading.value = false
    }
  }

  const recordTime = async () => {
    try {
      const response = await api.post('/time-records')
      todayRecords.value.push(response.data)
      return { success: true, data: response.data }
    } catch (error) {
      return { 
        success: false, 
        message: error.response?.data?.message || 'Erro ao registrar ponto' 
      }
    }
  }

  const fetchTodayRecords = async () => {
    try {
      const response = await api.get('/time-records/today')
      console.log('fetchTodayRecords response:', response.data)
      
      if (response.data.data) {
        todayRecords.value = response.data.data
      } else if (Array.isArray(response.data)) {
        todayRecords.value = response.data
      } else {
        todayRecords.value = []
      }
      
      console.log('Today records atualizados:', todayRecords.value)
    } catch (error) {
      console.error('Erro ao buscar registros de hoje:', error)
      todayRecords.value = []
    }
  }

  const canRecordTime = async () => {
    try {
      const response = await api.get('/time-records/can-record')
      return response.data
    } catch (error) {
      return {
        can_record: false,
        message: 'Erro ao verificar permissão'
      }
    }
  }

  const getRecordsByDateRange = async (startDate, endDate, userId = null) => {
    try {
      const params = { start_date: startDate, end_date: endDate }
      if (userId) params.user_id = userId
      
      const response = await api.get('/time-records/date-range', { params })
      return response.data
    } catch (error) {
      console.error('Erro ao buscar registros por período:', error)
      return []
    }
  }

  const getFormattedRecords = async (params = {}) => {
    loading.value = true
    try {
      const endpoint = '/time-records/my-records'
      const response = await api.get(endpoint, { params })
      
      console.log('Resposta da API:', response.data)
      
      records.value = response.data.data || []
      
      if (response.data.meta) {
        pagination.value = response.data.meta
      } else if (response.data.pagination) {
        pagination.value = response.data.pagination
      } else {
        pagination.value = {}
      }
      
      console.log('Records atualizados:', records.value)
      console.log('Pagination:', pagination.value)
      
      return response.data
    } catch (error) {
      console.error('Erro ao buscar registros formatados:', error)
      records.value = []
      pagination.value = {}
      return { data: [], pagination: {} }
    } finally {
      loading.value = false
    }
  }

  return {
    records,
    todayRecords,
    loading,
    pagination,
    fetchRecords,
    recordTime,
    fetchTodayRecords,
    canRecordTime,
    getRecordsByDateRange,
    getFormattedRecords
  }
})
