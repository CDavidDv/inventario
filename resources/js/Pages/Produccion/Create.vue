<template>
  <AppLayout title="Nueva Orden de Producción">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Nueva Orden de Producción
        </h2>
        <Link
          :href="route('production.index')"
          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Volver
        </Link>
      </div>
    </template>

    <Container>
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
          <form @submit.prevent="submit">
            <div class="space-y-6">
              <!-- Nombre de la orden -->
              <div>
                <InputLabel for="name" value="Nombre de la Orden *" />
                <TextInput
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="mt-1 block w-full"
                  required
                  autofocus
                  placeholder="Ej: Producción de Kit Arduino Básico - Lote 001"
                />
                <InputError class="mt-2" :message="form.errors.name" />
              </div>

              <!-- Descripción -->
              <div>
                <InputLabel for="description" value="Descripción" />
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  placeholder="Describe los detalles de esta orden de producción..."
                ></textarea>
                <InputError class="mt-2" :message="form.errors.description" />
              </div>

              <!-- Kit a producir -->
              <div>
                <InputLabel for="kit_id" value="Kit a Producir *" />
                <select
                  id="kit_id"
                  v-model="form.kit_id"
                  class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  required
                  @change="onKitChange"
                >
                  <option value="">Seleccionar kit</option>
                  <option v-for="kit in kits" :key="kit.id" :value="kit.id">
                    {{ kit.name }} (Stock actual: {{ kit.current_stock }})
                  </option>
                </select>
                <InputError class="mt-2" :message="form.errors.kit_id" />
              </div>

              <!-- Información del kit seleccionado -->
              <div v-if="selectedKit" class="bg-blue-50 p-4 rounded-lg">
                <h4 class="text-lg font-medium text-blue-900 mb-2">Información del Kit</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <span class="font-medium text-blue-800">Nombre:</span>
                    <span class="text-blue-900 ml-2">{{ selectedKit.name }}</span>
                  </div>
                  <div>
                    <span class="font-medium text-blue-800">Stock actual:</span>
                    <span class="text-blue-900 ml-2">{{ selectedKit.current_stock }} unidades</span>
                  </div>
                </div>
                
                <!-- Componentes del kit -->
                <div v-if="selectedKit.assigned_items && selectedKit.assigned_items.length > 0">
                  <h5 class="font-medium text-blue-800 mb-2">Componentes necesarios por kit:</h5>
                  <div class="space-y-2">
                    <div 
                      v-for="assignment in selectedKit.assigned_items" 
                      :key="assignment.id"
                      class="flex justify-between items-center bg-white p-2 rounded border-l-4"
                      :class="getComponentStatusClass(assignment, form.quantity_requested)"
                    >
                      <div>
                        <span class="font-medium">{{ assignment.item.name }}</span>
                        <span class="text-sm text-gray-600 ml-2">
                          ({{ assignment.quantity }} {{ assignment.quantity === 1 ? 'unidad' : 'unidades' }} por kit)
                        </span>
                      </div>
                      <div class="text-sm">
                        <span class="font-medium">Stock disponible:</span>
                        <span :class="assignment.item.current_stock >= (assignment.quantity * form.quantity_requested) ? 'text-green-600' : 'text-red-600'">
                          {{ assignment.item.current_stock }}
                        </span>
                        <span class="text-gray-600">
                          / {{ assignment.quantity * form.quantity_requested }} necesario
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Cantidad solicitada -->
              <div>
                <InputLabel for="quantity_requested" value="Cantidad a Producir *" />
                <TextInput
                  id="quantity_requested"
                  v-model.number="form.quantity_requested"
                  type="number"
                  min="1"
                  class="mt-1 block w-full"
                  required
                  placeholder="Cantidad de kits a producir"
                />
                <InputError class="mt-2" :message="form.errors.quantity_requested" />
                <div v-if="form.quantity_requested > 0" class="text-sm text-gray-600 mt-1">
                  Se producirán {{ form.quantity_requested }} {{ form.quantity_requested === 1 ? 'kit' : 'kits' }}
                </div>
              </div>

              <!-- Fecha límite -->
              <div>
                <InputLabel for="due_date" value="Fecha Límite" />
                <TextInput
                  id="due_date"
                  v-model="form.due_date"
                  type="datetime-local"
                  class="mt-1 block w-full"
                  :min="minDateTime"
                />
                <InputError class="mt-2" :message="form.errors.due_date" />
                <div class="text-sm text-gray-600 mt-1">
                  Opcional: Fecha y hora límite para completar la producción
                </div>
              </div>

              <!-- Notas -->
              <div>
                <InputLabel for="notes" value="Notas Adicionales" />
                <textarea
                  id="notes"
                  v-model="form.notes"
                  rows="3"
                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                  placeholder="Instrucciones especiales, consideraciones técnicas, etc."
                ></textarea>
                <InputError class="mt-2" :message="form.errors.notes" />
              </div>

              <!-- Resumen de la orden -->
              <div v-if="form.name && form.kit_id && form.quantity_requested > 0" class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-lg font-medium text-gray-900 mb-2">Resumen de la Orden</h4>
                <div class="space-y-2">
                  <div><span class="font-medium">Orden:</span> {{ form.name }}</div>
                  <div><span class="font-medium">Kit:</span> {{ selectedKit?.name }}</div>
                  <div><span class="font-medium">Cantidad:</span> {{ form.quantity_requested }} {{ form.quantity_requested === 1 ? 'kit' : 'kits' }}</div>
                  <div v-if="form.due_date">
                    <span class="font-medium">Fecha límite:</span> 
                    {{ new Date(form.due_date).toLocaleDateString('es-ES', { 
                      year: 'numeric', 
                      month: 'long', 
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    }) }}
                  </div>
                </div>
              </div>

              <!-- Advertencias -->
              <div v-if="hasInsufficientComponents" class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Stock Insuficiente</h3>
                    <div class="mt-2 text-sm text-red-700">
                      <p>No hay suficientes componentes en stock para completar esta producción. Verifica el inventario antes de continuar.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end mt-6 space-x-3">
              <Link
                :href="route('production.index')"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing || hasInsufficientComponents"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
              >
                <span v-if="form.processing">Creando...</span>
                <span v-else>Crear Orden de Producción</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  kits: Array
})

const form = useForm({
  name: '',
  description: '',
  kit_id: '',
  quantity_requested: 1,
  due_date: '',
  notes: ''
})

// Fecha mínima (mañana)
const minDateTime = computed(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().slice(0, 16)
})

// Kit seleccionado
const selectedKit = computed(() => {
  if (!form.kit_id) return null
  return props.kits.find(kit => kit.id == form.kit_id)
})

// Verificar si hay componentes insuficientes
const hasInsufficientComponents = computed(() => {
  if (!selectedKit.value || !selectedKit.value.assigned_items || form.quantity_requested <= 0) {
    return false
  }
  
  return selectedKit.value.assigned_items.some(assignment => {
    const neededQuantity = assignment.quantity * form.quantity_requested
    return assignment.item.current_stock < neededQuantity
  })
})

const onKitChange = () => {
  // Resetear cantidad cuando se cambia el kit
  if (form.quantity_requested <= 0) {
    form.quantity_requested = 1
  }
}

const getComponentStatusClass = (assignment, quantityRequested) => {
  const neededQuantity = assignment.quantity * quantityRequested
  const available = assignment.item.current_stock
  
  if (available >= neededQuantity) {
    return 'border-green-400'
  } else {
    return 'border-red-400'
  }
}

const submit = () => {
  form.post(route('production.store'))
}
</script>