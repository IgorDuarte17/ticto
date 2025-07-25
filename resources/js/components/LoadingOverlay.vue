<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50"
  >
    <div class="bg-white rounded-lg p-6 shadow-xl">
      <div class="flex items-center space-x-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <span class="text-gray-700 font-medium">{{ message || 'Carregando...' }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false
  },
  message: {
    type: String,
    default: 'Carregando...'
  }
})
</script>
