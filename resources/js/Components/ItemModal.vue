<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900" :class="getModalTitleColor()">
                    {{ getModalTitle() }}
                </h3>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
                <!-- Add/Edit Form -->
                <div v-if="modalType !== 'assign'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input v-model="formData.name" type="text" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                </div>

                <div v-if="modalType !== 'assign'" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select v-model="formData.category_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Seleccionar</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                        <input v-model="formData.unit" type="text" required
                            placeholder="ej: pcs, kg, L"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>
                </div>

                <div v-if="modalType !== 'assign'" class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                        <input v-model.number="formData.current_stock" type="number" min="0" step="1.00" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mín.</label>
                        <input v-model.number="formData.min_stock" type="number" min="0" step="1.00" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Máx.</label>
                        <input v-model.number="formData.max_stock" type="number" min="1" step="1.00" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>
                </div>
                
                <!-- Indicador de validación automática 
                <div v-if="isAutoValidating" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
                        <span class="text-sm text-blue-700">{{ autoValidationMessage }}</span>
                    </div>
                </div>
                -->
                

                <div v-if="modalType !== 'assign'" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Costo de compra (unidad)
                            <span v-if="type !== 'element'" class="text-xs text-blue-600">(Calculado automáticamente)</span>
                        </label>
                        <input v-model.number="formData.purchase_cost" type="number" min="0.00" step="0.01"
                            :placeholder="type !== 'element' ? 'Se calcula automáticamente' : '0.00'"
                            :readonly="type !== 'element'"
                            :class="type !== 'element' ? 'bg-gray-100 cursor-not-allowed' : ''"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        <div v-if="type !== 'element' && calculatedPurchaseCost > 0" class="text-xs text-blue-600 mt-1">
                            Costo calculado: ${{ calculatedPurchaseCost.toFixed(2) }}
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio de venta</label>
                        <input v-model.number="formData.sale_price" type="number" min="0" step="1.00"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>
                </div>

                <div v-if="modalType !== 'assign'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea v-model="formData.description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>

                <!-- Assignment Form -->
                <div v-if="type !== 'element' && modalType !== 'assign'">
                    <div class="border-t pt-4">
                                                 <h4 class="text-md font-medium text-gray-900 mb-3">
                             Añadir {{ type == 'component' ? 'Elementos' : type == 'kit' ? 'Elementos y/o Componentes' : 'Elementos' }}:
                         </h4>
                        
                        <!-- Formulario para agregar elementos -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <!-- Selector de tipo solo para kits -->
                            <div v-if="type === 'kit'" class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de item a agregar:</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center">
                                        <input type="radio" v-model="selectedItemType" value="element" class="mr-2">
                                        <span class="text-sm">Elemento</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" v-model="selectedItemType" value="component" class="mr-2">
                                        <span class="text-sm">Componente</span>
                    </label>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar {{ type === 'kit' ? selectedItemType : 'elemento' }}</label>
                                    <input type="text" v-model="searchElement" :placeholder="`Buscar por nombre...`"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Seleccionar {{ type === 'kit' ? selectedItemType : 'elemento' }}</label>
                                    <select v-model="selectedElement" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Seleccionar {{ type === 'kit' ? selectedItemType : 'elemento' }}</option>
                                        <option v-for="item in availableItems" :key="item.id" :value="item.id">
                                            {{ item.name }} ({{ item.categoryName || 'Sin categoría' }}) - {{ item.type }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                                    <input v-model.number="elementQuantity" type="number" min="1" step="1" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" @click="addElementToAssignment"
                                    :disabled="!selectedElement || !elementQuantity || elementQuantity < 1"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <Plus class="w-4 h-4" />
                                    Agregar {{ type === 'kit' ? selectedItemType : 'Elemento' }}
                                </button>
                            </div>
                        </div>

                        <!-- Lista de elementos asignados -->
                        <div v-if="assignedElements.length > 0" class="bg-white border border-gray-200 rounded-lg">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h5 class="text-sm font-medium text-gray-900">Elementos Asignados</h5>
                            </div>
                            <div class="divide-y divide-gray-200">
                                <div v-for="assignedElement in assignedElements" :key="assignedElement.id" 
                                    class="px-4 py-3 flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <span class="font-medium text-gray-900">{{ assignedElement.name }}</span>
                                            <span class="text-sm text-gray-500">{{ assignedElement.categoryName }}</span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            Cantidad: <span class="font-medium">{{ assignedElement.quantity }}</span>
                                            <span v-if="assignedElement.unit" class="ml-1">({{ assignedElement.unit }})</span>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeElementFromAssignment(assignedElement.id)"
                                        class="px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors flex items-center gap-1">
                                        <Trash2 class="w-4 h-4" />
                                        <span class="text-sm">Eliminar</span>
                            </button>
                        </div>
                            </div>
                        </div>

                        <!-- Mensaje cuando no hay elementos asignados -->
                        <div v-else class="text-center py-6 text-gray-500">
                            <Package class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                            <p>No hay elementos asignados</p>
                            <p class="text-sm">Agrega elementos usando el formulario de arriba</p>
                        </div>
                    </div>
                </div>

                <!-- Assignment Form para modo assign -->
                <div v-if="modalType === 'assign'">
                    <div class="border-t pt-4">
                        <h4 class="text-md font-medium text-gray-900 mb-3">
                            Asignar {{ type == 'component' ? 'Elementos' : 'Componentes y/o Elementos' }}:
                        </h4>
                        
                        <!-- Formulario para agregar elementos -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar elemento</label>
                                    <input type="text" v-model="searchElement" placeholder="Buscar por nombre..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Seleccionar elemento</label>
                                    <select v-model="selectedElement" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Seleccionar elemento</option>
                                        <option v-for="element in filteredElements" :key="element.id" :value="element.id">
                                            {{ element.name }} ({{ element.categoryName || 'Sin categoría' }})
                                </option>
                            </select>
                                </div>
                                <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                                    <input v-model.number="elementQuantity" type="number" min="1" step="1" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" @click="addElementToAssignment"
                                    :disabled="!selectedElement || !elementQuantity || elementQuantity < 1"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                    <Plus class="w-4 h-4" />
                                    Agregar Elemento
                            </button>
                        </div>
                        </div>

                        <!-- Lista de elementos asignados -->
                        <div v-if="assignedElements.length > 0" class="bg-white border border-gray-200 rounded-lg">
                            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                                <h5 class="text-sm font-medium text-gray-900">Elementos Asignados</h5>
                            </div>
                            <div class="divide-y divide-gray-200">
                                <div v-for="assignedElement in assignedElements" :key="assignedElement.id" 
                                    class="px-4 py-3 flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <span class="font-medium text-gray-900">{{ assignedElement.name }}</span>
                                            <span class="text-sm text-gray-500">{{ assignedElement.categoryName }}</span>
                                        </div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            Cantidad: <span class="font-medium">{{ assignedElement.quantity }}</span>
                                            <span v-if="assignedElement.unit" class="ml-1">({{ assignedElement.unit }})</span>
                                        </div>
                                    </div>
                                    <button type="button" @click="removeElementFromAssignment(assignedElement.id)"
                                        class="px-3 py-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors flex items-center gap-1">
                                <Trash2 class="w-4 h-4" />
                                        <span class="text-sm">Eliminar</span>
                            </button>
                                </div>
                            </div>
                        </div>
                       
                        <!-- Mensaje cuando no hay elementos asignados -->
                        <div v-else class="text-center py-6 text-gray-500">
                            <Package class="w-12 h-12 mx-auto mb-2 text-gray-300" />
                            <p>No hay elementos asignados</p>
                            <p class="text-sm">Agrega elementos usando el formulario de arriba</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" @click="$emit('close')"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        {{ getSubmitButtonText() }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, computed, onMounted, onUnmounted } from 'vue'
import { Plus, Trash2, Package } from 'lucide-vue-next';

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
    item: {
        type: Object,
        default: null
    },
    categories: {
        type: Array,
        required: true
    },
    modalType: {
        type: String,
        required: true,
        validator: (value) => ['add', 'edit', 'assign'].includes(value)
    },
    elements: {
        type: Array,
        required: false,
        default: () => []
    },
    kits: {
        type: Array,
        required: false,
        default: () => []
    },
    components: {
        type: Array,
        required: false,
        default: () => []
    }   
})

