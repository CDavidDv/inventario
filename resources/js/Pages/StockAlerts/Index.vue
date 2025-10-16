<template>
  <AppLayout title="Alertas de Stock">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Alertas de Stock
        </h2>
        <div class="flex space-x-2">
          <button
            @click="markAllAsRead"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
            :disabled="unreadAlerts === 0"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Marcar Todas Leídas
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Estadísticas de alertas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 7H4l5-5v5z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Total Alertas</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.total || 0 }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Críticas</div>
                <div class="text-2xl font-bold text-red-600">{{ stats.critical || 0 }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Sin Leer</div>
                <div class="text-2xl font-bold text-orange-600">{{ unreadAlerts }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Resueltas</div>
                <div class="text-2xl font-bold text-green-600">{{ stats.resolved || 0 }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input
                  v-model="form.search"
                  type="text"
                  placeholder="Nombre del elemento..."
                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  @input="debouncedSearch"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Alerta</label>
                <select
                  v-model="form.alert_type"
                  class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  @change="search"
                >
                  <option value="">Todos los tipos</option>
                  <option value="Agotado">Agotado</option>
                  <option value="Crítico">Crítico</option>
                  <option value="Mínimo">Mínimo</option>
                  <option value="Máximo">Máximo</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select
                  v-model="form.status"
                  class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  @change="search"
                >
                  <option value="">Todos los estados</option>
                  <option value="unread">Sin Leer</option>
                  <option value="read">Leídas</option>
                  <option value="unresolved">Sin Resolver</option>
                  <option value="resolved">Resueltas</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                <select
                  v-model="form.category"
                  class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  @change="search"
                >
                  <option value="">Todas las categorías</option>
                  <option v-for="category in categories" :key="category" :value="category">
                    {{ category }}
                  </option>
                </select>
              </div>

              <div class="flex items-end">
                <button
                  @click="clearFilters"
                  class="w-full bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                >
                  Limpiar Filtros
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de alertas -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div v-if="alerts.data.length === 0" class="text-center py-8 text-gray-500">
              <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p>No hay alertas que coincidan con los filtros</p>
              <p class="text-sm">Intenta ajustar los criterios de búsqueda</p>
            </div>

            <div v-else class="space-y-3">
              <div
                v-for="alert in alerts.data"
                :key="alert.id"
                class="border rounded-lg p-4 transition-all duration-200 hover:shadow-md cursor-pointer"
                :class="[
                  getAlertBorderClass(alert),
                  !alert.is_read ? 'bg-blue-50' : 'bg-white'
                ]"
                @click="handleAlertClick(alert)"
              >
                <div class="flex items-start justify-between">
                  <div class="flex items-start space-x-3 flex-1">
                    <!-- Icono de alerta -->
                    <div 
                      class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                      :class="getAlertIconBgClass(alert.alert_type)"
                    >
                      <svg class="w-5 h-5" :class="getAlertIconClass(alert.alert_type)" fill="currentColor" viewBox="0 0 20 20">
                        <path v-if="alert.alert_type === 'Agotado'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        <path v-else-if="alert.alert_type === 'Crítico'" fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        <path v-else-if="alert.alert_type === 'Mínimo'" fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        <path v-else fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                      </svg>
                    </div>

                    <!-- Contenido de la alerta -->
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center space-x-2 mb-1">
                        <h4 class="text-sm font-medium text-gray-900 truncate">
                          {{ alert.element.name }}
                        </h4>
                        <span 
                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          :class="getAlertTypeBadgeClass(alert.alert_type)"
                        >
                          {{ alert.alert_type }}
                        </span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                          {{ alert.element.category }}
                        </span>
                      </div>
                      
                      <p class="text-sm text-gray-600 mb-2">{{ alert.message }}</p>
                      
                      <div class="flex items-center justify-between text-xs text-gray-500">
                        <div class="flex items-center space-x-4">
                          <span>Stock actual: {{ alert.element.current_stock }} {{ alert.element.unit }}</span>
                          <span>Mínimo: {{ alert.element.min_stock }}</span>
                          <span v-if="alert.element.max_stock > 0">Máximo: {{ alert.element.max_stock }}</span>
                        </div>
                        <span>{{ formatTimeAgo(alert.date) }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Acciones -->
                  <div class="flex items-center space-x-2 ml-4">
                    <!-- Indicador de no leída -->
                    <div v-if="!alert.is_read" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    
                    <!-- Badge de estado -->
                    <span 
                      v-if="alert.is_resolved"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                    >
                      Resuelta
                    </span>

                    <!-- Botones de acción -->
                    <div class="flex space-x-1">
                      <button
                        v-if="!alert.is_read"
                        @click.stop="markAsRead(alert)"
                        class="text-blue-600 hover:text-blue-800 p-1"
                        title="Marcar como leída"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </button>
                      
                      <button
                        v-if="!alert.is_resolved"
                        @click.stop="markAsResolved(alert)"
                        class="text-green-600 hover:text-green-800 p-1"
                        title="Marcar como resuelta"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                      </button>
                      
                      <Link
                        :href="route('elements.show', alert.element.id)"
                        class="text-gray-600 hover:text-gray-800 p-1"
                        title="Ver elemento"
                        @click.stop
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                      </Link>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Paginación -->
            <div v-if="alerts.links" class="mt-6">
              <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                  Mostrando {{ alerts.from }} a {{ alerts.to }} de {{ alerts.total }} alertas
                </div>
                <div class="flex space-x-1">
                  <Link
                    v-for="link in alerts.links"
                    :key="link.label"
                    :href="link.url"
                    :class="[
                      'px-3 py-2 text-sm rounded-md',
                      link.active
                        ? 'bg-blue-500 text-white'
                        : link.url
                        ? 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { debounce } from 'lodash'

const props = defineProps({
  alerts: Object,
  categories: Array,
  filters: Object,
  stats: Object
})

const form = reactive({
  search: props.filters.search || '',
  alert_type: props.filters.alert_type || '',
  status: props.filters.status || '',
  category: props.filters.category || ''
})

const unreadAlerts = computed(() => {
  return props.alerts.data.filter(alert => !alert.is_read).length
})

const debouncedSearch = debounce(() => {
  search()
}, 500)

const search = () => {
  router.get(route('stock-alerts.index'), {
    search: form.search,
    alert_type: form.alert_type,
    status: form.status,
    category: form.category
  }, {
    preserveState: true,
    replace: true
  })
}

const clearFilters = () => {
  form.search = ''
  form.alert_type = ''
  form.status = ''
  form.category = ''
  search()
}

// Métodos para alertas
const getAlertBorderClass = (alert) => {
  switch (alert.alert_type) {
    case 'Agotado': return 'border-red-200'
    case 'Crítico': return 'border-orange-200'
    case 'Mínimo': return 'border-yellow-200'
    case 'Máximo': return 'border-purple-200'
    default: return 'border-gray-200'
  }
}

const getAlertIconBgClass = (type) => {
  switch (type) {
    case 'Agotado': return 'bg-red-100'
    case 'Crítico': return 'bg-orange-100'
    case 'Mínimo': return 'bg-yellow-100'
    case 'Máximo': return 'bg-purple-100'
    default: return 'bg-gray-100'
  }
}

const getAlertIconClass = (type) => {
  switch (type) {
    case 'Agotado': return 'text-red-600'
    case 'Crítico': return 'text-orange-600'
    case 'Mínimo': return 'text-yellow-600'
    case 'Máximo': return 'text-purple-600'
    default: return 'text-gray-600'
  }
}

const getAlertTypeBadgeClass = (type) => {
  switch (type) {
    case 'Agotado': return 'bg-red-100 text-red-800'
    case 'Crítico': return 'bg-orange-100 text-orange-800'
    case 'Mínimo': return 'bg-yellow-100 text-yellow-800'
    case 'Máximo': return 'bg-purple-100 text-purple-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const formatTimeAgo = (date) => {
  const now = new Date()
  const alertDate = new Date(date)
  const diffInMinutes = Math.floor((now - alertDate) / (1000 * 60))
  
  if (diffInMinutes < 1) return 'Ahora'
  if (diffInMinutes < 60) return `${diffInMinutes}m`
  
  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) return `${diffInHours}h`
  
  const diffInDays = Math.floor(diffInHours / 24)
  return `${diffInDays}d`
}

const handleAlertClick = (alert) => {
  if (!alert.is_read) {
    markAsRead(alert)
  }
  // Opcionalmente navegar al elemento
  router.visit(route('elements.show', alert.element.id))
}

const markAsRead = (alert) => {
  router.post(route('stock-alerts.mark-read', alert.id), {}, {
    preserveState: true
  })
}

const markAsResolved = (alert) => {
  router.post(route('stock-alerts.resolve', alert.id), {}, {
    preserveState: true
  })
}

const markAllAsRead = () => {
  router.post(route('stock-alerts.bulk-mark-read'), {}, {
    preserveState: true
  })
}
</script>
