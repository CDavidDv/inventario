<template>
  <AppLayout title="Editar Precio">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Editar Precio: {{ elementPrice.element.name }} - {{ elementPrice.supplier.name }}
        </h2>
        <div class="flex space-x-2">
          <Link 
            :href="route('element-prices.index')" 
            class="text-blue-600 hover:text-blue-800"
          >
            ← Volver a la lista
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Información actual del precio -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
              <h3 class="text-lg font-medium text-blue-900 mb-3">Relación Actual</h3>
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="text-center">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center text-white font-bold">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                      </svg>
                    </div>
                    <div class="text-xs text-blue-700 mt-1">Elemento</div>
                  </div>
                  <div class="text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </div>
                  <div class="text-center">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center text-white font-bold">
                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                      </svg>
                    </div>
                    <div class="text-xs text-green-700 mt-1">Proveedor</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-blue-900">
                    {{ elementPrice.element.name }} ← {{ elementPrice.supplier.name }}
                  </div>
                  <div class="text-xs text-blue-700">
                    {{ elementPrice.currency }} ${{ elementPrice.purchase_price?.toFixed(2) || '0.00' }} → ${{ elementPrice.selling_price?.toFixed(2) || '0.00' }}
                  </div>
                  <div v-if="elementPrice.purchase_price && elementPrice.selling_price" class="text-xs font-medium" :class="getCurrentMarginClass()">
                    Margen actual: {{ getCurrentMargin().toFixed(1) }}%
                  </div>
                </div>
              </div>
            </div>

            <!-- Información del elemento y proveedor -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-2">Información del Elemento</h4>
                <div class="text-sm space-y-1">
                  <div><strong>Nombre:</strong> {{ elementPrice.element.name }}</div>
                  <div><strong>Categoría:</strong> {{ elementPrice.element.category }}</div>
                  <div><strong>Stock actual:</strong> {{ elementPrice.element.current_stock }} {{ elementPrice.element.unit }}</div>
                  <div><strong>Stock mínimo:</strong> {{ elementPrice.element.min_stock }}</div>
                </div>
              </div>
              
              <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="font-medium text-gray-900 mb-2">Información del Proveedor</h4>
                <div class="text-sm space-y-1">
                  <div><strong>Nombre:</strong> {{ elementPrice.supplier.name }}</div>
                  <div><strong>País:</strong> {{ elementPrice.supplier.country }}</div>
                  <div><strong>Contacto:</strong> {{ elementPrice.supplier.contact || 'No especificado' }}</div>
                  <div><strong>Email:</strong> {{ elementPrice.supplier.email || 'No especificado' }}</div>
                </div>
              </div>
            </div>

            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Precio de Compra -->
                <div>
                  <InputLabel for="purchase_price" value="Precio de Compra" />
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <TextInput
                      id="purchase_price"
                      v-model="form.purchase_price"
                      type="number"
                      step="0.01"
                      min="0"
                      class="pl-7 block w-full"
                      placeholder="0.00"
                    />
                  </div>
                  <InputError class="mt-2" :message="form.errors.purchase_price" />
                  <div v-if="form.purchase_price != elementPrice.purchase_price" class="mt-1 text-sm text-orange-600">
                    ⚠️ Cambio: ${{ elementPrice.purchase_price?.toFixed(2) || '0.00' }} → ${{ parseFloat(form.purchase_price).toFixed(2) }}
                  </div>
                </div>

                <!-- Precio de Venta -->
                <div>
                  <InputLabel for="selling_price" value="Precio de Venta" />
                  <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <TextInput
                      id="selling_price"
                      v-model="form.selling_price"
                      type="number"
                      step="0.01"
                      min="0"
                      class="pl-7 block w-full"
                      placeholder="0.00"
                    />
                  </div>
                  <InputError class="mt-2" :message="form.errors.selling_price" />
                  <div v-if="form.selling_price != elementPrice.selling_price" class="mt-1 text-sm text-orange-600">
                    ⚠️ Cambio: ${{ elementPrice.selling_price?.toFixed(2) || '0.00' }} → ${{ parseFloat(form.selling_price).toFixed(2) }}
                  </div>
                </div>

                <!-- Moneda -->
                <div>
                  <InputLabel for="currency" value="Moneda *" />
                  <select
                    id="currency"
                    v-model="form.currency"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="MXN">MXN - Peso Mexicano</option>
                    <option value="USD">USD - Dólar Americano</option>
                    <option value="EUR">EUR - Euro</option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.currency" />
                  <div v-if="form.currency !== elementPrice.currency" class="mt-1 text-sm text-orange-600">
                    ⚠️ Cambio de moneda: {{ elementPrice.currency }} → {{ form.currency }}
                  </div>
                </div>
              </div>

              <!-- Comparación de cálculos -->
              <div v-if="hasChanges" class="mt-6 p-4 bg-orange-50 rounded-lg">
                <h4 class="text-sm font-medium text-orange-700 mb-3">Comparación de Cambios:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Valores actuales -->
                  <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-2">Valores Actuales:</h5>
                    <div class="space-y-2 text-sm">
                      <div class="flex justify-between">
                        <span>Precio Compra:</span>
                        <span class="font-medium">${{ elementPrice.purchase_price?.toFixed(2) || '0.00' }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span>Precio Venta:</span>
                        <span class="font-medium">${{ elementPrice.selling_price?.toFixed(2) || '0.00' }}</span>
                      </div>
                      <div v-if="elementPrice.purchase_price && elementPrice.selling_price" class="flex justify-between">
                        <span>Margen:</span>
                        <span class="font-medium" :class="getCurrentMarginClass()">{{ getCurrentMargin().toFixed(1) }}%</span>
                      </div>
                      <div v-if="elementPrice.purchase_price" class="flex justify-between">
                        <span>Valor Stock:</span>
                        <span class="font-medium">${{ (elementPrice.element.current_stock * elementPrice.purchase_price).toFixed(2) }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Nuevos valores -->
                  <div>
                    <h5 class="text-sm font-medium text-gray-700 mb-2">Nuevos Valores:</h5>
                    <div class="space-y-2 text-sm">
                      <div class="flex justify-between">
                        <span>Precio Compra:</span>
                        <span class="font-medium">${{ parseFloat(form.purchase_price || 0).toFixed(2) }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span>Precio Venta:</span>
                        <span class="font-medium">${{ parseFloat(form.selling_price || 0).toFixed(2) }}</span>
                      </div>
                      <div v-if="form.purchase_price && form.selling_price" class="flex justify-between">
                        <span>Margen:</span>
                        <span class="font-medium" :class="getNewMarginClass()">{{ calculateNewMargin().toFixed(1) }}%</span>
                      </div>
                      <div v-if="form.purchase_price" class="flex justify-between">
                        <span>Valor Stock:</span>
                        <span class="font-medium">${{ (elementPrice.element.current_stock * parseFloat(form.purchase_price)).toFixed(2) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Cálculos automáticos nuevos -->
              <div v-if="form.purchase_price && form.selling_price" class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Cálculos Actualizados:</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                  <div>
                    <span class="text-gray-600 font-medium">Nuevo Margen:</span>
                    <div class="text-lg font-bold" :class="getNewMarginClass()">
                      {{ calculateNewMargin().toFixed(2) }}%
                    </div>
                  </div>
                  <div>
                    <span class="text-gray-600 font-medium">Ganancia por Unidad:</span>
                    <div class="text-lg font-bold text-green-600">
                      ${{ (parseFloat(form.selling_price) - parseFloat(form.purchase_price)).toFixed(2) }}
                    </div>
                  </div>
                  <div>
                    <span class="text-gray-600 font-medium">Nuevo Valor del Stock:</span>
                    <div class="text-lg font-bold text-purple-600">
                      ${{ (elementPrice.element.current_stock * parseFloat(form.purchase_price)).toFixed(2) }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Advertencia sobre impacto -->
              <div v-if="hasSignificantChanges" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Cambios Significativos</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                      Los cambios en los precios afectarán los cálculos de valor del inventario y los márgenes de ganancia. 
                      Asegúrate de que los nuevos precios sean correctos.
                    </div>
                  </div>
                </div>
              </div>

              <!-- Botones -->
              <div class="flex items-center justify-end mt-6 space-x-3">
                <SecondaryButton type="button" @click="$inertia.visit(route('element-prices.index'))">
                  Cancelar
                </SecondaryButton>
                <PrimaryButton 
                  :class="{ 'opacity-25': form.processing }" 
                  :disabled="form.processing || !hasChanges"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Actualizar Precio
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
  elementPrice: Object
})

const form = useForm({
  purchase_price: props.elementPrice.purchase_price || '',
  selling_price: props.elementPrice.selling_price || '',
  currency: props.elementPrice.currency
})

const hasChanges = computed(() => {
  return form.purchase_price != props.elementPrice.purchase_price ||
         form.selling_price != props.elementPrice.selling_price ||
         form.currency !== props.elementPrice.currency
})

const hasSignificantChanges = computed(() => {
  if (!hasChanges.value) return false
  
  const oldPurchase = parseFloat(props.elementPrice.purchase_price || 0)
  const newPurchase = parseFloat(form.purchase_price || 0)
  const oldSelling = parseFloat(props.elementPrice.selling_price || 0)
  const newSelling = parseFloat(form.selling_price || 0)
  
  const purchaseChange = Math.abs(newPurchase - oldPurchase) / (oldPurchase || 1)
  const sellingChange = Math.abs(newSelling - oldSelling) / (oldSelling || 1)
  
  return purchaseChange > 0.1 || sellingChange > 0.1 // Cambio mayor al 10%
})

const getCurrentMargin = () => {
  const purchase = parseFloat(props.elementPrice.purchase_price || 0)
  const selling = parseFloat(props.elementPrice.selling_price || 0)
  
  if (!purchase || !selling) return 0
  return ((selling - purchase) / purchase) * 100
}

const calculateNewMargin = () => {
  const purchase = parseFloat(form.purchase_price || 0)
  const selling = parseFloat(form.selling_price || 0)
  
  if (!purchase || !selling) return 0
  return ((selling - purchase) / purchase) * 100
}

const getCurrentMarginClass = () => {
  const margin = getCurrentMargin()
  if (margin >= 30) return 'text-green-600'
  if (margin >= 15) return 'text-blue-600'
  if (margin >= 5) return 'text-yellow-600'
  return 'text-red-600'
}

const getNewMarginClass = () => {
  const margin = calculateNewMargin()
  if (margin >= 30) return 'text-green-600'
  if (margin >= 15) return 'text-blue-600'
  if (margin >= 5) return 'text-yellow-600'
  return 'text-red-600'
}

const submit = () => {
  form.put(route('element-prices.update', props.elementPrice.id), {
    onSuccess: () => {
      // Redirección manejada por el controlador
    }
  })
}
</script>
