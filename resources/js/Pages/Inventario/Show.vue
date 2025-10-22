<template>
    <AppLayout title="Detalle del Item">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalle del Item
                </h2>
                <button
                    @click="goBack"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center gap-2"
                >
                    <ArrowLeft class="w-4 h-4" />
                    Volver
                </button>
            </div>
        </template>

        <Container class="shadow-2xl">
            <div class="py-6 space-y-6">
                <!-- Header con información básica -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ item.name }}</h1>
                            <div class="flex flex-wrap items-center gap-3">
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-medium"
                                    :class="{
                                        'bg-blue-100 text-blue-800': item.type === 'element',
                                        'bg-purple-100 text-purple-800': item.type === 'component',
                                        'bg-green-100 text-green-800': item.type === 'kit'
                                    }"
                                >
                                    {{ getTypeLabel(item.type) }}
                                </span>
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-medium"
                                    :class="item.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                >
                                    {{ item.active ? 'Activo' : 'Inactivo' }}
                                </span>
                                <span
                                    class="px-3 py-1 rounded-full text-sm font-medium"
                                    :class="getStockStatusClass(item)"
                                >
                                    {{ getStockStatusText(item) }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">ID del Item</p>
                            <p class="text-2xl font-bold text-gray-900">#{{ item.id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Grid de información -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Stock actual -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium text-gray-500">Stock Actual</h3>
                            <Package class="w-5 h-5 text-blue-500" />
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ item.current_stock }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ item.unit }}</p>
                    </div>

                    <!-- Stock mínimo -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium text-gray-500">Stock Mínimo</h3>
                            <TrendingDown class="w-5 h-5 text-yellow-500" />
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ item.min_stock }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ item.unit }}</p>
                    </div>

                    <!-- Stock máximo -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium text-gray-500">Stock Máximo</h3>
                            <TrendingUp class="w-5 h-5 text-green-500" />
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ item.max_stock }}</p>
                        <p class="text-sm text-gray-500 mt-1">{{ item.unit }}</p>
                    </div>

                    <!-- Costo de compra -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium text-gray-500">Costo de Compra</h3>
                            <DollarSign class="w-5 h-5 text-red-500" />
                        </div>
                        <p class="text-3xl font-bold text-gray-900">${{ item.purchase_cost }}</p>
                    </div>

                    <!-- Precio de venta -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium text-gray-500">Precio de Venta</h3>
                            <DollarSign class="w-5 h-5 text-green-500" />
                        </div>
                        <p class="text-3xl font-bold text-gray-900">${{ item.sale_price }}</p>
                    </div>

                    <!-- Categoría -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-medium text-gray-500">Categoría</h3>
                            <Folder class="w-5 h-5 text-purple-500" />
                        </div>
                        <p class="text-lg font-semibold text-gray-900">{{ item.category?.name || 'Sin categoría' }}</p>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Adicional</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="item.description">
                            <p class="text-sm text-gray-500">Descripción</p>
                            <p class="text-gray-900">{{ item.description }}</p>
                        </div>
                        <div v-if="item.location">
                            <p class="text-sm text-gray-500">Ubicación</p>
                            <p class="text-gray-900">{{ item.location }}</p>
                        </div>
                        <div v-if="item.serial_number">
                            <p class="text-sm text-gray-500">Número de Serie</p>
                            <p class="text-gray-900">{{ item.serial_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- ACCIONES RÁPIDAS -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-lg p-6 border-2 border-blue-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <Zap class="w-6 h-6 text-blue-600" />
                        Acciones Rápidas
                    </h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                        <!-- Sumar cantidad -->
                        <button
                            @click="openQuickAction('add')"
                            class="flex flex-col items-center justify-center p-4 bg-white hover:bg-green-50 border-2 border-green-300 rounded-lg transition-all hover:shadow-md group"
                        >
                            <Plus class="w-8 h-8 text-green-600 mb-2 group-hover:scale-110 transition-transform" />
                            <span class="text-sm font-medium text-gray-700">Agregar Stock</span>
                        </button>

                        <!-- Defectuoso -->
                        <button
                            @click="openQuickAction('defective')"
                            class="flex flex-col items-center justify-center p-4 bg-white hover:bg-yellow-50 border-2 border-yellow-300 rounded-lg transition-all hover:shadow-md group"
                        >
                            <AlertTriangle class="w-8 h-8 text-yellow-600 mb-2 group-hover:scale-110 transition-transform" />
                            <span class="text-sm font-medium text-gray-700">Defectuoso</span>
                        </button>

                        <!-- Dañado -->
                        <button
                            @click="openQuickAction('damaged')"
                            class="flex flex-col items-center justify-center p-4 bg-white hover:bg-red-50 border-2 border-red-300 rounded-lg transition-all hover:shadow-md group"
                        >
                            <XCircle class="w-8 h-8 text-red-600 mb-2 group-hover:scale-110 transition-transform" />
                            <span class="text-sm font-medium text-gray-700">Dañado</span>
                        </button>

                        <!-- Devolución -->
                        <button
                            @click="openQuickAction('return')"
                            class="flex flex-col items-center justify-center p-4 bg-white hover:bg-blue-50 border-2 border-blue-300 rounded-lg transition-all hover:shadow-md group"
                        >
                            <RefreshCw class="w-8 h-8 text-blue-600 mb-2 group-hover:scale-110 transition-transform" />
                            <span class="text-sm font-medium text-gray-700">Devolución</span>
                        </button>

                        <!-- Venta -->
                        <button
                            @click="openQuickAction('sale')"
                            class="flex flex-col items-center justify-center p-4 bg-white hover:bg-purple-50 border-2 border-purple-300 rounded-lg transition-all hover:shadow-md group"
                        >
                            <ShoppingCart class="w-8 h-8 text-purple-600 mb-2 group-hover:scale-110 transition-transform" />
                            <span class="text-sm font-medium text-gray-700">Venta</span>
                        </button>

                        <!-- Mercado Libre -->
                        <button
                            @click="openQuickAction('mercadolibre')"
                            class="flex flex-col items-center justify-center p-4 bg-white hover:bg-yellow-50 border-2 border-yellow-400 rounded-lg transition-all hover:shadow-md group"
                        >
                            <ShoppingBag class="w-8 h-8 text-yellow-600 mb-2 group-hover:scale-110 transition-transform" />
                            <span class="text-sm font-medium text-gray-700">Mercado Libre</span>
                        </button>

                        <!-- Página Web -->
                        <button
                            @click="openQuickAction('website')"
                            class="flex flex-col items-center justify-center p-4 bg-white hover:bg-indigo-50 border-2 border-indigo-300 rounded-lg transition-all hover:shadow-md group"
                        >
                            <Globe class="w-8 h-8 text-indigo-600 mb-2 group-hover:scale-110 transition-transform" />
                            <span class="text-sm font-medium text-gray-700">Página Web</span>
                        </button>
                    </div>
                </div>

                <!-- Movimientos recientes -->
                <div v-if="movements && movements.data && movements.data.length > 0" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Movimientos Recientes</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="movement in movements.data" :key="movement.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(movement.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full font-medium"
                                            :class="{
                                                'bg-green-100 text-green-800': movement.movement_type === 'entrada',
                                                'bg-red-100 text-red-800': movement.movement_type === 'salida'
                                            }"
                                        >
                                            {{ movement.movement_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ movement.quantity }} {{ item.unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ movement.user?.name || 'Sistema' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ movement.concept || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Container>

        <!-- Modal de acción rápida -->
        <QuickActionModal
            :show="showActionModal"
            :action="currentAction"
            :item="item"
            @close="closeActionModal"
            @success="handleActionSuccess"
        />
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import QuickActionModal from '@/Components/QuickActionModal.vue'
import {
    ArrowLeft,
    Package,
    TrendingDown,
    TrendingUp,
    DollarSign,
    Folder,
    Plus,
    AlertTriangle,
    XCircle,
    RefreshCw,
    ShoppingCart,
    ShoppingBag,
    Globe,
    Zap
} from 'lucide-vue-next'

const props = defineProps({
    item: Object,
    movements: Object,
    assignedElements: Array
})

const showActionModal = ref(false)
const currentAction = ref(null)

const goBack = () => {
    router.visit(route('inventario.index'))
}

const getTypeLabel = (type) => {
    const labels = {
        element: 'Elemento',
        component: 'Componente',
        kit: 'Kit'
    }
    return labels[type] || type
}

const getStockStatusText = (item) => {
    const current = parseFloat(item.current_stock)
    const min = parseFloat(item.min_stock)
    const max = parseFloat(item.max_stock)

    if (current < min) return 'Bajo Stock'
    if (current === min) return 'En el Mínimo'
    if (current > max) return 'Sobre Stock'
    return 'Stock Normal'
}

const getStockStatusClass = (item) => {
    const current = parseFloat(item.current_stock)
    const min = parseFloat(item.min_stock)
    const max = parseFloat(item.max_stock)

    if (current < min) return 'bg-red-100 text-red-800'
    if (current === min) return 'bg-yellow-100 text-yellow-800'
    if (current > max) return 'bg-blue-100 text-blue-800'
    return 'bg-green-100 text-green-800'
}

const formatDate = (date) => {
    return new Date(date).toLocaleString('es-MX', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const openQuickAction = (action) => {
    currentAction.value = action
    showActionModal.value = true
}

const closeActionModal = () => {
    showActionModal.value = false
    currentAction.value = null
}

const handleActionSuccess = () => {
    // Recargar la página para mostrar los datos actualizados
    router.reload({ only: ['item', 'movements'] })
    closeActionModal()
}
</script>
