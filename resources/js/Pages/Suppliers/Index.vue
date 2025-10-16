<template>
  <AppLayout title="Gestión de Proveedores">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Gestión de Proveedores
        </h2>
        <div class="flex space-x-2">
          <Link
            :href="route('suppliers.create')"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Proveedor
          </Link>
        </div>
      </div>
    </template>

    <Container class="">
      <!-- Estadísticas del sistema -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border-l-4 border-l-blue-500 overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-500">Total Proveedores</div>
              <div class="text-2xl font-bold text-gray-900">{{ suppliers?.total || suppliers?.data?.length || 0 }}</div>
            </div>
          </div>
        </div>

        <div class="border-l-4 border-green-500 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-500">Proveedores Activos</div>
              <div class="text-2xl font-bold text-green-600">{{ getActiveSuppliers() }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white border-l-4 border-red-500 overflow -hidden shadow-xl sm:rounded-lg p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-500">Proveedores Inactivos</div>
              <div class="text-2xl font-bold text-red-600">{{ getInactiveSuppliers() }}</div>
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
                placeholder="Nombre, email o teléfono..."
                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                @input="debouncedSearch"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
              <select
                v-model="form.status"
                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                @change="search"
              >
                <option value="">Todos los estados</option>
                <option value="active">Activos</option>
                <option value="inactive">Inactivos</option>
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

      <!-- Tabla de proveedores -->
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
                    Proveedor
                    <span v-if="filters.sort === 'name'" class="ml-1">
                      {{ filters.direction === 'asc' ? '↑' : '↓' }}
                    </span>
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contacto
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Website
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
                <tr v-for="supplier in suppliers?.data" :key="supplier.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                        {{ supplier.name.charAt(0).toUpperCase() }}
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ supplier.name }}</div>
                        <div class="text-sm text-gray-500">{{ supplier.email || 'Sin email' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ supplier.phone || '-' }}</div>
                    <div class="text-sm text-gray-500">{{ supplier.address || '-' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <a 
                      v-if="supplier.website"
                      :href="supplier.website"
                      target="_blank"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      {{ supplier.website }}
                    </a>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="getSupplierStatusClass(supplier)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ getSupplierStatus(supplier) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <button
                        @click="toggleStatus(supplier)"
                        :class="supplier.status === 'active' ? 'text-orange-600 hover:text-orange-900' : 'text-green-600 hover:text-green-900'"
                      >
                        <div :class="supplier.status === 'active' ? 'bg-green-500' : 'bg-red-500'" class="size-5 rounded-full"></div>
                        
                      </button>
                      <Link
                        :href="route('suppliers.show', supplier.id)"
                        class="text-blue-600 hover:text-blue-900"
                      >
                        <Eye class="size-5" />
                      </Link>
                      <Link
                        :href="route('suppliers.edit', supplier.id)"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        <Pencil class="size-5" />
                      </Link>
                      <button
                        @click="confirmDelete(supplier)"
                        class="text-red-600 hover:text-red-900"
                      >
                        <Trash2 class="size-5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div v-if="suppliers?.links" class="mt-6">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-700">
                Mostrando {{ suppliers?.from }} a {{ suppliers?.to }} de {{ suppliers?.total }} resultados
              </div>
              <div class="flex space-x-1">
                <template v-for="link in suppliers?.links" :key="link?.label">
                  <Link
                    v-if="link.url"
                    :href="link.url"
                    :class="[
                      'px-3 py-2 text-sm rounded-md',
                      link.active
                        ? 'bg-blue-500 text-white'
                        : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                    ]"
                    v-html="link.label"
                  />
                  <span
                    v-else
                    class="px-3 py-2 text-sm rounded-md bg-gray-100 text-gray-400 cursor-not-allowed"
                    v-html="link.label"
                  />
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Container>


    <!-- Modal de confirmación de eliminación -->
    <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
      <template #title>
        Eliminar Proveedor
      </template>
      <template #content>
        ¿Estás seguro de que quieres eliminar el proveedor "{{ supplierToDelete?.name }}"? 
        Esta acción no se puede deshacer.
      </template>
      <template #footer>
        <SecondaryButton @click="showDeleteModal = false">
          Cancelar
        </SecondaryButton>
        <DangerButton class="ml-3" @click="deleteSupplier" :disabled="processing">
          Eliminar
        </DangerButton>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import { debounce } from 'lodash'
import { Eye, Pencil, Trash2 } from 'lucide-vue-next';
import Container from '@/Components/Container.vue'

const props = defineProps({
  suppliers: Object,
  filters: Object
})

const form = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || ''
})

const showDeleteModal = ref(false)
const supplierToDelete = ref(null)
const processing = ref(false)

const debouncedSearch = debounce(() => {
  search()
}, 500)

const search = () => {
  router.get(route('suppliers.index'), {
    search: form.search,
    status: form.status,
    sort: props.filters?.sort,
    direction: props.filters?.direction
  }, {
    preserveState: true,
    replace: true
  })
}

const sort = (field) => {
  const direction = props.filters?.sort === field && props.filters?.direction === 'asc' ? 'desc' : 'asc'
  
  router.get(route('suppliers.index'), {
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
  form.status = ''
  search()
}

// Métodos para estadísticas
const getActiveSuppliers = () => {
  if (!props.suppliers?.data) return 0
  return props.suppliers.data.filter(supplier => supplier.status === 'active').length
}

const getInactiveSuppliers = () => {
  if (!props.suppliers?.data) return 0
  return props.suppliers.data.filter(supplier => supplier.status === 'inactive').length
}

// Métodos para la tabla
const getSupplierStatus = (supplier) => {
  return supplier.status === 'active' ? 'Activo' : 'Inactivo'
}

const getSupplierStatusClass = (supplier) => {
  return supplier.status === 'active'
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800'
}

const toggleStatus = (supplier) => {
  router.post(route('suppliers.toggle-status', supplier.id), {}, {
    preserveScroll: true
  })
}

const confirmDelete = (supplier) => {
  supplierToDelete.value = supplier
  showDeleteModal.value = true
}

const deleteSupplier = () => {
  if (!supplierToDelete.value) return
  
  processing.value = true
  
  router.delete(route('suppliers.destroy', supplierToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      supplierToDelete.value = null
    },
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>