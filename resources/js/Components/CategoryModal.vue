<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Gestión de Categorías - {{ getTypeName() }}
                    </h3>
                    <button @click="$emit('close')"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div class="p-6">
                <!-- Add/Edit Category Form -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-md font-medium text-gray-900 mb-4">
                        {{ categoryModalType === 'add' ? 'Nueva Categoría' : 'Editar Categoría' }}
                    </h4>
                    <form @submit.prevent="handleCategorySubmit" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                            <input v-model="categoryFormData.name" type="text" required
                                :class="formErrors.name ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:border-transparent" />
                            <div v-if="formErrors.name" class="text-red-500 text-xs mt-1">{{ formErrors.name }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <input v-model="categoryFormData.description" type="text"
                                :class="formErrors.description ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'"
                                class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:border-transparent" />
                            <div v-if="formErrors.description" class="text-red-500 text-xs mt-1">{{ formErrors.description }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
                            <input v-model="categoryFormData.color" type="color"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent h-10" />
                        </div>
                        <div class="md:col-span-3 flex justify-end gap-3">
                            <button type="button" @click="resetCategoryForm"
                                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                Limpiar
                            </button>
                            <button type="submit" :disabled="!isFormValid"
                                :class="isFormValid ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                                class="px-4 py-2 text-white rounded-lg transition-colors">
                                {{ categoryModalType === 'add' ? 'Crear' : 'Actualizar' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Categories Table -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Color</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Descripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones</th>
                                </tr>
                            </thead>
                            <tbody v-if="categoriesList.length > 0" class="bg-white divide-y divide-gray-200">
                                <tr v-for="category in categoriesList" :key="category.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="w-6 h-6 rounded-full border border-gray-300"
                                            :style="{ backgroundColor: category.color || '#6B7280' }"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ category.description || 'Sin descripción' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">{{ category.items_count || 0 }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <button @click="editCategory(category)"
                                                class="text-blue-600 hover:text-blue-800 p-1 rounded transition-colors"
                                                title="Editar">
                                                <Edit class="w-4 h-4" />
                                            </button>
                                            <button @click="confirmDeleteCategory(category)"
                                                class="text-red-600 hover:text-red-800 p-1 rounded transition-colors"
                                                title="Eliminar">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>    
                            <tbody v-else>
                                <tr>    
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No hay categorías disponibles</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Category Confirmation Modal -->
        <div v-if="showDeleteCategoryModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-sm">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
                        <h3 class="text-lg font-semibold text-gray-900">Confirmar eliminación</h3>
                    </div>
                    <p class="text-gray-600 mb-6">
                        ¿Estás seguro de que deseas eliminar la categoría "{{ categoryToDelete?.name }}"? 
                        {{ categoryToDelete?.items_count > 0 ? 'Esta categoría tiene items asociados y no se puede eliminar.' : 'Esta acción no se puede deshacer.' }}
                    </p>
                    <div class="flex justify-end gap-3">
                        <button @click="showDeleteCategoryModal = false"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cancelar
                        </button>
                        <button v-if="categoryToDelete?.items_count === 0" @click="deleteCategory"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, computed } from 'vue'
import { X, Edit, Trash2, AlertTriangle } from 'lucide-vue-next'
import axios from '@/axios-config'
import Swal from 'sweetalert2'

// Props
const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    type: {
        type: String,
        required: true,
        validator: (value) => ['element', 'kit', 'component'].includes(value)
    },
    category: {
        type: Object,
        default: null
    },
    categories: {
        type: Array,
        required: true
    }
})

// Emits
const emit = defineEmits(['close', 'submit'])

// Estado reactivo
const categoryModalType = ref('add')
const categoriesList = ref([])
const showDeleteCategoryModal = ref(false)
const categoryToDelete = ref(null)
const formErrors = ref({})

const categoryFormData = reactive({
    name: '',
    description: '',
    color: '#3B82F6'
})

// Computed para validación
const isFormValid = computed(() => {
    return categoryFormData.name.trim().length > 0 && 
           categoryFormData.name.trim().length <= 255
})

// Métodos
const getTypeName = () => {
    switch (props.type) {
        case 'element': return 'Elementos'
        case 'kit': return 'Kits'
        case 'component': return 'Componentes'
        default: return 'Items'
    }
}

const validateForm = () => {
    formErrors.value = {}
    
    if (!categoryFormData.name.trim()) {
        formErrors.value.name = 'El nombre es obligatorio'
    } else if (categoryFormData.name.trim().length > 255) {
        formErrors.value.name = 'El nombre no puede tener más de 255 caracteres'
    }
    
    if (categoryFormData.description && categoryFormData.description.length > 1000) {
        formErrors.value.description = 'La descripción no puede tener más de 1000 caracteres'
    }
    
    return Object.keys(formErrors.value).length === 0
}

const handleCategorySubmit = async () => {
    // Validar formulario antes de enviar
    if (!validateForm()) {
        return
    }

    try {
        const url = categoryModalType.value === 'add' ? '/categories' : `/categories/${categoryFormData.id}`
        const method = categoryModalType.value === 'add' ? 'POST' : 'PUT'
        
        const requestData = {
            ...categoryFormData,
            type: props.type
        }
        
        console.log('CategoryModal - Props type:', props.type)
        console.log('CategoryModal - Modal type:', categoryModalType.value)
        console.log('Sending request:', {
            method,
            url,
            data: requestData
        })
        
        const response = await axios({
            method: method,
            url: url,
            data: requestData
        })
        
        if (response.status === 200 || response.status === 201) {
            // Emitir evento para que el componente padre actualice su estado
            emit('submit', {
                action: categoryModalType.value,
                category: response.data.category,
                type: props.type
            })
            
            // Actualizar la lista local también
            await loadCategories()
            resetCategoryForm()
            categoryModalType.value = 'add'
            formErrors.value = {}
            
            Swal.fire({
                title: '¡Éxito!',
                text: categoryModalType.value === 'add' ? 'Categoría creada correctamente' : 'Categoría actualizada correctamente',
                icon: 'success'
            })
        }
    } catch (error) {
        console.error('Error submitting category:', error)
        console.log('Error response:', error.response?.data)
        
        let errorMessage = 'Error al procesar la categoría'
        
        if (error.response?.data?.errors) {
            // Si hay errores de validación específicos del backend
            formErrors.value = error.response.data.errors
            console.log('Form errors:', formErrors.value)
            errorMessage = 'Por favor, corrige los errores en el formulario'
        } else if (error.response?.data?.message) {
            // Si hay un mensaje de error del backend
            errorMessage = error.response.data.message
        }
        
        Swal.fire({
            title: 'Error',
            text: errorMessage,
            icon: 'error'
        })
    }
}

const resetCategoryForm = () => {
    Object.assign(categoryFormData, {
        name: '',
        description: '',
        color: '#3B82F6'
    })
    formErrors.value = {}
}

const loadCategories = async () => {
    try {
        const response = await axios.get(`/categories?type=${props.type}`)
        categoriesList.value = response.data.map(cat => ({
            ...cat,
            items_count: cat.items?.length || 0
        }))
    } catch (error) {
        console.error('Error loading categories:', error)
    }
}

const editCategory = (category) => {
    categoryModalType.value = 'edit'
    Object.assign(categoryFormData, { ...category })
}

const confirmDeleteCategory = (category) => {
    categoryToDelete.value = category
    showDeleteCategoryModal.value = true
}

const deleteCategory = async () => {
    try {
        const response = await axios.delete(`/categories/${categoryToDelete.value.id}`)
        
        if (response.status === 200) {
            // Emitir evento para que el componente padre actualice su estado
            emit('submit', {
                action: 'delete',
                category: categoryToDelete.value,
                type: props.type
            })
            
            // Actualizar la lista local también
            await loadCategories()
            showDeleteCategoryModal.value = false
            categoryToDelete.value = null
            
            Swal.fire({
                title: '¡Eliminado!',
                text: 'La categoría ha sido eliminada correctamente',
                icon: 'success'
            })
        }
    } catch (error) {
        console.error('Error deleting category:', error)
        Swal.fire({
            title: 'Error',
            text: 'Error al eliminar la categoría',
            icon: 'error'
        })
    }
}

// Watchers
watch(() => props.show, (newShow) => {
    if (newShow) {
        loadCategories()
    }
})

// Inicialización
onMounted(() => {
    if (props.show) {
        loadCategories()
    }
})
</script>
