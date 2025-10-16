<template>
  <AppLayout :title="'Orden de Producción: ' + production.name">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Orden de Producción: {{ production.name }}
        </h2>
        <div class="flex space-x-2">
          <Link
            v-if="production.status === 'pending'"
            :href="route('production.edit', production.id)"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
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
      <div class="space-y-6">
        <!-- Estado y información básica -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-start mb-6">
              <div>
                <h3 class="text-lg font-medium text-gray-900">Información General</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Detalles de la orden de producción</p>
              </div>
              <span 
                :class="getStatusBadgeClass(production.status)"
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
              >
                {{ getStatusText(production.status) }}
              </span>
            </div>

            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
              <div>
                <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ production.name }}</dd>
              </div>
              <div v-if="production.description">
                <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ production.description }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Kit a Producir</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ production.kit?.name || 'N/A' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Cantidad Solicitada</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ production.quantity_requested }}</dd>
              </div>
              <div v-if="production.quantity_produced > 0">
                <dt class="text-sm font-medium text-gray-500">Cantidad Producida</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ production.quantity_produced }}</dd>
              </div>
              <div v-if="production.due_date">
                <dt class="text-sm font-medium text-gray-500">Fecha Límite</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ new Date(production.due_date).toLocaleDateString('es-ES', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                  }) }}
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Creado por</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ production.creator?.name || 'Usuario desconocido' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Fecha de Creación</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ new Date(production.created_at).toLocaleDateString('es-ES') }}
                </dd>
              </div>
              <div v-if="production.start_date">
                <dt class="text-sm font-medium text-gray-500">Fecha de Inicio</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ new Date(production.start_date).toLocaleDateString('es-ES', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                  }) }}
                </dd>
              </div>
              <div v-if="production.end_date">
                <dt class="text-sm font-medium text-gray-500">Fecha de Finalización</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ new Date(production.end_date).toLocaleDateString('es-ES', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                  }) }}
                </dd>
              </div>
            </dl>

            <!-- Progreso -->
            <div v-if="production.status === 'in_progress' && production.quantity_requested > 0" class="mt-6">
              <dt class="text-sm font-medium text-gray-500">Progreso</dt>
              <div class="mt-2">
                <div class="flex justify-between text-sm text-gray-700 mb-1">
                  <span>{{ production.quantity_produced }} de {{ production.quantity_requested }} completados</span>
                  <span>{{ Math.round((production.quantity_produced / production.quantity_requested) * 100) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full" 
                    :style="{ width: Math.round((production.quantity_produced / production.quantity_requested) * 100) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Componentes necesarios -->
        <div v-if="componentsStatus && componentsStatus.length > 0" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Componentes Necesarios</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Componente
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Por Kit
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Total Necesario
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Disponible
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Estado
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="component in componentsStatus" :key="component.component.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ component.component.name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ component.needed_per_kit }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ component.total_needed }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ component.available }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        :class="component.sufficient ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100'"
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      >
                        {{ component.sufficient ? 'Suficiente' : 'Insuficiente' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Componentes utilizados (solo si la producción está completada) -->
        <div v-if="production.components_used && production.components_used.length > 0" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Componentes Utilizados</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Componente
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Cantidad Utilizada
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="component in production.components_used" :key="component.item_id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ component.item_name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ component.quantity_used }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Notas -->
        <div v-if="production.notes" class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Notas</h3>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ production.notes }}</p>
          </div>
        </div>

        <!-- Acciones -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones</h3>
            <div class="flex flex-wrap gap-3">
              
              <!-- Iniciar producción -->
              <button
                v-if="production.status === 'pending'"
                @click="startProduction"
                :disabled="processing || hasInsufficientComponents"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
              >
                <span v-if="processing">Iniciando...</span>
                <span v-else>Iniciar Producción</span>
              </button>

              <!-- Actualizar progreso -->
              <button
                v-if="production.status === 'in_progress'"
                @click="showUpdateProgressModal = true"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
              >
                Actualizar Progreso
              </button>

              <!-- Completar producción -->
              <button
                v-if="production.status === 'in_progress'"
                @click="showCompleteModal = true"
                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
              >
                Completar Producción
              </button>

              <!-- Cancelar producción -->
              <button
                v-if="['pending', 'in_progress'].includes(production.status)"
                @click="cancelProduction"
                :disabled="processing"
                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
              >
                <span v-if="processing">Cancelando...</span>
                <span v-else>Cancelar Producción</span>
              </button>

              <!-- Eliminar -->
              <button
                v-if="['pending', 'cancelled'].includes(production.status)"
                @click="deleteProduction"
                :disabled="processing"
                class="bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
              >
                <span v-if="processing">Eliminando...</span>
                <span v-else>Eliminar</span>
              </button>
            </div>
            
            <!-- Advertencia de componentes insuficientes -->
            <div v-if="hasInsufficientComponents && production.status === 'pending'" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
              <div class="flex">
                <div class="flex-shrink-0">
                  <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="ml-3">
                  <h3 class="text-sm font-medium text-red-800">Stock Insuficiente</h3>
                  <div class="mt-2 text-sm text-red-700">
                    <p>No hay suficientes componentes en stock para iniciar esta producción.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Container>

    <!-- Modal para completar producción -->
    <DialogModal :show="showCompleteModal" @close="showCompleteModal = false">
      <template #title>
        Completar Producción
      </template>

      <template #content>
        <div class="space-y-4">
          <div>
            <InputLabel for="quantity_produced" value="Cantidad Producida *" />
            <TextInput
              id="quantity_produced"
              v-model.number="completeForm.quantity_produced"
              type="number"
              :min="1"
              :max="production.quantity_requested"
              class="mt-1 block w-full"
              required
            />
            <InputError class="mt-2" :message="completeForm.errors.quantity_produced" />
          </div>
          <div>
            <InputLabel for="completion_notes" value="Notas de Finalización" />
            <textarea
              id="completion_notes"
              v-model="completeForm.notes"
              rows="3"
              class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
              placeholder="Notas sobre la finalización de la producción..."
            ></textarea>
            <InputError class="mt-2" :message="completeForm.errors.notes" />
          </div>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="showCompleteModal = false">
          Cancelar
        </SecondaryButton>
        <PrimaryButton 
          class="ml-3" 
          @click="completeProduction" 
          :disabled="completeForm.processing"
        >
          <span v-if="completeForm.processing">Completando...</span>
          <span v-else>Completar Producción</span>
        </PrimaryButton>
      </template>
    </DialogModal>

    <!-- Modal para actualizar progreso -->
    <DialogModal :show="showUpdateProgressModal" @close="showUpdateProgressModal = false">
      <template #title>
        Actualizar Progreso
      </template>

      <template #content>
        <div class="space-y-4">
          <div>
            <InputLabel for="progress_quantity" value="Cantidad Producida Hasta Ahora *" />
            <TextInput
              id="progress_quantity"
              v-model.number="progressForm.quantity_produced"
              type="number"
              :min="0"
              :max="production.quantity_requested"
              class="mt-1 block w-full"
              required
            />
            <InputError class="mt-2" :message="progressForm.errors.quantity_produced" />
          </div>
          <div>
            <InputLabel for="progress_notes" value="Notas de Progreso" />
            <textarea
              id="progress_notes"
              v-model="progressForm.notes"
              rows="3"
              class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
              placeholder="Notas sobre el progreso actual..."
            ></textarea>
            <InputError class="mt-2" :message="progressForm.errors.notes" />
          </div>
        </div>
      </template>

      <template #footer>
        <SecondaryButton @click="showUpdateProgressModal = false">
          Cancelar
        </SecondaryButton>
        <PrimaryButton 
          class="ml-3" 
          @click="updateProgress" 
          :disabled="progressForm.processing"
        >
          <span v-if="progressForm.processing">Actualizando...</span>
          <span v-else>Actualizar Progreso</span>
        </PrimaryButton>
      </template>
    </DialogModal>
  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import DialogModal from '@/Components/DialogModal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  production: Object,
  componentsStatus: Array
})

