<template>
  <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="$emit('close')"></div>
      
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
      
      <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <form @submit.prevent="handleSubmit">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                  {{ isEditing ? 'Editar Funcionário' : 'Novo Funcionário' }}
                </h3>
                
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Nome *</label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.name }"
                    />
                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail *</label>
                    <input
                      v-model="form.email"
                      type="email"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.email }"
                    />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">CPF *</label>
                    <input
                      v-model="form.cpf"
                      type="text"
                      required
                      placeholder="000.000.000-00"
                      maxlength="14"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.cpf }"
                      @input="formatCpf"
                    />
                    <p v-if="errors.cpf" class="mt-1 text-sm text-red-600">{{ errors.cpf[0] }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">Data de Nascimento *</label>
                    <input
                      v-model="form.birth_date"
                      type="date"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.birth_date }"
                    />
                    <p v-if="errors.birth_date" class="mt-1 text-sm text-red-600">{{ errors.birth_date[0] }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">Cargo *</label>
                    <select
                      v-model="form.position"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.position }"
                    >
                      <option value="">Selecione um cargo</option>
                      <option 
                        v-for="position in FORM_POSITIONS" 
                        :key="position.value" 
                        :value="position.value"
                      >
                        {{ position.label }}
                      </option>
                    </select>
                    <p v-if="errors.position" class="mt-1 text-sm text-red-600">{{ errors.position[0] }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">CEP *</label>
                    <div class="flex space-x-2">
                      <input
                        v-model="form.cep"
                        type="text"
                        placeholder="00000-000"
                        maxlength="9"
                        required
                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        :class="{ 'border-red-300': errors.cep }"
                        @input="formatCep"
                        @blur="searchCep"
                      />
                      <button
                        type="button"
                        @click="searchCep"
                        :disabled="loadingCep"
                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-sm disabled:opacity-50"
                      >
                        {{ loadingCep ? '...' : 'Buscar' }}
                      </button>
                    </div>
                    <p v-if="errors.cep" class="mt-1 text-sm text-red-600">{{ errors.cep[0] }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">Endereço *</label>
                    <input
                      v-model="form.street"
                      type="text"
                      required
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.street }"
                    />
                    <p v-if="errors.street" class="mt-1 text-sm text-red-600">{{ errors.street[0] }}</p>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Número *</label>
                      <input
                        v-model="form.number"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        :class="{ 'border-red-300': errors.number }"
                      />
                      <p v-if="errors.number" class="mt-1 text-sm text-red-600">{{ errors.number[0] }}</p>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Complemento</label>
                      <input
                        v-model="form.complement"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      />
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Bairro</label>
                      <input
                        v-model="form.neighborhood"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Cidade</label>
                      <input
                        v-model="form.city"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <input
                      v-model="form.state"
                      type="text"
                      maxlength="2"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                  </div>

                  <div v-if="!isEditing">
                    <label class="block text-sm font-medium text-gray-700">Senha *</label>
                    <input
                      v-model="form.password"
                      type="password"
                      required
                      minlength="8"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      :class="{ 'border-red-300': errors.password }"
                    />
                    <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="errorMessage" class="px-4 pb-4">
            <div class="rounded-md bg-red-50 p-4">
              <div class="text-sm text-red-800">{{ errorMessage }}</div>
            </div>
          </div>
          
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="submit"
              :disabled="loading"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ loading ? 'Salvando...' : (isEditing ? 'Atualizar' : 'Criar') }}
            </button>
            <button
              type="button"
              @click="$emit('close')"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { useEmployeeStore } from '../stores/employee'
import { useNotificationStore } from '../stores/notification'
import { viaCepService } from '../services/viaCep'
import { FORM_POSITIONS } from '../constants/positions'

const props = defineProps({
  employee: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const employeeStore = useEmployeeStore()
const notificationStore = useNotificationStore()

const isEditing = computed(() => !!props.employee)

const form = reactive({
  name: '',
  email: '',
  cpf: '',
  birth_date: '',
  position: '',
  cep: '',
  street: '',
  number: '',
  complement: '',
  neighborhood: '',
  city: '',
  state: '',
  password: ''
})

const errors = ref({})
const errorMessage = ref('')
const loading = ref(false)
const loadingCep = ref(false)

watch(() => props.employee, (employee) => {
  if (employee) {
    Object.keys(form).forEach(key => {
      form[key] = ''
    })
    
    Object.assign(form, {
      name: employee.name || '',
      email: employee.email || '',
      cpf: employee.cpf || '',
      birth_date: employee.birth_date || '',
      position: employee.position || '',
      cep: employee.cep ? employee.cep.replace(/\D/g, '').replace(/(\d{5})(\d{3})/, '$1-$2') : '',
      street: employee.street || '',
      number: employee.number || '',
      complement: employee.complement || '',
      neighborhood: employee.neighborhood || '',
      city: employee.city || '',
      state: employee.state || '',
      password: ''
    })
  } else {
    Object.keys(form).forEach(key => {
      form[key] = ''
    })
    form.name = ''
    form.email = ''
    form.cpf = ''
    form.birth_date = ''
    form.position = ''
    form.cep = ''
    form.street = ''
    form.number = ''
    form.complement = ''
    form.neighborhood = ''
    form.city = ''
    form.state = ''
    form.password = ''
  }
}, { immediate: true })

const formatCpf = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  if (value.length <= 11) {
    value = value.replace(/(\d{3})(\d)/, '$1.$2')
    value = value.replace(/(\d{3})(\d)/, '$1.$2')
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2')
    form.cpf = value
  }
}

const formatCep = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  if (value.length <= 8) {
    value = value.replace(/(\d{5})(\d)/, '$1-$2')
    form.cep = value
  }
}

