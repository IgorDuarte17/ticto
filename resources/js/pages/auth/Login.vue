<template>
  <div>
    <form @submit.prevent="handleLogin" class="space-y-6">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">
          E-mail
        </label>
        <div class="mt-1">
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': errors.email }"
          />
          <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
        </div>
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">
          Senha
        </label>
        <div class="mt-1">
          <input
            id="password"
            v-model="form.password"
            type="password"
            required
            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': errors.password }"
          />
          <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</p>
        </div>
      </div>

      <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
        <div class="text-sm text-red-800">{{ errorMessage }}</div>
      </div>

      <div>
        <button
          type="submit"
          :disabled="authStore.loading"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg
            v-if="authStore.loading"
            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
          </svg>
          {{ authStore.loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </div>
    </form>

    <!-- Demo credentials -->
    <div class="mt-6 p-4 bg-blue-50 rounded-md">
      <h3 class="text-sm font-medium text-blue-800 mb-2">Credenciais de Demonstração:</h3>
      <div class="text-xs text-blue-700 space-y-1">
        <div><strong>Gerente:</strong> admin@ticto.com / password</div>
        <div><strong>Funcionário:</strong> employee@ticto.com / password</div>
        <div class="text-xs text-blue-600 mt-2">
          <em>Outros funcionários: maria@ticto.com, pedro@ticto.com / password</em>
        </div>
      </div>
      
      <!-- Quick login buttons -->
      <div class="mt-3 flex gap-2">
        <button
          type="button"
          @click="quickLogin('admin@ticto.com', 'password')"
          class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
        >
          Login Gerente
        </button>
        <button
          type="button"
          @click="quickLogin('employee@ticto.com', 'password')"
          class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
        >
          Login Funcionário
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  email: '',
  password: ''
})

const errors = ref({})
const errorMessage = ref('')

const handleLogin = async () => {
  errors.value = {}
  errorMessage.value = ''

  const result = await authStore.login(form)
  
  if (result.success) {
    router.push('/')
  } else {
    if (result.errors) {
      errors.value = result.errors
    } else {
      errorMessage.value = result.message
    }
  }
}

const quickLogin = (email, password) => {
  form.email = email
  form.password = password
  handleLogin()
}
</script>
