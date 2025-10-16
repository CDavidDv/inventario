<template>
  <AppLayout title="Administrar personal">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Administrar Personal
      </h2>
    </template>
    <Container>
      <div class="pb-8 space-y-8">
        <!-- Estadísticas generales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
              <div class="p-3 bg-blue-100 rounded-full">
                <User2Icon class="w-8 h-8 text-blue-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Usuarios</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.total_users }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-full">
                <CheckCircle2Icon class="w-8 h-8 text-green-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Con Roles</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.users_with_roles }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
              <div class="p-3 bg-yellow-100 rounded-full">
                <AlertTriangleIcon class="w-8 h-8 text-yellow-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Sin Roles</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.users_without_roles }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
              <div class="p-3 bg-purple-100 rounded-full">
                <FileIcon class="w-8 h-8 text-purple-600" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Roles</p>
                <p class="text-3xl font-bold text-gray-900">{{ stats.total_roles }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Lista de usuarios -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900 flex gap-2"><Users class="" /> Usuarios del Sistema</h3>
            <button @click="openCreateModal" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
              <User2Icon class="w-4 h-4" />
              Nuevo Usuario
            </button>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registro</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                        <span class="text-indigo-600 font-medium">{{ user.name.charAt(0).toUpperCase() }}</span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ user.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="role in user.roles" :key="role.id"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="getRoleColor(role.name)">
                        {{ getRoleLabel(role.name) }}
                      </span>
                      <span v-if="user.roles.length === 0" class="text-xs text-gray-400 italic">Sin roles asignados</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(user.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="user.active" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      Activo
                    </span>
                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                      Inactivo
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center gap-2">
                        <!-- Botón Activo/Inactivo -->
                        <button @click="toggleUserActive(user)"
                            :class="user.active ? 'text-green-600 hover:text-green-800' : 'text-gray-400 hover:text-gray-600'"
                            class="p-1 rounded transition-colors"
                            :title="user.active ? 'Desactivar' : 'Activar'">
                            <div v-if="user.active" class="w-4 h-4 bg-green-500 rounded-full"></div>
                            <div v-else class="w-4 h-4 bg-gray-300 rounded-full"></div>
                        </button>

                        <!-- Botón Ver detalles -->
                        <button @click="viewUser(user)"
                            class="text-gray-600 hover:text-gray-800 p-1 rounded transition-colors"
                            title="Ver detalles">
                            <Eye class="w-4 h-4" />
                        </button>
                        
                        <!-- Botón Asignar Roles -->
                        <button @click="openRoleModal(user)"
                            class="text-purple-600 hover:text-purple-800 p-1 rounded transition-colors"
                            title="Administrar Roles">
                            <ShieldIcon class="w-4 h-4" />
                        </button>
                        
                        <!-- Botón Editar -->
                        <button @click="openEditModal(user)"
                            class="text-blue-600 hover:text-blue-800 p-1 rounded transition-colors"
                            title="Editar Usuario">
                            <EditIcon class="w-4 h-4" />
                        </button>
                        
                        <!-- Botón Eliminar -->
                        <button @click="confirmDeleteUser(user)"
                            class="text-red-600 hover:text-red-800 p-1 rounded transition-colors"
                            title="Eliminar Usuario">
                            <Trash2Icon class="w-4 h-4" />
                        </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Lista de roles -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">🛡️ Roles del Sistema</h3>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div v-for="role in roles" :key="role.id"
                  class="border border-gray-400 rounded-lg p-4 hover:shadow-md transition-shadow">
              <div class=" flex items-center justify-between mb-2">
                <h4 class="text-lg font-medium text-gray-900">{{ getRoleLabel(role.name) }}</h4>
                <span :class="['px-2 py-1 rounded-full text-xs font-medium', getRoleColor(role.name)]">
                  {{ getRoleLabel(role.name) }}
                </span>
              </div>
              <!--
                <p class="text-sm text-gray-500">{{ role.permissions_count }} permisos</p>
              -->
              <p class="text-sm text-gray-500">{{ getUsersWithRole(role.name) }} usuarios</p>
            </div>
          </div>
        </div>
      </div>
    </Container>

    <!-- Modal para Administrar Roles -->
    <div v-if="showRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeRoleModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Administrar Roles</h3>
            <button @click="closeRoleModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div v-if="selectedUser" class="mb-4">
            <p class="text-sm text-gray-600 mb-2">Usuario: <span class="font-medium">{{ selectedUser.name }}</span></p>
            <p class="text-sm text-gray-600 mb-4">Email: <span class="font-medium">{{ selectedUser.email }}</span></p>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Rol asignado (solo uno):</label>
              <div class="space-y-2">
                <div v-for="role in roles" :key="role.id" class="flex items-center">
                  <input
                    :id="`role-${role.id}`"
                    v-model="selectedRole"
                    :value="role.id"
                    type="radio"
                    name="user-role"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                  />
                  <label :for="`role-${role.id}`" class="ml-2 block text-sm text-gray-900">
                    <span :class="['px-2 py-1 rounded-full text-xs font-medium mr-2', getRoleColor(role.name)]">
                      {{ getRoleLabel(role.name) }}
                    </span>
                    ({{ role.permissions_count }} permisos)
                  </label>
                </div>
              </div>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3">
            <button @click="closeRoleModal" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
              Cancelar
            </button>
            <button @click="updateUserRoles" 
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Guardar Cambios
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para Editar Usuario -->
    <div v-if="showEditModal" class="fixed  inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeEditModal">
      <div class="relative top-20 mx-auto  py-10 border max-w-3xl px-10 min-w-3xl shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3 ">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Editar Usuario</h3>
            <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="updateUser" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nombre</label>
              <input 
                v-model="editForm.name"
                placeholder="Nombre completo"
                type="text" 
                required
                class="mt-1 py-2 px-3 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <input 
                v-model="editForm.email"
                placeholder="example@email.com"
                type="email" 
                required
                class="mt-1 py-2 px-3 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Contraseña <small>(Opcional)</small></label>
              <input v-model="editForm.password" 
                     class="mt-1 py-2 px-3 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                     type="password" 
                     placeholder="Nueva contraseña"  />
            </div>
            
            <div v-if="editForm.password">
              <label class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
              <input v-model="editForm.password_confirmation" 
                     class=" py-2 px-3 mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                     type="password" 
                     placeholder="Confirmar nueva contraseña"  />
            </div>
            
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeEditModal" 
                      class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Cancelar
              </button>
              <button type="submit" 
                      class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Actualizar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal para Crear Usuario -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-scroll h-full w-full z-50" @click="closeCreateModal">
      <div class="relative top-5 mx-auto py-10 overflow-auto border max-w-3xl px-10 min-w-3xl shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Crear Nuevo Usuario</h3>
            <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="createUser" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nombre *</label>
              <input 
                v-model="createForm.name"
                placeholder="Nombre completo"
                type="text" 
                required
                class="mt-1 py-2 px-3 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Email *</label>
              <input 
                v-model="createForm.email"
                placeholder="example@email.com"
                type="email" 
                required
                class="mt-1 py-2 px-3 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Contraseña *</label>
              <input 
                v-model="createForm.password"
                placeholder="Contraseña segura (mínimo 8 caracteres)"
                type="password" 
                required
                minlength="8"
                class=" py-2 px-3 mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Rol (Opcional - solo uno)</label>
              <div class="mt-2 space-y-2 max-h-32 overflow-y-auto">
                <div v-for="role in roles" :key="role.id" class="flex items-center">
                  <input
                    :id="`create-role-${role.id}`"
                    v-model="createForm.role"
                    :value="role.id"
                    type="radio"
                    name="create-user-role"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                  />
                  <label :for="`create-role-${role.id}`" class="ml-2 block text-sm text-gray-900">
                    <span :class="['px-2 py-1 rounded-full text-xs font-medium mr-2', getRoleColor(role.name)]">
                      {{ getRoleLabel(role.name) }}
                    </span>
                    ({{ role.permissions_count }} permisos)
                  </label>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button type="button" @click="closeCreateModal" 
                      class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Cancelar
              </button>
              <button type="submit" 
                      class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Crear Usuario
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { AlertTriangle, AlertTriangleIcon, CheckCircle2, CheckCircle2Icon, FileIcon, User2Icon, Users, ShieldIcon, EditIcon, Trash2Icon, Eye } from 'lucide-vue-next';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from '@/axios-config';
import Swal from 'sweetalert2';

const props = defineProps({
  users: Array,
  roles: Array,
  stats: Object
});

// Estados reactivos
const showRoleModal = ref(false);
const showEditModal = ref(false);
const showCreateModal = ref(false);
const selectedUser = ref(null);
const selectedRole = ref(null);
const editForm = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});
const createForm = ref({
  name: '',
  email: '',
  password: '',
  role: null
});

