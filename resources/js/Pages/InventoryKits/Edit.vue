<template>
  <AppLayout title="Editar Kit">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Editar Kit: {{ kit.name }}
        </h2>
        <div class="flex space-x-2">
          <Link 
            :href="route('inventory-kits.show', kit.id)" 
            class="text-blue-600 hover:text-blue-800"
          >
            Ver Detalles
          </Link>
          <Link 
            :href="route('inventory-kits.index')" 
            class="text-blue-600 hover:text-blue-800"
          >
            ← Volver a la lista
          </Link>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Información actual del kit -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
              <h3 class="text-lg font-medium text-blue-900 mb-2">Estado Actual del Kit</h3>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                  <span class="text-blue-700 font-medium">Disponibilidad:</span>
                  <div class="text-lg font-bold" :class="kit.can_be_assembled ? 'text-green-600' : 'text-red-600'">
                    {{ kit.can_be_assembled ? 'Disponible' : 'No Disponible' }}
                  </div>
                </div>
                <div>
                  <span class="text-blue-700 font-medium">Kits Posibles:</span>
                  <div class="text-lg font-bold">{{ kit.max_assemblies || 0 }}</div>
                </div>
                <div>
                  <span class="text-blue-700 font-medium">Componentes:</span>
                  <div class="text-lg font-bold">{{ kit.kit_components.length }}</div>
                </div>
                <div>
                  <span class="text-blue-700 font-medium">Costo Total:</span>
                  <div class="text-lg font-bold text-green-600">
                    ${{ kit.total_cost?.toFixed(2) || '0.00' }}
                  </div>
                </div>
              </div>
            </div>

            <form @submit.prevent="submit">
              <!-- Información básica del kit -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="md:col-span-2">
                  <InputLabel for="name" value="Nombre del Kit *" />
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    placeholder="Ej: Kit de Resistencias Básicas"
                  />
                  <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="md:col-span-2">
                  <InputLabel for="description" value="Descripción" />
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    placeholder="Descripción detallada del kit..."
                  ></textarea>
                  <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div>
                  <InputLabel for="category" value="Categoría" />
                  <select
                    id="category"
                    v-model="form.category"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  >
                    <option value="">Seleccionar categoría</option>
                    <option value="Electrónicos">Electrónicos</option>
                    <option value="Mecánicos">Mecánicos</option>
                    <option value="Químicos">Químicos</option>
                    <option value="Herramientas">Herramientas</option>
                    <option value="Educativo">Educativo</option>
                    <option value="Reparación">Reparación</option>
                    <option value="Otros">Otros</option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.category" />
                </div>
              </div>

              <!-- Componentes del kit -->
              <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                  <h3 class="text-lg font-medium text-gray-900">Componentes del Kit</h3>
                  <button
                    type="button"
                    @click="addComponent"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Agregar Componente
                  </button>
                </div>

                <div v-if="form.components.length === 0" class="text-center py-8 text-gray-500">
                  <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                  </svg>
                  <p>No hay componentes en el kit</p>
                  <p class="text-sm">Haz clic en "Agregar Componente" para empezar</p>
                </div>

                <div v-else class="space-y-3">
                  <div 
                    v-for="(component, index) in form.components" 
                    :key="component.temp_id || component.id || index"
                    class="flex items-center space-x-4 p-4 rounded-lg"
                    :class="component.is_new ? 'bg-green-50 border border-green-200' : 'bg-gray-50'"
                  >
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                      <!-- Selector de elemento -->
                      <div class="md:col-span-2">
                        <select
                          v-model="component.element_id"
                          @change="updateElementInfo(index)"
                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                          required
                        >
                          <option value="">Seleccionar elemento</option>
                          <option 
                            v-for="element in availableElements" 
                            :key="element.id" 
                            :value="element.id"
                          >
                            {{ element.name }} ({{ element.current_stock }} {{ element.unit }} disponibles)
                          </option>
                        </select>
                        <div v-if="component.element" class="mt-1 text-sm text-gray-500">
                          {{ component.element.category }} - {{ component.element.description }}
                        </div>
                        <div v-if="component.is_new" class="mt-1 text-xs text-green-600 font-medium">
                          ✓ Nuevo componente
                        </div>
                        <div v-else-if="hasComponentChanged(component, index)" class="mt-1 text-xs text-orange-600 font-medium">
                          ⚠️ Componente modificado
                        </div>
                      </div>

                      <!-- Cantidad -->
                      <div>
                        <input
                          v-model.number="component.quantity"
                          type="number"
                          min="1"
                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                          placeholder="Cantidad"
                          required
                        />
                        <div v-if="component.element" class="mt-1 text-xs text-gray-500">
                          {{ component.element.unit }}
                        </div>
                        <div v-if="!component.is_new && component.original_quantity && component.quantity !== component.original_quantity" class="mt-1 text-xs text-orange-600">
                          Anterior: {{ component.original_quantity }}
                        </div>
                      </div>
                    </div>

                    <!-- Información adicional -->
                    <div v-if="component.element" class="text-right">
                      <div class="text-sm">
                        <span 
                          :class="component.quantity <= component.element.current_stock ? 'text-green-600' : 'text-red-600'"
                          class="font-medium"
                        >
                          {{ component.quantity <= component.element.current_stock ? '✓ Disponible' : '✗ Insuficiente' }}
                        </span>
                      </div>
                      <div class="text-xs text-gray-500">
                        Stock: {{ component.element.current_stock }}
                      </div>
                    </div>

                    <!-- Botón eliminar -->
                    <button
                      type="button"
                      @click="removeComponent(index)"
                      class="text-red-600 hover:text-red-800"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </div>

                <InputError class="mt-2" :message="form.errors.components" />
              </div>

              <!-- Resumen de cambios -->
              <div v-if="hasChanges" class="mb-8 p-4 bg-orange-50 rounded-lg">
                <h4 class="font-medium text-orange-900 mb-3">Resumen de Cambios</h4>
                <div class="space-y-2 text-sm">
                  <div v-if="form.name !== kit.name" class="text-orange-700">
                    • Nombre: "{{ kit.name }}" → "{{ form.name }}"
                  </div>
                  <div v-if="form.description !== kit.description" class="text-orange-700">
                    • Descripción actualizada
                  </div>
                  <div v-if="form.category !== kit.category" class="text-orange-700">
                    • Categoría: "{{ kit.category }}" → "{{ form.category }}"
                  </div>
                  <div v-if="newComponentsCount > 0" class="text-green-700">
                    • {{ newComponentsCount }} componente(s) agregado(s)
                  </div>
                  <div v-if="modifiedComponentsCount > 0" class="text-orange-700">
                    • {{ modifiedComponentsCount }} componente(s) modificado(s)
                  </div>
                  <div v-if="removedComponentsCount > 0" class="text-red-700">
                    • {{ removedComponentsCount }} componente(s) eliminado(s)
                  </div>
                </div>
              </div>

              <!-- Resumen del kit actualizado -->
              <div v-if="form.components.length > 0" class="mb-8 p-4 bg-blue-50 rounded-lg">
                <h4 class="font-medium text-blue-900 mb-3">Resumen del Kit Actualizado</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                  <div>
                    <span class="text-blue-700 font-medium">Total Componentes:</span>
                    <div class="text-lg font-bold text-blue-900">{{ form.components.length }}</div>
                  </div>
                  <div>
                    <span class="text-blue-700 font-medium">Disponibilidad:</span>
                    <div class="text-lg font-bold" :class="canBeAssembled ? 'text-green-600' : 'text-red-600'">
                      {{ canBeAssembled ? 'Disponible' : 'No Disponible' }}
                    </div>
                  </div>
                  <div>
                    <span class="text-blue-700 font-medium">Nuevo Costo:</span>
                    <div class="text-lg font-bold text-blue-900">
                      ${{ estimatedCost.toFixed(2) }}
                    </div>
                  </div>
                </div>
                <div v-if="!canBeAssembled" class="mt-3 text-sm text-red-600">
                  ⚠️ Algunos componentes no tienen stock suficiente
                </div>
              </div>

              <!-- Botones -->
              <div class="flex items-center justify-end space-x-3">
                <SecondaryButton type="button" @click="$inertia.visit(route('inventory-kits.show', kit.id))">
                  Cancelar
                </SecondaryButton>
                <PrimaryButton 
                  :class="{ 'opacity-25': form.processing }" 
                  :disabled="form.processing || form.components.length === 0 || !hasChanges"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Actualizar Kit
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
import { ref, computed, onMounted } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
  kit: Object,
  elements: Array
})

