<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-sm">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <AlertTriangle class="w-6 h-6 text-red-600 mr-3" />
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar eliminación</h3>
                </div>
                <p class="text-gray-600 mb-6">
                    ¿Estás seguro de que deseas eliminar "{{ item?.name }}"? Esta acción no se puede deshacer.
                </p>
                <div class="flex justify-end gap-3">
                    <button @click="$emit('close')"
                        class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button @click="$emit('confirm')"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { AlertTriangle } from 'lucide-vue-next'

// Props
const props = defineProps({
    show: {
        type: Boolean,
        required: true
    },
    item: {
        type: Object,
        default: null
    },
    type: {
        type: String,
        default: 'item'
    }
})

// Emits
const emit = defineEmits(['close', 'confirm'])
</script>
