<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-2xl font-bold text-gray-900">Funcionários</h1>
          <p class="mt-2 text-sm text-gray-700">
            Gerenciar funcionários da empresa
          </p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
          <button
            @click="openCreateModal"
            type="button"
            class="inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"
          >
            Novo Funcionário
          </button>
        </div>
      </div>

      <div class="mt-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">Buscar</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nome, email ou CPF..."
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="debounceSearch"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Cargo</label>
            <select
              v-model="filters.position"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="searchEmployees"
            >
              <option 
                v-for="position in FILTER_POSITIONS" 
                :key="position.value" 
                :value="position.value"
              >
                {{ position.label }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Ações</label>
            <button
              @click="clearFilters"
              class="mt-1 w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-md text-sm"
            >
              Limpar Filtros
            </button>
          </div>
        </div>
      </div>

      <div class="mt-6 bg-white shadow rounded-lg overflow-hidden">
        <div v-if="employeeStore.loading" class="p-6 text-center">
          <div class="inline-flex items-center">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Carregando funcionários...
          </div>
        </div>

        <div v-else-if="employees.length === 0" class="p-6 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum funcionário encontrado</h3>
          <p class="mt-1 text-sm text-gray-500">Comece criando um novo funcionário.</p>
        </div>

        <div v-else>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Funcionário
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cargo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Idade
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Gerente
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="relative px-6 py-3">
                  <span class="sr-only">Ações</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="employee in employees" :key="employee.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0">
                      <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-indigo-800">
                          {{ employee.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ employee.name }}</div>
                      <div class="text-sm text-gray-500">{{ employee.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ employee.position }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ calculateAge(employee.birth_date) }} anos
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ employee.manager?.name || 'Sem gerente' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    Ativo
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="editEmployee(employee)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Editar
                    </button>
                    <button
                      @click="confirmDelete(employee)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Excluir
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="pagination.total_pages > 1" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
                </p>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="goToPage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="px-3 py-1 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Anterior
                </button>
                <span class="px-3 py-1 text-sm text-gray-700">
                  Página {{ pagination.current_page }} de {{ pagination.total_pages }}
                </span>
                <button
                  @click="goToPage(pagination.current_page + 1)"
                  :disabled="!pagination.has_more_pages"
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

    <EmployeeModal
      v-if="showModal"
      :employee="selectedEmployee"
      @close="closeModal"
      @saved="handleEmployeeSaved"
    />

    <ConfirmModal
      v-if="showDeleteModal"
      title="Excluir Funcionário"
      message="Tem certeza que deseja excluir este funcionário? Esta ação não pode ser desfeita."
      @confirm="handleDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useEmployeeStore } from '../stores/employee'
import { FILTER_POSITIONS } from '../constants/positions'
import EmployeeModal from '../components/EmployeeModal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'

const employeeStore = useEmployeeStore()

const showModal = ref(false)
const showDeleteModal = ref(false)
const selectedEmployee = ref(null)
const employeeToDelete = ref(null)

const filters = ref({
  search: '',
  position: '',
  page: 1
})

const employees = computed(() => employeeStore.employees)
const pagination = computed(() => {
  const p = employeeStore.pagination
  return {
    from: p.from,
    to: p.to,
    total: Array.isArray(p.total) ? p.total[0] : p.total,
    current_page: Array.isArray(p.current_page) ? p.current_page[0] : p.current_page,
    total_pages: p.total_pages,
    has_more_pages: p.has_more_pages
  }
})

let searchTimeout

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    searchEmployees()
  }, 500)
}

const searchEmployees = () => {
  filters.value.page = 1
  employeeStore.fetchEmployees(filters.value)
}

const clearFilters = () => {
  filters.value.search = ''
  filters.value.position = ''
  filters.value.page = 1
  searchEmployees()
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR')
}

const calculateAge = (birthDate) => {
  if (!birthDate) return '-'
  
  const today = new Date()
  const birth = new Date(birthDate)
  let age = today.getFullYear() - birth.getFullYear()
  const monthDiff = today.getMonth() - birth.getMonth()
  
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--
  }
  
  return age
}

const goToPage = (page) => {
  filters.value.page = page
  employeeStore.fetchEmployees(filters.value)
}

const openCreateModal = () => {
  selectedEmployee.value = null
  showModal.value = true
}

const editEmployee = (employee) => {
  selectedEmployee.value = employee
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedEmployee.value = null
}

const handleEmployeeSaved = () => {
  closeModal()
  searchEmployees()
}

const confirmDelete = (employee) => {
  employeeToDelete.value = employee
  showDeleteModal.value = true
}

const handleDelete = async () => {
  if (employeeToDelete.value) {
    const result = await employeeStore.deleteEmployee(employeeToDelete.value.id)
    
    if (result.success) {
      searchEmployees()
    } else {
      alert(result.message || 'Erro ao excluir funcionário')
    }
  }
  
  showDeleteModal.value = false
  employeeToDelete.value = null
}

onMounted(() => {
  searchEmployees()
})
</script>
