<template>
  <AppLayout title="Crear Proveedor">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Crear Nuevo Proveedor
        </h2>
        <Link :href="route('suppliers.index')" class="text-blue-600 hover:text-blue-800">
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
                  <InputLabel for="name" value="Nombre del Proveedor *" />
                  <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    placeholder="Ej: Distribuidora Electrónica S.A."
                  />
                  <InputError class="mt-2" :message="form.errors.name" />
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
                </div>

                <!-- Dirección -->
                <div class="md:col-span-2">
                  <InputLabel for="address" value="Dirección" />
                  <textarea
                    id="address"
                    v-model="form.address"
                    rows="3"
                    class="mt-1 py-2 px-3 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    placeholder="Calle, número, colonia, ciudad, código postal..."
                  ></textarea>
                  <InputError class="mt-2" :message="form.errors.address" />
                </div>
              </div>

              <!-- Vista previa -->
              <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Vista Previa:</h4>
                <div class="text-sm text-gray-600">
                  <div class="flex items-center mb-2">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-3">
                      {{ form.name ? form.name.charAt(0).toUpperCase() : '?' }}
                    </div>
                    <div>
                      <div class="font-medium">{{ form.name || 'Nombre del proveedor' }}</div>
                      <div class="text-gray-500">
                        <span :class="form.status === 'active' ? 'text-green-600' : 'text-red-600'">
                          {{ form.status === 'active' ? 'Activo' : 'Inactivo' }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                      <span class="font-medium">Email:</span> {{ form.email || 'No especificado' }}
                    </div>
                    <div>
                      <span class="font-medium">Teléfono:</span> {{ form.phone || 'No especificado' }}
                    </div>
                    <div>
                      <span class="font-medium">Website:</span> {{ form.website || 'No especificado' }}
                    </div>
                    <div class="md:col-span-2">
                      <span class="font-medium">Dirección:</span> {{ form.address || 'No especificada' }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Botones -->
              <div class="flex items-center justify-end mt-6 space-x-3">
                <SecondaryButton type="button" @click="$inertia.visit(route('suppliers.index'))">
                  Cancelar
                </SecondaryButton>
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Crear Proveedor
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

const form = useForm({
  name: '',
  phone: '',
  email: '',
  address: '',
  website: '',
  status: 'active'
})

const submit = () => {
  form.post(route('suppliers.store'), {
    onSuccess: () => {
      // Redirección manejada por el controlador
    }
  })
}
</script>
