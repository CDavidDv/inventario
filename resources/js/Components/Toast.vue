<template>
  <Teleport to="body">
    <div class="fixed top-4 right-4 z-50 space-y-2 max-w-sm w-full">
      <TransitionGroup
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform translate-x-full opacity-0"
        enter-to-class="transform translate-x-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="transform translate-x-0 opacity-100"
        leave-to-class="transform translate-x-full opacity-0"
        move-class="transition-transform duration-300"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
          :class="getToastClasses(toast)"
        >
          <div class="p-4">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <component 
                  :is="getIcon(toast.type)" 
                  class="h-6 w-6" 
                  :class="getIconClasses(toast.type)"
                />
              </div>
              <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium text-gray-900">{{ toast.title }}</p>
                <p v-if="toast.message" class="mt-1 text-sm text-gray-500">{{ toast.message }}</p>
              </div>
              <div class="ml-4 flex-shrink-0 flex">
                <button
                  @click="removeToast(toast.id)"
                  class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span class="sr-only">Cerrar</span>
                  <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Barra de progreso -->
          <div 
            v-if="toast.duration && toast.duration > 0"
            class="h-1 bg-gray-200"
          >
            <div 
              class="h-full transition-all duration-100 ease-linear"
              :class="getProgressBarClasses(toast.type)"
              :style="{ width: `${getProgressPercentage(toast)}%` }"
            ></div>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'

// Store global de toasts
const toasts = ref([])
const timers = new Map()

// Tipos de iconos
const CheckCircleIcon = `<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>`
const ExclamationTriangleIcon = `<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>`
const XCircleIcon = `<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>`
const InformationCircleIcon = `<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>`

const getIcon = (type) => {
  const icons = {
    success: 'svg',
    error: 'svg',
    warning: 'svg',
    info: 'svg'
  }
  return icons[type] || 'svg'
}

const getIconPath = (type) => {
  const paths = {
    success: CheckCircleIcon,
    error: XCircleIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon
  }
  return paths[type] || InformationCircleIcon
}

const getToastClasses = (toast) => {
  const classes = {
    success: 'border-l-4 border-green-400',
    error: 'border-l-4 border-red-400',
    warning: 'border-l-4 border-yellow-400',
    info: 'border-l-4 border-blue-400'
  }
  return classes[toast.type] || classes.info
}

const getIconClasses = (type) => {
  const classes = {
    success: 'text-green-400',
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400'
  }
  return classes[type] || classes.info
}

const getProgressBarClasses = (type) => {
  const classes = {
    success: 'bg-green-400',
    error: 'bg-red-400',
    warning: 'bg-yellow-400',
    info: 'bg-blue-400'
  }
  return classes[type] || classes.info
}

const getProgressPercentage = (toast) => {
  if (!toast.duration || !toast.startTime) return 100
  
  const elapsed = Date.now() - toast.startTime
  const percentage = Math.max(0, 100 - (elapsed / toast.duration) * 100)
  return percentage
}

const addToast = (toast) => {
  const id = Date.now() + Math.random()
  const newToast = {
    id,
    type: toast.type || 'info',
    title: toast.title,
    message: toast.message,
    duration: toast.duration || 5000,
    startTime: Date.now()
  }
  
  toasts.value.unshift(newToast)
  
  // Auto-remover después de la duración especificada
  if (newToast.duration > 0) {
    const timer = setTimeout(() => {
      removeToast(id)
    }, newToast.duration)
    
    timers.set(id, timer)
  }
  
  return id
}

const removeToast = (id) => {
  const index = toasts.value.findIndex(toast => toast.id === id)
  if (index > -1) {
    toasts.value.splice(index, 1)
  }
  
  // Limpiar timer si existe
  if (timers.has(id)) {
    clearTimeout(timers.get(id))
    timers.delete(id)
  }
}

const clearAllToasts = () => {
  toasts.value = []
  timers.forEach(timer => clearTimeout(timer))
  timers.clear()
}

// Funciones de conveniencia
const success = (title, message, duration) => {
  return addToast({ type: 'success', title, message, duration })
}

const error = (title, message, duration) => {
  return addToast({ type: 'error', title, message, duration })
}

const warning = (title, message, duration) => {
  return addToast({ type: 'warning', title, message, duration })
}

const info = (title, message, duration) => {
  return addToast({ type: 'info', title, message, duration })
}

// Limpiar timers al desmontar
onUnmounted(() => {
  timers.forEach(timer => clearTimeout(timer))
  timers.clear()
})

// Exponer métodos para uso global
defineExpose({
  addToast,
  removeToast,
  clearAllToasts,
  success,
  error,
  warning,
  info
})

// Hacer disponible globalmente
if (typeof window !== 'undefined') {
  window.$toast = {
    success,
    error,
    warning,
    info,
    add: addToast,
    remove: removeToast,
    clear: clearAllToasts
  }
}
</script>

<style scoped>
svg {
  fill: currentColor;
}
</style>
