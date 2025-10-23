<template>
    <!-- Modal para Agregar Elementos -->
    <DialogModal :show="showElementModal" @close="closeElementModal">
        <template #title>
            Agregar Elemento al Inventario
        </template>

        <template #content>
            <div class="space-y-4 z-[99]">
                <div>
                    <InputLabel for="element_id" value="Elemento" />
                    <select 
                        id="element_id" 
                        v-model="elementForm.item_id"
                        @change="loadElementData"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar elemento...</option>
                        <option 
                            v-for="item in elements" 
                            :key="item.id" 
                            :value="item.id"
                        >
                            {{ item.name }} (Stock: {{ item.current_stock }} {{ item.unit }})
                        </option>
                    </select>
                </div>

                <div>
                    <InputLabel for="quantity" value="Cantidad" />
                    <TextInput
                        id="quantity"
                        v-model="elementForm.quantity"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="mt-1 block w-full"
                        placeholder="Ingrese la cantidad"
                    />
                </div>

                <div>
                    <InputLabel for="supplier_id" value="Proveedor" />
                    <select 
                        id="supplier_id" 
                        v-model="elementForm.supplier_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar proveedor...</option>
                        <option 
                            v-for="supplier in suppliers" 
                            :key="supplier.id" 
                            :value="supplier.id"
                        >
                            {{ supplier.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <InputLabel for="cost" value="Costo por Unidad" />
                    <TextInput
                        id="cost"
                        v-model="elementForm.cost"
                        type="number"
                        step="0.01"
                        min="0"
                        class="mt-1 block w-full"
                        placeholder="0.00"
                    />
                </div>

                <div>
                    <InputLabel for="element_notes" value="Notas (Opcional)" />
                    <textarea
                        id="element_notes"
                        v-model="elementForm.notes"
                        rows="3"
                        class="mt-1 py-2 px-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Notas adicionales..."
                    ></textarea>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeElementModal">
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ml-3"
                :class="{ 'opacity-25': elementForm.processing }"
                :disabled="elementForm.processing"
                @click="submitElementForm"
            >
                <span v-if="elementForm.processing">Agregando...</span>
                <span v-else>Agregar Elemento</span>
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal para Agregar Componentes -->
    <DialogModal :show="showComponentModal" @close="closeComponentModal">
        <template #title>
            Crear Componente
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="component_id" value="Componente" />
                    <select 
                        id="component_id" 
                        v-model="componentForm.component_id"
                        @change="loadComponentData"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar componente...</option>
                        <option 
                            v-for="component in components" 
                            :key="component.id" 
                            :value="component.id"
                        >
                            {{ component.name }} (Stock: {{ component.current_stock }} {{ component.unit }})
                        </option>
                    </select>
                </div>

                <div v-if="selectedComponentData && selectedComponentData.components">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Elementos requeridos:</h4>
                    <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                        <div 
                            v-for="comp in selectedComponentData.components" 
                            :key="comp.item.id"
                            class="flex justify-between items-center text-sm"
                            :class="comp.sufficient ? 'text-green-700' : 'text-red-700'"
                        >
                            <span>{{ comp.item.name }}</span>
                            <span>
                                Necesario: {{ comp.quantity_needed }} | 
                                Disponible: {{ comp.available_stock }}
                                <span v-if="!comp.sufficient" class="font-semibold"> ⚠️</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <InputLabel for="component_quantity" value="Cantidad a Crear" />
                    <TextInput
                        id="component_quantity"
                        v-model="componentForm.quantity"
                        type="number"
                        min="1"
                        class="mt-1 block w-full"
                        placeholder="1"
                    />
                </div>

                <div>
                    <InputLabel for="component_notes" value="Notas (Opcional)" />
                    <textarea
                        id="component_notes"
                        v-model="componentForm.notes"
                        rows="3"
                        class="mt-1 py-2 px-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Notas adicionales..."
                    ></textarea>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeComponentModal">
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ml-3"
                :class="{ 'opacity-25': componentForm.processing }"
                :disabled="componentForm.processing || !canCreateComponent"
                @click="submitComponentForm"
            >
                <span v-if="componentForm.processing">Creando...</span>
                <span v-else>Crear Componente</span>
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal para Agregar Kits -->
    <DialogModal :show="showKitModal" @close="closeKitModal">
        <template #title>
            Ensamblar Kit
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="kit_id" value="Kit" />
                    <select 
                        id="kit_id" 
                        v-model="kitForm.kit_id"
                        @change="loadKitData"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Seleccionar kit...</option>
                        <option 
                            v-for="kit in kits" 
                            :key="kit.id" 
                            :value="kit.id"
                        >
                            {{ kit.name }} (Stock: {{ kit.current_stock }} {{ kit.unit }})
                        </option>
                    </select>
                </div>

                <div v-if="selectedKitData && selectedKitData.components">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Componentes/Elementos requeridos:</h4>
                    <div class="bg-gray-50 rounded-lg p-3 space-y-2">
                        <div 
                            v-for="comp in selectedKitData.components" 
                            :key="comp.item.id"
                            class="flex justify-between items-center text-sm"
                            :class="comp.sufficient ? 'text-green-700' : 'text-red-700'"
                        >
                            <span>{{ comp.item.name }} ({{ comp.item.type }})</span>
                            <span>
                                Necesario: {{ comp.quantity_needed }} | 
                                Disponible: {{ comp.available_stock }}
                                <span v-if="!comp.sufficient" class="font-semibold"> ⚠️</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <InputLabel for="kit_quantity" value="Cantidad a Ensamblar" />
                    <TextInput
                        id="kit_quantity"
                        v-model="kitForm.quantity"
                        type="number"
                        min="1"
                        class="mt-1 block w-full"
                        placeholder="1"
                    />
                </div>

                <div>
                    <InputLabel for="kit_notes" value="Notas (Opcional)" />
                    <textarea
                        id="kit_notes"
                        v-model="kitForm.notes"
                        rows="3"
                        class="mt-1 py-2 px-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Notas adicionales..."
                    ></textarea>
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="closeKitModal">
                Cancelar
            </SecondaryButton>

            <PrimaryButton
                class="ml-3"
                :class="{ 'opacity-25': kitForm.processing }"
                :disabled="kitForm.processing || !canCreateKit"
                @click="submitKitForm"
            >
                <span v-if="kitForm.processing">Ensamblando...</span>
                <span v-else>Ensamblar Kit</span>
            </PrimaryButton>
        </template>
    </DialogModal>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DialogModal from '@/Components/DialogModal.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import axios from 'axios'

const emit = defineEmits(['success'])

// Estados de los modales
const showElementModal = ref(false)
const showComponentModal = ref(false)
const showKitModal = ref(false)

// Datos para las opciones
const elements = ref([])
const components = ref([])
const kits = ref([])
const suppliers = ref([])

// Datos seleccionados
const selectedComponentData = ref(null)
const selectedKitData = ref(null)

// Formularios
const elementForm = useForm({
    item_id: '',
    quantity: '',
    supplier_id: '',
    cost: '',
    notes: ''
})

const componentForm = useForm({
    component_id: '',
    quantity: 1,
    notes: ''
})

const kitForm = useForm({
    kit_id: '',
    quantity: 1,
    notes: ''
})

// Computed properties
const canCreateComponent = computed(() => {
    return selectedComponentData.value && 
           selectedComponentData.value.components && 
           selectedComponentData.value.components.every(comp => comp.sufficient)
})

const canCreateKit = computed(() => {
    return selectedKitData.value && 
           selectedKitData.value.components && 
           selectedKitData.value.components.every(comp => comp.sufficient)
})

// Métodos de carga de datos
const loadElements = async () => {
    try {
        const response = await axios.get('production/api/items/element')
        elements.value = response.data
    } catch (error) {
        console.error('Error loading elements:', error)
    }
}

const loadComponents = async () => {
    try {
        const response = await axios.get('production/api/items/component')
        components.value = response.data
    } catch (error) {
        console.error('Error loading components:', error)
    }
}

const loadKits = async () => {
    try {
        const response = await axios.get('production/api/items/kit')
        kits.value = response.data
    } catch (error) {
        console.error('Error loading kits:', error)
    }
}

const loadSuppliers = async () => {
    try {
        const response = await axios.get('production/api/suppliers')
        suppliers.value = response.data
    } catch (error) {
        console.error('Error loading suppliers:', error)
    }
}

const loadElementData = () => {
    // Aquí podrías cargar datos adicionales del elemento si fuera necesario
}

const loadComponentData = async () => {
    if (componentForm.component_id) {
        try {
            const response = await axios.get(`production/api/components/${componentForm.component_id}`)
            selectedComponentData.value = response.data
        } catch (error) {
            console.error('Error loading component data:', error)
        }
    } else {
        selectedComponentData.value = null
    }
}

const loadKitData = async () => {
    if (kitForm.kit_id) {
        try {
            const response = await axios.get(`production/api/components/${kitForm.kit_id}`)
            selectedKitData.value = response.data
        } catch (error) {
            console.error('Error loading kit data:', error)
        }
    } else {
        selectedKitData.value = null
    }
}

// Métodos de apertura de modales
const openElementModal = async () => {
    await loadElements()
    await loadSuppliers()
    showElementModal.value = true
}

const openComponentModal = async () => {
    await loadComponents()
    showComponentModal.value = true
}

const openKitModal = async () => {
    await loadKits()
    showKitModal.value = true
}

// Métodos de cierre de modales
const closeElementModal = () => {
    showElementModal.value = false
    elementForm.reset()
}

const closeComponentModal = () => {
    showComponentModal.value = false
    componentForm.reset()
    selectedComponentData.value = null
}

const closeKitModal = () => {
    showKitModal.value = false
    kitForm.reset()
    selectedKitData.value = null
}

// Métodos de envío de formularios
const submitElementForm = () => {
    elementForm.post('/production/add-element', {
        onSuccess: () => {
            closeElementModal()
            emit('success', 'Elemento agregado exitosamente')
        }
    })
}

const submitComponentForm = () => {
    componentForm.post('/production/add-component', {
        onSuccess: () => {
            closeComponentModal()
            emit('success', 'Componente creado exitosamente')
        }
    })
}

const submitKitForm = () => {
    kitForm.post('/production/add-kit', {
        onSuccess: () => {
            
            closeKitModal()
            emit('success', 'Kit ensamblado exitosamente')
        }
    })
}

// Exponer métodos para el componente padre
defineExpose({
    openElementModal,
    openComponentModal,
    openKitModal
})
</script>