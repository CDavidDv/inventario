<template>
    <AppLayout title="Importaciones Excel">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Importaciones Excel
            </h2>
        </template>
        <Container>
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Historial de Importaciones Excel</h1>
                        <p class="text-sm text-gray-600 mt-1">Visualiza todas las importaciones realizadas desde Excel agrupadas por lote</p>
                    </div>
                    <div class="flex gap-3 mt-4 sm:mt-0">
                        <button @click="router.visit(route('inventory-movements.index'))"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Volver a Movimientos
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <StatCard
                    title="Total Importaciones"
                    :value="stats.total_imports"
                    color="blue" />
                <StatCard
                    title="Items Importados"
                    :value="stats.total_items_imported"
                    color="green" />
                <StatCard
                    title="Importaciones Hoy"
                    :value="stats.imports_today"
                    color="purple" />
                <StatCard
                    title="Importaciones Este Mes"
                    :value="stats.imports_this_month"
                    color="orange" />
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- User Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                        <select v-model="form.user_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Todos los usuarios</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }} {{ user.apellido_p }}
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
                    <div class="flex items-end gap-2 md:col-span-3">
                        <button @click="applyFilters" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                            Filtrar
                        </button>
                        <button @click="clearFilters" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Imports Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Items</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creados</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actualizados</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entradas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salidas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template v-for="(importBatch, index) in imports.data" :key="importBatch.reference">
                                <!-- Main Row -->
                                <tr class="hover:bg-gray-50 cursor-pointer" @click="toggleExpanded(importBatch.reference)">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(importBatch.date) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ importBatch.user?.name || 'Sistema' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                        {{ importBatch.total_items }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Badge color="green" :text="importBatch.items_created.toString()" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Badge color="blue" :text="importBatch.items_updated.toString()" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium">
                                        +{{ importBatch.total_quantity_in }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 font-medium">
                                        -{{ importBatch.total_quantity_out }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        ${{ formatNumber(importBatch.total_cost) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <button class="text-blue-600 hover:text-blue-900 flex items-center">
                                            <svg
                                                class="h-5 w-5 transition-transform duration-200"
                                                :class="{ 'rotate-90': expandedRows.has(importBatch.reference) }"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                            <span class="ml-1">{{ expandedRows.has(importBatch.reference) ? 'Ocultar' : 'Ver' }} detalles</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Expanded Details Row -->
                                <tr v-if="expandedRows.has(importBatch.reference)" class="bg-gray-50">
                                    <td colspan="9" class="px-6 py-4">
                                        <div class="space-y-2">
                                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Movimientos Individuales ({{ importBatch.movements.length }})</h4>
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-300">
                                                    <thead class="bg-gray-100">
                                                        <tr>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Item</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Tipo Item</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Tipo Mov.</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Concepto</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Cantidad</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Stock Ant.</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Stock Post.</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Costo Unit.</th>
                                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Costo Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <tr v-for="movement in importBatch.movements" :key="movement.id" class="hover:bg-gray-50">
                                                            <td class="px-4 py-2 text-sm text-gray-900 font-medium">
                                                                {{ movement.component?.name || 'N/A' }}
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-gray-600">
                                                                <Badge
                                                                    :color="getItemTypeColor(movement.component?.type)"
                                                                    :text="getItemTypeLabel(movement.component?.type)" />
                                                            </td>
                                                            <td class="px-4 py-2 text-sm">
                                                                <Badge
                                                                    :color="movement.type === 'in' ? 'green' : movement.type === 'out' ? 'red' : 'gray'"
                                                                    :text="movement.type_label" />
                                                            </td>
                                                            <td class="px-4 py-2 text-xs text-gray-700 max-w-xs truncate" :title="movement.concept">
                                                                {{ movement.concept }}
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-gray-900 font-medium">
                                                                {{ movement.quantity }}
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-gray-600">
                                                                {{ movement.quantity_before }}
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-gray-900 font-semibold">
                                                                {{ movement.quantity_after }}
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-gray-700">
                                                                ${{ formatNumber(movement.unit_cost) }}
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-gray-900 font-medium">
                                                                ${{ formatNumber(movement.total_cost) }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="imports.data.length === 0">
                                <td colspan="9" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    No se encontraron importaciones
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button v-if="imports.prev_page_url" @click="router.visit(imports.prev_page_url)"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Anterior
                    </button>
                    <div v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50">
                        Anterior
                    </div>
                    <button v-if="imports.next_page_url" @click="router.visit(imports.next_page_url)"
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
                            Mostrando <span class="font-medium">{{ imports.from || 0 }}</span> a <span class="font-medium">{{ imports.to || 0 }}</span>
                            de <span class="font-medium">{{ imports.total || 0 }}</span> importaciones
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <button v-if="imports.prev_page_url" @click="router.visit(imports.prev_page_url)"
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
                            <button v-if="imports.next_page_url" @click="router.visit(imports.next_page_url)"
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
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import StatCard from '@/Components/StatCard.vue'
import Badge from '@/Components/Badge.vue'

const props = defineProps({
    imports: Object,
    users: Array,
    filters: Object,
    stats: Object
})

const form = reactive({
    user_id: props.filters.user_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
})

const expandedRows = ref(new Set())

const applyFilters = () => {
    router.get(route('inventory-movements.excel-imports'), form, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    Object.keys(form).forEach(key => form[key] = '')
    applyFilters()
}

const toggleExpanded = (reference) => {
    if (expandedRows.value.has(reference)) {
        expandedRows.value.delete(reference)
    } else {
        expandedRows.value.add(reference)
    }
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

const formatNumber = (number) => {
    return new Intl.NumberFormat('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number || 0)
}

const getItemTypeLabel = (type) => {
    const labels = {
        'element': 'Elemento',
        'component': 'Componente',
        'kit': 'Kit'
    }
    return labels[type] || type
}

const getItemTypeColor = (type) => {
    const colors = {
        'element': 'blue',
        'component': 'green',
        'kit': 'orange'
    }
    return colors[type] || 'gray'
}
</script>
