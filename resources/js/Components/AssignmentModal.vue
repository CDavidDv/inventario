<template>
    <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ getModalTitle() }}
                </h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <Package class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <div class="text-sm font-medium text-blue-900">{{ item.name }}</div>
                            <div class="text-sm text-blue-700">{{ item.categoryName }} - {{ item.type }}</div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de asignación -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ getAssignmentLabel() }}
                        </label>
                        <select v-model="selectedItem" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Seleccionar {{ getItemTypeLabel() }}</option>
                            <option v-for="availableItem in filteredAvailableItems" :key="availableItem.id" :value="availableItem.id">
                                {{ availableItem.name }} ({{ availableItem.categoryName }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Cantidad
                        </label>
                        <input v-model="quantity" type="number" min="1" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Lista de items asignados -->
                <div v-if="assignedItems.length > 0" class="mt-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Items Asignados</h4>
                    <div class="space-y-2">
                        <div v-for="assignedItem in assignedItems" :key="assignedItem.id" 
                             class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                                    <Package class="w-4 h-4 text-gray-600" />
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ assignedItem.name }}</div>
                                    <div class="text-xs text-gray-500">{{ assignedItem.categoryName }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-600">Cantidad: {{ assignedItem.quantity }}</span>
                                <button @click="removeAssignment(assignedItem.id)" 
                                        class="text-red-600 hover:text-red-800 p-1 rounded transition-colors"
                                        title="Eliminar asignación">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-3">
                <button @click="$emit('close')" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancelar
                </button>
                <button @click="addAssignment" 
                        :disabled="!selectedItem || !quantity"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    Agregar
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Package, Trash2 } from 'lucide-vue-next'

// Props
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    item: {
        type: Object,
        default: () => ({})
    },
    itemType: {
        type: String,
        default: 'element'
    },
    availableItems: {
        type: Array,
        default: () => []
    },
    assignedItems: {
        type: Array,
        default: () => []
    }
})

// Emits
const emit = defineEmits(['close', 'add-assignment', 'remove-assignment'])

// Estado reactivo
const selectedItem = ref('')
const quantity = ref(1)

// Computed
const getModalTitle = () => {
    if (props.itemType === 'element') {
        return 'Asignar Elementos a Componente'
    } else if (props.itemType === 'component') {
        return 'Asignar Componentes a Kit'
    }
    return 'Asignar Items'
}

const getAssignmentLabel = () => {
    if (props.itemType === 'element') {
        return 'Elemento a asignar'
    } else if (props.itemType === 'component') {
        return 'Componente a asignar'
    }
    return 'Item a asignar'
}

const getItemTypeLabel = () => {
    if (props.itemType === 'element') {
        return 'elemento'
    } else if (props.itemType === 'component') {
        return 'componente'
    }
    return 'item'
}

// Computed para filtrar items ya asignados
const filteredAvailableItems = computed(() => {
    const assignedIds = props.assignedItems.map(item => item.id)
    return props.availableItems.filter(item => !assignedIds.includes(item.id))
})

// Métodos
const addAssignment = () => {
    if (!selectedItem.value || !quantity.value) return

    const itemToAssign = filteredAvailableItems.value.find(item => item.id === selectedItem.value)
    if (itemToAssign) {
        emit('add-assignment', {
            itemId: selectedItem.value,
            item: itemToAssign,
            quantity: parseInt(quantity.value)
        })
        
        // Limpiar formulario
        selectedItem.value = ''
        quantity.value = 1
    }
}

const removeAssignment = (itemId) => {
    emit('remove-assignment', itemId)
}

// Watchers
watch(() => props.show, (newValue) => {
    if (newValue) {
        selectedItem.value = ''
        quantity.value = 1
    }
})
</script>
