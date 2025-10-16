<template>
  <div class="relative ">
    <!-- Botón de notificaciones -->
    <button
      @click="toggleNotifications"
      class="relative flex items-center justify-center w-8 h-8 text-gray-100 md:text-gray-700 hover:text-gray-500 md:hover:text-green-500 focus:outline-none focus:text-gray-700"
    >
      <MessageCircle class="w-6 h-6" />
      
      <!-- Badge de notificaciones no leídas -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-red-500 rounded-full min-w-[18px]"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Panel de notificaciones -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="showNotifications"
        class="absolute right-0 z-50 mt-2 w-96 bg-white rounded-md shadow-lg overflow-hidden ring-1 ring-black ring-opacity-5"
      >
        <!-- Header -->
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
          <div class="flex items-center">
            <h3 class="text-lg font-medium text-gray-900">Notificaciones</h3>
            <span
              v-if="unreadCount > 0"
              class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
            >
              {{ unreadCount }} nuevas
            </span>
          </div>
          <div class="flex items-center space-x-2">
            <button
              v-if="notifications.length > 0"
              @click="markAllAsRead"
              class="text-sm text-blue-600 hover:text-blue-800"
            >
              Marcar todas como leídas
            </button>
            <button
              @click="closeNotifications"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Lista de notificaciones -->
        <div class="max-h-96 overflow-y-auto">
          <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-sm">No hay notificaciones</p>
          </div>

          <div v-else class="divide-y divide-gray-200">
            <div
              v-for="notification in notifications.slice(0, maxNotifications)"
              :key="notification.id"
              class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition-colors duration-150"
              :class="{ 'bg-blue-50': !notification.read }"
              @click="markAsRead(notification)"
            >
              <div class="flex items-start">
                <!-- Icono de la notificación -->
                <div class="flex-shrink-0">
                  <div 
                    class="w-8 h-8 rounded-full flex items-center justify-center"
                    :class="getNotificationBgClass(notification)"
                  >
                    <svg class="w-4 h-4" :class="getNotificationIconClass(notification)" fill="currentColor" viewBox="0 0 20 20">
                      <path v-if="notification.type === 'stock_alert'" fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                      <path v-else-if="notification.type === 'info'" fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                      <path v-else fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                </div>

                <!-- Contenido -->
                <div class="ml-3 flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">
                      {{ notification.title }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ formatTimeAgo(notification.created_at) }}
                    </p>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">
                    {{ notification.message }}
                  </p>
                  <div v-if="notification.data" class="mt-2 space-x-2">
                    <span
                      v-if="notification.data.item"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                    >
                      {{ notification.data.item.name }}
                    </span>
                    <span
                      v-if="notification.data.current_stock !== undefined"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                      :class="{
                        'bg-red-100 text-red-800': notification.data.current_stock === 0,
                        'bg-orange-100 text-orange-800': notification.data.current_stock > 0 && notification.data.current_stock <= notification.data.min_stock * 0.5,
                        'bg-yellow-100 text-yellow-800': notification.data.current_stock > notification.data.min_stock * 0.5 && notification.data.current_stock <= notification.data.min_stock
                      }"
                    >
                      Stock: {{ notification.data.current_stock }} / {{ notification.data.min_stock }}
                    </span>
                    <span
                      v-if="notification.data.alert_type"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                      :class="{
                        'bg-red-100 text-red-800': notification.data.alert_type === 'Agotado',
                        'bg-orange-100 text-orange-800': notification.data.alert_type === 'Crítico',
                        'bg-yellow-100 text-yellow-800': notification.data.alert_type === 'Mínimo',
                        'bg-blue-100 text-blue-800': notification.data.alert_type === 'Máximo'
                      }"
                    >
                      {{ notification.data.alert_type }}
                    </span>
                  </div>
                </div>

                <!-- Indicador de no leída -->
                <div v-if="!notification.read" class="flex-shrink-0 ml-2">
                  <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="notifications.length > maxNotifications" class="px-4 py-3 bg-gray-50 border-t border-gray-200">
          <button
            @click="viewAllNotifications"
            class="w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium"
          >
            Ver todas las notificaciones ({{ notifications.length }})
          </button>
        </div>
      </div>
    </Transition>

    <!-- Toast de nueva notificación -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="transform translate-y-2 opacity-0"
      enter-to-class="transform translate-y-0 opacity-100"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="transform translate-y-0 opacity-100"
      leave-to-class="transform translate-y-2 opacity-0"
    >
      <div
        v-if="showToast && lastNotification"
        class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div 
                class="w-8 h-8 rounded-full flex items-center justify-center"
                :class="getNotificationBgClass(lastNotification)"
              >
                <svg class="w-4 h-4" :class="getNotificationIconClass(lastNotification)" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
              <p class="text-sm font-medium text-gray-900">{{ lastNotification.title }}</p>
              <p class="mt-1 text-sm text-gray-500">{{ lastNotification.message }}</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="hideToast"
                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none"
              >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { MessageCircle } from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  initialNotifications: {
    type: Array,
    default: () => []
  }
})

const notifications = ref(props.initialNotifications || [])
const showNotifications = ref(false)
const showToast = ref(false)
const lastNotification = ref(null)
const maxNotifications = ref(5)

