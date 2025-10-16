<template>
  <AppLayout title="Administración de Permisos">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Administración de Permisos
      </h2>
    </template>

    <Container>
      <div class="pb-8">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
          <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-full">
              <Shield class="w-8 h-8 text-purple-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-2xl font-bold text-gray-900">Gestión de Permisos por Rol</h3>
              <p class="text-sm text-gray-600 mt-1">Configura los permisos específicos de cada rol del sistema</p>
            </div>
          </div>
        </div>

        <!-- Roles con Permisos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 mt-8 gap-6">
          <div v-for="role in roles" :key="role.id" class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h4 class="text-lg font-bold text-gray-900 capitalize">{{ getRoleLabel(role.name) }}</h4>
                <p class="text-sm text-gray-500">{{ role.permissions.length }} permisos asignados</p>
              </div>
              <button
                @click="openRoleModal(role)"
                class="p-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors"
                title="Editar permisos">
                <Edit class="w-5 h-5" />
              </button>
            </div>

            <div class="space-y-2 max-h-96 overflow-y-auto">
              <div
                v-for="permission in role.permissions"
                :key="permission.id"
                class="flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg">
                <Check class="w-4 h-4 text-green-600 flex-shrink-0" />
                <span class="text-sm text-gray-700">{{ permission.description || permission.name }}</span>
              </div>

              <div v-if="role.permissions.length === 0" class="text-center py-8 text-gray-400 italic">
                Sin permisos asignados
              </div>
            </div>
          </div>
        </div>

        <!-- Modal de Edición de Permisos -->
        <div
          v-if="showModal"
          class="fixed inset-0 pt-10 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
          @click="closeModal">
          <div class="relative  mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white" @click.stop>
            <div class="mt-3">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                  Administrar Permisos de {{ getRoleLabel(selectedRole?.name) }}
                </h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                  <X class="w-6 h-6" />
                </button>
              </div>

              <div v-if="selectedRole" class="mb-4">
                <p class="text-sm text-gray-600 mb-4">
                  Selecciona los permisos que deseas asignar a este rol
                </p>

                <!-- Permisos agrupados por módulo -->
                <div class="space-y-6 max-h-96 overflow-y-auto">
                  <div v-for="(permissionGroup, module) in permissions" :key="module">
                    <h4 class="text-md font-semibold text-gray-700 mb-2 capitalize">
                      {{ getModuleLabel(module) }}
                    </h4>
                    <div class="space-y-2 ml-4">
                      <div v-for="permission in permissionGroup" :key="permission.id" class="flex items-center">
                        <input
                          :id="`permission-${permission.id}`"
                          v-model="selectedPermissions"
                          :value="permission.id"
                          type="checkbox"
                          class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <label :for="`permission-${permission.id}`" class="ml-2 block text-sm text-gray-900">
                          {{ permission.description || permission.name }}
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-end space-x-3 mt-6">
                <button
                  @click="closeModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                  Cancelar
                </button>
                <button
                  @click="updatePermissions"
                  class="px-4 py-2 text-sm font-medium text-white bg-purple-600 border border-transparent rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                  Guardar Cambios
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { Shield, Edit, Check, X } from 'lucide-vue-next';
import Swal from 'sweetalert2';

const props = defineProps({
  roles: Array,
  permissions: Object,
});

const showModal = ref(false);
const selectedRole = ref(null);
const selectedPermissions = ref([]);

const getRoleLabel = (roleName) => {
  const labels = {
    admin: 'Administrador',
    supervisor: 'Supervisor',
    worker: 'Trabajador',
    manager: 'Gerente',
    user: 'Usuario',
  };
  return labels[roleName] || roleName;
};

const getModuleLabel = (module) => {
  const labels = {
    dashboard: 'Dashboard',
    inventory: 'Inventario',
    production: 'Producción',
    supplier: 'Distribuidores',
    movements: 'Movimientos',
    supervisor: 'Supervisor',
    system: 'Sistema',
    permissions: 'Permisos',
    reports: 'Reportes',
    categories: 'Categorías',
    settings: 'Configuración',
    general: 'General',
  };
  return labels[module] || module;
};

const openRoleModal = (role) => {
  selectedRole.value = role;
  selectedPermissions.value = role.permissions.map(p => p.id);
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedRole.value = null;
  selectedPermissions.value = [];
};

const updatePermissions = async () => {
  try {
    await router.post(route('permissions.roles.update', selectedRole.value.id), {
      permissions: selectedPermissions.value,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        closeModal();
        Swal.fire({
          title: '¡Actualizado!',
          text: 'Permisos del rol actualizados correctamente',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
        });
      },
      onError: (error) => {
        Swal.fire({
          title: 'Error',
          text: 'No se pudieron actualizar los permisos',
          icon: 'error',
        });
      },
    });
  } catch (error) {
    console.error('Error updating permissions:', error);
    Swal.fire({
      title: 'Error',
      text: 'Ocurrió un error inesperado',
      icon: 'error',
    });
  }
};
</script>
