<template>
  <AppLayout title="Editar Proveedor">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Editar Proveedor: {{ supplier.name }}
        </h2>
        <Link
          :href="route('suppliers.index')"
          class="text-blue-600 hover:text-blue-800"
        >
          ← Volver a la lista
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Información actual del proveedor -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
              <h3 class="text-lg font-medium text-blue-900 mb-2">Información Actual</h3>
              <div class="flex items-center mb-3">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                  {{ supplier.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <div class="font-medium text-blue-900">{{ supplier.name }}</div>
                  <div class="text-blue-700 text-sm">
                    <span :class="supplier.status === 'active' ? 'text-green-600' : 'text-red-600'">
                      {{ supplier.status === 'active' ? 'Activo' : 'Inactivo' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <form @submit.prevent="submit">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre -->
                <div class="md:col-span-2">
                  <InputLabel for="name" value="Nombre del Proveedor *" />
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    placeholder="Ej: Distribuidora Electrónica S.A."
                  />
                  <InputError class="mt-2" :message="form.errors.name" />
                  <div v-if="form.name !== supplier.name" class="mt-1 text-sm text-orange-600">
                    ⚠️ Cambio: "{{ supplier.name }}" → "{{ form.name }}"
                  </div>
                </div>

                <!-- Email -->
                <div>
                  <InputLabel for="email" value="Email" />
                  <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    placeholder="Ej: contacto@empresa.com"
                  />
                  <InputError class="mt-2" :message="form.errors.email" />
                  <div v-if="form.email !== (supplier.email || '')" class="mt-1 text-sm text-orange-600">
                    ⚠️ Cambio de email
                  </div>
                </div>

                <!-- Teléfono -->
                <div>
                  <InputLabel for="phone" value="Teléfono" />
                  <TextInput
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="mt-1 block w-full"
                    placeholder="Ej: +52 55 1234 5678"
                  />
                  <InputError class="mt-2" :message="form.errors.phone" />
                  <div v-if="form.phone !== (supplier.phone || '')" class="mt-1 text-sm text-orange-600">
                    ⚠️ Cambio de teléfono
                  </div>
                </div>

                <!-- Website -->
                <div>
                  <InputLabel for="website" value="Sitio Web" />
                  <TextInput
                    id="website"
                    v-model="form.website"
                    type="url"
                    class="mt-1 block w-full"
                    placeholder="https://ejemplo.com"
                  />
                  <InputError class="mt-2" :message="form.errors.website" />
                  <div v-if="form.website !== (supplier.website || '')" class="mt-1 text-sm text-orange-600">
                    ⚠️ Cambio de website
                  </div>
                </div>

                <!-- Estado -->
                <div>
                  <InputLabel for="status" value="Estado *" />
                  <select
                    id="status"
                    v-model="form.status"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    required
                  >
                    <option value="active">Activo</option>
                    <option value="inactive">Inactivo</option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.status" />
                  <div v-if="form.status !== supplier.status" class="mt-1 text-sm text-orange-600">
                    ⚠️ Cambio: "{{ supplier.status }}" → "{{ form.status }}"
                  </div>
                </div>

                <!-- Dirección -->
                <div class="md:col-span-2">
                  <InputLabel for="address" value="Dirección" />
                  <textarea
                    id="address"
                    v-model="form.address"
                    rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    placeholder="Calle, número, colonia, ciudad, código postal..."
                  ></textarea>
                  <InputError class="mt-2" :message="form.errors.address" />
                  <div v-if="form.address !== (supplier.address || '')" class="mt-1 text-sm text-orange-600">
                    ⚠️ Dirección modificada
                  </div>
                </div>
              </div>

              <!-- Resumen de cambios -->
              <div v-if="hasChanges" class="mt-6 p-4 bg-orange-50 rounded-lg">
                <h4 class="text-sm font-medium text-orange-700 mb-2">Resumen de Cambios:</h4>
                <ul class="text-sm text-orange-600 space-y-1">
                  <li v-if="form.name !== supplier.name">
                    • Nombre: "{{ supplier.name }}" → "{{ form.name }}"
                  </li>
                  <li v-if="form.phone !== (supplier.phone || '')">
                    • Teléfono: "{{ supplier.phone || 'Sin teléfono' }}" → "{{ form.phone || 'Sin teléfono' }}"
                  </li>
                  <li v-if="form.email !== (supplier.email || '')">
                    • Email: "{{ supplier.email || 'Sin email' }}" → "{{ form.email || 'Sin email' }}"
                  </li>
                  <li v-if="form.website !== (supplier.website || '')">
                    • Website: "{{ supplier.website || 'Sin website' }}" → "{{ form.website || 'Sin website' }}"
                  </li>
                  <li v-if="form.status !== supplier.status">
                    • Estado: "{{ supplier.status }}" → "{{ form.status }}"
                  </li>
                  <li v-if="form.address !== (supplier.address || '')">
                    • Dirección actualizada
                  </li>
                </ul>
              </div>

              <!-- Información del registro -->
              <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Información del registro:</h4>
                <div class="text-sm text-gray-600 grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <span class="font-medium">Creado:</span> 
                    {{ new Date(supplier.created_at).toLocaleDateString('es-ES') }}
                  </div>
                  <div>
                    <span class="font-medium">Actualizado:</span> 
                    {{ new Date(supplier.updated_at).toLocaleDateString('es-ES') }}
                  </div>
                </div>
              </div>

              <!-- Botones -->
              <div class="flex items-center justify-end mt-6 space-x-3">
                <SecondaryButton type="button" @click="$inertia.visit(route('suppliers.index'))">
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
                  Actualizar Proveedor
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
  supplier: Object
})

const form = useForm({
  name: props.supplier.name,
  phone: props.supplier.phone || '',
  email: props.supplier.email || '',
  address: props.supplier.address || '',
  website: props.supplier.website || '',
  status: props.supplier.status
})

const hasChanges = computed(() => {
  return form.name !== props.supplier.name ||
         form.phone !== (props.supplier.phone || '') ||
         form.email !== (props.supplier.email || '') ||
         form.address !== (props.supplier.address || '') ||
         form.website !== (props.supplier.website || '') ||
         form.status !== props.supplier.status
})

const submit = () => {
  form.put(route('suppliers.update', props.supplier.id), {
    onSuccess: () => {
      // Redirección manejada por el controlador
    }
  })
}
</script>