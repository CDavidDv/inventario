<template>
    <AppLayout title="Detalles del Movimiento">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalles del Movimiento de Inventario
            </h2>
        </template>
        <div class="">
            <Container>
                <!-- Header with Back Button -->
                <div class="mb-6">
                    <button @click="goBack" 
                            class="inline-flex items-center text-blue-600 hover:text-blue-500 mb-4">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver a Movimientos
                    </button>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Movimiento #{{ movement.id }}</h1>
                                <p class="text-sm text-gray-600 mt-1">{{ formatDate(movement.movement_date) }}</p>
                            </div>
                            <Badge 
                                :color="movement.type === 'in' ? 'green' : 'red'" 
                                size="lg"
                                :text="movement.type === 'in' ? 'Entrada' : 'Salida'" />
                        </div>
                    </div>
                </div>

                <!-- Movement Details -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Información Básica</h2>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Item</label>
                                    <p class="mt-1 text-sm font-medium text-gray-900">
                                        {{ movement.component?.name || 'N/A' }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Concepto</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ movement.concept }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Cantidad</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">
                                        {{ movement.type === 'in' ? '+' : '-' }}{{ movement.quantity }}
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Stock Anterior</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ movement.quantity_before }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Stock Posterior</label>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">{{ movement.quantity_after }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4" v-if="movement.unit_cost || movement.total_cost">
                                <div v-if="movement.unit_cost">
                                    <label class="block text-sm font-medium text-gray-500">Costo Unitario</label>
                                    <p class="mt-1 text-sm text-gray-900">${{ movement.unit_cost }}</p>
                                </div>
                                <div v-if="movement.total_cost">
                                    <label class="block text-sm font-medium text-gray-500">Costo Total</label>
                                    <p class="mt-1 text-sm font-semibold text-gray-900">${{ movement.total_cost }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-500">Usuario Responsable</label>
                                <div class="mt-1 flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-medium mr-3">
                                        {{ movement.user ? movement.user.name.charAt(0).toUpperCase() : 'S' }}
                                    </div>
                                    <span class="text-sm text-gray-900">
                                        {{ movement.user?.name || 'Sistema' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Información Adicional</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Notas</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ movement.notes || 'Sin notas disponibles' }}
                                </p>
                            </div>

                            <div v-if="movement.metadata && Object.keys(movement.metadata).length > 0">
                                <label class="block text-sm font-medium text-gray-500">Metadatos</label>
                                <div class="mt-2 bg-gray-50 rounded-lg p-3">
                                    <pre class="text-xs text-gray-600">{{ JSON.stringify(movement.metadata, null, 2) }}</pre>
                                </div>
                            </div>

                            <!-- Related Information -->
                            <div v-if="movement.related_kit || movement.related_movement">
                                <label class="block text-sm font-medium text-gray-500">Información Relacionada</label>
                                <div class="mt-2 space-y-2">
                                    <div v-if="movement.related_kit" class="flex items-center text-sm">
                                        <span class="text-gray-500 mr-2">Kit relacionado:</span>
                                        <span class="text-gray-900 font-medium">{{ movement.related_kit.name }}</span>
                                    </div>
                                    <div v-if="movement.related_movement" class="flex items-center text-sm">
                                        <span class="text-gray-500 mr-2">Movimiento relacionado:</span>
                                        <button @click="viewRelatedMovement(movement.related_movement)"
                                                class="text-blue-600 hover:text-blue-500 font-medium">
                                            #{{ movement.related_movement.id }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Approval Information -->
                            <div v-if="movement.approver">
                                <label class="block text-sm font-medium text-gray-500">Aprobado por</label>
                                <div class="mt-1 flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center text-white text-sm font-medium mr-3">
                                        {{ movement.approver.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="text-sm text-gray-900">{{ movement.approver.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item Current Information (if available) -->
                <div v-if="movement.component" class="mt-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Estado Actual del Item</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ movement.component.current_stock }}</div>
                                <div class="text-sm text-blue-600">Stock Actual</div>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                                <div class="text-2xl font-bold text-yellow-600">{{ movement.component.min_stock }}</div>
                                <div class="text-sm text-yellow-600">Stock Mínimo</div>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-lg">
                                <div class="text-2xl font-bold text-red-600">{{ movement.component.max_stock }}</div>
                                <div class="text-sm text-red-600">Stock Máximo</div>
                            </div>
                        </div>

                        <div v-if="movement.component.current_stock <= movement.component.min_stock" 
                             class="mt-4 p-3 bg-red-50 border-l-4 border-red-400 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">
                                        <strong>Alerta:</strong> El stock actual está por debajo del mínimo recomendado.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Container>
        </div>
    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    movement: Object
})

const goBack = () => {
    router.visit(route('inventory-movements.index'))
}

const viewRelatedMovement = (relatedMovement) => {
    router.visit(route('inventory-movements.show', relatedMovement.id))
}

const formatDate = (date) => {
    return new Date(date).toLocaleString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>