// Emits
const emit = defineEmits(['close', 'submit'])

// Estado reactivo
const formData = reactive({
    name: '',
    category_id: '',
    unit: '',
    current_stock: 0,
    min_stock: 0,
    max_stock: 1,
    purchase_cost: 0,
    sale_price: 0,
    description: '',
    assignedTo: null,
    assignedElements: []
})

// Estado para validación automática
const isAutoValidating = ref(false)
const autoValidationMessage = ref('')

// Variables para elementos
const selectedElement = ref('')
const searchElement = ref('')
const elementQuantity = ref(1)
const assignedElements = ref([])
const selectedItemType = ref('element') // Nuevo: para seleccionar tipo de item en kits

// Computed properties
const filteredElements = computed(() => {
    if (!props.elements) return []
    
    let filtered = props.elements.filter(element => 
        element.active !== false
    )
    
    if (searchElement.value) {
        filtered = filtered.filter(element => 
            element.name.toLowerCase().includes(searchElement.value.toLowerCase())
        )
    }
    
    return filtered
})

// Computed para filtrar componentes disponibles (solo para kits)
const filteredComponents = computed(() => {
    if (!props.components || props.type !== 'kit') return []
    
    let filtered = props.components.filter(component => 
        component.active !== false
    )
    
    if (searchElement.value) {
        filtered = filtered.filter(component => 
            component.name.toLowerCase().includes(searchElement.value.toLowerCase())
        )
    }
    
    return filtered
})

