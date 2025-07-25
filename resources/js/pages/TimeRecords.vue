<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-2xl font-bold text-gray-900">Registros de Ponto</h1>
          <p class="mt-2 text-sm text-gray-700">
            {{ authStore.isEmployee ? 'Seus registros de ponto' : 'Registros de ponto dos funcionários' }}
          </p>
        </div>
        <div v-if="authStore.isEmployee" class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
          <button
            @click="recordTime"
            :disabled="!canRecord || timeRecordStore.loading"
            class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="timeRecordStore.loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ timeRecordStore.loading ? 'Registrando...' : 'Registrar Ponto' }}
          </button>
        </div>
      </div>

      <div class="mt-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Data Início</label>
            <input
              v-model="filters.start_date"
              type="date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="searchRecords"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Data Fim</label>
            <input
              v-model="filters.end_date"
              type="date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="searchRecords"
            />
          </div>
          <div v-if="!authStore.isEmployee">
            <label class="block text-sm font-medium text-gray-700">Funcionário</label>
            <select
              v-model="filters.user_id"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="searchRecords"
            >
              <option value="">Todos os funcionários</option>
              <option v-for="employee in employees" :key="employee.id" :value="employee.id">
                {{ employee.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Ações</label>
            <div class="mt-1 flex space-x-2">
              <button
                @click="setToday"
                class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-700 py-2 px-3 rounded-md text-sm"
              >
                Hoje
              </button>
              <button
                @click="clearFilters"
                class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-md text-sm"
              >
                Limpar
              </button>
            </div>
          </div>
        </div>

        <div v-if="authStore.isEmployee && recordStatus.message" class="mt-4 p-3 rounded-md"
             :class="canRecord ? 'bg-green-50 text-green-800' : 'bg-orange-50 text-orange-800'">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg v-if="canRecord" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium">{{ recordStatus.message }}</p>
              <p v-if="recordStatus.next_allowed_at" class="text-xs mt-1">
                Próximo registro liberado às: {{ recordStatus.next_allowed_at }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="authStore.isEmployee && todayRecords.length > 0" class="mt-6 bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            Registros de Hoje ({{ formatDate(new Date()) }})
          </h3>
          <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div v-for="(record, index) in todayRecords" :key="record.id" class="text-center">
              <div class="text-sm text-gray-500">{{ getRecordLabel(index) }}</div>
              <div class="text-lg font-semibold text-gray-900">
                {{ formatTime(record.recorded_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
        <div v-if="timeRecordStore.loading" class="p-6 text-center">
          <div class="inline-flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Carregando registros...
          </div>
        </div>

        <div v-else-if="records.length === 0" class="p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum registro encontrado</h3>
          <p class="mt-1 text-sm text-gray-500">
            {{ authStore.isEmployee ? 'Você ainda não fez nenhum registro de ponto.' : 'Não há registros para os filtros selecionados.' }}
          </p>
        </div>

        <div v-else>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th v-if="!authStore.isEmployee" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Funcionário
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Data
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Hora
                </th>
                <th v-if="!authStore.isEmployee" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cargo
                </th>
                <th v-if="!authStore.isEmployee" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Gerente
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="record in records" :key="record.id">
                <td v-if="!authStore.isEmployee" class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-8 w-8 flex-shrink-0">
                      <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-xs font-medium text-indigo-800">
                          {{ record.employee_name?.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ record.employee_name }}</div>
                      <div class="text-sm text-gray-500">{{ record.age }} anos</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.recorded_date }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    {{ record.recorded_time }}
                  </span>
                </td>
                <td v-if="!authStore.isEmployee" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ record.position }}
                </td>
                <td v-if="!authStore.isEmployee" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ record.manager_name }}
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="records.length > 0 || pagination.total >= 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  <span v-if="pagination.total > 0">
                    Mostrando {{ pagination.from || 1 }} a {{ pagination.to || pagination.total }} de {{ pagination.total }} resultados
                  </span>
                  <span v-else-if="records.length > 0">
                    Mostrando {{ records.length }} resultados
                  </span>
                  <span v-else>
                    Nenhum resultado encontrado
                  </span>
                </p>
              </div>
              <div v-if="pagination.last_page > 1" class="flex space-x-2">
                <button
                  @click="goToPage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Anterior
                </button>
                <span class="px-3 py-1 text-sm text-gray-700">
                  Página {{ pagination.current_page }} de {{ pagination.last_page }}
                </span>
                <button
                  @click="goToPage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Próxima
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useTimeRecordStore } from '../stores/timeRecord'
import { useEmployeeStore } from '../stores/employee'

const authStore = useAuthStore()
const timeRecordStore = useTimeRecordStore()
const employeeStore = useEmployeeStore()

const filters = ref({
  start_date: '',
  end_date: '',
  user_id: '',
  page: 1
})

const canRecord = ref(false)
const recordStatus = ref({})

const records = computed(() => timeRecordStore.records)
const pagination = computed(() => timeRecordStore.pagination)
const todayRecords = computed(() => timeRecordStore.todayRecords)
const employees = computed(() => employeeStore.employees)

const searchRecords = async (resetPage = true) => {
  if (resetPage) {
    filters.value.page = 1
  }
  
  if (authStore.isEmployee) {
    const result = await timeRecordStore.getFormattedRecords(filters.value)
  } else {
    await timeRecordStore.fetchRecords(filters.value)
  }
}

const goToPage = (page) => {
  filters.value.page = page
  searchRecords(false)
}

const setToday = () => {
  const today = new Date().toISOString().split('T')[0]
  filters.value.start_date = today
  filters.value.end_date = today
  searchRecords()
}

const clearFilters = () => {
  filters.value.start_date = ''
  filters.value.end_date = ''
  filters.value.user_id = ''
  filters.value.page = 1
  searchRecords()
}

const recordTime = async () => {
  const result = await timeRecordStore.recordTime()
  
  if (result.success) {
    await checkCanRecord()
    await timeRecordStore.fetchTodayRecords()
    await searchRecords()
    alert('Ponto registrado com sucesso!')
  } else {
    alert(result.message || 'Erro ao registrar ponto')
  }
}

const checkCanRecord = async () => {
  if (authStore.isEmployee) {
    recordStatus.value = await timeRecordStore.canRecordTime()
    canRecord.value = recordStatus.value.can_record
  }
}

const getRecordLabel = (index) => {
  const labels = ['Entrada', 'Saída Almoço', 'Volta Almoço', 'Saída']
  return labels[index] || `Registro ${index + 1}`
}

const formatDate = (date) => {
  return date.toLocaleDateString('pt-BR')
}

const formatTime = (datetime) => {
  try {
    if (!datetime) return '--:--'
    
    let date
    
    if (datetime instanceof Date) {
      date = datetime
    } else if (typeof datetime === 'string' && datetime.includes('/')) {
      // Formato brasileiro dd/mm/yyyy hh:mm:ss
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

onMounted(async () => {
  await checkCanRecord()
  
  if (authStore.isEmployee) {
    await timeRecordStore.fetchTodayRecords()
  } else {
    await employeeStore.fetchEmployees()
  }
  
  await searchRecords()
})
</script>
