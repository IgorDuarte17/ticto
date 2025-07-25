<template>
  <div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Meu Perfil</h1>
        <p class="mt-2 text-sm text-gray-700">
          Gerencie suas informações pessoais e configurações
        </p>
      </div>

      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
            Informações Pessoais
          </h3>
          
          <form @submit.prevent="updateProfile" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <input
                  v-model="profileForm.name"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-300': profileErrors.name }"
                />
                <p v-if="profileErrors.name" class="mt-1 text-sm text-red-600">{{ profileErrors.name[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">E-mail</label>
                <input
                  v-model="profileForm.email"
                  type="email"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-300': profileErrors.email }"
                />
                <p v-if="profileErrors.email" class="mt-1 text-sm text-red-600">{{ profileErrors.email[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">CPF</label>
                <input
                  v-if="isAdmin"
                  v-model="profileForm.cpf"
                  type="text"
                  maxlength="14"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-300': profileErrors.cpf }"
                  @input="formatCpfField"
                />
                <input
                  v-else
                  :value="formatCpf(authStore.user?.cpf)"
                  type="text"
                  readonly
                  class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm"
                />
                <p v-if="!isAdmin" class="mt-1 text-xs text-gray-500">O CPF não pode ser alterado</p>
                <p v-if="profileErrors.cpf" class="mt-1 text-sm text-red-600">{{ profileErrors.cpf[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Data de Nascimento</label>
                <input
                  v-model="profileForm.birth_date"
                  type="date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-300': profileErrors.birth_date }"
                />
                <p v-if="profileErrors.birth_date" class="mt-1 text-sm text-red-600">{{ profileErrors.birth_date[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Cargo</label>
                <input
                  v-if="isAdmin"
                  v-model="profileForm.position"
                  type="text"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  :class="{ 'border-red-300': profileErrors.position }"
                />
                <input
                  v-else
                  :value="authStore.user?.position"
                  type="text"
                  readonly
                  class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm"
                />
                <p v-if="!isAdmin" class="mt-1 text-xs text-gray-500">O cargo é definido pelo administrador</p>
                <p v-if="profileErrors.position" class="mt-1 text-sm text-red-600">{{ profileErrors.position[0] }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Função</label>
                <input
                  :value="getRoleLabel(authStore.user?.role)"
                  type="text"
                  readonly
                  class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm"
                />
              </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
              <h4 class="text-md font-medium text-gray-900 mb-4">Endereço</h4>
              
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <label class="block text-sm font-medium text-gray-700">CEP</label>
                  <div class="flex space-x-2">
                    <input
                      v-model="profileForm.cep"
                      type="text"
                      placeholder="00000-000"
                      maxlength="9"
                      class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      @input="formatCepField"
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
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Estado</label>
                  <input
                    v-model="profileForm.state"
                    type="text"
                    maxlength="2"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                </div>

                <div class="sm:col-span-2">
                  <label class="block text-sm font-medium text-gray-700">Endereço</label>
                  <input
                    v-model="profileForm.address"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Bairro</label>
                  <input
                    v-model="profileForm.neighborhood"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700">Cidade</label>
                  <input
                    v-model="profileForm.city"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  />
                </div>
              </div>
            </div>

            <div v-if="profileErrorMessage" class="rounded-md bg-red-50 p-4">
              <div class="text-sm text-red-800">{{ profileErrorMessage }}</div>
            </div>

            <div v-if="profileSuccessMessage" class="rounded-md bg-green-50 p-4">
              <div class="text-sm text-green-800">{{ profileSuccessMessage }}</div>
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="loadingProfile"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ loadingProfile ? 'Salvando...' : 'Salvar Alterações' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
            Alterar Senha
          </h3>
          
          <form @submit.prevent="changePassword" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700">Senha Atual</label>
              <input
                v-model="passwordForm.current_password"
                type="password"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': passwordErrors.current_password }"
              />
              <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">{{ passwordErrors.current_password[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Nova Senha</label>
              <input
                v-model="passwordForm.new_password"
                type="password"
                required
                minlength="6"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': passwordErrors.new_password }"
              />
              <p v-if="passwordErrors.new_password" class="mt-1 text-sm text-red-600">{{ passwordErrors.new_password[0] }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
              <input
                v-model="passwordForm.new_password_confirmation"
                type="password"
                required
                minlength="6"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': passwordErrors.new_password_confirmation }"
              />
              <p v-if="passwordErrors.new_password_confirmation" class="mt-1 text-sm text-red-600">{{ passwordErrors.new_password_confirmation[0] }}</p>
            </div>

            <div v-if="passwordErrorMessage" class="rounded-md bg-red-50 p-4">
              <div class="text-sm text-red-800">{{ passwordErrorMessage }}</div>
            </div>

            <div v-if="passwordSuccessMessage" class="rounded-md bg-green-50 p-4">
              <div class="text-sm text-green-800">{{ passwordSuccessMessage }}</div>
            </div>

            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="loadingPassword"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ loadingPassword ? 'Alterando...' : 'Alterar Senha' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { viaCepService } from '../services/viaCep'

const authStore = useAuthStore()

const isAdmin = computed(() => authStore.user?.role === 'admin')

const profileForm = reactive({
  name: '',
  email: '',
  cpf: '',
  position: '',
  birth_date: '',
  cep: '',
  address: '',
  neighborhood: '',
  city: '',
  state: ''
})

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const profileErrors = ref({})
const passwordErrors = ref({})
const profileErrorMessage = ref('')
const passwordErrorMessage = ref('')
const profileSuccessMessage = ref('')
const passwordSuccessMessage = ref('')
const loadingProfile = ref(false)
const loadingPassword = ref(false)
const loadingCep = ref(false)

const loadUserData = async () => {
  await authStore.fetchUser()
  
  if (authStore.user) {
    Object.assign(profileForm, {
      name: authStore.user.name || '',
      email: authStore.user.email || '',
      cpf: authStore.user.cpf ? authStore.user.cpf.replace(/\D/g, '') : '',
      position: authStore.user.position || '',
      birth_date: authStore.user.birth_date || '',
      cep: authStore.user.cep || '',
      address: authStore.user.street || '',
      neighborhood: authStore.user.neighborhood || '',
      city: authStore.user.city || '',
      state: authStore.user.state || ''
    })
  }
}

const updateProfile = async () => {
  profileErrors.value = {}
  profileErrorMessage.value = ''
  profileSuccessMessage.value = ''
  loadingProfile.value = true

  try {
    const profileData = {
      name: profileForm.name,
      email: profileForm.email,
      birth_date: profileForm.birth_date,
      cep: profileForm.cep.replace(/\D/g, ''),
      street: profileForm.address,
      neighborhood: profileForm.neighborhood,
      city: profileForm.city,
      state: profileForm.state
    }

    if (isAdmin.value) {
      profileData.cpf = profileForm.cpf.replace(/\D/g, '')
      profileData.position = profileForm.position
    }

    const result = await authStore.updateProfile(profileData)
    
    if (result.success) {
      profileSuccessMessage.value = result.message
    } else {
      profileErrorMessage.value = result.message
      profileErrors.value = result.errors
    }
    
  } catch (error) {
    profileErrorMessage.value = 'Erro ao atualizar perfil'
  } finally {
    loadingProfile.value = false
  }
}

const changePassword = async () => {
  passwordErrors.value = {}
  passwordErrorMessage.value = ''
  passwordSuccessMessage.value = ''

  if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
    passwordErrors.value.new_password_confirmation = ['As senhas não coincidem']
    return
  }

  loadingPassword.value = true

  try {
    const result = await authStore.changePassword({
      current_password: passwordForm.current_password,
      new_password: passwordForm.new_password,
      new_password_confirmation: passwordForm.new_password_confirmation
    })

    if (result.success) {
      passwordSuccessMessage.value = 'Senha alterada com sucesso!'
      Object.assign(passwordForm, {
        current_password: '',
        new_password: '',
        new_password_confirmation: ''
      })
    } else {
      passwordErrorMessage.value = result.message
    }
  } catch (error) {
    passwordErrorMessage.value = 'Erro ao alterar senha'
  } finally {
    loadingPassword.value = false
  }
}

const formatCepField = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  if (value.length <= 8) {
    value = value.replace(/(\d{5})(\d)/, '$1-$2')
    profileForm.cep = value
  }
}

const formatCpfField = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  if (value.length <= 11) {
    value = value.replace(/(\d{3})(\d)/, '$1.$2')
    value = value.replace(/(\d{3})(\d)/, '$1.$2')
    value = value.replace(/(\d{3})(\d{1,2})/, '$1-$2')
    profileForm.cpf = value
  }
}

const searchCep = async () => {
  if (profileForm.cep && profileForm.cep.length >= 8) {
    loadingCep.value = true
    try {
      const address = await viaCepService.getAddressByCep(profileForm.cep)
      profileForm.address = address.address
      profileForm.neighborhood = address.neighborhood
      profileForm.city = address.city
      profileForm.state = address.state
    } catch (error) {
      console.error('Erro ao buscar CEP:', error)
    } finally {
      loadingCep.value = false
    }
  }
}

const formatCpf = (cpf) => {
  if (!cpf) return ''
  return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

const getRoleLabel = (role) => {
  const labels = {
    admin: 'Administrador',
    manager: 'Gerente',
    employee: 'Funcionário'
  }
  return labels[role] || role
}

onMounted(async () => {
  await loadUserData()
})
</script>
