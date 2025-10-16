<template>
    <AppLayout title="Registro de Auditoría del Sistema">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registro de Auditoría del Sistema
            </h2>

        </template>
        <Container>
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Registro de Auditoría del Sistema</h1>
                        <p class="text-sm text-gray-600 mt-1">Historial completo de actividades del sistema</p>
                    </div>
                    <div class="flex gap-3 mt-4 sm:mt-0">
                        <button @click="cleanupLogs" 
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1-1H9a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Limpiar (90+ días)
                        </button>
                        <!--
                        <button @click="exportLogs" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Exportar CSV
                        </button>
                        -->
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <StatCard 
                    title="Registros"
                    label="Total Registros" 
                    text="Total de acciones registradas en el sistema"
                    :value="stats.total_logs" 
                    variant="primary" />
                <StatCard
                    title="Actividad"
                    label="Hoy" 
                    :value="stats.logs_today" 
                    text="Acciones registradas en las últimas 24 horas"
                    variant="success" />
                <StatCard 
                    title="Actividad"
                    label="Esta Semana" 
                    :value="stats.logs_this_week" 
                    text="Acciones registradas en los últimos 7 días"
                    variant="info" />
                <StatCard 
                    title="Actividad"
                    label="Este Mes" 
                    :value="stats.logs_this_month" 
                    text="Acciones registradas en los últimos 30 días"
                    variant="warning" />
                <StatCard 
                    title="Usuarios"
                    label="Usuarios Activos Hoy" 
                    :value="stats.unique_users_today" 
                    text="Número de usuarios únicos que han realizado acciones hoy"
                    variant="default" />
            </div>

            <!-- Most Active User Card -->
            <div v-if="stats.most_active_user" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Usuario Más Activo</h3>
                <div class="flex items-center">
                    <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center text-white text-lg font-bold mr-4">
                        {{ stats.most_active_user.user?.name?.charAt(0).toUpperCase() || '?' }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ stats.most_active_user.user?.name || 'Usuario desconocido' }}</p>
                        <p class="text-sm text-gray-600">{{ stats.most_active_user.log_count }} acciones registradas</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input 
                            v-model="form.search" 
                            type="text" 
                            placeholder="Buscar en logs..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Action Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Acción</label>
                        <select v-model="form.action" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todas las acciones</option>
                            <option v-for="action in actions" :key="action" :value="action">
                                {{ getActionLabel(action) }}
                            </option>
                        </select>
                    </div>

                    <!-- Module Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Módulo</label>
                        <select v-model="form.module" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos los módulos</option>
                            <option v-for="module in modules" :key="module" :value="module">
                                {{ getModuleLabel(module) }}
                            </option>
                        </select>
                    </div>

                    <!-- User Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                        <select v-model="form.user_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos los usuarios</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                        <input 
                            v-model="form.date_from" 
                            type="date" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                        <input 
                            v-model="form.date_to" 
                            type="date" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Filter Actions -->
                    <div class="flex items-end gap-2 col-span-2">
                        <button @click="applyFilters" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                            Filtrar
                        </button>
                        <button @click="clearFilters" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(log.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-medium mr-3">
                                            {{ log.user ? log.user.name.charAt(0).toUpperCase() : 'S' }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ log.user?.name || 'Sistema' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge :color="getActionColor(log.action)" :text="getActionLabel(log.action)" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                        {{ getModuleLabel(log.module) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                                    {{ log.description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ log.ip_address }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button @click="viewLog(log)" 
                                            class="text-blue-600 hover:text-blue-900">
                                        Ver detalles
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No se encontraron registros
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button v-if="logs.prev_page_url" @click="router.visit(logs.prev_page_url)" 
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Anterior
                    </button>
                    <div v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50">
                        Anterior
                    </div>
                    <button v-if="logs.next_page_url" @click="router.visit(logs.next_page_url)"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Siguiente
                    </button>
                    <div v-else class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50">
                        Siguiente
                    </div>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Mostrando <span class="font-medium">{{ logs.from || 0 }}</span> a <span class="font-medium">{{ logs.to || 0 }}</span> 
                            de <span class="font-medium">{{ logs.total || 0 }}</span> resultados
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button v-if="logs.prev_page_url" @click="router.visit(logs.prev_page_url)" 
                                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div v-else class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-300">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <button v-if="logs.next_page_url" @click="router.visit(logs.next_page_url)"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div v-else class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-300">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </Container>
    
    </AppLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import Swal from 'sweetalert2'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import StatCard from '@/Components/StatCard.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    logs: Object,
    users: Array,
    actions: Array,
    modules: Array,
    filters: Object,
    stats: Object
})

const form = reactive({
    search: props.filters.search || '',
    action: props.filters.action || '',
    module: props.filters.module || '',
    user_id: props.filters.user_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
})

const applyFilters = () => {
    router.get(route('system-logs.index'), form, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    Object.keys(form).forEach(key => form[key] = '')
    applyFilters()
}

const exportLogs = () => {
    const params = new URLSearchParams(form).toString()
    window.open(`${route('system-logs.export')}?${params}`, '_blank')
}

const cleanupLogs = async () => {
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: 'Se eliminarán todos los registros de más de 90 días. Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar'
    })

    if (result.isConfirmed) {
        try {
            const response = await axios.post(route('system-logs.cleanup'))
            Swal.fire('¡Éxito!', response.data.message, 'success')
            location.reload()
        } catch (error) {
            Swal.fire('Error', 'Ocurrió un error al limpiar los registros', 'error')
        }
    }
}

const viewLog = (log) => {
    router.visit(route('system-logs.show', log.id))
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

const getActionLabel = (action) => {
    const labels = {
        'create': 'Crear',
        'update': 'Actualizar',
        'delete': 'Eliminar',
        'login': 'Iniciar Sesión',
        'logout': 'Cerrar Sesión',
        'view': 'Ver',
        'export': 'Exportar',
        'import': 'Importar',
        'restore': 'Restaurar',
        'force_delete': 'Eliminar Permanentemente',
    }
    return labels[action] || action.charAt(0).toUpperCase() + action.slice(1)
}

const getModuleLabel = (module) => {
    const labels = {
        'user': 'Usuario',
        'item': 'Item',
        'category': 'Categoría',
        'supplier': 'Proveedor',
        'inventory': 'Inventario',
        'production': 'Producción',
        'auth': 'Autenticación',
        'system': 'Sistema',
    }
    return labels[module] || module.charAt(0).toUpperCase() + module.slice(1)
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

// Auto-apply filters when form changes (debounced)
let filterTimeout
watch(() => form.search, () => {
    clearTimeout(filterTimeout)
    filterTimeout = setTimeout(applyFilters, 500)
})
</script>