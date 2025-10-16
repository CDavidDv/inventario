<template>
    <AppLayout title="Detalle del Usuario">
        <div class="min-h-screen bg-gray-50">
        <Container>
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <Link
                            :href="route('supervisor.dashboard')"
                            class="inline-flex items-center px-4 py-2 bg-slate-50 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <ArrowLeftIcon class="w-4 h-4 mr-2" />
                            Regresar
                        </Link>
                        
                    </div>
                    <div class="flex items-center space-x-4">
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ user.name }}
                        </h1>
                        <Badge
                            :variant="user.active ? 'success' : 'error'"
                            :text="user.active ? 'Activo' : 'Inactivo'"
                        />
                        <p class="mt-2 text-gray-600">
                            {{ user.email }}
                        </p> 
                       
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <StatCard
                    title="Total de Logs"
                    :value="stats.total_system_logs"
                    icon="document-text"
                    color="blue"
                />
                <StatCard
                    title="Movimientos de Inventario"
                    :value="stats.total_inventory_movements"
                    icon="arrows-up-down"
                    color="green"
                />
                <StatCard
                    title="Días en el Sistema"
                    :value="stats.days_since_creation"
                    icon="calendar"
                    color="purple"
                />
                <StatCard
                    title="Última Actividad"
                    :value="stats.last_activity ? formatDate(stats.last_activity) : 'Sin actividad'"
                    icon="clock"
                    color="orange"
                />
            </div>

            <!-- User Information -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- General Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-md border border-gray-200 sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Información General
                            </h3>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                                    <dd class="text-sm text-gray-900 text-gray-900">{{ user.id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                                    <dd class="text-sm text-gray-900 text-gray-900">{{ user.name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="text-sm text-gray-900 text-gray-900">{{ user.email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                    <dd class="text-sm">
                                        <Badge 
                                            :variant="user.active ? 'success' : 'error'"
                                            :text="user.active ? 'Activo' : 'Inactivo'"
                                        />
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email verificado</dt>
                                    <dd class="text-sm">
                                        <Badge 
                                            :variant="user.email_verified_at ? 'success' : 'warning'"
                                            :text="user.email_verified_at ? 'Verificado' : 'No verificado'"
                                        />
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Fecha de registro</dt>
                                    <dd class="text-sm text-gray-900 text-gray-900">{{ formatDate(user.created_at) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Última actualización</dt>
                                    <dd class="text-sm text-gray-900 text-gray-900">{{ formatDate(user.updated_at) }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Roles -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-md border border-gray-200 sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 text-gray-900 mb-4">
                                Roles Asignados
                            </h3>
                            <div v-if="user.roles.length > 0" class="space-y-2">
                                <Badge
                                    v-for="role in user.roles"
                                    :key="role.id"
                                    variant="primary"
                                    :text="role.name"
                                />
                            </div>
                            <p v-else class="text-sm text-gray-500">
                                No tiene roles asignados
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Top Items -->
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-md border border-gray-200 sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 text-gray-900 mb-4">
                                Items Más Manipulados
                            </h3>
                            <div v-if="topItems.length > 0" class="space-y-3">
                                <div
                                    v-for="item in topItems"
                                    :key="item.component_id"
                                    class="flex justify-between items-center"
                                >
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 text-gray-900">
                                            {{ item.component?.name || 'Item eliminado' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            ID: {{ item.component?.id }}
                                        </p>
                                    </div>
                                    <span class="text-sm font-semibold text-blue-600 text-blue-600">
                                        {{ item.total_movements }} movs.
                                    </span>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-500">
                                Sin movimientos de inventario
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Stats -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Activity by Action Type -->
                <div class="bg-white overflow-hidden shadow-md border border-gray-200 sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 text-gray-900 mb-4">
                            Actividad por Tipo (Últimos 30 días)
                        </h3>
                        <div v-if="activityStats.length > 0" class="space-y-3">
                            <div
                                v-for="stat in activityStats"
                                :key="stat.action"
                                class="flex justify-between items-center"
                            >
                                <span class="text-sm font-medium text-gray-900 text-gray-900 capitalize">
                                    {{ getActionLabel(stat.action) }}
                                </span>
                                <span class="text-sm font-semibold text-blue-600 text-blue-600">
                                    {{ stat.count }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-500">
                            Sin actividad registrada
                        </p>
                    </div>
                </div>

                <!-- Movement Stats -->
                <div class="bg-white overflow-hidden shadow-md border border-gray-200 sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 text-gray-900 mb-4">
                            Movimientos por Tipo (Últimos 30 días)
                        </h3>
                        <div v-if="movementStats.length > 0" class="space-y-3">
                            <div
                                v-for="stat in movementStats"
                                :key="stat.movement_type"
                                class="flex justify-between items-center"
                            >
                                <span class="text-sm font-medium text-gray-900 text-gray-900 capitalize">
                                    {{ getMovementLabel(stat.movement_type) }}
                                </span>
                                <span class="text-sm font-semibold text-green-600 text-green-600">
                                    {{ stat.count }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-500">
                            Sin movimientos de inventario
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white overflow-hidden shadow-md border border-gray-200 sm:rounded-lg">
                <div class="border-b border-gray-200 border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            v-for="tab in tabs"
                            :key="tab.key"
                            @click="activeTab = tab.key"
                            :class="[
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                                activeTab === tab.key
                                    ? 'border-blue-500 text-blue-600 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 text-gray-400 hover:text-gray-700'
                            ]"
                        >
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <div class="p-6">
                    <!-- Inventory Movements Tab -->
                    <div v-if="activeTab === 'movements'">
                        <div v-if="inventoryMovements.data.length > 0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 divide-gray-200">
                                    <thead class="bg-gray-50 bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Item
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Tipo
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Cantidad
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Notas
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fecha
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 divide-gray-200">
                                        <tr v-for="movement in inventoryMovements.data" :key="movement.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 text-gray-900">
                                                        {{ movement.component?.name || 'Item eliminado' }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        ID: {{ movement.component?.id }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <Badge
                                                    :variant="getMovementBadgeVariant(movement.type)"
                                                    :text="getMovementLabel(movement.type)"
                                                />
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                                :class="movement.quantity > 0 ? 'text-green-600 text-green-600' : 'text-red-600 text-red-600'"
                                            >
                                                {{ movement.quantity > 0 ? '+' : '' }}{{ movement.quantity }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 text-gray-900 max-w-xs truncate">
                                                {{ movement.notes || '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(movement.created_at) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination for movements -->
                            <div class="mt-6 flex items-center justify-between">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <Link
                                        v-if="inventoryMovements.prev_page_url"
                                        :href="inventoryMovements.prev_page_url"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Anterior
                                    </Link>
                                    <Link
                                        v-if="inventoryMovements.next_page_url"
                                        :href="inventoryMovements.next_page_url"
                                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Siguiente
                                    </Link>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            Mostrando
                                            <span class="font-medium">{{ inventoryMovements.from }}</span>
                                            a
                                            <span class="font-medium">{{ inventoryMovements.to }}</span>
                                            de
                                            <span class="font-medium">{{ inventoryMovements.total }}</span>
                                            resultados
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                            <Link
                                                v-if="inventoryMovements.prev_page_url"
                                                :href="inventoryMovements.prev_page_url"
                                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                            >
                                                Anterior
                                            </Link>
                                            <Link
                                                v-if="inventoryMovements.next_page_url"
                                                :href="inventoryMovements.next_page_url"
                                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                            >
                                                Siguiente
                                            </Link>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-gray-500">
                                Este usuario no tiene movimientos de inventario registrados.
                            </p>
                        </div>
                    </div>

                    <!-- System Logs Tab -->
                    <div v-if="activeTab === 'logs'">
                        <div v-if="systemLogs.data.length > 0">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 divide-gray-200">
                                    <thead class="bg-gray-50 bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Acción
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Descripción
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                IP
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Fecha
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 divide-gray-200">
                                        <tr v-for="log in systemLogs.data" :key="log.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <Badge
                                                    :variant="getActionBadgeVariant(log.action)"
                                                    :text="getActionLabel(log.action)"
                                                />
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 text-gray-900 max-w-md truncate">
                                                {{ log.description }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ log.ip_address }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(log.created_at) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination for logs -->
                            <div class="mt-6 flex items-center justify-between">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <Link
                                        v-if="systemLogs.prev_page_url"
                                        :href="systemLogs.prev_page_url"
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Anterior
                                    </Link>
                                    <Link
                                        v-if="systemLogs.next_page_url"
                                        :href="systemLogs.next_page_url"
                                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Siguiente
                                    </Link>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            Mostrando
                                            <span class="font-medium">{{ systemLogs.from }}</span>
                                            a
                                            <span class="font-medium">{{ systemLogs.to }}</span>
                                            de
                                            <span class="font-medium">{{ systemLogs.total }}</span>
                                            resultados
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                            <Link
                                                v-if="systemLogs.prev_page_url"
                                                :href="systemLogs.prev_page_url"
                                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                            >
                                                Anterior
                                            </Link>
                                            <Link
                                                v-if="systemLogs.next_page_url"
                                                :href="systemLogs.next_page_url"
                                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                                            >
                                                Siguiente
                                            </Link>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <p class="text-gray-500">
                                Este usuario no tiene logs del sistema registrados.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Container>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import StatCard from '@/Components/StatCard.vue'
import Badge from '@/Components/Badge.vue'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    user: Object,
    stats: Object,
    inventoryMovements: Object,
    systemLogs: Object,
    activityStats: Array,
    movementStats: Array,
    dailyActivity: Array,
    topItems: Array
})

const activeTab = ref('movements')

const tabs = [
    { key: 'movements', label: 'Movimientos de Inventario' },
    { key: 'logs', label: 'Logs del Sistema' }
]

// Helper functions
const formatDate = (dateString) => {
    if (!dateString) return 'Sin fecha'
    const date = new Date(dateString)
    return date.toLocaleString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getActionLabel = (action) => {
    const labels = {
        'create': 'Crear',
        'update': 'Actualizar',
        'delete': 'Eliminar',
        'login': 'Iniciar Sesión',
        'logout': 'Cerrar Sesión',
        'view': 'Ver',
        'index': 'Listar',
        'store': 'Guardar',
        'destroy': 'Destruir',
        'show': 'Mostrar',
        'edit': 'Editar'
    }
    return labels[action] || action
}

const getMovementLabel = (type) => {
    const labels = {
        'in': 'Entrada',
        'out': 'Salida',
        'adjustment': 'Ajuste',
        'production': 'Producción',
        'damage': 'Daño',
        'return': 'Devolución'
    }
    return labels[type] || type
}

const getActionBadgeVariant = (action) => {
    const variants = {
        'create': 'success',
        'update': 'warning',
        'delete': 'error',
        'login': 'success',
        'logout': 'secondary',
        'view': 'secondary',
        'index': 'secondary'
    }
    return variants[action] || 'secondary'
}

const getMovementBadgeVariant = (type) => {
    const variants = {
        'in': 'success',
        'out': 'error',
        'adjustment': 'warning',
        'production': 'primary',
        'damage': 'error',
        'return': 'warning'
    }
    return variants[type] || 'secondary'
}
</script>