// Computed para obtener todos los items disponibles según el tipo
const availableItems = computed(() => {
    console.log(props.type)
    if (props.type === 'kit') {
        console.log('kit')
        // Para kits: filtrar según el tipo seleccionado
        if (selectedItemType.value === 'element') {
            return filteredElements.value
        } else if (selectedItemType.value === 'component') {
            return filteredComponents.value
        } else {
            return [...filteredElements.value, ...filteredComponents.value]
        }
    } else if (props.type === 'component') {
        console.log('component')
        // Para componentes: solo elementos
        return filteredElements.value
    } else {
        console.log('element')
        // Para elementos: nada
        return []
    }
})

// Computed para calcular costo automático
const calculatedPurchaseCost = computed(() => {
    if (props.type === 'element') return formData.purchase_cost
    
    let totalCost = 0
    assignedElements.value.forEach(element => {
        // Buscar en elementos
        const elementData = props.elements.find(el => el.id === element.id)
        if (elementData && elementData.purchase_cost) {
            totalCost += elementData.purchase_cost * element.quantity
        } else {
            // Buscar en componentes
            const componentData = props.components.find(comp => comp.id === element.id)
            if (componentData && componentData.purchase_cost) {
                totalCost += componentData.purchase_cost * element.quantity
            }
        }
    })
    
    return totalCost
})

// Métodos
const getModalTitle = () => {
    switch (props.modalType) {
        case 'add': return `Nuevo ${getTypeName()}`
        case 'edit': return `Editar ${getTypeName()}`
        case 'assign': return `Asignar ${getTypeName()}`
        default: return ''
    }
}

const getModalTitleColor = () => {
    switch (props.modalType) {
        case 'add': return 'text-blue-600'
        case 'edit': return 'text-blue-600'
        case 'assign': return 'text-green-600'
        default: return 'text-gray-900'
    }
}

const getSubmitButtonText = () => {
    switch (props.modalType) {
        case 'add': return 'Crear'
        case 'edit': return 'Actualizar'
        case 'assign': return 'Asignar'
        default: return 'Guardar'
    }
}

const getTypeName = () => {
    switch (props.type) {
        case 'element': return 'Elemento'
        case 'kit': return 'Kit'
        case 'component': return 'Componente'
        default: return 'Item'
    }
}

const handleSubmit = () => {
    // Filtrar los datos según el tipo de modal
    let dataToSubmit = { ...formData }
    
    // Agregar los elementos asignados al formulario
    dataToSubmit.assignedElements = assignedElements.value
    
    // Si es kit o componente, usar el costo calculado automáticamente
    if (props.type !== 'element') {
        dataToSubmit.purchase_cost = calculatedPurchaseCost.value
    }
    
    if (props.modalType === 'edit') {
        // Para edición, no enviar assignedTo ya que no es un campo de la tabla items
        delete dataToSubmit.assignedTo
    } else if (props.modalType === 'assign') {
        // Para asignación, solo enviar assignedTo y assignedElements
        dataToSubmit = { 
            assignedTo: formData.assignedTo,
            assignedElements: assignedElements.value
        }
    }
    
    emit('submit', dataToSubmit)
}

