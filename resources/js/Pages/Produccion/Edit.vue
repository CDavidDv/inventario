<template>
  <AppLayout title="Editar Orden de Producción">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Editar Orden de Producción: {{ production.name }}
        </h2>
        <div class="flex space-x-2">
          <Link
            :href="route('production.show', production.id)"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
          >
            Ver Detalles
          </Link>
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
      </div>
    </template>

    <Container>
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
          <!-- Estado actual -->
          <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h3 class="text-lg font-medium text-yellow-900 mb-2">Información Actual</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
              <div>
                <span class="font-medium text-yellow-800">Estado:</span>
                <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                  Pendiente
                </span>
              </div>
              <div>
                <span class="font-medium text-yellow-800">Creado:</span>
                <span class="text-yellow-900 ml-2">
                  {{ new Date(production.created_at).toLocaleDateString('es-ES') }}
                </span>
              </div>
              <div>
                <span class="font-medium text-yellow-800">Creado por:</span>
                <span class="text-yellow-900 ml-2">{{ production.creator?.name || 'Usuario desconocido' }}</span>
              </div>
            </div>
          </div>

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
                  placeholder="Ej: Producción de Kit Arduino Básico - Lote 001"
                />
                <InputError class="mt-2" :message="form.errors.name" />
                <div v-if="form.name !== production.name" class="mt-1 text-sm text-orange-600">
                  ⚠️ Cambio: "{{ production.name }}" → "{{ form.name }}"
                </div>
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
                <div v-if="form.description !== (production.description || '')" class="mt-1 text-sm text-orange-600">
                  ⚠️ Descripción modificada
                </div>
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
                <div v-if="form.kit_id != production.kit_id" class="mt-1 text-sm text-orange-600">
                  ⚠️ Kit cambiado: "{{ production.kit?.name }}" → "{{ selectedKit?.name }}"
                </div>
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
                <div v-if="form.quantity_requested !== production.quantity_requested" class="mt-1 text-sm text-orange-600">
                  ⚠️ Cantidad cambiada: {{ production.quantity_requested }} → {{ form.quantity_requested }}
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
                <div v-if="form.due_date !== formatDateTime(production.due_date)" class="mt-1 text-sm text-orange-600">
                  ⚠️ Fecha límite modificada
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
                <div v-if="form.notes !== (production.notes || '')" class="mt-1 text-sm text-orange-600">
                  ⚠️ Notas modificadas
                </div>
              </div>

              <!-- Resumen de cambios -->
              <div v-if="hasChanges" class="bg-orange-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-orange-700 mb-2">Resumen de Cambios:</h4>
                <ul class="text-sm text-orange-600 space-y-1">
                  <li v-if="form.name !== production.name">
                    • Nombre: "{{ production.name }}" → "{{ form.name }}"
                  </li>
                  <li v-if="form.description !== (production.description || '')">
                    • Descripción actualizada
                  </li>
                  <li v-if="form.kit_id != production.kit_id">
                    • Kit: "{{ production.kit?.name }}" → "{{ selectedKit?.name }}"
                  </li>
                  <li v-if="form.quantity_requested !== production.quantity_requested">
                    • Cantidad: {{ production.quantity_requested }} → {{ form.quantity_requested }}
                  </li>
                  <li v-if="form.due_date !== formatDateTime(production.due_date)">
                    • Fecha límite actualizada
                  </li>
                  <li v-if="form.notes !== (production.notes || '')">
                    • Notas actualizadas
                  </li>
                </ul>
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
                      <p>No hay suficientes componentes en stock para completar esta producción con la nueva configuración.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Botones -->
            <div class="flex items-center justify-end mt-6 space-x-3">
              <Link
                :href="route('production.show', production.id)"
                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing || !hasChanges || hasInsufficientComponents"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
              >
                <span v-if="form.processing">Guardando...</span>
                <span v-else>Guardar Cambios</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'

const props = defineProps({
  production: Object,
  kits: Array
})

const form = useForm({
  name: props.production.name,
  description: props.production.description || '',
  kit_id: props.production.kit_id,
  quantity_requested: props.production.quantity_requested,
  due_date: formatDateTime(props.production.due_date),
  notes: props.production.notes || ''
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

// Verificar si hay cambios
const hasChanges = computed(() => {
  return form.name !== props.production.name ||
         form.description !== (props.production.description || '') ||
         form.kit_id != props.production.kit_id ||
         form.quantity_requested !== props.production.quantity_requested ||
         form.due_date !== formatDateTime(props.production.due_date) ||
         form.notes !== (props.production.notes || '')
})

// Verificar si hay componentes insuficientes
const hasInsufficientComponents = computed(() => {
  if (!selectedKit.value || form.quantity_requested <= 0) {
    return false
  }
  
  // Nota: Aquí necesitaríamos los componentes del kit, 
  // pero para simplificar, asumimos que está disponible la información
  return false // Implementar lógica de verificación si es necesario
})

function formatDateTime(dateTime) {
  if (!dateTime) return ''
  const date = new Date(dateTime)
  return date.toISOString().slice(0, 16)
}

const onKitChange = () => {
  // Resetear cantidad cuando se cambia el kit si es necesario
  if (form.quantity_requested <= 0) {
    form.quantity_requested = 1
  }
}

const submit = () => {
  form.put(route('production.update', props.production.id))
}
</script>