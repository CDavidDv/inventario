<template>
  <AppLayout title="Dashboard Analítico">
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Dashboard Analítico del Inventario
        </h2>
        <div class="flex space-x-2">
          <button
            @click="refreshAllData"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center"
            :disabled="globalLoading"
          >
            <svg 
              class="w-4 h-4 mr-2" 
              :class="{ 'animate-spin': globalLoading }" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Actualizar Todo
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- KPIs Principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Valor Total Inventario</div>
                <div class="text-2xl font-bold text-gray-900">
                  ${{ formatCurrency(kpis.totalInventoryValue) }}
                </div>
                <div class="text-sm" :class="kpis.inventoryGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ kpis.inventoryGrowth >= 0 ? '+' : '' }}{{ kpis.inventoryGrowth.toFixed(1) }}% vs mes anterior
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Rotación de Inventario</div>
                <div class="text-2xl font-bold text-gray-900">
                  {{ kpis.inventoryTurnover.toFixed(1) }}x
                </div>
                <div class="text-sm text-gray-500">
                  {{ kpis.avgDaysInStock }} días promedio
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Elementos en Riesgo</div>
                <div class="text-2xl font-bold text-red-600">
                  {{ kpis.criticalItems }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ ((kpis.criticalItems / kpis.totalElements) * 100).toFixed(1) }}% del total
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Eficiencia de Stock</div>
                <div class="text-2xl font-bold text-purple-600">
                  {{ kpis.stockEfficiency.toFixed(1) }}%
                </div>
                <div class="text-sm text-gray-500">
                  Optimización del inventario
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Primera fila de gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Gráfico de movimientos de stock -->
          <StockChart title="Movimientos de Stock (Últimos 30 días)" />
          
          <!-- Gráfico de distribución por categorías -->
          <CategoryChart title="Distribución por Categorías" metric="value" />
        </div>

        <!-- Segunda fila de gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
          <!-- Gráfico de alertas -->
          <AlertsChart title="Evolución de Alertas" />
          
          <!-- Análisis ABC -->
          <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Análisis ABC del Inventario</h3>
              <Link
                :href="route('inventory-reports.abc-analysis')"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
              >
                Ver análisis completo →
              </Link>
            </div>
            
            <div class="h-64">
              <BaseChart
                type="doughnut"
                :data="abcChartData"
                :options="abcChartOptions"
                :loading="abcLoading"
              />
            </div>

            <div class="mt-4 space-y-2">
              <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                  <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                  <span class="text-sm font-medium">Categoría A</span>
                </div>
                <div class="text-sm text-gray-600">
                  {{ abcData.categoryA }}% del valor ({{ abcData.itemsA }} elementos)
                </div>
              </div>
              <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                  <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                  <span class="text-sm font-medium">Categoría B</span>
                </div>
                <div class="text-sm text-gray-600">
                  {{ abcData.categoryB }}% del valor ({{ abcData.itemsB }} elementos)
                </div>
              </div>
              <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                  <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                  <span class="text-sm font-medium">Categoría C</span>
                </div>
                <div class="text-sm text-gray-600">
                  {{ abcData.categoryC }}% del valor ({{ abcData.itemsC }} elementos)
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tercera fila: Tablas de insights -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Top elementos más valiosos -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Elementos Más Valiosos</h3>
            </div>
            <div class="divide-y divide-gray-200">
              <div 
                v-for="(item, index) in topValueItems" 
                :key="item.id"
                class="px-6 py-4 flex items-center justify-between hover:bg-gray-50"
              >
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                      <span class="text-blue-600 font-medium text-sm">{{ index + 1 }}</span>
                    </div>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                    <div class="text-sm text-gray-500">{{ item.category }}</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">
                    ${{ formatCurrency(item.total_value) }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ item.current_stock }} {{ item.unit }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Elementos con mayor rotación -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Mayor Rotación (30 días)</h3>
            </div>
            <div class="divide-y divide-gray-200">
              <div 
                v-for="(item, index) in topTurnoverItems" 
                :key="item.id"
                class="px-6 py-4 flex items-center justify-between hover:bg-gray-50"
              >
                <div class="flex items-center space-x-3">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                      <span class="text-green-600 font-medium text-sm">{{ index + 1 }}</span>
                    </div>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                    <div class="text-sm text-gray-500">{{ item.category }}</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-green-600">
                    {{ item.movements_count }} movimientos
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ item.total_moved }} {{ item.unit }} movidos
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="mt-8 bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Acciones Recomendadas</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <Link
                :href="route('inventory-reports.stock-status')"
                class="flex items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition-colors duration-200"
              >
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                  <div class="font-medium text-red-900">Revisar Stock Crítico</div>
                  <div class="text-sm text-red-700">{{ kpis.criticalItems }} elementos</div>
                </div>
              </Link>

              <Link
                :href="route('suppliers.index')"
                class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200"
              >
                <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <div>
                  <div class="font-medium text-blue-900">Contactar Proveedores</div>
                  <div class="text-sm text-blue-700">Para reposición</div>
                </div>
              </Link>

              <Link
                :href="route('inventory-reports.abc-analysis')"
                class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200"
              >
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <div>
                  <div class="font-medium text-green-900">Optimizar Inventario</div>
                  <div class="text-sm text-green-700">Análisis ABC</div>
                </div>
              </Link>

              <a
                :href="route('inventory-reports.elements-export')"
                class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200"
              >
                <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                  <div class="font-medium text-purple-900">Exportar Datos</div>
                  <div class="text-sm text-purple-700">Reporte completo</div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BaseChart from '@/Components/Charts/BaseChart.vue'