const resetForm = () => {
    Object.assign(formData, {
        name: '',
        category_id: '',
        unit: '',
        current_stock: 0,
        min_stock: 0,
        max_stock: 1,
        purchase_cost: 0,
        sale_price: 0,
        description: '',
        assignedTo: null,
        assignedElements: []
    })
    
    // Resetear variables de elementos
    selectedElement.value = ''
    searchElement.value = ''
    elementQuantity.value = 1
    assignedElements.value = []
    selectedItemType.value = 'element' // Resetear el tipo seleccionado
}

const addElementToAssignment = () => {
    if (!selectedElement.value || !elementQuantity.value || elementQuantity.value < 1) {
        return
    }
    
    // Verificar si el elemento ya está asignado
    const existingIndex = assignedElements.value.findIndex(el => el.id === selectedElement.value)
    
    if (existingIndex !== -1) {
        // Actualizar cantidad si ya existe
        assignedElements.value[existingIndex].quantity = elementQuantity.value
    } else {
        // Agregar nuevo elemento - buscar en todos los items disponibles
        const selectedItem = availableItems.value.find(item => item.id === selectedElement.value)
        if (selectedItem) {
            assignedElements.value.push({
                id: selectedItem.id,
                name: selectedItem.name,
                categoryName: selectedItem.categoryName || 'Sin categoría',
                quantity: elementQuantity.value,
                unit: selectedItem.unit,
                type: selectedItem.type // Agregar el tipo para referencia
            })
        }
    }
    
    // Limpiar formulario
    selectedElement.value = ''
    elementQuantity.value = 1
    searchElement.value = ''
}

const removeElementFromAssignment = (elementId) => {
    assignedElements.value = assignedElements.value.filter(element => element.id !== elementId)
}

// Watchers
watch([() => props.item, () => props.modalType], ([newItem, newModalType]) => {
    
    if (newItem && newModalType === 'edit') {
        Object.assign(formData, {
            name: newItem.name || '',
            category_id: newItem.category_id || '',
            unit: newItem.unit || '',
            current_stock: newItem.current_stock || 0,
            min_stock: newItem.min_stock || 0,
            max_stock: Math.max(1, newItem.max_stock || 1),
            purchase_cost: newItem.purchase_cost || 0,
            sale_price: newItem.sale_price || 0,
            description: newItem.description || '',
            assignedTo: newItem.assignedTo || null
        })
        
        // Cargar elementos asignados - buscar en las props actualizadas
        if (newItem.assignedElements && Array.isArray(newItem.assignedElements)) {
            assignedElements.value = newItem.assignedElements.map(assignedEl => {
                // Buscar el elemento completo en los props para obtener información actualizada
                let fullItem = null
                
                // Buscar en elementos
                if (props.elements) {
                    fullItem = props.elements.find(el => el.id === assignedEl.id)
                }
                
                // Si no se encontró en elementos, buscar en componentes
                if (!fullItem && props.components) {
                    fullItem = props.components.find(comp => comp.id === assignedEl.id)
                }
                
                return {
                    id: assignedEl.id,
                    name: fullItem?.name || assignedEl.name,
                    categoryName: fullItem?.categoryName || fullItem?.categoryName || assignedEl.categoryName || 'Sin categoría',
                    quantity: assignedEl.quantity,
                    unit: fullItem?.unit || assignedEl.unit,
                    type: fullItem?.type || assignedEl.type || 'element'
                }
            })
        } else {
            assignedElements.value = []
        }
        
        // Asegurar que max_stock sea mayor que min_stock
        if (formData.max_stock <= formData.min_stock) {
            formData.max_stock = Math.max(1, formData.min_stock + 1)
        }
    } else if (newModalType === 'assign' && newItem) {
        formData.assignedTo = newItem.assignedTo
        assignedElements.value = newItem.assignedElements || []
    } else if (newModalType === 'add' || !newItem) {
        resetForm()
    }
}, { immediate: true, deep: true })

// Watcher para actualizar costo automáticamente cuando cambien los elementos asignados
watch(assignedElements, () => {
    if (props.type !== 'element') {
        formData.purchase_cost = calculatedPurchaseCost.value
    }
}, { deep: true })

// Watcher para limpiar elemento seleccionado cuando cambie el tipo en kits
watch(selectedItemType, () => {
    selectedElement.value = ''
    searchElement.value = ''
})

