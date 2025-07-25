<template>
  <div class="space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700">CEP</label>
      <div class="mt-1 relative">
        <input
          v-model="cepInput"
          type="text"
          placeholder="00000-000"
          maxlength="9"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :class="{ 'border-red-300': error }"
          @input="handleCepInput"
          @blur="searchCep"
        />
        <div
          v-if="loading"
          class="absolute right-3 top-2"
        >
          <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-indigo-600"></div>
        </div>
      </div>
      <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-gray-700">Logradouro</label>
        <input
          v-model="address.street"
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :readonly="readonly"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Número</label>
        <input
          v-model="address.number"
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Complemento</label>
        <input
          v-model="address.complement"
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Bairro</label>
        <input
          v-model="address.neighborhood"
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :readonly="readonly"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Cidade</label>
        <input
          v-model="address.city"
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :readonly="readonly"
        />
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700">Estado</label>
        <input
          v-model="address.state"
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :readonly="readonly"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import viaCepService from '../services/viaCep'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      cep: '',
      street: '',
      number: '',
      complement: '',
      neighborhood: '',
      city: '',
      state: ''
    })
  },
  readonly: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const cepInput = ref(props.modelValue.cep || '')
const address = ref({ ...props.modelValue })
const loading = ref(false)
const error = ref('')

const handleCepInput = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  
  if (value.length > 5) {
    value = value.replace(/(\d{5})(\d{1,3})/, '$1-$2')
  }
  
  cepInput.value = value
  address.value.cep = value.replace(/\D/g, '')
  
  error.value = ''
  
  if (value.length < 8) {
    address.value.street = ''
    address.value.neighborhood = ''
    address.value.city = ''
    address.value.state = ''
  }
  
  emitUpdate()
}

const searchCep = async () => {
  const cleanCep = cepInput.value.replace(/\D/g, '')
  
  if (cleanCep.length !== 8) {
    if (cleanCep.length > 0) {
      error.value = 'CEP deve ter 8 dígitos'
    }
    return
  }
  
  loading.value = true
  error.value = ''
  
  try {
    const data = await viaCepService.getAddressByCep(cleanCep)
    
    if (data.erro) {
      error.value = 'CEP não encontrado'
      return
    }
    
    address.value = {
      ...address.value,
      cep: cleanCep,
      street: data.logradouro || '',
      neighborhood: data.bairro || '',
      city: data.localidade || '',
      state: data.uf || ''
    }
    
    emitUpdate()
  } catch (err) {
    error.value = 'Erro ao buscar CEP'
    console.error('Erro ao buscar CEP:', err)
  } finally {
    loading.value = false
  }
}

const emitUpdate = () => {
  emit('update:modelValue', { ...address.value })
}

watch(() => props.modelValue, (newValue) => {
  address.value = { ...newValue }
  cepInput.value = viaCepService.formatCep(newValue.cep || '')
}, { deep: true })

watch(address, () => {
  emitUpdate()
}, { deep: true })
</script>
