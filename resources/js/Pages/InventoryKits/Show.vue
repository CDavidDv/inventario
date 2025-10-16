<template>
  <AppLayout title="Detalles del Kit">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ kit.name }}
        </h2>
        <div class="flex space-x-2">
          <Link
            v-if="$page.props.auth.user.permissions.includes('edit kits')"
            :href="route('inventory-kits.edit', kit.id)"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
          </Link>
          <Link :href="route('inventory-kits.index')" class="text-blue-600 hover:text-blue-800">
            ← Volver a la lista
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Información principal del kit -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
          <!-- Información básica -->
          <div class="lg:col-span-2 bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Información del Kit</h3>
                <span 
                  class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                  :class="getAvailabilityBadgeClass()"
                >
                  {{ getAvailabilityText() }}
                </span>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ kit.name }}</dd>
                </div>
                <div v-if="kit.category">
                  <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                  <dd class="mt-1">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                      {{ kit.category }}
                    </span>
                  </dd>
                </div>
                <div v-if="kit.description" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ kit.description }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Total Componentes</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ kit.kit_components.length }} elementos</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Fechas</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    Creado: {{ formatDate(kit.created_at) }} | 
                    Actualizado: {{ formatDate(kit.updated_at) }}
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Estado y métricas -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Estado del Kit</h3>
              
              <div class="space-y-4">
                <div class="text-center">
                  <div class="text-3xl font-bold" :class="getAvailabilityClass()">
                    {{ kit.max_assemblies || 0 }}
                  </div>
                  <div class="text-sm text-gray-500">Kits disponibles</div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <dt class="text-gray-500">Costo Total</dt>
                    <dd class="font-medium text-green-600">${{ kit.total_cost?.toFixed(2) || '0.00' }}</dd>
                  </div>
                  <div>
                    <dt class="text-gray-500">Precio Venta</dt>
                    <dd class="font-medium text-blue-600">${{ kit.total_selling_price?.toFixed(2) || '0.00' }}</dd>
                  </div>
                </div>

                <!-- Botón de ensamblaje -->
                <div v-if="$page.props.auth.user.permissions.includes('assemble kits') && kit.can_be_assembled" class="pt-4 border-t">
                  <button
                    @click="showAssembleModal = true"
                    class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                  >
                    Ensamblar Kit
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Componentes del kit -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Componentes del Kit</h3>
            
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Elemento
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Cantidad Requerida
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stock Disponible
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Estado
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Costo
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="component in kit.kit_components" :key="component.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div>
                          <div class="text-sm font-medium text-gray-900">
                            <Link 
                              :href="route('elements.show', component.element.id)"
                              class="text-blue-600 hover:text-blue-800"
                            >
                              {{ component.element.name }}
                            </Link>
                          </div>
                          <div class="text-sm text-gray-500">{{ component.element.category }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ component.quantity }} {{ component.element.unit }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ component.element.current_stock }} {{ component.element.unit }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="getComponentStatusClass(component)"
                      >
                        {{ getComponentStatus(component) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      <div v-if="component.element.prices && component.element.prices.length > 0">
                        <div class="text-sm font-medium text-green-600">
                          ${{ getComponentCost(component).toFixed(2) }}
                        </div>
                        <div class="text-xs text-gray-500">
                          por {{ component.quantity }} {{ component.element.unit }}
                        </div>
                      </div>
                      <div v-else class="text-sm text-gray-400">
                        Sin precio
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Historial de ensamblajes -->
        <div v-if="recentAssemblies && recentAssemblies.length > 0" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Historial de Ensamblajes Recientes</h3>
            
            <div class="space-y-3">
              <div
                v-for="assembly in recentAssemblies"
                :key="assembly.id"
                class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
              >
                <div>
                  <div class="font-medium">{{ assembly.concept || 'Ensamblaje de kit' }}</div>
                  <div class="text-sm text-gray-500">
                    {{ formatDate(assembly.movement_date || assembly.created_at) }}
                    por {{ assembly.user?.name || 'Sistema' }}
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-bold text-green-600">
                    {{ Math.abs(assembly.quantity) }} kits
                  </div>
                  <div class="text-sm text-gray-500">
                    ${{ assembly.total_cost?.toFixed(2) || '0.00' }}
                  </div>
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
        Ensamblar Kit - {{ kit.name }}
      </template>
      <template #content>
        <div class="space-y-4">
          <div class="text-center p-4 bg-gray-50 rounded-lg mb-4">
            <div class="text-sm text-gray-500">Kits disponibles para ensamblar</div>
            <div class="text-2xl font-bold text-green-600">{{ kit.max_assemblies }}</div>
          </div>
          
          <div>
            <InputLabel for="quantity" value="Cantidad a Ensamblar" />
            <TextInput
              id="quantity"
              v-model="assembleForm.quantity"
              type="number"
              :max="kit.max_assemblies"
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

          <!-- Desglose de componentes -->
          <div class="p-4 bg-blue-50 rounded-lg">
            <h4 class="font-medium text-blue-900 mb-2">Componentes que se utilizarán:</h4>
            <div class="space-y-2">
              <div 
                v-for="component in kit.kit_components" 
                :key="component.id"
                class="flex justify-between text-sm"
              >
                <span>{{ component.element.name }}</span>
                <span class="font-medium">
                  {{ component.quantity * (assembleForm.quantity || 1) }} {{ component.element.unit }}
                </span>
              </div>
            </div>
            <div class="mt-3 pt-2 border-t border-blue-200">
              <div class="flex justify-between font-medium text-blue-900">
                <span>Costo Total:</span>
                <span>${{ ((kit.total_cost || 0) * (assembleForm.quantity || 1)).toFixed(2) }}</span>
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
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DialogModal from '@/Components/DialogModal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
  kit: Object,
  recentAssemblies: Array
})

const showAssembleModal = ref(false)

const assembleForm = useForm({
  quantity: 1,
  reason: ''
})

// Métodos para el estado del kit
const getAvailabilityClass = () => {
  if (!props.kit.can_be_assembled) return 'text-red-600'
  if ((props.kit.max_assemblies || 0) === 0) return 'text-gray-600'
  return 'text-green-600'
}

const getAvailabilityBadgeClass = () => {
  if (!props.kit.can_be_assembled) return 'bg-red-100 text-red-800'
  if ((props.kit.max_assemblies || 0) === 0) return 'bg-gray-100 text-gray-800'
  return 'bg-green-100 text-green-800'
}

const getAvailabilityText = () => {
  if (!props.kit.can_be_assembled) return 'No Disponible'
  if ((props.kit.max_assemblies || 0) === 0) return 'Sin Stock'
  return 'Disponible'
}

const getComponentStatus = (component) => {
  if (component.element.current_stock >= component.quantity) return 'Disponible'
  if (component.element.current_stock === 0) return 'Agotado'
  return 'Insuficiente'
}

const getComponentStatusClass = (component) => {
  if (component.element.current_stock >= component.quantity) return 'bg-green-100 text-green-800'
  if (component.element.current_stock === 0) return 'bg-red-100 text-red-800'
  return 'bg-yellow-100 text-yellow-800'
}

const getComponentCost = (component) => {
  if (!component.element.prices || component.element.prices.length === 0) return 0
  const avgPrice = component.element.prices.reduce((sum, price) => sum + (price.purchase_price || 0), 0) / component.element.prices.length
  return avgPrice * component.quantity
}

// Método para formatear fechas
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Ensamblar kit
const assembleKit = () => {
  assembleForm.post(route('inventory-kits.assemble', props.kit.id), {
    onSuccess: () => {
      showAssembleModal.value = false
      assembleForm.reset()
    }
  })
}
</script>