const availableElements = ref(props.elements || [])
const originalComponents = ref([...props.kit.kit_components])

const form = useForm({
  name: props.kit.name,
  description: props.kit.description || '',
  category: props.kit.category || '',
  components: props.kit.kit_components.map(comp => ({
    id: comp.id,
    element_id: comp.element_id,
    quantity: comp.quantity,
    element: comp.element,
    original_quantity: comp.quantity,
    is_new: false
  }))
})

// Computed properties
const hasChanges = computed(() => {
  const basicInfoChanged = form.name !== props.kit.name || 
                           form.description !== (props.kit.description || '') || 
                           form.category !== (props.kit.category || '')
  
  const componentsChanged = newComponentsCount.value > 0 || 
                           modifiedComponentsCount.value > 0 || 
                           removedComponentsCount.value > 0
  
  return basicInfoChanged || componentsChanged
})

const newComponentsCount = computed(() => {
  return form.components.filter(comp => comp.is_new).length
})

const modifiedComponentsCount = computed(() => {
  return form.components.filter(comp => 
    !comp.is_new && 
    (comp.quantity !== comp.original_quantity || 
     !originalComponents.value.find(orig => orig.id === comp.id))
  ).length
})

const removedComponentsCount = computed(() => {
  const currentIds = form.components.filter(comp => !comp.is_new).map(comp => comp.id)
  return originalComponents.value.filter(orig => !currentIds.includes(orig.id)).length
})