const processing = ref(false)
const showCompleteModal = ref(false)
const showUpdateProgressModal = ref(false)

const completeForm = useForm({
  quantity_produced: props.production.quantity_requested,
  notes: ''
})

const progressForm = useForm({
  quantity_produced: props.production.quantity_produced || 0,
  notes: ''
})

// Verificar si hay componentes insuficientes
const hasInsufficientComponents = computed(() => {
  if (!props.componentsStatus) return false
  return props.componentsStatus.some(component => !component.sufficient)
})

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: 'Pendiente',
    in_progress: 'En Progreso',
    completed: 'Completado',
    cancelled: 'Cancelado'
  }
  return texts[status] || status
}

const startProduction = () => {
  processing.value = true
  router.post(route('production.start', props.production.id), {}, {
    onSuccess: () => {
      processing.value = false
    },
    onError: () => {
      processing.value = false
    }
  })
}

const completeProduction = () => {
  completeForm.post(route('production.complete', props.production.id), {
    onSuccess: () => {
      showCompleteModal.value = false
      completeForm.reset()
    }
  })
}

const updateProgress = () => {
  progressForm.post(route('production.update-progress', props.production.id), {
    onSuccess: () => {
      showUpdateProgressModal.value = false
    }
  })
}

const cancelProduction = () => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción cancelará la orden de producción',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Sí, cancelar',
    cancelButtonText: 'No'
  }).then((result) => {
    if (result.isConfirmed) {
      processing.value = true
      router.post(route('production.cancel', props.production.id), {}, {
        onSuccess: () => {
          processing.value = false
        },
        onError: () => {
          processing.value = false
        }
      })
    }
  })
}

const deleteProduction = () => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: 'Esta acción eliminará permanentemente la orden de producción',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      processing.value = true
      router.delete(route('production.destroy', props.production.id), {
        onSuccess: () => {
          processing.value = false
        },
        onError: () => {
          processing.value = false
        }
      })
    }
  })
}
</script>