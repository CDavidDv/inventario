<template>
    <AppLayout title="Detalles del Registro de Auditoría">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalles del Registro de Auditoría
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
                        Volver a Registros
                    </button>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Registro de Auditoría #{{ log.id }}</h1>
                                <p class="text-sm text-gray-600 mt-1">{{ formatDate(log.created_at) }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Badge 
                                    :color="getActionColor(log.action)" 
                                    size="lg"
                                    :text="getOperationType(log.action)" />
                                <span class="px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 rounded-full">
                                    {{ log.module || 'Sistema' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Information -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información Principal</h2>
                            
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Acción</label>
                                        <p class="mt-1 text-sm font-medium text-gray-900">{{ getOperationType(log.action) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-500">Módulo</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ log.module || 'Sistema' }}</p>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Descripción</label>
                                    <p class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">
                                        {{ log.description }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Usuario Responsable</label>
                                    <div class="mt-2 flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-medium mr-3">
                                            {{ log.user ? log.user.name.charAt(0).toUpperCase() : 'S' }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ log.user?.name || 'Sistema' }}
                                            </p>
                                            <p v-if="log.user?.email" class="text-xs text-gray-500">
                                                {{ log.user.email }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Changes Information -->
                        <div v-if="log.old_values || log.new_values" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Cambios Realizados</h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Old Values -->
                                <div v-if="log.old_values && Object.keys(log.old_values).length > 0">
                                    <h3 class="text-md font-medium text-gray-900 mb-3 flex items-center">
                                        <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                        </svg>
                                        Valores Anteriores
                                    </h3>
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <div class="space-y-2">
                                            <div v-for="(value, key) in log.old_values" :key="`old-${key}`" class="text-sm">
                                                <span class="font-medium text-red-800">{{ formatFieldName(key) }}:</span>
                                                <span class="text-red-700 ml-2">{{ formatValue(value) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- New Values -->
                                <div v-if="log.new_values && Object.keys(log.new_values).length > 0">
                                    <h3 class="text-md font-medium text-gray-900 mb-3 flex items-center">
                                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Valores Nuevos
                                    </h3>
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <div class="space-y-2">
                                            <div v-for="(value, key) in log.new_values" :key="`new-${key}`" class="text-sm">
                                                <span class="font-medium text-green-800">{{ formatFieldName(key) }}:</span>
                                                <span class="text-green-700 ml-2">{{ formatValue(value) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Comparison View -->
                            <div v-if="log.old_values && log.new_values" class="mt-6">
                                <h3 class="text-md font-medium text-gray-900 mb-3">Comparación de Cambios</h3>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg overflow-hidden">
                                    <table class="w-full">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campo</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Antes</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Después</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <tr v-for="key in getAllChangedFields()" :key="`compare-${key}`" class="hover:bg-gray-50">
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ formatFieldName(key) }}</td>
                                                <td class="px-4 py-3 text-sm text-red-600">{{ formatValue(log.old_values[key]) || '-' }}</td>
                                                <td class="px-4 py-3 text-sm text-green-600">{{ formatValue(log.new_values[key]) || '-' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Related Object Information -->
                        <div v-if="log.loggable" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Objeto Relacionado</h2>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="h-8 w-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17v4a2 2 0 002 2h4M13 13h4a2 2 0 012 2v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4a2 2 0 012-2z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-blue-900">
                                            {{ formatModelName(log.loggable_type) }}
                                        </p>
                                        <p class="text-sm text-blue-700">ID: {{ log.loggable_id }}</p>
                                        <p v-if="log.loggable.name" class="text-sm text-blue-700">{{ log.loggable.name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Technical Information Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Información Técnica</h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Fecha y Hora</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ formatFullDate(log.created_at) }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Dirección IP</label>
                                    <p class="mt-1 text-sm text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded">
                                        {{ log.ip_address || 'N/A' }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">ID de Sesión</label>
                                    <p class="mt-1 text-xs text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded break-all">
                                        {{ log.session_id || 'N/A' }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">User Agent</label>
                                    <p class="mt-1 text-xs text-gray-900 bg-gray-100 px-2 py-1 rounded break-words">
                                        {{ log.user_agent || 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Categories -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Categorización</h2>
                            
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Nivel de Riesgo</span>
                                    <Badge 
                                        :color="getRiskLevel(log.action).color" 
                                        size="sm"
                                        :text="getRiskLevel(log.action).label" />
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Tipo de Operación</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ getOperationType(log.action) }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Requiere Atención</span>
                                    <span :class="requiresAttention(log.action) ? 'text-red-600' : 'text-green-600'" class="text-sm font-medium">
                                        {{ requiresAttention(log.action) ? 'Sí' : 'No' }}
                                    </span>
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
    log: Object
})

const goBack = () => {
    router.visit(route('system-logs.index'))
}

const formatDate = (date) => {
    return new Date(date).toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatFullDate = (date) => {
    return new Date(date).toLocaleString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZoneName: 'short'
    })
}

const formatFieldName = (fieldName) => {
    const fieldNames = {
        'name': 'Nombre',
        'email': 'Email',
        'current_stock': 'Stock Actual',
        'min_stock': 'Stock Mínimo',
        'max_stock': 'Stock Máximo',
        'price': 'Precio',
        'description': 'Descripción',
        'status': 'Estado',
        'created_at': 'Fecha de Creación',
        'updated_at': 'Fecha de Actualización',
    }
    return fieldNames[fieldName] || fieldName.charAt(0).toUpperCase() + fieldName.slice(1).replace('_', ' ')
}

const formatValue = (value) => {
    if (value === null || value === undefined) return 'N/A'
    if (typeof value === 'boolean') return value ? 'Sí' : 'No'
    if (typeof value === 'object') return JSON.stringify(value, null, 2)
    return String(value)
}

const formatModelName = (modelType) => {
    const modelNames = {
        'App\\Models\\Item': 'Item',
        'App\\Models\\User': 'Usuario',
        'App\\Models\\Category': 'Categoría',
        'App\\Models\\Supplier': 'Proveedor',
        'App\\Models\\InventoryMovement': 'Movimiento de Inventario',
    }
    return modelNames[modelType] || modelType.split('\\').pop()
}

const getActionColor = (action) => {
    const colors = {
        'create': 'green',
        'update': 'blue',
        'delete': 'red',
        'login': 'green',
        'logout': 'gray',
        'view': 'blue',
        'export': 'purple',
        'import': 'indigo',
        'restore': 'yellow',
        'force_delete': 'red',
    }
    return colors[action] || 'gray'
}

const getRiskLevel = (action) => {
    const riskLevels = {
        'create': { label: 'Bajo', color: 'green' },
        'update': { label: 'Medio', color: 'yellow' },
        'delete': { label: 'Alto', color: 'red' },
        'force_delete': { label: 'Crítico', color: 'red' },
        'login': { label: 'Bajo', color: 'green' },
        'logout': { label: 'Bajo', color: 'green' },
        'view': { label: 'Muy Bajo', color: 'gray' },
        'export': { label: 'Medio', color: 'yellow' },
        'import': { label: 'Alto', color: 'orange' },
        'restore': { label: 'Alto', color: 'orange' },
    }
    return riskLevels[action] || { label: 'Desconocido', color: 'gray' }
}

const getOperationType = (action) => {
    const operationTypes = {
        'create': 'Creación',
        'update': 'Modificación',
        'delete': 'Eliminación',
        'force_delete': 'Eliminación Permanente',
        'login': 'Autenticación',
        'logout': 'Autenticación',
        'view': 'Consulta',
        'export': 'Exportación',
        'import': 'Importación',
        'restore': 'Restauración',
    }
    return operationTypes[action] || 'Otra'
}

const requiresAttention = (action) => {
    const criticalActions = ['delete', 'force_delete', 'import', 'restore']
    return criticalActions.includes(action)
}

const getAllChangedFields = () => {
    const oldKeys = props.log.old_values ? Object.keys(props.log.old_values) : []
    const newKeys = props.log.new_values ? Object.keys(props.log.new_values) : []
    return [...new Set([...oldKeys, ...newKeys])]
}
</script>