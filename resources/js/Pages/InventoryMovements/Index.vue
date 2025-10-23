<template>
    <AppLayout title="Movimientos de Inventario">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Movimientos de Inventario
            </h2>
        </template>
        <Container>
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Movimientos de Inventario</h1>
                        <p class="text-sm text-gray-600 mt-1">Historial completo de movimientos de stock</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <StatCard 
                    title="Total Movimientos" 
                    :value="stats.total_movements" 
                    color="blue" />
                <StatCard 
                    title="Hoy" 
                    :value="stats.movements_today" 
                    color="green" />
                <StatCard 
                    title="Esta Semana" 
                    :value="stats.movements_this_week" 
                    color="purple" />
                <StatCard 
                    title="Este Mes" 
                    :value="stats.movements_this_month" 
                    color="orange" />
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
                            placeholder="Buscar movimientos..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select v-model="form.type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos los tipos</option>
                            <option value="in">Entrada</option>
                            <option value="out">Salida</option>
                        </select>
                    </div>

                    <!-- Concept Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Concepto</label>
                        <select v-model="form.concept" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos los conceptos</option>
                            <option v-for="concept in concepts" :key="concept" :value="concept">
                                {{ getConceptLabel(concept) }}
                            </option>
                        </select>
                    </div>

                    <!-- Item Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item</label>
                        <select v-model="form.item_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos los items</option>
                            <option v-for="item in items" :key="item.id" :value="item.id">
                                {{ item.name }}
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
                    <div class="flex items-end gap-2">
                        <button @click="applyFilters" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                            Filtrar
                        </button>
                        <button @click="clearFilters" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Movements Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Anterior</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Posterior</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(movement.movement_date) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ movement.component?.name || 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge 
                                        :color="movement.type === 'in' ? 'green' : 'red'"
                                        :text="movement.type === 'in' ? 'Entrada' : 'Salida'" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ getConceptLabel(movement.concept) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ movement.quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ movement.quantity_before }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ movement.quantity_after }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ movement.user?.name || 'Sistema' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button @click="viewMovement(movement)" 
                                            class="text-blue-600 hover:text-blue-900">
                                        Ver detalles
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="movements.data.length === 0">
                                <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No se encontraron movimientos
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button v-if="movements.prev_page_url" @click="router.visit(movements.prev_page_url)" 
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Anterior
                    </button>
                    <div v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50">
                        Anterior
                    </div>
                    <button v-if="movements.next_page_url" @click="router.visit(movements.next_page_url)"
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
                            Mostrando <span class="font-medium">{{ movements.from || 0 }}</span> a <span class="font-medium">{{ movements.to || 0 }}</span> 
                            de <span class="font-medium">{{ movements.total || 0 }}</span> resultados
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button v-if="movements.prev_page_url" @click="router.visit(movements.prev_page_url)" 
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
                            <button v-if="movements.next_page_url" @click="router.visit(movements.next_page_url)"
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
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import StatCard from '@/Components/StatCard.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    movements: Object,
    items: Array,
    users: Array,
    concepts: Array,
    filters: Object,
    stats: Object
})

const form = reactive({
    search: props.filters.search || '',
    type: props.filters.type || '',
    concept: props.filters.concept || '',
    item_id: props.filters.item_id || '',
    user_id: props.filters.user_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
})

const applyFilters = () => {
    router.get(route('inventory-movements.index'), form, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    Object.keys(form).forEach(key => form[key] = '')
    applyFilters()
}

const exportMovements = () => {
    const params = new URLSearchParams(form).toString()
    window.open(`${route('inventory-movements.export')}?${params}`, '_blank')
}

const viewMovement = (movement) => {
    router.visit(route('inventory-movements.show', movement.id))
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

const getConceptLabel = (concept) => {
    const labels = {
        'manual_addition': 'Adición Manual',
        'component_assembly': 'Ensamble de Componente',
        'component_creation': 'Creación de Componente',
        'defective': 'Defectuoso',
        'kit_assembly': 'Ensamble de Kit',
        'return': 'Devolución',
        'damaged': 'Dañado',
        'sale': 'Venta',
        'internet_sale': 'Venta Internet',
        'website_sale': 'Venta Sitio Web',
        'mercadolibre_sale': 'Venta MercadoLibre'
    }
    return labels[concept] || concept
}

// Auto-apply filters when form changes (debounced)
let filterTimeout
watch(() => form.search, () => {
    clearTimeout(filterTimeout)
    filterTimeout = setTimeout(applyFilters, 500)
})
</script>