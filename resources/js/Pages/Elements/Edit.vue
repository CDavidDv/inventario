<template>
  <AppLayout title="Editar Elemento">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Editar Elemento: {{ element.name }}
        </h2>
        <div class="flex space-x-2">
          <Link 
            :href="route('elements.show', element.id)" 
            class="text-blue-600 hover:text-blue-800"
          >
            Ver Detalles
          </Link>
          <Link 
            :href="route('elements.index')" 
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
            <!-- Información actual del elemento -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
              <h3 class="text-lg font-medium text-blue-900 mb-2">Estado Actual</h3>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                  <span class="text-blue-700 font-medium">Stock Actual:</span>
                  <div class="text-2xl font-bold" :class="getCurrentStockClass()">
                    {{ element.current_stock }} {{ element.unit }}
                  </div>
                </div>
                <div>
                  <span class="text-blue-700 font-medium">Stock Mínimo:</span>
                  <div class="text-lg">{{ element.min_stock }}</div>
                </div>
                <div>
                  <span class="text-blue-700 font-medium">Stock Máximo:</span>
                  <div class="text-lg">{{ element.max_stock }}</div>
                </div>
                <div>
                  <span class="text-blue-700 font-medium">Estado:</span>
                  <div>
                    <span 
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="getCurrentStatusBadgeClass()"
                    >
                      {{ getCurrentStatus() }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div class="md:col-span-2">
                  <InputLabel for="name" value="Nombre del Elemento *" />
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    placeholder="Ej: Resistencia 1K Ohm"
                  />
                  <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <!-- Descripción -->
                <div class="md:col-span-2">
                  <InputLabel for="description" value="Descripción" />
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    placeholder="Descripción detallada del elemento..."
                  ></textarea>
                  <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <!-- Categoría -->
                <div>
                  <InputLabel for="category" value="Categoría *" />
                  <select
                    id="category"
                    v-model="form.category"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="">Seleccionar categoría</option>
                    <option value="Electrónicos">Electrónicos</option>
                    <option value="Mecánicos">Mecánicos</option>
                    <option value="Químicos">Químicos</option>
                    <option value="Herramientas">Herramientas</option>
                    <option value="Materiales">Materiales</option>
                    <option value="Consumibles">Consumibles</option>
                    <option value="Otros">Otros</option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.category" />
                </div>

                <!-- Unidad -->
                <div>
                  <InputLabel for="unit" value="Unidad de Medida *" />
                  <select
                    id="unit"
                    v-model="form.unit"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="">Seleccionar unidad</option>
                    <option value="Pieza">Pieza</option>
                    <option value="Metro">Metro</option>
                    <option value="Kilogramo">Kilogramo</option>
                    <option value="Gramo">Gramo</option>
                    <option value="Litro">Litro</option>
                    <option value="Mililitro">Mililitro</option>
                    <option value="Caja">Caja</option>
                    <option value="Paquete">Paquete</option>
                    <option value="Rollo">Rollo</option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.unit" />
                </div>

                <!-- Stock Mínimo -->
                <div>
                  <InputLabel for="min_stock" value="Stock Mínimo *" />
                  <TextInput
                    id="min_stock"
                    v-model="form.min_stock"
                    type="number"
                    class="mt-1 block w-full"
                    min="0"
                    required
                    placeholder="0"
                  />
                  <InputError class="mt-2" :message="form.errors.min_stock" />
                  <p class="mt-1 text-sm text-gray-500">
                    Se generará una alerta cuando el stock esté por debajo de este valor
                  </p>
                </div>

                <!-- Stock Máximo -->
                <div>
                  <InputLabel for="max_stock" value="Stock Máximo *" />
                  <TextInput
                    id="max_stock"
                    v-model="form.max_stock"
                    type="number"
                    class="mt-1 block w-full"
                    min="0"
                    required
                    placeholder="0"
                  />
                  <InputError class="mt-2" :message="form.errors.max_stock" />
                  <p class="mt-1 text-sm text-gray-500">
                    Se generará una alerta cuando el stock supere este valor
                  </p>
                </div>

                <!-- Stock Actual -->
                <div>
                  <InputLabel for="current_stock" value="Stock Actual *" />
                  <TextInput
                    id="current_stock"
                    v-model="form.current_stock"
                    type="number"
                    class="mt-1 block w-full"
                    min="0"
                    required
                    placeholder="0"
                  />
                  <InputError class="mt-2" :message="form.errors.current_stock" />
                  <div v-if="form.current_stock != element.current_stock" class="mt-1 text-sm">
                    <span class="text-orange-600">
                      ⚠️ Cambio de stock: {{ element.current_stock }} → {{ form.current_stock }}
                      ({{ form.current_stock - element.current_stock > 0 ? '+' : '' }}{{ form.current_stock - element.current_stock }})
                    </span>
                  </div>
                </div>

                <!-- Ubicación -->
                <div>
                  <InputLabel for="location" value="Ubicación" />
                  <TextInput
                    id="location"
                    v-model="form.location"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Ej: Estante A1, Almacén B"
                  />
                  <InputError class="mt-2" :message="form.errors.location" />
                </div>
              </div>

              <!-- Vista previa del nuevo estado del stock -->
              <div v-if="form.min_stock && form.max_stock && form.current_stock" class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Vista Previa del Nuevo Estado de Stock:</h4>
                <div class="flex items-center space-x-4">
                  <div class="flex-1">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        class="h-2 rounded-full transition-all duration-300"
                        :class="getNewStockBarClass()"
                        :style="`width: ${getNewStockPercentage()}%`"
                      ></div>
                    </div>
                  </div>
                  <div>
                    <span 
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="getNewStockBadgeClass()"
                    >
                      {{ getNewStockStatus() }}
                    </span>
                  </div>
                </div>
                <div class="mt-2 text-xs text-gray-500 flex justify-between">
                  <span>Mín: {{ form.min_stock }}</span>
                  <span>Actual: {{ form.current_stock }}</span>
                  <span>Máx: {{ form.max_stock }}</span>
                </div>
              </div>

              <!-- Botones -->
              <div class="flex items-center justify-end mt-6 space-x-3">
                <SecondaryButton type="button" @click="$inertia.visit(route('elements.show', element.id))">
                  Cancelar
                </SecondaryButton>
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Actualizar Elemento
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
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

const props = defineProps({
  element: Object
})

const form = useForm({
  name: props.element.name,
  description: props.element.description || '',
  category: props.element.category,
  unit: props.element.unit,
  min_stock: props.element.min_stock,
  max_stock: props.element.max_stock,
  current_stock: props.element.current_stock,
  location: props.element.location || ''
})

const submit = () => {
  form.put(route('elements.update', props.element.id), {
    onSuccess: () => {
      // Redirección manejada por el controlador
    }
  })
}

// Métodos para el estado actual
const getCurrentStockClass = () => {
  if (props.element.current_stock <= 0) return 'text-red-600'
  if (props.element.current_stock <= props.element.min_stock) return 'text-yellow-600'
  if (props.element.current_stock >= props.element.max_stock) return 'text-purple-600'
  return 'text-green-600'
}

const getCurrentStatus = () => {
  if (props.element.current_stock <= 0) return 'Agotado'
  if (props.element.current_stock <= props.element.min_stock) return 'Stock Bajo'
  if (props.element.current_stock >= props.element.max_stock) return 'Sobre Stock'
  return 'Normal'
}

const getCurrentStatusBadgeClass = () => {
  if (props.element.current_stock <= 0) return 'bg-red-100 text-red-800'
  if (props.element.current_stock <= props.element.min_stock) return 'bg-yellow-100 text-yellow-800'
  if (props.element.current_stock >= props.element.max_stock) return 'bg-purple-100 text-purple-800'
  return 'bg-green-100 text-green-800'
}

// Métodos para la vista previa del nuevo estado
const getNewStockPercentage = () => {
  if (!form.max_stock || form.max_stock === 0) return 0
  return Math.min((form.current_stock / form.max_stock) * 100, 100)
}

const getNewStockStatus = () => {
  if (form.current_stock <= 0) return 'Agotado'
  if (form.current_stock <= form.min_stock) return 'Stock Bajo'
  if (form.current_stock >= form.max_stock) return 'Sobre Stock'
  return 'Normal'
}

const getNewStockBarClass = () => {
  if (form.current_stock <= 0) return 'bg-red-500'
  if (form.current_stock <= form.min_stock) return 'bg-yellow-500'
  if (form.current_stock >= form.max_stock) return 'bg-purple-500'
  return 'bg-green-500'
}

const getNewStockBadgeClass = () => {
  if (form.current_stock <= 0) return 'bg-red-100 text-red-800'
  if (form.current_stock <= form.min_stock) return 'bg-yellow-100 text-yellow-800'
  if (form.current_stock >= form.max_stock) return 'bg-purple-100 text-purple-800'
  return 'bg-green-100 text-green-800'
}
</script>
