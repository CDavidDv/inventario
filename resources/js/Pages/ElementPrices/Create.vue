<template>
  <AppLayout title="Crear Precio">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Asignar Precio a Elemento
        </h2>
        <Link :href="route('element-prices.index')" class="text-blue-600 hover:text-blue-800">
          ← Volver a la lista
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Elemento -->
                <div class="md:col-span-2">
                  <InputLabel for="element_id" value="Elemento *" />
                  <select
                    id="element_id"
                    v-model="form.element_id"
                    @change="updateElementInfo"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="">Seleccionar elemento</option>
                    <option v-for="element in elements" :key="element.id" :value="element.id">
                      {{ element.name }} ({{ element.category }})
                    </option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.element_id" />
                  <div v-if="selectedElement" class="mt-2 p-3 bg-blue-50 rounded-lg">
                    <div class="text-sm">
                      <div class="font-medium text-blue-900">{{ selectedElement.name }}</div>
                      <div class="text-blue-700">{{ selectedElement.description }}</div>
                      <div class="flex justify-between mt-2">
                        <span>Stock actual: {{ selectedElement.current_stock }} {{ selectedElement.unit }}</span>
                        <span>Categoría: {{ selectedElement.category }}</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Proveedor -->
                <div class="md:col-span-2">
                  <InputLabel for="supplier_id" value="Proveedor *" />
                  <select
                    id="supplier_id"
                    v-model="form.supplier_id"
                    @change="updateSupplierInfo"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="">Seleccionar proveedor</option>
                    <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                      {{ supplier.name }} ({{ supplier.country }})
                    </option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.supplier_id" />
                  <div v-if="selectedSupplier" class="mt-2 p-3 bg-green-50 rounded-lg">
                    <div class="text-sm">
                      <div class="font-medium text-green-900">{{ selectedSupplier.name }}</div>
                      <div class="text-green-700">{{ selectedSupplier.contact || 'Sin contacto' }}</div>
                      <div class="flex justify-between mt-2">
                        <span>{{ selectedSupplier.email || 'Sin email' }}</span>
                        <span>{{ selectedSupplier.country }}</span>
                      </div>
                    </div>
                  </div>
                </div>

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
                </div>
              </div>

              <!-- Cálculos automáticos -->
              <div v-if="form.purchase_price && form.selling_price" class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Cálculos Automáticos:</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                  <div>
                    <span class="text-gray-600 font-medium">Margen:</span>
                    <div class="text-lg font-bold" :class="getMarginClass()">
                      {{ calculateMargin().toFixed(2) }}%
                    </div>
                  </div>
                  <div>
                    <span class="text-gray-600 font-medium">Ganancia por Unidad:</span>
                    <div class="text-lg font-bold text-green-600">
                      ${{ (parseFloat(form.selling_price) - parseFloat(form.purchase_price)).toFixed(2) }}
                    </div>
                  </div>
                  <div v-if="selectedElement">
                    <span class="text-gray-600 font-medium">Valor del Stock:</span>
                    <div class="text-lg font-bold text-purple-600">
                      ${{ (selectedElement.current_stock * parseFloat(form.purchase_price)).toFixed(2) }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Vista previa de la relación -->
              <div v-if="selectedElement && selectedSupplier" class="mt-6 p-4 bg-blue-50 rounded-lg">
                <h4 class="text-sm font-medium text-blue-700 mb-3">Vista Previa:</h4>
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
                      {{ selectedElement.name }} ← {{ selectedSupplier.name }}
                    </div>
                    <div class="text-xs text-blue-700">
                      {{ form.currency }} ${{ form.purchase_price || '0.00' }} → ${{ form.selling_price || '0.00' }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Verificación de duplicados -->
              <div v-if="duplicateWarning" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Atención</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                      Ya existe un precio para este elemento con este proveedor. 
                      Si continúas, se creará una entrada adicional.
                    </div>
                  </div>
                </div>
              </div>

              <!-- Información adicional -->
              <div class="mt-6 p-4 bg-green-50 rounded-lg">
                <h4 class="text-sm font-medium text-green-700 mb-2">💡 Consejos:</h4>
                <ul class="text-sm text-green-600 space-y-1">
                  <li>• Un elemento puede tener múltiples proveedores con diferentes precios</li>
                  <li>• El margen se calcula automáticamente: (Precio Venta - Precio Compra) / Precio Compra</li>
                  <li>• Los precios de compra son utilizados para calcular el valor del inventario</li>
                  <li>• Los precios de venta son opcionales pero recomendados para análisis de márgenes</li>
                </ul>
              </div>

              <!-- Botones -->
              <div class="flex items-center justify-end mt-6 space-x-3">
                <SecondaryButton type="button" @click="$inertia.visit(route('element-prices.index'))">
                  Cancelar
                </SecondaryButton>
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Crear Precio
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
import { ref, computed, watch } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
  elements: Array,
  suppliers: Array,
  preselectedElement: Object,
  preselectedSupplier: Object
})

const selectedElement = ref(null)
const selectedSupplier = ref(null)
const duplicateWarning = ref(false)

const form = useForm({
  element_id: props.preselectedElement?.id || '',
  supplier_id: props.preselectedSupplier?.id || '',
  purchase_price: '',
  selling_price: '',
  currency: 'MXN'
})

// Inicializar elementos preseleccionados
if (props.preselectedElement) {
  selectedElement.value = props.preselectedElement
}
if (props.preselectedSupplier) {
  selectedSupplier.value = props.preselectedSupplier
}

const calculateMargin = () => {
  const purchase = parseFloat(form.purchase_price)
  const selling = parseFloat(form.selling_price)
  
  if (!purchase || !selling) return 0
  return ((selling - purchase) / purchase) * 100
}

const getMarginClass = () => {
  const margin = calculateMargin()
  if (margin >= 30) return 'text-green-600'
  if (margin >= 15) return 'text-blue-600'
  if (margin >= 5) return 'text-yellow-600'
  return 'text-red-600'
}

const updateElementInfo = () => {
  const elementId = parseInt(form.element_id)
  selectedElement.value = props.elements.find(el => el.id === elementId) || null
  checkForDuplicates()
}

const updateSupplierInfo = () => {
  const supplierId = parseInt(form.supplier_id)
  selectedSupplier.value = props.suppliers.find(sup => sup.id === supplierId) || null
  checkForDuplicates()
}

const checkForDuplicates = () => {
  if (!form.element_id || !form.supplier_id) {
    duplicateWarning.value = false
    return
  }
  
  // Verificar en el backend si ya existe esta combinación
  fetch(`/api/inventory/check-price-duplicate?element_id=${form.element_id}&supplier_id=${form.supplier_id}`)
    .then(response => response.json())
    .then(data => {
      duplicateWarning.value = data.exists || false
    })
    .catch(error => {
      console.error('Error checking for duplicates:', error)
      duplicateWarning.value = false
    })
}

// Watch para cambios en element_id y supplier_id
watch([() => form.element_id, () => form.supplier_id], () => {
  checkForDuplicates()
})

const submit = () => {
  form.post(route('element-prices.store'), {
    onSuccess: () => {
      // Redirección manejada por el controlador
    }
  })
}
</script>
