<template>
  <AppLayout title="Gestión de Precios">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Gestión de Precios de Elementos
        </h2>
        <div class="flex space-x-2">
          <Link
            v-if="$page.props.auth.user.permissions.includes('create prices')"
            :href="route('element-prices.create')"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Precio
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Total Precios</div>
                <div class="text-2xl font-bold text-gray-900">{{ elementPrices.total || elementPrices.data?.length || 0 }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Margen Promedio</div>
                <div class="text-2xl font-bold text-green-600">{{ averageMargin.toFixed(1) }}%</div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Proveedores Únicos</div>
                <div class="text-2xl font-bold text-purple-600">{{ uniqueSuppliers }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Elementos Únicos</div>
                <div class="text-2xl font-bold text-orange-600">{{ uniqueElements }}</div>
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
                  placeholder="Elemento o proveedor..."
                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  @input="debouncedSearch"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor</label>
                <select
                  v-model="form.supplier_id"
                  class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  @change="search"
                >
                  <option value="">Todos los proveedores</option>
                  <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                    {{ supplier.name }}
                  </option>
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Moneda</label>
                <select
                  v-model="form.currency"
                  class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  @change="search"
                >
                  <option value="">Todas las monedas</option>
                  <option value="MXN">MXN</option>
                  <option value="USD">USD</option>
                  <option value="EUR">EUR</option>
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

        <!-- Tabla de precios -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th 
                      scope="col" 
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                      @click="sort('element_name')"
                    >
                      Elemento
                      <span v-if="filters.sort === 'element_name'" class="ml-1">
                        {{ filters.direction === 'asc' ? '↑' : '↓' }}
                      </span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Proveedor
                    </th>
                    <th 
                      scope="col" 
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                      @click="sort('purchase_price')"
                    >
                      Precio Compra
                      <span v-if="filters.sort === 'purchase_price'" class="ml-1">
                        {{ filters.direction === 'asc' ? '↑' : '↓' }}
                      </span>
                    </th>
                    <th 
                      scope="col" 
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                      @click="sort('selling_price')"
                    >
                      Precio Venta
                      <span v-if="filters.sort === 'selling_price'" class="ml-1">
                        {{ filters.direction === 'asc' ? '↑' : '↓' }}
                      </span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Margen
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stock Actual
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Valor Stock
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="price in elementPrices.data" :key="price.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div>
                          <div class="text-sm font-medium text-gray-900">
                            <Link 
                              :href="route('elements.show', price.element.id)"
                              class="text-blue-600 hover:text-blue-800"
                            >
                              {{ price.element.name }}
                            </Link>
                          </div>
                          <div class="text-sm text-gray-500">{{ price.element.category }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3">
                          {{ price.supplier.name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                          <div class="text-sm font-medium text-gray-900">
                            <Link 
                              :href="route('suppliers.show', price.supplier.id)"
                              class="text-blue-600 hover:text-blue-800"
                            >
                              {{ price.supplier.name }}
                            </Link>
                          </div>
                          <div class="text-sm text-gray-500">{{ price.supplier.country }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-green-600">
                        {{ price.currency }} ${{ price.purchase_price?.toFixed(2) || 'N/A' }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-blue-600">
                        {{ price.currency }} ${{ price.selling_price?.toFixed(2) || 'N/A' }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div v-if="price.purchase_price && price.selling_price">
                        <div 
                          class="text-sm font-medium"
                          :class="getMarginClass(calculateMargin(price))"
                        >
                          {{ calculateMargin(price).toFixed(1) }}%
                        </div>
                        <div class="text-xs text-gray-500">
                          ${{ (price.selling_price - price.purchase_price).toFixed(2) }}
                        </div>
                      </div>
                      <div v-else class="text-sm text-gray-400">N/A</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">
                        {{ price.element.current_stock }} {{ price.element.unit }}
                      </div>
                      <div class="text-sm" :class="getStockStatusClass(price.element)">
                        {{ getStockStatus(price.element) }}
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div v-if="price.purchase_price" class="text-sm font-medium text-purple-600">
                        ${{ (price.element.current_stock * price.purchase_price).toFixed(2) }}
                      </div>
                      <div v-else class="text-sm text-gray-400">N/A</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <Link
                          v-if="$page.props.auth.user.permissions.includes('edit prices')"
                          :href="route('element-prices.edit', price.id)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          Editar
                        </Link>
                        <button
                          v-if="$page.props.auth.user.permissions.includes('delete prices')"
                          @click="confirmDelete(price)"
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
            <div v-if="elementPrices.links" class="mt-6">
              <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                  Mostrando {{ elementPrices.from }} a {{ elementPrices.to }} de {{ elementPrices.total }} precios
                </div>
                <div class="flex space-x-1">
                  <Link
                    v-for="link in elementPrices.links"
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
        Eliminar Precio
      </template>
      <template #content>
        ¿Estás seguro de que quieres eliminar el precio de "{{ priceToDelete?.element?.name }}" 
        del proveedor "{{ priceToDelete?.supplier?.name }}"? Esta acción no se puede deshacer.
      </template>
      <template #footer>
        <SecondaryButton @click="showDeleteModal = false">
          Cancelar
        </SecondaryButton>
        <DangerButton class="ml-3" @click="deletePrice" :disabled="processing">
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
  elementPrices: Object,
  suppliers: Array,
  categories: Array,
  filters: Object
})

const form = reactive({
  search: props.filters.search || '',
  supplier_id: props.filters.supplier_id || '',
  category: props.filters.category || '',
  currency: props.filters.currency || ''
})

const showDeleteModal = ref(false)
const priceToDelete = ref(null)
const processing = ref(false)

// Computed properties
const averageMargin = computed(() => {
  if (!props.elementPrices.data || props.elementPrices.data.length === 0) return 0
  
  const marginsWithValues = props.elementPrices.data
    .filter(price => price.purchase_price && price.selling_price)
    .map(price => calculateMargin(price))
  
  if (marginsWithValues.length === 0) return 0
  
  return marginsWithValues.reduce((sum, margin) => sum + margin, 0) / marginsWithValues.length
})

const uniqueSuppliers = computed(() => {
  if (!props.elementPrices.data) return 0
  const supplierIds = new Set(props.elementPrices.data.map(price => price.supplier.id))
  return supplierIds.size
})

const uniqueElements = computed(() => {
  if (!props.elementPrices.data) return 0
  const elementIds = new Set(props.elementPrices.data.map(price => price.element.id))
  return elementIds.size
})

const debouncedSearch = debounce(() => {
  search()
}, 500)

// Methods
const search = () => {
  router.get(route('element-prices.index'), {
    search: form.search,
    supplier_id: form.supplier_id,
    category: form.category,
    currency: form.currency,
    sort: props.filters.sort,
    direction: props.filters.direction
  }, {
    preserveState: true,
    replace: true
  })
}

const sort = (field) => {
  const direction = props.filters.sort === field && props.filters.direction === 'asc' ? 'desc' : 'asc'
  
  router.get(route('element-prices.index'), {
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
  form.supplier_id = ''
  form.category = ''
  form.currency = ''
  search()
}

const calculateMargin = (price) => {
  if (!price.purchase_price || !price.selling_price) return 0
  return ((price.selling_price - price.purchase_price) / price.purchase_price) * 100
}

const getMarginClass = (margin) => {
  if (margin >= 30) return 'text-green-600'
  if (margin >= 15) return 'text-blue-600'
  if (margin >= 5) return 'text-yellow-600'
  return 'text-red-600'
}

const getStockStatus = (element) => {
  if (element.current_stock <= 0) return 'Agotado'
  if (element.current_stock <= element.min_stock) return 'Stock Bajo'
  return 'Normal'
}

const getStockStatusClass = (element) => {
  if (element.current_stock <= 0) return 'text-red-600'
  if (element.current_stock <= element.min_stock) return 'text-yellow-600'
  return 'text-green-600'
}

const confirmDelete = (price) => {
  priceToDelete.value = price
  showDeleteModal.value = true
}

const deletePrice = () => {
  if (!priceToDelete.value) return
  
  processing.value = true
  
  router.delete(route('element-prices.destroy', priceToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      priceToDelete.value = null
    },
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