const searchCep = async () => {
  if (form.cep && form.cep.length >= 8) {
    loadingCep.value = true
    try {
      const address = await viaCepService.getAddressByCep(form.cep.replace(/\D/g, ''))
      form.street = address.street || address.logradouro || ''
      form.neighborhood = address.neighborhood || address.bairro || ''
      form.city = address.city || address.localidade || ''
      form.state = address.state || address.uf || ''
      if (address.complement || address.complemento) {
        form.complement = address.complement || address.complemento
      }
    } catch (error) {
      console.error('Erro ao buscar CEP:', error)
      notificationStore.error('Erro ao buscar CEP. Verifique se o CEP está correto.')
    } finally {
      loadingCep.value = false
    }
  }
}

const handleSubmit = async () => {
  
  errors.value = {}
  errorMessage.value = ''
  loading.value = true

  try {
    const data = { ...form }

    
    const requiredFields = ['name', 'email', 'cpf', 'position', 'birth_date', 'cep', 'street', 'number', 'neighborhood', 'city', 'state']
    
    if (!isEditing.value) {
      requiredFields.push('password')
    }
    
    const missingFields = requiredFields.filter(field => {
      const value = data[field]
      if (value === null || value === undefined) return true
      if (typeof value === 'string' && value.trim() === '') return true
      if (typeof value === 'number' && isNaN(value)) return true
      return false
    })
    


    requiredFields.forEach(field => {
      const value = data[field]
  
    })

    
    if (missingFields.length > 0) {
      const fieldLabels = {
        name: 'Nome',
        email: 'E-mail',
        cpf: 'CPF',
        position: 'Cargo',
        birth_date: 'Data de Nascimento',
        cep: 'CEP',
        street: 'Endereço',
        number: 'Número',
        neighborhood: 'Bairro',
        city: 'Cidade',
        state: 'Estado',
        password: 'Senha'
      }
      
      const errorObj = {}
      missingFields.forEach(field => {
        errorObj[field] = [`${fieldLabels[field]} é obrigatório`]
      })
      
  
      errors.value = errorObj
      notificationStore.error('Por favor, preencha todos os campos obrigatórios')
      loading.value = false
      return
    }
    
    const originalCpf = data.cpf
    data.cpf = data.cpf.replace(/\D/g, '')

    
    const originalCep = data.cep
    data.cep = data.cep.replace(/\D/g, '')

    
    if (data.cpf.length !== 11) {
  
      errors.value = { cpf: ['CPF deve conter 11 dígitos'] }
      notificationStore.error('CPF inválido')
      loading.value = false
      return
    }
    
    if (data.cep.length !== 8) {
  
      errors.value = { cep: ['CEP deve conter 8 dígitos'] }
      notificationStore.error('CEP inválido')
      loading.value = false
      return
    }
    
    if (!isEditing.value && data.password && data.password.length < 8) {
  
      errors.value = { password: ['A senha deve ter pelo menos 8 caracteres'] }
      notificationStore.error('A senha deve ter pelo menos 8 caracteres')
      loading.value = false
      return
    }
    

    
    let result
    if (isEditing.value) {
  
      if (!data.password) {
        delete data.password
    
      }
      result = await employeeStore.updateEmployee(props.employee.id, data)
    } else {
  
      result = await employeeStore.createEmployee(data)
    }



    if (result.success) {
  
      notificationStore.success(
        isEditing.value ? 'Funcionário atualizado com sucesso!' : 'Funcionário criado com sucesso!'
      )
      emit('saved')
    } else {
  
      if (result.errors) {
    
        errors.value = result.errors
        notificationStore.error('Por favor, corrija os erros no formulário')
      } else {
    
        errorMessage.value = result.message
        notificationStore.error(result.message || 'Erro ao salvar funcionário')
      }
    }
  } catch (error) {
    console.error('=== ERRO CAPTURADO ===')
    console.error('Tipo do erro:', typeof error)
    console.error('Erro completo:', error)
    console.error('Stack trace:', error.stack)
    console.error('Message:', error.message)
    
    errorMessage.value = 'Erro inesperado. Tente novamente.'
    notificationStore.error('Erro inesperado. Tente novamente.')
  } finally {

    loading.value = false
  }
}
</script>
