<template>
  <AppLayout title="Crear Elemento">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Crear Nuevo Elemento
        </h2>
        <Link :href="route('elements.index')" class="text-blue-600 hover:text-blue-800">
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
                <!-- Nombre -->
                <div class="md:col-span-2">
                  <InputLabel for="name" value="Nombre del Elemento *" />
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
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

              <!-- Vista previa del estado del stock -->
              <div v-if="form.min_stock && form.max_stock && form.current_stock" class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Vista Previa del Estado de Stock:</h4>
                <div class="flex items-center space-x-4">
                  <div class="flex-1">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        class="h-2 rounded-full transition-all duration-300"
                        :class="getStockBarClass()"
                        :style="`width: ${getStockPercentage()}%`"
                      ></div>
                    </div>
                  </div>
                  <div>
                    <span 
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="getStockBadgeClass()"
                    >
                      {{ getStockStatus() }}
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
                <SecondaryButton type="button" @click="$inertia.visit(route('elements.index'))">
                  Cancelar
                </SecondaryButton>
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Crear Elemento
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

const form = useForm({
  name: '',
  description: '',
  category: '',
  unit: '',
  min_stock: 0,
  max_stock: 0,
  current_stock: 0,
  location: ''
})

const submit = () => {
  form.post(route('elements.store'), {
    onSuccess: () => {
      // Redirección manejada por el controlador
    }
  })
}

// Métodos para la vista previa del stock
const getStockPercentage = () => {
  if (!form.max_stock || form.max_stock === 0) return 0
  return Math.min((form.current_stock / form.max_stock) * 100, 100)
}

const getStockStatus = () => {
  if (form.current_stock <= 0) return 'Agotado'
  if (form.current_stock <= form.min_stock) return 'Stock Bajo'
  if (form.current_stock >= form.max_stock) return 'Sobre Stock'
  return 'Normal'
}

const getStockBarClass = () => {
  if (form.current_stock <= 0) return 'bg-red-500'
  if (form.current_stock <= form.min_stock) return 'bg-yellow-500'
  if (form.current_stock >= form.max_stock) return 'bg-purple-500'
  return 'bg-green-500'
}

const getStockBadgeClass = () => {
  if (form.current_stock <= 0) return 'bg-red-100 text-red-800'
  if (form.current_stock <= form.min_stock) return 'bg-yellow-100 text-yellow-800'
  if (form.current_stock >= form.max_stock) return 'bg-purple-100 text-purple-800'
  return 'bg-green-100 text-green-800'
}
</script>
