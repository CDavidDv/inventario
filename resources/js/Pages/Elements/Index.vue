<template>
  <AppLayout title="Gestión de Elementos">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Gestión de Elementos
        </h2>
        <div class="flex space-x-2">
          <Link
            v-if="$page.props.auth.user.permissions.includes('create elements')"
            :href="route('elements.create')"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Elemento
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Estadísticas del sistema -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Total Elementos</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Stock Bajo</div>
                <div class="text-2xl font-bold text-yellow-600">{{ stats.low_stock }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Agotados</div>
                <div class="text-2xl font-bold text-red-600">{{ stats.out_of_stock }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Sobre Stock</div>
                <div class="text-2xl font-bold text-purple-600">{{ stats.over_stock }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input
                  v-model="form.search"
                  type="text"
                  placeholder="Nombre, descripción o ubicación..."
                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  @input="debouncedSearch"
                />
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado de Stock</label>
                <select
                  v-model="form.stock_status"
                  class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  @change="search"
                >
                  <option value="">Todos los estados</option>
                  <option value="low">Stock Bajo</option>
                  <option value="out">Agotado</option>
                  <option value="over">Sobre Stock</option>
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

        <!-- Tabla de elementos -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th 
                      scope="col" 
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                      @click="sort('name')"
                    >
                      Nombre
                      <span v-if="filters.sort === 'name'" class="ml-1">
                        {{ filters.direction === 'asc' ? '↑' : '↓' }}
                      </span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Categoría
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stock Actual
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Min/Max
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Ubicación
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="element in elements.data" :key="element.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div>
                          <div class="text-sm font-medium text-gray-900">{{ element.name }}</div>
                          <div class="text-sm text-gray-500">{{ element.description }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ element.category }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div class="flex items-center">
                        <span class="font-bold" :class="getStockClass(element)">
                          {{ element.current_stock }}
                        </span>
                        <span class="ml-1 text-gray-500">{{ element.unit }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ element.min_stock }} / {{ element.max_stock }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ element.location || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="getStatusBadgeClass(element)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                        {{ getStatusText(element) }}
                      </span>
                      <div v-if="element.stock_alerts && element.stock_alerts.length > 0" class="mt-1">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                          {{ element.stock_alerts.length }} alerta(s)
                        </span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <Link
                          :href="route('elements.show', element.id)"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          Ver
                        </Link>
                        <Link
                          v-if="$page.props.auth.user.permissions.includes('edit elements')"
                          :href="route('elements.edit', element.id)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          Editar
                        </Link>
                        <button
                          v-if="$page.props.auth.user.permissions.includes('delete elements')"
                          @click="confirmDelete(element)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Eliminar
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Paginación -->
            <div v-if="elements.links" class="mt-6">
              <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                  Mostrando {{ elements.from }} a {{ elements.to }} de {{ elements.total }} resultados
                </div>
                <div class="flex space-x-1">
                  <Link
                    v-for="link in elements.links"
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

    <!-- Modal de confirmación de eliminación -->
    <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
      <template #title>
        Eliminar Elemento
      </template>
      <template #content>
        ¿Estás seguro de que quieres eliminar el elemento "{{ elementToDelete?.name }}"? Esta acción no se puede deshacer.
      </template>
      <template #footer>
        <SecondaryButton @click="showDeleteModal = false">
          Cancelar
        </SecondaryButton>
        <DangerButton class="ml-3" @click="deleteElement" :disabled="processing">
          Eliminar
        </DangerButton>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import { debounce } from 'lodash'

const props = defineProps({
  elements: Object,
  categories: Array,
  filters: Object,
  stats: Object
})

const form = reactive({
  search: props.filters.search || '',
  category: props.filters.category || '',
  stock_status: props.filters.stock_status || ''
})

const showDeleteModal = ref(false)
const elementToDelete = ref(null)
const processing = ref(false)

const debouncedSearch = debounce(() => {
  search()
}, 500)

const search = () => {
  router.get(route('elements.index'), {
    search: form.search,
    category: form.category,
    stock_status: form.stock_status,
    sort: props.filters.sort,
    direction: props.filters.direction
  }, {
    preserveState: true,
    replace: true
  })
}

const sort = (field) => {
  const direction = props.filters.sort === field && props.filters.direction === 'asc' ? 'desc' : 'asc'
  
  router.get(route('elements.index'), {
    ...form,
    sort: field,
    direction
  }, {
    preserveState: true,
    replace: true
  })
}

const clearFilters = () => {
  form.search = ''
  form.category = ''
  form.stock_status = ''
  search()
}

const getStockClass = (element) => {
  if (element.current_stock <= 0) return 'text-red-600'
  if (element.current_stock <= element.min_stock) return 'text-yellow-600'
  if (element.current_stock >= element.max_stock && element.max_stock > 0) return 'text-purple-600'
  return 'text-green-600'
}

const getStatusBadgeClass = (element) => {
  if (element.current_stock <= 0) return 'bg-red-100 text-red-800'
  if (element.current_stock <= element.min_stock) return 'bg-yellow-100 text-yellow-800'
  if (element.current_stock >= element.max_stock && element.max_stock > 0) return 'bg-purple-100 text-purple-800'
  return 'bg-green-100 text-green-800'
}

const getStatusText = (element) => {
  if (element.current_stock <= 0) return 'Agotado'
  if (element.current_stock <= element.min_stock) return 'Stock Bajo'
  if (element.current_stock >= element.max_stock && element.max_stock > 0) return 'Sobre Stock'
  return 'Normal'
}

const confirmDelete = (element) => {
  elementToDelete.value = element
  showDeleteModal.value = true
}

const deleteElement = () => {
  if (!elementToDelete.value) return
  
  processing.value = true
  
  router.delete(route('elements.destroy', elementToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      elementToDelete.value = null
    },
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
