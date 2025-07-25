<template>
  <div id="app">
    <div v-if="isInitializing" class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
      <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
          <div class="flex flex-col items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            <p class="mt-4 text-sm text-gray-600">Carregando aplicação...</p>
          </div>
        </div>
      </div>
    </div>
    
    <template v-else>
      <router-view />
      <NotificationList />
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import NotificationList from './components/NotificationList.vue'

const authStore = useAuthStore()
const isInitializing = ref(true)

onMounted(async () => {
  try {
    await authStore.initialize()
  } catch (error) {
    console.error('Erro na inicialização:', error)
  } finally {
    isInitializing.value = false
  }
})
</script>

