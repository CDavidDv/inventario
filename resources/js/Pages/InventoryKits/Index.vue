<template>
  <AppLayout title="Gestión de Kits">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Gestión de Kits de Inventario
        </h2>
        <div class="flex space-x-2">
          <Link
            v-if="$page.props.auth.user.permissions.includes('create kits')"
            :href="route('inventory-kits.create')"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Kit
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Estadísticas del sistema -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Total Kits</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
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
                <div class="text-sm font-medium text-gray-500">Kits Disponibles</div>
                <div class="text-2xl font-bold text-green-600">{{ stats.available }}</div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Categorías</div>
                <div class="text-2xl font-bold text-purple-600">{{ stats.categories }}</div>
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
                  placeholder="Nombre o descripción..."
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
                <label class="block text-sm font-medium text-gray-700 mb-2">Disponibilidad</label>
                <select
                  v-model="form.availability"
                  class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  @change="search"
                >
                  <option value="">Todos los estados</option>
                  <option value="available">Disponibles</option>
                  <option value="unavailable">No Disponibles</option>
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

        <!-- Tabla de kits -->
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
                      Componentes
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Disponibilidad
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Costo Total
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="kit in kits.data" :key="kit.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div>
                          <div class="text-sm font-medium text-gray-900">{{ kit.name }}</div>
                          <div class="text-sm text-gray-500">{{ kit.description }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <span v-if="kit.category" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ kit.category }}
                      </span>
                      <span v-else class="text-gray-400">Sin categoría</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ kit.kit_components ? kit.kit_components.length : 0 }} elementos
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="flex-1 mr-3">
                          <div class="text-sm font-medium">
                            <span :class="kit.can_be_assembled ? 'text-green-600' : 'text-red-600'">
                              {{ kit.can_be_assembled ? 'Disponible' : 'No Disponible' }}
                            </span>
                          </div>
                          <div class="text-xs text-gray-500">
                            Máximo: {{ kit.max_assemblies }} unidades
                          </div>
                        </div>
                        <div>
                          <span 
                            :class="kit.can_be_assembled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          >
                            {{ kit.can_be_assembled ? '✓' : '✗' }}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div>
                        <div class="text-sm font-medium text-green-600">
                          ${{ kit.total_cost?.toFixed(2) || '0.00' }}
                        </div>
                        <div class="text-xs text-blue-600">
                          PV: ${{ kit.total_selling_price?.toFixed(2) || '0.00' }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <Link
                          :href="route('inventory-kits.show', kit.id)"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          Ver
                        </Link>
                        <Link
                          v-if="$page.props.auth.user.permissions.includes('edit kits')"
                          :href="route('inventory-kits.edit', kit.id)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          Editar
                        </Link>
                        <button
                          v-if="$page.props.auth.user.permissions.includes('assemble kits') && kit.can_be_assembled"
                          @click="openAssembleModal(kit)"
                          class="text-green-600 hover:text-green-900"
                        >
                          Ensamblar
                        </button>
                        <button
                          v-if="$page.props.auth.user.permissions.includes('delete kits')"
                          @click="confirmDelete(kit)"
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
            <div v-if="kits.links" class="mt-6">
              <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                  Mostrando {{ kits.from }} a {{ kits.to }} de {{ kits.total }} resultados
                </div>
                <div class="flex space-x-1">
                  <Link
                    v-for="link in kits.links"
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

    <!-- Modal de ensamblaje -->
    <DialogModal :show="showAssembleModal" @close="showAssembleModal = false">
      <template #title>
        Ensamblar Kit - {{ selectedKit?.name }}
      </template>
      <template #content>
        <div class="space-y-4">
          <div class="text-center p-4 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500">Kits disponibles para ensamblar</div>
            <div class="text-2xl font-bold text-green-600">{{ selectedKit?.max_assemblies }}</div>
          </div>
          
          <div>
            <InputLabel for="quantity" value="Cantidad a Ensamblar" />
            <TextInput
              id="quantity"
              v-model="assembleForm.quantity"
              type="number"
              :max="selectedKit?.max_assemblies"
              min="1"
              class="mt-1 block w-full"
              placeholder="1"
            />
            <InputError class="mt-2" :message="assembleForm.errors.quantity" />
          </div>
          
          <div>
            <InputLabel for="reason" value="Razón del Ensamblaje (Opcional)" />
            <TextInput
              id="reason"
              v-model="assembleForm.reason"
              type="text"
              class="mt-1 block w-full"
              placeholder="Ej: Pedido especial, Producción programada"
            />
            <InputError class="mt-2" :message="assembleForm.errors.reason" />
          </div>

          <div v-if="selectedKit" class="p-4 bg-blue-50 rounded-lg">
            <h4 class="font-medium text-blue-900 mb-2">Componentes del Kit:</h4>
            <div class="space-y-2">
              <div 
                v-for="component in selectedKit.kit_components" 
                :key="component.id"
                class="flex justify-between text-sm"
              >
                <span>{{ component.element.name }}</span>
                <span>{{ component.quantity }} {{ component.element.unit }}</span>
              </div>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <SecondaryButton @click="showAssembleModal = false">
          Cancelar
        </SecondaryButton>
        <PrimaryButton 
          class="ml-3" 
          @click="assembleKit" 
          :class="{ 'opacity-25': assembleForm.processing }" 
          :disabled="assembleForm.processing"
        >
          Ensamblar
        </PrimaryButton>
      </template>
    </DialogModal>

    <!-- Modal de confirmación de eliminación -->
    <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
      <template #title>
        Eliminar Kit
      </template>
      <template #content>
        ¿Estás seguro de que quieres eliminar el kit "{{ kitToDelete?.name }}"? Esta acción no se puede deshacer.
      </template>
      <template #footer>
        <SecondaryButton @click="showDeleteModal = false">
          Cancelar
        </SecondaryButton>
        <DangerButton class="ml-3" @click="deleteKit" :disabled="processing">
          Eliminar
        </DangerButton>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import { debounce } from 'lodash'

const props = defineProps({
  kits: Object,
  categories: Array,
  filters: Object,
  stats: Object
})

const form = reactive({
  search: props.filters.search || '',
  category: props.filters.category || '',
  availability: props.filters.availability || ''
})

const showAssembleModal = ref(false)
const showDeleteModal = ref(false)
const selectedKit = ref(null)
const kitToDelete = ref(null)
const processing = ref(false)

const assembleForm = useForm({
  quantity: 1,
  reason: ''
})

const debouncedSearch = debounce(() => {
  search()
}, 500)

const search = () => {
  router.get(route('inventory-kits.index'), {
    search: form.search,
    category: form.category,
    availability: form.availability,
    sort: props.filters.sort,
    direction: props.filters.direction
  }, {
    preserveState: true,
    replace: true
  })
}

const sort = (field) => {
  const direction = props.filters.sort === field && props.filters.direction === 'asc' ? 'desc' : 'asc'
  
  router.get(route('inventory-kits.index'), {
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
  form.availability = ''
  search()
}

const openAssembleModal = (kit) => {
  selectedKit.value = kit
  assembleForm.quantity = 1
  assembleForm.reason = ''
  showAssembleModal.value = true
}

const assembleKit = () => {
  if (!selectedKit.value) return
  
  assembleForm.post(route('inventory-kits.assemble', selectedKit.value.id), {
    onSuccess: () => {
      showAssembleModal.value = false
      assembleForm.reset()
      selectedKit.value = null
    }
  })
}

const confirmDelete = (kit) => {
  kitToDelete.value = kit
  showDeleteModal.value = true
}

const deleteKit = () => {
  if (!kitToDelete.value) return
  
  processing.value = true
  
  router.delete(route('inventory-kits.destroy', kitToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      kitToDelete.value = null
    },
    onFinish: () => {
      processing.value = false
    }
  })
}
</script>