// Función para obtener el color del rol
const getRoleColor = (roleName) => {
  const colors = {
    admin: 'bg-red-100 text-red-800',
    supervisor: 'bg-blue-100 text-blue-800',
    worker: 'bg-green-100 text-green-800',
    manager: 'bg-blue-100 text-blue-800',
    user: 'bg-green-100 text-green-800',
    inventory: 'bg-purple-100 text-purple-800'
  };
  return colors[roleName] || 'bg-gray-100 text-gray-800';
};

// Función para traducir nombres de roles
const getRoleLabel = (roleName) => {
  const labels = {
    admin: 'Administrador',
    supervisor: 'Supervisor',
    worker: 'Trabajador',
    manager: 'Gerente',
    user: 'Usuario',
    inventory: 'Inventario'
  };
  return labels[roleName] || roleName;
};

// Función para contar usuarios con un rol específico
const getUsersWithRole = (roleName) => {
  return props.users.filter(user => 
    user.roles.some(role => role.name === roleName)
  ).length;
};

// Función para formatear fecha
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

// Función para ver detalles del usuario
const viewUser = (user) => {
  router.visit(route('supervisor.users.show', user.id));
};

// Funciones para administrar usuarios
const toggleUserActive = async (user) => {
  try {
    const action = user.active ? 'desactivar' : 'activar';
    const result = await Swal.fire({
      title: `¿${action.charAt(0).toUpperCase() + action.slice(1)} usuario?`,
      text: `¿Estás seguro de que deseas ${action} a ${user.name}?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: user.active ? '#EF4444' : '#10B981',
      cancelButtonColor: '#6B7280',
      confirmButtonText: `Sí, ${action}`,
      cancelButtonText: 'Cancelar',
      customClass: {
        confirmButton: 'swal2-confirm-visible',
        cancelButton: 'swal2-cancel-visible'
      },
      buttonsStyling: true
    });

    if (result.isConfirmed) {
      const response = await router.post(`/supervisor/users/${user.id}/toggle-active`, {}, {
        preserveScroll: true,
        onSuccess: () => {
          user.active = !user.active;
          Swal.fire({
            title: '¡Actualizado!',
            text: `Usuario ${user.active ? 'activado' : 'desactivado'} correctamente`,
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        },
        onError: (error) => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo actualizar el estado del usuario',
            icon: 'error'
          });
        }
      });
    }
  } catch (error) {
    console.error('Error toggling user status:', error);
    Swal.fire({
      title: 'Error',
      text: 'Ocurrió un error inesperado',
      icon: 'error'
    });
  }
};

const openRoleModal = (user) => {
  selectedUser.value = user;
  selectedRole.value = user.roles.length > 0 ? user.roles[0].id : null;
  showRoleModal.value = true;
};

const closeRoleModal = () => {
  showRoleModal.value = false;
  selectedUser.value = null;
  selectedRole.value = null;
};

const updateUserRoles = async () => {
  try {
    await router.post(`/supervisor/users/${selectedUser.value.id}/roles`, {
      roles: selectedRole.value ? [selectedRole.value] : []
    }, {
      preserveScroll: true,
      onSuccess: () => {
        // Actualizar roles en el objeto usuario local
        const userIndex = props.users.findIndex(u => u.id === selectedUser.value.id);
        if (userIndex !== -1) {
          const updatedRoles = selectedRole.value
            ? props.roles.filter(role => role.id === selectedRole.value)
            : [];
          props.users[userIndex].roles = updatedRoles;
        }

        closeRoleModal();
        Swal.fire({
          title: '¡Actualizado!',
          text: 'Rol del usuario actualizado correctamente',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
      },
      onError: (error) => {
        Swal.fire({
          title: 'Error',
          text: 'No se pudo actualizar el rol del usuario',
          icon: 'error'
        });
      }
    });
  } catch (error) {
    console.error('Error updating user roles:', error);
    Swal.fire({
      title: 'Error',
      text: 'Ocurrió un error inesperado',
      icon: 'error'
    });
  }
};

const openEditModal = (user) => {
  selectedUser.value = user;
  editForm.value = {
    name: user.name,
    email: user.email,
    password: '',
    password_confirmation: ''
  };
  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  selectedUser.value = null;
  editForm.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  };
};

const openCreateModal = () => {
  showCreateModal.value = true;
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  createForm.value = {
    name: '',
    email: '',
    password: '',
    role: null
  };
};

const updateUser = async () => {
  try {
    await router.put(`/supervisor/users/${selectedUser.value.id}`, editForm.value, {
      preserveScroll: true,
      onSuccess: () => {
        // Actualizar datos en el objeto usuario local
        const userIndex = props.users.findIndex(u => u.id === selectedUser.value.id);
        if (userIndex !== -1) {
          props.users[userIndex].name = editForm.value.name;
          props.users[userIndex].email = editForm.value.email;
          props.users[userIndex].password = editForm.value.password;
        }
        
        closeEditModal();
        Swal.fire({
          title: '¡Actualizado!',
          text: 'Usuario actualizado correctamente',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
      },
      onError: (error) => {
        Swal.fire({
          title: 'Error',
          text: 'No se pudo actualizar el usuario',
          icon: 'error'
        });
      }
    });
  } catch (error) {
    console.error('Error updating user:', error);
    Swal.fire({
      title: 'Error',
      text: 'Ocurrió un error inesperado',
      icon: 'error'
    });
  }
};

const confirmDeleteUser = async (user) => {
  const result = await Swal.fire({
    title: '¿Eliminar usuario?',
    text: `¿Estás seguro de que deseas eliminar a ${user.name}? Esta acción no se puede deshacer.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#EF4444',
    cancelButtonColor: '#6B7280',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar',
    customClass: {
      confirmButton: 'swal2-confirm-visible',
      cancelButton: 'swal2-cancel-visible'
    },
    buttonsStyling: true
  });

  if (result.isConfirmed) {
    try {
      await router.delete(`/supervisor/users/${user.id}`, {
        preserveScroll: true,
        onSuccess: () => {
          // Remover usuario de la lista local
          const userIndex = props.users.findIndex(u => u.id === user.id);
          if (userIndex !== -1) {
            props.users.splice(userIndex, 1);
          }
          
          Swal.fire({
            title: '¡Eliminado!',
            text: 'Usuario eliminado correctamente',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });
        },
        onError: (error) => {
          Swal.fire({
            title: 'Error',
            text: 'No se pudo eliminar el usuario',
            icon: 'error'
          });
        }
      });
    } catch (error) {
      console.error('Error deleting user:', error);
      Swal.fire({
        title: 'Error',
        text: 'Ocurrió un error inesperado',
        icon: 'error'
      });
    }
  }
};

const createUser = async () => {
  try {
    const formData = {
      name: createForm.value.name,
      email: createForm.value.email,
      password: createForm.value.password,
      roles: createForm.value.role ? [createForm.value.role] : []
    };

    await router.post('/supervisor/users', formData, {
      preserveScroll: true,
      onSuccess: (page) => {
        // Agregar el nuevo usuario a la lista local
        if (page.props.users) {
          // El nuevo usuario viene en la respuesta del servidor
          const newUser = page.props.users[page.props.users.length - 1];
          if (newUser && !props.users.find(u => u.id === newUser.id)) {
            props.users.push(newUser);
          }
        }

        closeCreateModal();
        Swal.fire({
          title: '¡Usuario creado!',
          text: 'El nuevo usuario ha sido creado correctamente',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false
        });
      },
      onError: (errors) => {
        let errorMessage = 'No se pudo crear el usuario';

        if (errors.email) {
          errorMessage = errors.email[0];
        } else if (errors.password) {
          errorMessage = errors.password[0];
        } else if (errors.name) {
          errorMessage = errors.name[0];
        }

        Swal.fire({
          title: 'Error',
          text: errorMessage,
          icon: 'error'
        });
      }
    });
  } catch (error) {
    console.error('Error creating user:', error);
    Swal.fire({
      title: 'Error',
      text: 'Ocurrió un error inesperado',
      icon: 'error'
    });
  }
};
</script>

<style scoped>
/* Estilos globales para SweetAlert2 - Sin scoped para mayor especificidad */

/* Sobrescribir reset de Tailwind para botones de SweetAlert */
.swal2-popup button[class*="swal2-"]:not([type]),
.swal2-popup button[class*="swal2-"][type="button"],
.swal2-popup .swal2-confirm,
.swal2-popup .swal2-cancel,
.swal2-popup .swal2-confirm-visible,
.swal2-popup .swal2-cancel-visible {
  color: #fff !important;
  font-weight: 500 !important;
  border: none !important;
  border-radius: 0.375rem !important;
  padding: 0.5rem 1rem !important;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
  cursor: pointer !important;
}

/* Colores base por defecto para todos los botones */
.swal2-popup .swal2-confirm {
  background-color: #DC2626 !important;
}

.swal2-popup .swal2-cancel {
  background-color: #4B5563 !important;
}

/* Colores específicos más fuertes que sobrescriben cualquier estilo inline */
.swal2-popup .swal2-confirm[style*="#EF4444"],
.swal2-popup .swal2-confirm[style*="rgb(239, 68, 68)"],
.swal2-popup .swal2-confirm[style*="239, 68, 68"] {
  background-color: #B91C1C !important;
  color: #fff !important;
}

.swal2-popup .swal2-confirm[style*="#10B981"],
.swal2-popup .swal2-confirm[style*="rgb(16, 185, 129)"],
.swal2-popup .swal2-confirm[style*="16, 185, 129"] {
  background-color: #047857 !important;
  color: #fff !important;
}

.swal2-popup .swal2-confirm[style*="#3085d6"],
.swal2-popup .swal2-confirm[style*="rgb(48, 133, 214)"],
.swal2-popup .swal2-confirm[style*="48, 133, 214"] {
  background-color: #1D4ED8 !important;
  color: #fff !important;
}

.swal2-popup .swal2-cancel[style*="#6B7280"],
.swal2-popup .swal2-cancel[style*="rgb(107, 114, 128)"],
.swal2-popup .swal2-cancel[style*="107, 114, 128"] {
  background-color: #374151 !important;
  color: #fff !important;
}

/* Estados hover específicos para mejor interactividad */
.swal2-popup .swal2-confirm[style*="#EF4444"]:hover,
.swal2-popup .swal2-confirm[style*="rgb(239, 68, 68)"]:hover {
  background-color: #B91C1C !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4) !important;
}

.swal2-popup .swal2-confirm[style*="#10B981"]:hover,
.swal2-popup .swal2-confirm[style*="rgb(16, 185, 129)"]:hover {
  background-color: #047857 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4) !important;
}

.swal2-popup .swal2-confirm[style*="#3085d6"]:hover,
.swal2-popup .swal2-confirm[style*="rgb(48, 133, 214)"]:hover {
  background-color: #1D4ED8 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4) !important;
}

.swal2-popup .swal2-cancel[style*="#6B7280"]:hover,
.swal2-popup .swal2-cancel[style*="rgb(107, 114, 128)"]:hover {
  background-color: #374151 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 4px 12px rgba(75, 85, 99, 0.4) !important;
}

/* Estados focus para accesibilidad */
.swal2-popup button[class*="swal2-"]:focus {
  outline: 2px solid rgba(59, 130, 246, 0.5) !important;
  outline-offset: 2px !important;
}

</style>
