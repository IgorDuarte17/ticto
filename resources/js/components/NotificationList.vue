<template>
  <div
    v-if="notificationStore.notifications.length > 0"
    aria-live="assertive"
    class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6 z-50"
  >
    <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
      <TransitionGroup
        name="notification"
        tag="div"
        class="flex flex-col space-y-4 w-full sm:w-auto"
        appear
      >
        <div
          v-for="notification in notificationStore.notifications"
          :key="notification.id"
          class="pointer-events-auto w-full sm:w-96 max-w-md overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5"
        >
          <div class="p-4 sm:p-6">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <CheckCircleIcon
                  v-if="notification.type === 'success'"
                  class="h-6 w-6 text-green-400"
                  aria-hidden="true"
                />
                <ExclamationTriangleIcon
                  v-else-if="notification.type === 'warning'"
                  class="h-6 w-6 text-yellow-400"
                  aria-hidden="true"
                />
                <XCircleIcon
                  v-else-if="notification.type === 'error'"
                  class="h-6 w-6 text-red-400"
                  aria-hidden="true"
                />
                <InformationCircleIcon
                  v-else
                  class="h-6 w-6 text-blue-400"
                  aria-hidden="true"
                />
              </div>
              <div class="ml-3 flex-1 min-w-0 pr-2">
                <p class="text-sm font-medium text-gray-900 break-words leading-5">{{ notification.title }}</p>
                <p class="mt-1 text-sm text-gray-500 break-words leading-5">{{ notification.message }}</p>
              </div>
              <div class="ml-2 flex flex-shrink-0">
                <button
                  type="button"
                  @click="notificationStore.removeNotification(notification.id)"
                  class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                  <span class="sr-only">Fechar</span>
                  <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </div>
</template>

<script setup>
import {
  CheckCircleIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  InformationCircleIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import { useNotificationStore } from '../stores/notification'

const notificationStore = useNotificationStore()
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}
</style>