// Validación de campos
// Debounce para min_stock - dar 1 segundo al usuario para escribir
let minStockTimeout = null
watch(() => formData.min_stock, (newMin) => {
    // Limpiar el timeout anterior si existe
    if (minStockTimeout) {
        clearTimeout(minStockTimeout)
    }
    
    // Mostrar mensaje de validación automática
    isAutoValidating.value = true
    autoValidationMessage.value = 'Validando en 1 segundo...'
    
    // Crear un nuevo timeout de 1 segundo
    minStockTimeout = setTimeout(() => {
        // Solo aplicar validación si el valor sigue siendo inválido después de 1 segundo
        if (formData.max_stock <= newMin) {
            formData.max_stock = Math.max(1, newMin + 1)
            autoValidationMessage.value = 'Stock máximo ajustado automáticamente'
        } else {
            autoValidationMessage.value = 'Validación completada'
        }
        
        // Ocultar mensaje después de 2 segundos
        setTimeout(() => {
            isAutoValidating.value = false
            autoValidationMessage.value = ''
        }, 2000)
    }, 1000) // 1 segundo de delay
})

// Debounce para max_stock - dar 1 segundo al usuario para escribir
let maxStockTimeout = null
watch(() => formData.max_stock, (newMax) => {
    // Limpiar el timeout anterior si existe
    if (maxStockTimeout) {
        clearTimeout(maxStockTimeout)
    }
    
    // Mostrar mensaje de validación automática
    isAutoValidating.value = true
    autoValidationMessage.value = 'Validando en 1 segundo...'
    
    // Crear un nuevo timeout de 1 segundo
    maxStockTimeout = setTimeout(() => {
        // Solo aplicar validación si el valor sigue siendo inválido después de 1 segundo
        if (newMax < 1) {
            formData.max_stock = 1
            autoValidationMessage.value = 'Stock máximo ajustado automáticamente'
        } else if (newMax <= formData.min_stock) {
            formData.max_stock = Math.max(1, formData.min_stock + 1)
            autoValidationMessage.value = 'Stock máximo ajustado automáticamente'
        } else {
            autoValidationMessage.value = 'Validación completada'
        }
        
        // Ocultar mensaje después de 2 segundos
        setTimeout(() => {
            isAutoValidating.value = false
            autoValidationMessage.value = ''
        }, 2000)
    }, 1000) // 1 segundo de delay
})

// Inicialización
onMounted(() => {
    
    // Si ya tenemos un item y estamos en modo edit, cargar los datos
    if (props.item && props.modalType === 'edit') {
        Object.assign(formData, {
            name: props.item.name || '',
            category_id: props.item.category_id || '',
            unit: props.item.unit || '',
            current_stock: props.item.current_stock || 0,
            min_stock: props.item.min_stock || 0,
            max_stock: Math.max(1, props.item.max_stock || 1),
            purchase_cost: props.item.purchase_cost || 0,
            sale_price: props.item.sale_price || 0,
            description: props.item.description || '',
            assignedTo: props.item.assignedTo || null
        })

        // Cargar elementos asignados con información completa
        if (props.item.assignedElements && Array.isArray(props.item.assignedElements)) {
            assignedElements.value = props.item.assignedElements.map(assignedEl => {
                // Buscar el elemento completo en los props para obtener información actualizada
                let fullItem = null
                
                // Buscar en elementos
                if (props.elements) {
                    fullItem = props.elements.find(el => el.id === assignedEl.id)
                }
                
                // Si no se encontró en elementos, buscar en componentes
                if (!fullItem && props.components) {
                    fullItem = props.components.find(comp => comp.id === assignedEl.id)
                }
                
                return {
                    id: assignedEl.id,
                    name: fullItem?.name || assignedEl.name,
                    categoryName: fullItem?.categoryName || fullItem?.categoryName || assignedEl.categoryName || 'Sin categoría',
                    quantity: assignedEl.quantity,
                    unit: fullItem?.unit || assignedEl.unit,
                    type: fullItem?.type || assignedEl.type || 'element'
                }
            })
        } else {
            assignedElements.value = []
        }
    } else {
        resetForm()
    }
})
    
// Limpiar timeouts al desmontar
onUnmounted(() => {
    if (maxStockTimeout) {
        clearTimeout(maxStockTimeout)
    }
    if (minStockTimeout) {
        clearTimeout(minStockTimeout)
    }
})
</script>
