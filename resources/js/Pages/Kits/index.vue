<template>
    <AppLayout title="Gestión de Kits">
        <div class="w-full h-full p-4 space-y-6">
            <Container>
                <template #title>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 mt-2">Administra los kits de elementos del inventario</p>
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-between">
                    <span></span>
                    <button
                        @click="createKit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center"
                    >
                        <Plus class="size-4 mr-2" />
                        Crear Kit
                    </button>
                </div>
                <!-- Tabla de kits -->
                <div v-if="kits?.data && kits.data.length > 0" class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nombre
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Categoría
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Componentes
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Disponibilidad
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="kit in kits.data" :key="kit.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ kit.name }}</div>
                                            <div v-if="kit.description" class="text-sm text-gray-500">{{ kit.description }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span v-if="kit.category" class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                            {{ kit.category }}
                                        </span>
                                        <span v-else class="text-gray-400">Sin categoría</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ kit.kit_components?.length || 0 }} componentes
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="kit.can_be_assembled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                            class="px-2 py-1 rounded-full text-xs font-medium"
                                        >
                                            {{ kit.can_be_assembled ? 'Disponible' : 'No disponible' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center gap-2">
                                            <Link
                                                :href="route('kits.show', kit.id)"
                                                class="text-blue-600 hover:text-blue-900 p-1"
                                                title="Ver detalles"
                                            >
                                                <Eye class="w-4 h-4" />
                                            </Link>
                                            <Link
                                                :href="route('kits.edit', kit.id)"
                                                class="text-indigo-600 hover:text-indigo-900 p-1"
                                                title="Editar"
                                            >
                                                <Edit class="w-4 h-4" />
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Estado vacío -->
                <div v-else class="bg-white rounded-lg shadow-sm p-8">
                    <div class="text-center">
                        <Package class="w-16 h-16 text-gray-400 mb-4 mx-auto" />
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay kits disponibles</h3>
                        <p class="text-gray-500 mb-4">Comienza creando tu primer kit.</p>
                        <button
                            @click="createKit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors inline-flex items-center"
                        >
                            <Plus class="w-4 h-4 mr-2" />
                            Crear primer kit
                        </button>
                    </div>
                </div>
            </Container>
        </div>

        <!-- Modal para crear kit -->
        <Modal :show="showCreateModal" @close="closeCreateModal">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Crear Nuevo Kit</h3>
                    <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submitKit" class="space-y-4">
                    <!-- Información básica -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del Kit *
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ej: Kit de Mantenimiento Básico"
                        />
                        <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                                Categoría
                            </label>
                            <input
                                id="category"
                                v-model="form.category"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Ej: Mantenimiento"
                            />
                            <div v-if="form.errors.category" class="text-red-600 text-sm mt-1">{{ form.errors.category }}</div>
                        </div>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Descripción
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Describe el propósito del kit..."
                        ></textarea>
                        <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">{{ form.errors.description }}</div>
                    </div>

                    <!-- Componentes del kit -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">
                                Componentes del Kit *
                            </label>
                            <button
                                type="button"
                                @click="addComponent"
                                class="text-blue-600 hover:text-blue-800 text-sm flex items-center"
                            >
                                <Plus class="w-4 h-4 mr-1" />
                                Agregar
                            </button>
                        </div>

                        <div v-if="form.errors.components" class="text-red-600 text-sm mb-2">{{ form.errors.components }}</div>

                        <div v-if="form.components.length === 0" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                            <Package class="w-12 h-12 text-gray-400 mx-auto mb-2" />
                            <p class="text-gray-500 text-sm">No hay componentes agregados</p>
                            <button
                                type="button"
                                @click="addComponent"
                                class="mt-2 text-blue-600 hover:text-blue-800 text-sm"
                            >
                                Agregar primer componente
                            </button>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="(component, index) in form.components"
                                :key="index"
                                class="border border-gray-200 rounded-lg p-3"
                            >
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Elemento *
                                        </label>
                                        <select
                                            v-model="component.element_id"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            required
                                        >
                                            <option value="">Seleccionar elemento</option>
                                            <option v-for="element in elements" :key="element.id" :value="element.id">
                                                {{ element.name }} (Stock: {{ element.current_stock }})
                                            </option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Cantidad *
                                        </label>
                                        <input
                                            v-model.number="component.quantity"
                                            type="number"
                                            min="1"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        />
                                    </div>

                                    <div class="flex justify-end">
                                        <button
                                            type="button"
                                            @click="removeComponent(index)"
                                            class="text-red-600 hover:text-red-800 p-2"
                                            title="Eliminar componente"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex justify-end gap-3 pt-4 border-t">
                        <button
                            type="button"
                            @click="closeCreateModal"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing || form.components.length === 0"
                            class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg transition-colors flex items-center"
                        >
                            <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
                            <Save v-else class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Guardando...' : 'Crear Kit' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import Modal from '@/Components/Modal.vue'
import { Plus, Eye, Edit, Package, X, Trash2, Save, Loader2 } from 'lucide-vue-next'

const props = defineProps({
    kits: {
        type: Object,
        default: () => ({ data: [] })
    },
    categories: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, available: 0 })
    },
    elements: {
        type: Array,
        default: () => []
    },
    components: {
        type: Object,
        default: () => ({}) 
    }
})

// Estado del modal
const showCreateModal = ref(false)

// Formulario
const form = useForm({
    name: '',
    description: '',
    category: '',
    components: []
})

// Métodos
const createKit = () => {
    showCreateModal.value = true
}

const closeCreateModal = () => {
    showCreateModal.value = false
    form.reset()
    form.clearErrors()
}

const addComponent = () => {
    form.components.push({
        element_id: '',
        quantity: 1
    })
}

const removeComponent = (index) => {
    form.components.splice(index, 1)
}

const submitKit = () => {
    form.post(route('kits.store'), {
        onSuccess: () => {
            closeCreateModal()
        },
        preserveScroll: true
    })
}
</script>