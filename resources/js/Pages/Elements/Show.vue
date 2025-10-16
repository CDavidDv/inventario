<template>
  <AppLayout title="Detalles del Elemento">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ element.name }}
        </h2>
        <div class="flex space-x-2">
          <Link
            v-if="$page.props.auth.user.permissions.includes('edit elements')"
            :href="route('elements.edit', element.id)"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
          </Link>
          <Link :href="route('elements.index')" class="text-blue-600 hover:text-blue-800">
            ← Volver a la lista
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Información principal del elemento -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
          <!-- Información básica -->
          <div class="lg:col-span-2 bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Información del Elemento</h3>
                <span 
                  class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
                  :class="getStatusBadgeClass()"
                >
                  {{ getStatusText() }}
                </span>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ element.name }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Categoría</dt>
                  <dd class="mt-1">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                      {{ element.category }}
                    </span>
                  </dd>
                </div>
                <div v-if="element.description" class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ element.description }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Unidad de Medida</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ element.unit }}</dd>
                </div>
                <div v-if="element.location">
                  <dt class="text-sm font-medium text-gray-500">Ubicación</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ element.location }}</dd>
                </div>
                <div class="md:col-span-2">
                  <dt class="text-sm font-medium text-gray-500">Fechas</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    Creado: {{ formatDate(element.created_at) }} | 
                    Actualizado: {{ formatDate(element.updated_at) }}
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Estado del stock -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Estado del Stock</h3>
              
              <div class="space-y-4">
                <div class="text-center">
                  <div class="text-3xl font-bold" :class="getStockClass()">
                    {{ element.current_stock }}
                  </div>
                  <div class="text-sm text-gray-500">{{ element.unit }}</div>
                </div>

                <div class="w-full bg-gray-200 rounded-full h-3">
                  <div 
                    class="h-3 rounded-full transition-all duration-300"
                    :class="getStockBarClass()"
                    :style="`width: ${getStockPercentage()}%`"
                  ></div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <dt class="text-gray-500">Mínimo</dt>
                    <dd class="font-medium">{{ element.min_stock }}</dd>
                  </div>
                  <div>
                    <dt class="text-gray-500">Máximo</dt>
                    <dd class="font-medium">{{ element.max_stock }}</dd>
                  </div>
                </div>

                <!-- Botón de ajuste de stock -->
                <div v-if="$page.props.auth.user.permissions.includes('manage elements stock')" class="pt-4 border-t">
                  <button
                    @click="showStockModal = true"
                    class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                  >
                    Ajustar Stock
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Alertas activas -->
        <div v-if="recentAlerts && recentAlerts.length > 0" class="mb-6">
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Alertas Activas</h3>
              <div class="space-y-2">
                <div
                  v-for="alert in recentAlerts"
                  :key="alert.id"
                  class="flex items-center justify-between p-3 rounded-lg"
                  :class="getAlertBgClass(alert.alert_type)"
                >
                  <div class="flex items-center">
                    <div :class="getAlertIconClass(alert.alert_type)" class="w-5 h-5 mr-3">
                      <svg fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                      </svg>
                    </div>
                    <div>
                      <div class="font-medium">{{ alert.message }}</div>
                      <div class="text-sm opacity-75">{{ formatDate(alert.date) }}</div>
                    </div>
                  </div>
                  <span class="px-2 py-1 text-xs font-semibold rounded-full bg-white bg-opacity-20">
                    {{ alert.alert_type }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Secciones inferiores -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Precios y proveedores -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Precios y Proveedores</h3>
              
              <div v-if="element.prices && element.prices.length > 0" class="space-y-4">
                <div
                  v-for="price in element.prices"
                  :key="price.id"
                  class="border rounded-lg p-4"
                >
                  <div class="flex justify-between items-start mb-2">
                    <div>
                      <h4 class="font-medium">{{ price.supplier.name }}</h4>
                      <p class="text-sm text-gray-500">{{ price.supplier.contact }}</p>
                    </div>
                    <div class="text-right">
                      <div class="text-sm text-gray-500">{{ price.currency }}</div>
                    </div>
                  </div>
                  <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <dt class="text-gray-500">Precio Compra</dt>
                      <dd class="font-medium text-green-600">
                        ${{ price.purchase_price || 'N/A' }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-gray-500">Precio Venta</dt>
                      <dd class="font-medium text-blue-600">
                        ${{ price.selling_price || 'N/A' }}
                      </dd>
                    </div>
                  </div>
                </div>
              </div>
              
              <div v-else class="text-center text-gray-500 py-4">
                No hay precios registrados para este elemento
              </div>
            </div>
          </div>

          <!-- Movimientos recientes -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Movimientos Recientes</h3>
              
              <div v-if="recentMovements && recentMovements.length > 0" class="space-y-3">
                <div
                  v-for="movement in recentMovements"
                  :key="movement.id"
                  class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                >
                  <div>
                    <div class="font-medium">{{ movement.concept || movement.type }}</div>
                    <div class="text-sm text-gray-500">
                      {{ formatDate(movement.movement_date || movement.created_at) }}
                    </div>
                  </div>
                  <div class="text-right">
                    <div 
                      class="font-bold"
                      :class="movement.quantity > 0 ? 'text-green-600' : 'text-red-600'"
                    >
                      {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                    </div>
                    <div class="text-sm text-gray-500">{{ element.unit }}</div>
                  </div>
                </div>
              </div>
              
              <div v-else class="text-center text-gray-500 py-4">
                No hay movimientos registrados
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de ajuste de stock -->
    <DialogModal :show="showStockModal" @close="showStockModal = false">
      <template #title>
        Ajustar Stock - {{ element.name }}
      </template>
      <template #content>
        <div class="mb-4">
          <div class="text-center p-4 bg-gray-50 rounded-lg mb-4">
            <div class="text-sm text-gray-500">Stock Actual</div>
            <div class="text-2xl font-bold">{{ element.current_stock }} {{ element.unit }}</div>
          </div>
          
          <div class="space-y-4">
            <div>
              <InputLabel for="stock_adjustment" value="Ajuste de Stock" />
              <TextInput
                id="stock_adjustment"
                v-model="stockForm.stock_adjustment"
                type="number"
                class="mt-1 block w-full"
                placeholder="Ej: +10 o -5"
              />
              <p class="mt-1 text-sm text-gray-500">
                Usar números positivos para aumentar stock, negativos para reducir
              </p>
              <InputError class="mt-2" :message="stockForm.errors.stock_adjustment" />
            </div>
            
            <div>
              <InputLabel for="reason" value="Razón del Ajuste" />
              <TextInput
                id="reason"
                v-model="stockForm.reason"
                type="text"
                class="mt-1 block w-full"
                placeholder="Ej: Recepción de mercancía, Corrección de inventario"
              />
              <InputError class="mt-2" :message="stockForm.errors.reason" />
            </div>

            <div v-if="stockForm.stock_adjustment" class="p-3 bg-blue-50 rounded-lg">
              <div class="text-sm">
                <strong>Vista previa:</strong>
                {{ element.current_stock }} {{ element.unit }} 
                → 
                {{ element.current_stock + parseInt(stockForm.stock_adjustment || 0) }} {{ element.unit }}
                <span class="ml-2" :class="stockForm.stock_adjustment > 0 ? 'text-green-600' : 'text-red-600'">
                  ({{ stockForm.stock_adjustment > 0 ? '+' : '' }}{{ stockForm.stock_adjustment }})
                </span>
              </div>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <SecondaryButton @click="showStockModal = false">
          Cancelar
        </SecondaryButton>
        <PrimaryButton 
          class="ml-3" 
          @click="updateStock" 
          :class="{ 'opacity-25': stockForm.processing }" 
          :disabled="stockForm.processing"
        >
          Actualizar Stock
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
  element: Object,
  recentMovements: Array,
  recentAlerts: Array
})

const showStockModal = ref(false)

const stockForm = useForm({
  stock_adjustment: '',
  reason: ''
})

// Métodos para el estado del stock
const getStockClass = () => {
  if (props.element.current_stock <= 0) return 'text-red-600'
  if (props.element.current_stock <= props.element.min_stock) return 'text-yellow-600'
  if (props.element.current_stock >= props.element.max_stock && props.element.max_stock > 0) return 'text-purple-600'
  return 'text-green-600'
}

const getStatusBadgeClass = () => {
  if (props.element.current_stock <= 0) return 'bg-red-100 text-red-800'
  if (props.element.current_stock <= props.element.min_stock) return 'bg-yellow-100 text-yellow-800'
  if (props.element.current_stock >= props.element.max_stock && props.element.max_stock > 0) return 'bg-purple-100 text-purple-800'
  return 'bg-green-100 text-green-800'
}

const getStatusText = () => {
  if (props.element.current_stock <= 0) return 'Agotado'
  if (props.element.current_stock <= props.element.min_stock) return 'Stock Bajo'
  if (props.element.current_stock >= props.element.max_stock && props.element.max_stock > 0) return 'Sobre Stock'
  return 'Normal'
}

const getStockBarClass = () => {
  if (props.element.current_stock <= 0) return 'bg-red-500'
  if (props.element.current_stock <= props.element.min_stock) return 'bg-yellow-500'
  if (props.element.current_stock >= props.element.max_stock && props.element.max_stock > 0) return 'bg-purple-500'
  return 'bg-green-500'
}

const getStockPercentage = () => {
  if (!props.element.max_stock || props.element.max_stock === 0) return 50
  return Math.min((props.element.current_stock / props.element.max_stock) * 100, 100)
}

// Métodos para las alertas
const getAlertBgClass = (type) => {
  switch (type) {
    case 'Agotado': return 'bg-red-100 text-red-800'
    case 'Crítico': return 'bg-orange-100 text-orange-800'
    case 'Mínimo': return 'bg-yellow-100 text-yellow-800'
    case 'Máximo': return 'bg-purple-100 text-purple-800'
    default: return 'bg-gray-100 text-gray-800'
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

// Actualizar stock
const updateStock = () => {
  stockForm.post(route('elements.update-stock', props.element.id), {
    onSuccess: () => {
      showStockModal.value = false
      stockForm.reset()
    }
  })
}
</script>
