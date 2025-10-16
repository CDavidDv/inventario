<template>
  <AppLayout title="Centro de Reportes">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Centro de Reportes y Analytics
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Estadísticas Generales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Elementos -->
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
                <div class="text-sm font-medium text-gray-500">Elementos Totales</div>
                <div class="text-2xl font-bold text-gray-900">{{ stats.elements.total }}</div>
                <div class="text-sm text-gray-500">{{ stats.elements.categories }} categorías</div>
              </div>
            </div>
          </div>

          <!-- Stock Crítico -->
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
                <div class="text-sm font-medium text-gray-500">Stock Crítico</div>
                <div class="text-2xl font-bold text-red-600">{{ stats.elements.low_stock + stats.elements.out_of_stock }}</div>
                <div class="text-sm text-gray-500">{{ stats.elements.out_of_stock }} agotados</div>
              </div>
            </div>
          </div>

          <!-- Proveedores -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Proveedores</div>
                <div class="text-2xl font-bold text-green-600">{{ stats.suppliers.total }}</div>
                <div class="text-sm text-gray-500">{{ stats.suppliers.active }} activos</div>
              </div>
            </div>
          </div>

          <!-- Alertas -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 7H4l5-5v5z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-500">Alertas Activas</div>
                <div class="text-2xl font-bold text-orange-600">{{ stats.alerts.total }}</div>
                <div class="text-sm text-gray-500">{{ stats.alerts.critical }} críticas</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Reportes Disponibles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Reporte de Estado de Stock -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Estado de Stock</h3>
                  <p class="text-sm text-gray-500">Análisis completo del estado actual del inventario</p>
                </div>
              </div>
              <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Stock Normal:</span>
                  <span class="font-medium text-green-600">{{ calculateNormalStock() }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Stock Bajo:</span>
                  <span class="font-medium text-yellow-600">{{ stats.elements.low_stock }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Agotados:</span>
                  <span class="font-medium text-red-600">{{ stats.elements.out_of_stock }}</span>
                </div>
              </div>
              <div class="flex space-x-2">
                <Link
                  :href="route('inventory-reports.stock-status')"
                  class="flex-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center text-sm"
                >
                  Ver Reporte
                </Link>
                <a
                  :href="route('inventory-reports.elements-export')"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-3 rounded text-sm"
                  title="Exportar a CSV"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </a>
              </div>
            </div>
          </div>

          <!-- Reporte de Alertas -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Alertas de Stock</h3>
                  <p class="text-sm text-gray-500">Gestión y seguimiento de alertas críticas</p>
                </div>
              </div>
              <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Total Alertas:</span>
                  <span class="font-medium">{{ stats.alerts.total }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Críticas:</span>
                  <span class="font-medium text-red-600">{{ stats.alerts.critical }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Recientes (7 días):</span>
                  <span class="font-medium text-orange-600">{{ stats.alerts.recent }}</span>
                </div>
              </div>
              <div class="flex space-x-2">
                <Link
                  :href="route('stock-alerts.index')"
                  class="flex-1 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-center text-sm"
                >
                  Ver Alertas
                </Link>
                <a
                  :href="route('inventory-reports.low-stock-alerts')"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-3 rounded text-sm"
                  title="Exportar Alertas"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </a>
              </div>
            </div>
          </div>

          <!-- Reporte de Kits -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Análisis de Kits</h3>
                  <p class="text-sm text-gray-500">Estado de disponibilidad y costos de kits</p>
                </div>
              </div>
              <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Total Kits:</span>
                  <span class="font-medium">{{ stats.kits.total }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Disponibles:</span>
                  <span class="font-medium text-green-600">{{ stats.kits.available }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">No Disponibles:</span>
                  <span class="font-medium text-red-600">{{ stats.kits.total - stats.kits.available }}</span>
                </div>
              </div>
              <div class="flex space-x-2">
                <Link
                  :href="route('inventory-kits.index')"
                  class="flex-1 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-center text-sm"
                >
                  Ver Kits
                </Link>
                <a
                  :href="route('inventory-reports.kits-export')"
                  class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-3 rounded text-sm"
                  title="Exportar Kits"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </a>
              </div>
            </div>
          </div>

          <!-- Reporte de Proveedores -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Análisis de Proveedores</h3>
                  <p class="text-sm text-gray-500">Rendimiento y estadísticas de proveedores</p>
                </div>
              </div>
              <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Total Proveedores:</span>
                  <span class="font-medium">{{ stats.suppliers.total }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Activos:</span>
                  <span class="font-medium text-green-600">{{ stats.suppliers.active }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Países:</span>
                  <span class="font-medium text-blue-600">{{ stats.suppliers.countries }}</span>
                </div>
              </div>
              <div class="flex space-x-2">
                <Link
                  :href="route('suppliers.index')"
                  class="flex-1 bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center text-sm"
                >
                  Ver Proveedores
                </Link>
              </div>
            </div>
          </div>

          <!-- Reporte de Movimientos -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Movimientos de Inventario</h3>
                  <p class="text-sm text-gray-500">Historial y análisis de movimientos</p>
                </div>
              </div>
              <div class="space-y-2 mb-4">
                <div class="text-sm text-gray-600">
                  Seguimiento completo de entradas, salidas y ajustes de inventario
                </div>
              </div>
              <div class="flex space-x-2">
                <Link
                  :href="route('inventory-reports.stock-movements')"
                  class="flex-1 bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-center text-sm"
                >
                  Ver Movimientos
                </Link>
              </div>
            </div>
          </div>

          <!-- Configuración de Reportes -->
          <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center mb-4">
                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                  <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">Configuración</h3>
                  <p class="text-sm text-gray-500">Personalizar reportes y alertas</p>
                </div>
              </div>
              <div class="space-y-2 mb-4">
                <div class="text-sm text-gray-600">
                  Configurar parámetros de alertas, umbrales de stock y formatos de exportación
                </div>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="showConfigModal = true"
                  class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center text-sm"
                >
                  Configurar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="mt-8 bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Acciones Rápidas</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <a
                :href="route('inventory-reports.elements-export')"
                class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors duration-200"
              >
                <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                  <div class="font-medium text-blue-900">Exportar Elementos</div>
                  <div class="text-sm text-blue-700">CSV completo</div>
                </div>
              </a>

              <Link
                :href="route('elements.create')"
                class="flex items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors duration-200"
              >
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <div>
                  <div class="font-medium text-green-900">Nuevo Elemento</div>
                  <div class="text-sm text-green-700">Agregar al inventario</div>
                </div>
              </Link>

              <Link
                :href="route('stock-alerts.index')"
                class="flex items-center p-4 bg-red-50 hover:bg-red-100 rounded-lg transition-colors duration-200"
              >
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                  <div class="font-medium text-red-900">Ver Alertas</div>
                  <div class="text-sm text-red-700">{{ stats.alerts.total }} activas</div>
                </div>
              </Link>

              <Link
                :href="route('inventory-kits.create')"
                class="flex items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors duration-200"
              >
                <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <div>
                  <div class="font-medium text-purple-900">Nuevo Kit</div>
                  <div class="text-sm text-purple-700">Crear conjunto</div>
                </div>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats: Object
})

const showConfigModal = ref(false)

// Calcular stock normal
const calculateNormalStock = () => {
  return props.stats.elements.total - props.stats.elements.low_stock - props.stats.elements.out_of_stock
}
</script>