// Computed
const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read).length
})

// Métodos
const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
}

const closeNotifications = () => {
  showNotifications.value = false
}

const markAsRead = (notification) => {
  if (!notification.read) {
    notification.read = true
    
    // Si es una alerta de stock, extraer el ID correcto
    let alertId = notification.id
    if (notification.id.toString().startsWith('alert_')) {
      alertId = notification.id.replace('alert_', '')
    }
    
    // Llamar API para marcar como leída
    if (notification.data?.alert_id || alertId !== notification.id) {
      axios.post(`/api/notifications/${alertId}/mark-read`)
        .catch(error => {
          console.error('Error al marcar notificación como leída:', error)
          notification.read = false
        })
    }
  }
  
  // Navegar a los detalles del item
  navigateToItem(notification)
}

const markAllAsRead = () => {
  const unreadNotifications = notifications.value.filter(n => !n.read)
  unreadNotifications.forEach(n => n.read = true)
  
  axios.post('/api/notifications/mark-all-read')
    .catch(error => {
      console.error('Error al marcar todas las notificaciones como leídas:', error)
      unreadNotifications.forEach(n => n.read = false)
    })
}

const navigateToItem = (notification) => {
  closeNotifications()
  
  // Determinar la ruta según el tipo de item
  if (notification.data?.item_id) {
    // Es un item del sistema principal
    router.visit(`/items/${notification.data.item_id}`)
  } else if (notification.data?.item) {
    // Tiene datos del item, usar el ID
    router.visit(`/items/${notification.data.item.id}`)
  } else {
    // Si no tiene datos específicos, ir a la página de inventario
    router.visit('/inventario')
  }
}

const loadNotifications = async () => {
  try {
    const response = await axios.get('/api/notifications')
    if (response.data.success) {
      notifications.value = response.data.data
    }
  } catch (error) {
    console.error('Error cargando notificaciones:', error)
  }
}

const refreshNotifications = () => {
  loadNotifications()
}

const viewAllNotifications = () => {
  router.visit('/notifications')
}

const addNotification = (notification) => {
  notifications.value.unshift(notification)
  
  // Mostrar toast
  lastNotification.value = notification
  showToast.value = true
  
  // Ocultar toast después de 5 segundos
  setTimeout(() => {
    showToast.value = false
  }, 5000)

  // Reproducir sonido de notificación
  playNotificationSound()
}

const hideToast = () => {
  showToast.value = false
}

const playNotificationSound = () => {
  // Reproducir sonido solo si el usuario ha interactuado con la página
  try {
    const audio = new Audio('/sound/videoplayback.mp3')
    audio.volume = 0.3
    audio.play().catch(error => {
      console.log('No se pudo reproducir el sonido de notificación:', error)
    })
  } catch (error) {
    console.log('Error al reproducir sonido:', error)
  }
}

const getNotificationBgClass = (notification) => {
  switch (notification.type) {
    case 'stock_alert':
      return 'bg-red-100'
    case 'success':
      return 'bg-green-100'
    case 'warning':
      return 'bg-yellow-100'
    case 'info':
    default:
      return 'bg-blue-100'
  }
}

const getNotificationIconClass = (notification) => {
  switch (notification.type) {
    case 'stock_alert':
      return 'text-red-600'
    case 'success':
      return 'text-green-600'
    case 'warning':
      return 'text-yellow-600'
    case 'info':
    default:
      return 'text-blue-600'
  }
}

const formatTimeAgo = (date) => {
  const now = new Date()
  const notificationDate = new Date(date)
  const diffInMinutes = Math.floor((now - notificationDate) / (1000 * 60))
  
  if (diffInMinutes < 1) return 'Ahora'
  if (diffInMinutes < 60) return `${diffInMinutes}m`
  
  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours}h`
  
  const diffInDays = Math.floor(diffInHours / 24)
  return `${diffInDays}d`
}

// Variable para el intervalo de refresco
let refreshInterval = null

// Configurar WebSocket/Echo para escuchar eventos en tiempo real
onMounted(async () => {
  // Cargar notificaciones iniciales
  await loadNotifications()
  
  // Configurar refresco automático cada 30 segundos
  refreshInterval = setInterval(loadNotifications, 30000)
  
  // Escuchar eventos de alertas de stock
  if (window.Echo) {
    window.Echo.channel('stock-alerts')
      .listen('.stock.alert', (event) => {
        const notification = {
          id: Date.now(), // ID temporal
          type: 'stock_alert',
          title: 'Alerta de Stock',
          message: event.alert.message,
          data: {
            element: event.alert.element,
            alert_type: event.alert.alert_type
          },
          read: false,
          created_at: event.timestamp
        }
        
        addNotification(notification)
      })
  }
})

// Limpiar el intervalo al desmontar el componente
onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
  if (window.Echo) {
    window.Echo.leaveChannel('stock-alerts')
  }
})

// Cerrar panel de notificaciones al hacer clic fuera
onMounted(() => {
  document.addEventListener('click', (event) => {
    if (!event.target.closest('.relative')) {
      showNotifications.value = false
    }
  })
})
</script>
