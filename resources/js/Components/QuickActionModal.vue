<template>
    <Modal :show="show" @close="close" max-width="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-900">{{ actionTitle }}</h2>
                <button
                    @click="close"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <X class="w-6 h-6" />
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Item seleccionado:</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <p class="font-semibold text-gray-900">{{ item.name }}</p>
                    <p class="text-sm text-gray-600">Stock actual: {{ item.current_stock }} {{ item.unit }}</p>
                </div>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Cantidad -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Cantidad {{ action === 'add' ? 'a agregar' : 'a restar' }}
                    </label>
                    <input
                        v-model="form.quantity"
                        type="number"
                        step="0.01"
                        min="0.01"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        :class="{ 'border-red-500': errors.quantity }"
                    />
                    <p v-if="errors.quantity" class="text-red-500 text-sm mt-1">{{ errors.quantity }}</p>
                </div>

                <!-- Concepto/Razón -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ action === 'add' ? 'Concepto' : 'Motivo' }}
                    </label>
                    <textarea
                        v-model="form.concept"
                        rows="3"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                        :class="{ 'border-red-500': errors.concept }"
                        :placeholder="getConceptPlaceholder()"
                    ></textarea>
                    <p v-if="errors.concept" class="text-red-500 text-sm mt-1">{{ errors.concept }}</p>
                </div>

                <!-- Stock resultante (preview) -->
                <div v-if="form.quantity > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-sm text-gray-700">
                        <span class="font-medium">Stock resultante:</span>
                        <span class="text-lg font-bold text-blue-600 ml-2">
                            {{ calculateResultingStock() }} {{ item.unit }}
                        </span>
                    </p>
                    <p v-if="willBeLowStock()" class="text-sm text-amber-600 mt-1">
                        ⚠️ El stock quedará por debajo del mínimo ({{ item.min_stock }})
                    </p>
                </div>

                <!-- Botones -->
                <div class="flex gap-3 mt-6">
                    <button
                        type="button"
                        @click="close"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        :disabled="processing"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="getActionButtonClass()"
                        :disabled="processing || !form.quantity || !form.concept"
                    >
                        <span v-if="!processing">{{ getActionButtonText() }}</span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { X } from 'lucide-vue-next'
import Modal from '@/Components/Modal.vue'
import axios from '@/axios-config'
import Swal from 'sweetalert2'

const props = defineProps({
    show: Boolean,
    action: String, // 'add', 'defective', 'damaged', 'return', 'sale', 'mercadolibre', 'website'
    item: Object
})

const emit = defineEmits(['close', 'success'])

const processing = ref(false)
const errors = ref({})

const form = reactive({
    quantity: '',
    concept: ''
})

const actionTitle = computed(() => {
    const titles = {
        add: 'Agregar Stock',
        defective: 'Marcar como Defectuoso',
        damaged: 'Marcar como Dañado',
        return: 'Registrar Devolución',
        sale: 'Registrar Venta',
        mercadolibre: 'Venta Mercado Libre',
        website: 'Venta Página Web'
    }
    return titles[props.action] || 'Acción Rápida'
})

const getConceptPlaceholder = () => {
    const placeholders = {
        add: 'Ej: Compra a proveedor, producción interna, etc.',
        defective: 'Ej: Defecto de fábrica, mal funcionamiento, etc.',
        damaged: 'Ej: Daño en transporte, manipulación incorrecta, etc.',
        return: 'Ej: Devolución de cliente, garantía, etc.',
        sale: 'Ej: Venta directa, pedido especial, etc.',
        mercadolibre: 'Ej: Orden #ML123456, comprador: Juan Pérez',
        website: 'Ej: Pedido web #WEB789, cliente: María López'
    }
    return placeholders[props.action] || 'Ingrese el concepto o motivo'
}

const calculateResultingStock = () => {
    if (!form.quantity) return props.item.current_stock

    const quantity = parseFloat(form.quantity)
    const currentStock = parseFloat(props.item.current_stock)

    if (props.action === 'add') {
        return (currentStock + quantity).toFixed(2)
    } else {
        return (currentStock - quantity).toFixed(2)
    }
}

const willBeLowStock = () => {
    if (props.action === 'add') return false

    const resultingStock = parseFloat(calculateResultingStock())
    const minStock = parseFloat(props.item.min_stock)

    return resultingStock < minStock
}

const getActionButtonClass = () => {
    const classes = {
        add: 'bg-green-600 hover:bg-green-700',
        defective: 'bg-yellow-600 hover:bg-yellow-700',
        damaged: 'bg-red-600 hover:bg-red-700',
        return: 'bg-blue-600 hover:bg-blue-700',
        sale: 'bg-purple-600 hover:bg-purple-700',
        mercadolibre: 'bg-yellow-500 hover:bg-yellow-600',
        website: 'bg-indigo-600 hover:bg-indigo-700'
    }
    return classes[props.action] || 'bg-gray-600 hover:bg-gray-700'
}

const getActionButtonText = () => {
    const texts = {
        add: 'Agregar Stock',
        defective: 'Marcar Defectuoso',
        damaged: 'Marcar Dañado',
        return: 'Registrar Devolución',
        sale: 'Registrar Venta',
        mercadolibre: 'Confirmar Venta ML',
        website: 'Confirmar Venta Web'
    }
    return texts[props.action] || 'Confirmar'
}

const close = () => {
    if (!processing.value) {
        form.quantity = ''
        form.concept = ''
        errors.value = {}
        emit('close')
    }
}

const handleSubmit = async () => {
    errors.value = {}
    processing.value = true

    try {
        // Validación básica
        if (!form.quantity || parseFloat(form.quantity) <= 0) {
            errors.value.quantity = 'La cantidad debe ser mayor a 0'
            processing.value = false
            return
        }

        if (!form.concept || form.concept.trim() === '') {
            errors.value.concept = 'El concepto es requerido'
            processing.value = false
            return
        }

        // Validar que no se reste más de lo disponible
        if (props.action !== 'add') {
            const resultingStock = parseFloat(calculateResultingStock())
            if (resultingStock < 0) {
                await Swal.fire({
                    title: 'Stock Insuficiente',
                    text: `No hay suficiente stock. Stock actual: ${props.item.current_stock} ${props.item.unit}`,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                })
                processing.value = false
                return
            }
        }

        // Preparar datos según el tipo de acción
        const movementType = props.action === 'add' ? 'add' : 'remove'
        const movementReason = getMovementReason()

        const response = await axios.post(route('items.adjust-stock', props.item.id), {
            quantity: parseFloat(form.quantity),
            type: movementType,
            reason: movementReason,
            concept: form.concept
        })

        if (response.status === 200) {
            await Swal.fire({
                title: '¡Éxito!',
                text: 'La acción se realizó correctamente',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            })

            emit('success')
            close()
        }
    } catch (error) {
        console.error('Error performing action:', error)

        let errorMessage = 'Ocurrió un error al procesar la acción'
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message
        }

        await Swal.fire({
            title: 'Error',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#ef4444'
        })
    } finally {
        processing.value = false
    }
}

const getMovementReason = () => {
    const reasons = {
        add: 'Entrada de stock',
        defective: 'Producto defectuoso',
        damaged: 'Producto dañado',
        return: 'Devolución',
        sale: 'Venta directa',
        mercadolibre: 'Venta Mercado Libre',
        website: 'Venta página web'
    }
    return reasons[props.action] || 'Ajuste de inventario'
}

// Limpiar formulario cuando se cambia de acción
watch(() => props.show, (newVal) => {
    if (!newVal) {
        form.quantity = ''
        form.concept = ''
        errors.value = {}
    }
})
</script>