const canBeAssembled = computed(() => {
  return form.components.every(component => {
    return component.element && component.quantity <= component.element.current_stock
  })
})

const estimatedCost = computed(() => {
  return form.components.reduce((total, component) => {
    if (component.element && component.element.prices && component.element.prices.length > 0) {
      const avgPrice = component.element.prices.reduce((sum, price) => sum + (price.purchase_price || 0), 0) / component.element.prices.length
      return total + (avgPrice * component.quantity)
    }
    return total
  }, 0)
})

// Methods
const addComponent = () => {
  form.components.push({
    temp_id: Date.now(), // ID temporal para componentes nuevos
    element_id: '',
    quantity: 1,
    element: null,
    is_new: true
  })
}

const removeComponent = (index) => {
  form.components.splice(index, 1)
}

const updateElementInfo = (index) => {
  const elementId = form.components[index].element_id
  const element = availableElements.value.find(el => el.id == elementId)
  form.components[index].element = element
}

const hasComponentChanged = (component, index) => {
  if (component.is_new) return false
  const original = originalComponents.value.find(orig => orig.id === component.id)
  if (!original) return true
  return component.element_id !== original.element_id || component.quantity !== original.quantity
}

const submit = () => {
  // Preparar datos para envío
  const submitData = {
    name: form.name,
    description: form.description,
    category: form.category,
    components: form.components.map(comp => ({
      id: comp.is_new ? undefined : comp.id,
      element_id: comp.element_id,
      quantity: comp.quantity
    }))
  }

  form.transform(data => submitData).put(route('inventory-kits.update', props.kit.id), {
    onSuccess: () => {
      // Redirección manejada por el controlador
    }
  })
}

onMounted(() => {
  // Cargar elementos si no se pasaron como prop
  if (!props.elements) {
    fetch('/api/inventory/elements/search')
      .then(response => response.json())
      .then(data => {
        availableElements.value = data
      })
      .catch(error => {
        console.error('Error al cargar elementos:', error)
      })
  }
})
</script>
