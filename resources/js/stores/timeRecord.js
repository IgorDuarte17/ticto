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
      
      if (response.data.data) {
        records.value = response.data.data
        
        if (response.data.meta) {
          const meta = response.data.meta
          pagination.value = {
            current_page: meta.current_page || 1,
            last_page: meta.total_pages || 1,
            per_page: meta.per_page || 15,
            total: meta.total || 0,
            from: meta.from || 0,
            to: meta.to || 0
          }
        } else {
          pagination.value = {
            current_page: 1,
            last_page: 1,
            per_page: 15,
            total: records.value.length,
            from: records.value.length > 0 ? 1 : 0,
            to: records.value.length
          }
        }
      } else if (Array.isArray(response.data)) {
        records.value = response.data
        pagination.value = {}
      } else {
        records.value = []
        pagination.value = {}
      }
      
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
      
      if (response.data.data) {
        todayRecords.value = response.data.data
      } else if (Array.isArray(response.data)) {
        todayRecords.value = response.data
      } else {
        todayRecords.value = []
      }
      
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
      
      
      records.value = response.data.data || []
      
      if (response.data.meta) {
        const meta = response.data.meta
        pagination.value = {
          current_page: meta.current_page || 1,
          last_page: meta.total_pages || 1,
          per_page: meta.per_page || 15,
          total: meta.total || 0,
          from: meta.from || 0,
          to: meta.to || 0
        }
      } else {
        pagination.value = {
          current_page: 1,
          last_page: 1,
          per_page: 15,
          total: records.value.length,
          from: records.value.length > 0 ? 1 : 0,
          to: records.value.length
        }
      }
      
      
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
