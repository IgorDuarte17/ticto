import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref([])

  const addNotification = (notification) => {
    const id = Date.now()
    const newNotification = {
      id,
      type: 'info',
      title: '',
      message: '',
      duration: 5000,
      ...notification
    }
    
    notifications.value.push(newNotification)
    
    if (newNotification.duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, newNotification.duration)
    }
    
    return id
  }

  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  const success = (message, title = 'Sucesso') => {
    return addNotification({ type: 'success', title, message })
  }

  const error = (message, title = 'Erro') => {
    return addNotification({ type: 'error', title, message, duration: 7000 })
  }

  const warning = (message, title = 'Atenção') => {
    return addNotification({ type: 'warning', title, message })
  }

  const info = (message, title = 'Informação') => {
    return addNotification({ type: 'info', title, message })
  }

  const clear = () => {
    notifications.value = []
  }

  return {
    notifications,
    addNotification,
    removeNotification,
    success,
    error,
    warning,
    info,
    clear
  }
})