import StockChart from '@/Components/Charts/StockChart.vue'
import CategoryChart from '@/Components/Charts/CategoryChart.vue'
import AlertsChart from '@/Components/Charts/AlertsChart.vue'

const props = defineProps({
  initialKpis: {
    type: Object,
    default: () => ({})
  }
})

const globalLoading = ref(false)
const abcLoading = ref(false)

// KPIs principales
const kpis = ref({
  totalInventoryValue: 0,
  inventoryGrowth: 0,
  inventoryTurnover: 0,
  avgDaysInStock: 0,
  criticalItems: 0,
  totalElements: 0,
  stockEfficiency: 0,
  ...props.initialKpis
})

// Datos para análisis ABC
const abcData = ref({
  categoryA: 0,
  categoryB: 0,
  categoryC: 0,
  itemsA: 0,
  itemsB: 0,
  itemsC: 0
})

// Top elementos
const topValueItems = ref([])
const topTurnoverItems = ref([])

// Computed para gráfico ABC
const abcChartData = computed(() => ({
  labels: ['Categoría A (Alta)', 'Categoría B (Media)', 'Categoría C (Baja)'],
  datasets: [{
    data: [abcData.value.categoryA, abcData.value.categoryB, abcData.value.categoryC],
    backgroundColor: [
      'rgb(34, 197, 94)',  // green-500
      'rgb(234, 179, 8)',  // yellow-500
      'rgb(239, 68, 68)'   // red-500
    ],
    borderWidth: 2,
    borderColor: '#fff'
  }]
}))

const abcChartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        usePointStyle: true,
        padding: 20
      }
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          const value = context.parsed
          const total = context.dataset.data.reduce((sum, val) => sum + val, 0)
          const percentage = ((value / total) * 100).toFixed(1)
          return `${context.label}: ${percentage}%`
        }
      }
    }
  }
}))

// Métodos
const formatCurrency = (value) => {
  return new Intl.NumberFormat('es-ES', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value || 0)
}

const fetchKpis = async () => {
  try {
    const response = await fetch('/api/inventory/dashboard-kpis')
    const data = await response.json()
    kpis.value = { ...kpis.value, ...data }
  } catch (error) {
    console.error('Error al cargar KPIs:', error)
  }
}

const fetchAbcAnalysis = async () => {
  abcLoading.value = true
  try {
    const response = await fetch('/api/inventory/abc-summary')
    const data = await response.json()
    abcData.value = data
  } catch (error) {
    console.error('Error al cargar análisis ABC:', error)
  } finally {
    abcLoading.value = false
  }
}

const fetchTopItems = async () => {
  try {
    const [valueResponse, turnoverResponse] = await Promise.all([
      fetch('/api/inventory/top-value-items?limit=5'),
      fetch('/api/inventory/top-turnover-items?limit=5')
    ])
    
    const valueData = await valueResponse.json()
    const turnoverData = await turnoverResponse.json()
    
    topValueItems.value = valueData.items || []
    topTurnoverItems.value = turnoverData.items || []
  } catch (error) {
    console.error('Error al cargar top elementos:', error)
  }
}

const refreshAllData = async () => {
  globalLoading.value = true
  try {
    await Promise.all([
      fetchKpis(),
      fetchAbcAnalysis(),
      fetchTopItems()
    ])
  } finally {
    globalLoading.value = false
  }
}

onMounted(() => {
  refreshAllData()
})
</script>
