<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="lg:flex lg:items-center lg:justify-between">
        <div class="min-w-0 flex-1">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
            Dashboard
          </h2>
          <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
            <div class="mt-2 flex items-center text-sm text-gray-500">
              Bem-vindo, {{ authStore.user?.name }}
            </div>
          </div>
        </div>
      </div>

      <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Ponto Eletrônico</dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ currentTime }}
                  </dd>
                </dl>
              </div>
            </div>
            <div class="mt-5">
              <button
                v-if="authStore.isEmployee"
                @click.prevent="recordTime"
                :disabled="!canRecord || timeRecordStore.loading || isProcessingRecord"
                class="w-full py-2 px-4 rounded-lg transition-all duration-200 font-medium"
                :class="canRecord && !timeRecordStore.loading && !isProcessingRecord
                  ? 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg hover:shadow-xl transform hover:scale-105' 
                  : 'bg-gray-400 text-gray-200 cursor-not-allowed'"
              >
                <span v-if="timeRecordStore.loading || isProcessingRecord" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Registrando...
                </span>
                <span v-else-if="canRecord">
                  🕐 Registrar Ponto
                </span>
                <span v-else>
                  ⏳ Aguardando...
                </span>
              </button>
              <p v-else class="text-sm text-gray-500">
                Acesso de gerente - veja registros da equipe em "Registros"
              </p>
              
              <p v-if="!canRecord && recordStatus.message" class="mt-2 text-sm text-orange-600 bg-orange-50 p-2 rounded">
                {{ recordStatus.message }}
              </p>
              
              <p v-if="recordStatus.next_allowed_at" class="text-xs text-gray-500 mt-1">
                Próximo registro: {{ formatNextAllowedTime(recordStatus.next_allowed_at) }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14.25v2.25m3-4.5v4.5m3-6.75v6.75m3-9v9M6 20.25h12A2.25 2.25 0 0020.25 18V6A2.25 2.25 0 0018 3.75H6A2.25 2.25 0 003.75 6v12A2.25 2.25 0 006 20.25z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    {{ authStore.isAdmin ? 'Registros Hoje (Todos)' : 'Seus Registros Hoje' }}
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ todayRecordsCount }} {{ todayRecordsCount === 1 ? 'registro' : 'registros' }}
                  </dd>
                </dl>
              </div>
            </div>
            <div class="mt-3">
              <div v-if="todayRecords.length > 0" class="text-xs text-gray-500 space-y-1">
                <div v-for="record in todayRecords.slice(-3)" :key="record.id">
                  <span v-if="authStore.isAdmin" class="font-medium">{{ record.user?.name }}:</span>
                  {{ formatTime(record.recorded_at) }}
                </div>
                <div v-if="todayRecords.length > 3" class="text-xs text-gray-400 pt-1">
                  + {{ todayRecords.length - 3 }} mais...
                </div>
              </div>
              <div v-else class="text-xs text-gray-500">
                {{ authStore.isAdmin ? 'Nenhum registro hoje' : 'Você ainda não registrou ponto hoje' }}
              </div>
            </div>
          </div>
        </div>

        <div v-if="authStore.isAdmin || authStore.isManager" class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Funcionários</dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ employeeStore.employees.length }} ativos
                  </dd>
                </dl>
              </div>
            </div>
            <div class="mt-5">
              <router-link
                to="/employees"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition-colors inline-block text-center"
              >
                Gerenciar Funcionários
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-8">
        <div class="bg-white shadow rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              Registros Recentes
            </h3>
            
            <div v-if="recentRecords.length > 0" class="flow-root">
              <div class="-my-5 divide-y divide-gray-200">
                <div v-for="record in recentRecords" :key="record.id" class="py-4">
                  <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                      <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-xs font-medium text-indigo-600">
                          {{ (record.employee_name || record.user?.name || 'U').charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <p class="text-sm font-medium text-gray-900">
                        {{ record.employee_name || record.user?.name || 'Usuário' }}
                      </p>
                      <p class="text-sm text-gray-500">
                        {{ record.position || record.user?.position || 'Funcionário' }}
                      </p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm text-gray-900">
                        {{ formatDateTime(record.recorded_at) }}
                      </p>
                      <p class="text-xs text-gray-500">
                        Registro {{ record.id }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-6">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3-6h3.75m-3 3h3.75m-3 3h3.75M3 18V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5V18a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum registro</h3>
              <p class="mt-1 text-sm text-gray-500">Não há registros recentes para exibir.</p>
            </div>
            
            <div class="mt-6">
              <router-link
                to="/time-records"
                class="w-full bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 py-2 px-4 rounded-lg transition-colors inline-block text-center"
              >
                Ver Todos os Registros
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useTimeRecordStore } from '../stores/timeRecord'
import { useEmployeeStore } from '../stores/employee'

const authStore = useAuthStore()
const timeRecordStore = useTimeRecordStore()
const employeeStore = useEmployeeStore()

const currentTime = ref('')
const canRecord = ref(false)
const recordStatus = ref({})
const recentRecords = ref([])
const isProcessingRecord = ref(false)

const todayRecords = computed(() => timeRecordStore.todayRecords)
const todayRecordsCount = computed(() => todayRecords.value.length)

const updateCurrentTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('pt-BR')
}

const recordTime = async () => {
  if (isProcessingRecord.value) {
    console.log('🔵 Já processando, ignorando clique')
    return
  }
  
  if (!authStore.isEmployee) {
    alert('Apenas funcionários podem registrar ponto!')
    return
  }
  
  if (!canRecord.value) {
    alert('Não é possível registrar ponto agora. ' + recordStatus.value.message)
    return
  }
  
  isProcessingRecord.value = true
  canRecord.value = false
  
  recordStatus.value = {
    can_record: false,
    message: 'Registrando ponto...',
    next_allowed_at: null
  }
  
  try {
    const result = await timeRecordStore.recordTime()
    
    if (result.success) {
      await timeRecordStore.fetchTodayRecords()
      await loadRecentRecords()
      await checkCanRecord()
      
    } else {
      let errorMessage = result.message || 'Erro ao registrar ponto'
      
      if (result.errors) {
        const firstError = Object.values(result.errors)[0]
        if (firstError && firstError.length > 0) {
          errorMessage = firstError[0]
        }
      }
      
      alert('❌ Erro: ' + errorMessage)
      await checkCanRecord() // Recarregar o estado real
    }
    
  } catch (error) {
    alert('❌ Erro inesperado ao registrar ponto')
    await checkCanRecord()
  } finally {
    isProcessingRecord.value = false
  }
}

const checkCanRecord = async () => {
  if (authStore.isEmployee) {
    recordStatus.value = await timeRecordStore.canRecordTime()
    canRecord.value = recordStatus.value.can_record
    
    isProcessingRecord.value = false

    if (!recordStatus.value.can_record && recordStatus.value.next_allowed_at) {
      const now = new Date()
      const nextTime = new Date()
      
      const timeParts = recordStatus.value.next_allowed_at.split(':')
      nextTime.setHours(parseInt(timeParts[0]), parseInt(timeParts[1]), parseInt(timeParts[2] || '0'), 0)
      
      if (nextTime > now) {
        const waitTime = nextTime.getTime() - now.getTime()
        
        setTimeout(async () => {
          await checkCanRecord()
        }, waitTime + 1000) // +1 segundo para garantir
      }
    }
  }
}

const loadRecentRecords = async () => {
  try {
    if (authStore.isEmployee) {
      const result = await timeRecordStore.getFormattedRecords({ per_page: 5 })
      recentRecords.value = (result.data || []).map(record => ({
        ...record,
        employee_name: record.user?.name || authStore.user?.name,
        position: record.user?.position || authStore.user?.position
      }))
    } else {
      await timeRecordStore.fetchRecords({ per_page: 5, sort: 'recorded_at', order: 'desc' })
      recentRecords.value = timeRecordStore.records.map(record => ({
        ...record,
        employee_name: record.user?.name || record.employee_name,
        position: record.user?.position || record.position
      }))
    }
    
  } catch (error) {
    console.error('Erro ao carregar registros recentes:', error)
    recentRecords.value = []
  }
}

const formatTime = (datetime) => {
  try {
    if (!datetime) return '--:--'
    
    let date
    
    if (datetime instanceof Date) {
      date = datetime
    } else if (typeof datetime === 'string' && datetime.includes('/')) {
      const [datePart, timePart] = datetime.split(' ')
      const [day, month, year] = datePart.split('/')
      const timeString = timePart || '00:00:00'
      date = new Date(`${year}-${month}-${day}T${timeString}`)
    } else {
      date = new Date(datetime)
    }
    
    if (isNaN(date.getTime())) {
      return '--:--'
    }
    
    return date.toLocaleTimeString('pt-BR', {
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    console.error('Erro ao formatar tempo:', error)
    return '--:--'
  }
}

const formatDateTime = (datetime) => {
  try {
    if (!datetime) return 'Data inválida'
    
    
    let date
    
    if (datetime instanceof Date) {
      date = datetime
    }
    else if (typeof datetime === 'string' && datetime.includes('/')) {
      const [datePart, timePart] = datetime.split(' ')
      const [day, month, year] = datePart.split('/')
      const timeString = timePart || '00:00:00'
      
      date = new Date(`${year}-${month}-${day}T${timeString}`)
    }
    else {
      date = new Date(datetime)
    }
    
    if (isNaN(date.getTime())) {
      console.error('Data inválida:', datetime)
      return 'Data inválida'
    }
    
    const formatted = date.toLocaleString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    })
    
    return formatted
    
  } catch (error) {
    console.error('Erro ao formatar data:', error, 'input:', datetime)
    return 'Data inválida'
  }
}

const formatNextAllowedTime = (timeString) => {
  try {
    if (!timeString) return '--:--'
    
    if (timeString.match(/^\d{2}:\d{2}:\d{2}$/)) {
      return timeString.substring(0, 5)
    }
    
    const date = new Date(timeString)
    if (isNaN(date.getTime())) {
      return timeString
    }
    
    return date.toLocaleTimeString('pt-BR', {
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (error) {
    console.error('Erro ao formatar próximo horário:', error, 'input:', timeString)
    return timeString
  }
}

let timeInterval

onMounted(async () => {
  updateCurrentTime()
  timeInterval = setInterval(updateCurrentTime, 1000)
  
  await checkCanRecord()
  await timeRecordStore.fetchTodayRecords()
  await loadRecentRecords()
  
  if (authStore.isAdmin || authStore.isManager) {
    await employeeStore.fetchEmployees()
  }
})

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval)
  }
})
</script>
