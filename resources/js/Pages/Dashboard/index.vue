<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Sistema de Inventario
            </h2>
        </template>
        <Container>
            <div class="pb-8 space-y-8">
                <!-- Resumen de Estados del Stock -->
                <div class=" rounded-xl ">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white rounded-lg p-4 shadow-lg border-l-4 border-red-500 " >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Bajo Stock</p>
                                    <p class="text-2xl font-bold text-red-600">{{ stats.low_stock_elements + stats.low_stock_kits + stats.low_stock_components }}</p>
                                </div>
                                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                    <div class="w-6 h-6 bg-red-500 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-lg border-l-4 border-yellow-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">En el Mínimo</p>
                                    <p class="text-2xl font-bold text-yellow-600">{{ getStockStatusCount('en_minimo') }}</p>
                                </div>
                                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <div class="w-6 h-6 bg-yellow-500 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-lg border-l-4 border-green-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Stock Normal</p>
                                    <p class="text-2xl font-bold text-green-600">{{ getStockStatusCount('normal') }}</p>
                                </div>
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                    <div class="w-6 h-6 bg-green-500 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg p-4 shadow-lg border-l-4 border-blue-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Sobre Stock</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ getStockStatusCount('sobre_stock') }}</p>
                                </div>
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <div class="w-6 h-6 bg-blue-500 rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas por Tipo de Item -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Elementos -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-100 rounded-full">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Elementos</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ stats.total_elements || 0 }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-red-600 font-medium capitalize">{{ stats.low_stock_elements || 0 }} bajo stock</div>
                                <div class="text-sm text-green-600 font-medium capitalize">{{ stats.normal_stock_elements || 0 }} stock normal</div>
                                <div class="text-sm text-blue-600 font-medium capitalize">{{ stats.over_stock_elements || 0 }} sobre stock</div>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-green-500 h-2 rounded-full" :style="{ width: getElementStockPercentage() + '%' }"></div>
                        </div>
                    </div>

                    <!-- Componentes -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-100 rounded-full">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600 ">Componentes</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ stats.total_components || 0 }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-red-600 font-medium capitalize">{{ stats.low_stock_components || 0 }} bajo stock</div>
                                <div class="text-sm text-green-600 font-medium capitalize">{{ stats.normal_stock_components || 0 }} stock normal</div>
                                <div class="text-sm text-blue-600 font-medium capitalize">{{ stats.over_stock_components || 0 }} sobre stock</div>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden ">
                            <div class="bg-blue-500 h-2 rounded-full" :style="{ width: getComponentStockPercentage() + '%' }"></div>
                        </div>
                    </div>

                    <!-- Kits -->
                    <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center">
                                <div class="p-3 bg-purple-100 rounded-full">
                                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Kits</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ stats.total_kits || 0 }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-red-600 font-medium capitalize">{{ stats.low_stock_kits || 0 }} bajo stock</div>
                                <div class="text-sm text-green-600 font-medium capitalize">{{ stats.normal_stock_kits || 0 }} stock normal</div>
                                <div class="text-sm text-blue-600 font-medium capitalize">{{ stats.over_stock_kits || 0 }} sobre stock</div>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-purple-500 h-2 rounded-full" :style="{ width: getKitStockPercentage() + '%' }"></div>
                        </div>
                    </div>
                </div>

                

                <!-- Gráficas y Análisis -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Gráfica Circular de Distribución por Tipo -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">📊 Distribución por Tipo</h3>
                        <div class="flex items-center justify-center">
                            <div class="relative w-48 h-48">
                                <!-- Círculo de fondo -->
                                <svg class="w-48 h-48 transform -rotate-90" viewBox="0 0 100 100">
                                    <!-- Elementos -->
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#10b981" stroke-width="8" 
                                            :stroke-dasharray="getCircleDashArray('elements')" 
                                            stroke-dashoffset="0"/>
                                    <!-- Kits -->
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#8b5cf6" stroke-width="8" 
                                            :stroke-dasharray="getCircleDashArray('kits')" 
                                            :stroke-dashoffset="getCircleDashOffset('kits')"/>
                                    <!-- Componentes -->
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="#3b82f6" stroke-width="8" 
                                            :stroke-dasharray="getCircleDashArray('components')" 
                                            :stroke-dashoffset="getCircleDashOffset('components')"/>
                                </svg>
                                <!-- Texto central -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-gray-900">{{ getTotalItems() }}</div>
                                        <div class="text-sm text-gray-600">Total Items</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Leyenda -->
                        <div class="mt-6 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-sm text-gray-600">Elementos</span>
                                </div>
                                <span class="text-sm font-medium">{{ stats.total_elements || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-purple-500 rounded-full mr-2"></div>
                                    <span class="text-sm text-gray-600">Kits</span>
                                </div>
                                <span class="text-sm font-medium">{{ stats.total_kits || 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-blue-500 rounded-full mr-2"></div>
                                    <span class="text-sm text-gray-600">Componentes</span>
                                </div>
                                <span class="text-sm font-medium">{{ stats.total_components || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfica de Barras de Estados de Stock -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">📈 Estados de Stock</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-red-500 rounded-full mr-3"></div>
                                    <span class="text-sm text-gray-600">Bajo Stock</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                        <div class="bg-red-500 h-2 rounded-full" :style="{ width: getBarPercentage('bajo_stock') + '%' }"></div>
                                    </div>
                                    <span class="text-sm font-medium w-8">{{ getStockStatusCount('bajo_stock') }}</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-yellow-500 rounded-full mr-3"></div>
                                    <span class="text-sm text-gray-600">En el Mínimo</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                        <div class="bg-yellow-500 h-2 rounded-full" :style="{ width: getBarPercentage('en_minimo') + '%' }"></div>
                                    </div>
                                    <span class="text-sm font-medium w-8">{{ getStockStatusCount('en_minimo') }}</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                                    <span class="text-sm text-gray-600">Stock Normal</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                        <div class="bg-green-500 h-2 rounded-full" :style="{ width: getBarPercentage('normal') + '%' }"></div>
                                    </div>
                                    <span class="text-sm font-medium w-8">{{ getStockStatusCount('normal') }}</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 bg-blue-500 rounded-full mr-3"></div>
                                    <span class="text-sm text-gray-600">Sobre Stock</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                        <div class="bg-blue-500 h-2 rounded-full" :style="{ width: getBarPercentage('sobre_stock') + '%' }"></div>
                                    </div>
                                    <span class="text-sm font-medium w-8">{{ getStockStatusCount('sobre_stock') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen de Valor Total -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">💰 Valor del Inventario</h3>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-green-600 mb-2">
                                {{ formatCurrency(stats.total_value || 0) }}
                            </div>
                            <div class="text-sm text-gray-600 mb-6">Valor Total</div>
                            
                            <!-- Desglose por tipo -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Elementos</span>
                                    <span class="text-sm font-medium text-green-600">{{ formatCurrency(getElementValue()) }}</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Kits</span>
                                    <span class="text-sm font-medium text-purple-600">{{ formatCurrency(getKitValue()) }}</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                    <span class="text-sm text-gray-600">Componentes</span>
                                    <span class="text-sm font-medium text-blue-600">{{ formatCurrency(getComponentValue()) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenido Principal -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Items con Stock Bajo -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">⚠️ Stock Bajo - Acción Requerida</h3>
                            <button @click="navigateTo('/inventario')" 
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ver Todos →
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div v-for="item in getLowStockItems()" :key="item.id" 
                                    class="border-l-4 border-red-500 pl-4 py-3 rounded-r-lg bg-red-50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ item.name }}</h4>
                                        <p class="text-sm text-gray-600">{{ item.categoryName || 'Sin categoría' }}</p>
                                        <div class="flex items-center space-x-4 mt-2">
                                            <span class="text-sm text-red-600">
                                                Stock: {{ item.current_stock }}
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                Mín: {{ item.min_stock }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded-full border border-red-300">
                                            {{ getStockStatusText(item) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="getLowStockItems().length === 0" class="text-center py-8 text-gray-500">
                                ✅ No hay items con stock bajo
                            </div>
                        </div>
                    </div>
                    <!-- Movimientos Recientes -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Movimientos Recientes</h3>
                        <button @click="navigateTo('/inventory-movements')" 
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Ver Historial Completo →
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div v-for="movement in recentMovements" :key="movement.id" 
                                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">{{ movement.component?.name }}</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ movement.type }} - {{ movement.quantity }} unidades
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ movement.user?.name }} • {{ formatDate(movement.created_at) }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-medium" 
                                        :class="movement.type === 'entrada' ? 'text-green-600' : 'text-red-600'">
                                    {{ movement.type === 'entrada' ? '+' : '-' }}{{ movement.quantity }}
                                </span>
                            </div>
                        </div>
                        <div v-if="!recentMovements || recentMovements?.length === 0" class="text-center py-8 text-gray-500">
                            No hay movimientos recientes
                        </div>
                    </div>
                </div>
                </div>


                

            </div>
        </Container>
        
    </AppLayout>
</template>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { ref, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    message: String,
    stats: Object,
    categoryDistribution: Array,
    topValueComponents: Array,
    overStockItems: Array,
    lowStockItems: Array,
    elementItems: Array,
    kitItems: Array,
    componentItems: Array,
    recentMovements: Array
});

// Estado reactivo para estadísticas
const stats = ref(props.stats || {
    total_items: 0,
    total_components: 0,
    total_elements: 0,
    total_kits: 0,
    low_stock_elements: 0,
    over_stock_elements: 0,
    low_stock_kits: 0,
    over_stock_kits: 0,
    low_stock_components: 0,
    over_stock_components: 0,
    total_categories: 0,
    total_value: 0
});

const loading = ref(false);

// Datos reactivos para movimientos recientes
const recentMovements = ref(props.recentMovements || []);

// Función para navegar a diferentes secciones
const navigateTo = (route) => {
    router.visit(route);
};

// Función para formatear fechas
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Función para formatear moneda
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(amount);
};

// Función para obtener el color de la categoría
const getCategoryColor = (index) => {
    const colors = ['bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-red-500', 'bg-purple-500', 'bg-indigo-500'];
    return colors[index % colors?.length];
};

// Función para obtener el porcentaje de stock
const getStockPercentage = (current, min, max) => {
    if (max === 0) return 0;
    return Math.round((current / max) * 100);
};

// Función para obtener el estado del stock
const getStockStatus = (current, min, max) => {
    if (current <= min) return { status: 'Bajo', color: 'text-red-600', bg: 'bg-red-100' };
    if (current >= max) return { status: 'Alto', color: 'text-yellow-600', bg: 'bg-yellow-100' };
    return { status: 'Normal', color: 'text-green-600', bg: 'bg-green-100' };
};

// Función para contar items por estado de stock
const getStockStatusCount = (status) => {
    let count = 0;
    
    // Función helper para calcular el estado del stock
    const getItemStockStatus = (item) => {
        const current = parseFloat(item.current_stock);
        const min = parseFloat(item.min_stock);
        const max = parseFloat(item.max_stock);
        
        if (current < min) {
            return 'bajo_stock';
        } else if (current === min) {
            return 'en_minimo';
        } else if (current > max) {
            return 'sobre_stock';
        } else {
            return 'normal';
        }
    };
    
    // Contar en elementos
    if (props.elementItems) {
        count += props.elementItems.filter(item => getItemStockStatus(item) === status).length;
    }
    
    // Contar en kits
    if (props.kitItems) {
        count += props.kitItems.filter(item => getItemStockStatus(item) === status).length;
    }
    
    // Contar en componentes
    if (props.componentItems) {
        count += props.componentItems.filter(item => getItemStockStatus(item) === status).length;
    }
    
    return count;
};

// Función para calcular porcentaje de stock por tipo
const getElementStockPercentage = () => {
    const total = stats.value.total_elements || 0;
    const normal = getStockStatusCount('normal');
    return total > 0 ? Math.round((normal / total) * 100) : 0;
};

const getKitStockPercentage = () => {
    const total = stats.value.total_kits || 0;
    const normal = getStockStatusCount('normal');
    console.log('Kit Stock Percentage:', { total, normal });
    return total > 0 ? Math.round((normal / total) * 100) : 0;
};

const getComponentStockPercentage = () => {
    const total = stats.value.total_components || 0;
    const normal = getStockStatusCount('normal');
    return total > 0 ? Math.round((normal / total) * 100) : 0;
};

// Funciones para gráficas circulares
const getTotalItems = () => {
    return (stats.value.total_elements || 0) + (stats.value.total_kits || 0) + (stats.value.total_components || 0);
};

const getCircleDashArray = (type) => {
    const total = getTotalItems();
    if (total === 0) return '0 251.2';
    
    let count = 0;
    switch (type) {
        case 'elements':
            count = stats.value.total_elements || 0;
            break;
        case 'kits':
            count = stats.value.total_kits || 0;
            break;
        case 'components':
            count = stats.value.total_components || 0;
            break;
    }
    
    const percentage = (count / total) * 100;
    const circumference = 2 * Math.PI * 40; // r=40
    const dashLength = (percentage / 100) * circumference;
    return `${dashLength} ${circumference}`;
};

const getCircleDashOffset = (type) => {
    const total = getTotalItems();
    if (total === 0) return 0;
    
    let offset = 0;
    const circumference = 2 * Math.PI * 40;
    
    if (type === 'kits') {
        const elementsCount = stats.value.total_elements || 0;
        const percentage = (elementsCount / total) * 100;
        offset = (percentage / 100) * circumference;
    } else if (type === 'components') {
        const elementsCount = stats.value.total_elements || 0;
        const kitsCount = stats.value.total_kits || 0;
        const percentage = ((elementsCount + kitsCount) / total) * 100;
        offset = (percentage / 100) * circumference;
    }
    
    return -offset;
};

// Función para barras de progreso
const getBarPercentage = (status) => {
    const total = getTotalItems();
    if (total === 0) return 0;
    const count = getStockStatusCount(status);
    return Math.round((count / total) * 100);
};

// Funciones para valores monetarios
const getElementValue = () => {
    if (!props.elementItems) return 0;
    return props.elementItems.reduce((total, item) => {
        return total + (parseFloat(item.current_stock) * parseFloat(item.purchase_cost || 0));
    }, 0);
};

const getKitValue = () => {
    if (!props.kitItems) return 0;
    return props.kitItems.reduce((total, item) => {
        return total + (parseFloat(item.current_stock) * parseFloat(item.purchase_cost || 0));
    }, 0);
};

const getComponentValue = () => {
    if (!props.componentItems) return 0;
    return props.componentItems.reduce((total, item) => {
        return total + (parseFloat(item.current_stock) * parseFloat(item.purchase_cost || 0));
    }, 0);
};

// Función para obtener items con stock bajo
const getLowStockItems = () => {
    const allItems = [
        ...(props.elementItems || []),
        ...(props.kitItems || []),
        ...(props.componentItems || [])
    ];
    
    return allItems.filter(item => {
        const current = parseFloat(item.current_stock);
        const min = parseFloat(item.min_stock);
        return current <= min;
    }).slice(0, 5); // Solo mostrar los primeros 5
};

// Función para obtener el texto del estado del stock
const getStockStatusText = (item) => {
    const current = parseFloat(item.current_stock);
    const min = parseFloat(item.min_stock);
    const max = parseFloat(item.max_stock);
    
    if (current < min) {
        return 'Bajo Stock';
    } else if (current === min) {
        return 'En el Mínimo';
    } else if (current > max) {
        return 'Sobre Stock';
    } else {
        return 'Normal';
    }
};

onMounted(() => {
    // Los datos ya vienen del controlador
    loading.value = false;
});
</script>
