<template>
  <AppLayout :title="`${item.name} - Detalles`">
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link :href="route('inventario.index')" 
                class="text-gray-500 hover:text-gray-700 transition-colors">
            <ArrowLeft class="w-6 h-6" />
          </Link>
          <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
              {{ item.name }}
            </h2>
            <p class="text-sm text-gray-600">{{ getItemTypeLabel(item.type) }} - {{ item.category?.name || 'Sin categoría' }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span :class="getItemStatusClass(item)" 
                class="px-3 py-1 rounded-full text-sm font-medium">
            {{ item.active ? 'Activo' : 'Inactivo' }}
          </span>
          <span :class="getStockStatusClass(item)" 
                class="px-3 py-1 rounded-full text-sm font-medium">
            {{ getStockStatusText(item) }}
          </span>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Estadísticas Generales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white overflow-hidden shadow-xl rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Package class="w-8 h-8 text-blue-600" />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-500">Stock Actual</div>
                  <div class="text-2xl font-bold text-gray-900">{{ parseFloat(item.current_stock).toFixed(2) }} {{ item.unit }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <TrendingUp class="w-8 h-8 text-green-600" />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-500">Total Entradas</div>
                  <div class="text-2xl font-bold text-green-700">+{{ stats.total_entries || 0 }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <TrendingDown class="w-8 h-8 text-red-600" />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-500">Total Salidas</div>
                  <div class="text-2xl font-bold text-red-700">-{{ getQuantityDisplay( movement.type, stats.total_exits)  }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-xl rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <Activity class="w-8 h-8 text-purple-600" />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-500">Total Movimientos</div>
                  <div class="text-2xl font-bold text-purple-700">{{ stats.total_movements || 0 }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Información Detallada -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Información del Item -->
          <div class="bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Información General</h3>
            </div>
            <div class="p-6 space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nombre</label>
                <p class="mt-1 text-sm text-gray-900">{{ item.name }}</p>
              </div>
              
              <div v-if="item.description">
                <label class="block text-sm font-medium text-gray-700">Descripción</label>
                <p class="mt-1 text-sm text-gray-900">{{ item.description }}</p>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Unidad</label>
                  <p class="mt-1 text-sm text-gray-900">{{ item.unit }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Tipo</label>
                  <p class="mt-1 text-sm text-gray-900">{{ getItemTypeLabel(item.type) }}</p>
                </div>
              </div>
              
              <div class="grid grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Stock Actual</label>
                  <p class="mt-1 text-sm font-bold" :class="getStockColor(item)">
                    {{ item.current_stock }}
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Mínimo</label>
                  <p class="mt-1 text-sm text-gray-900">{{ item.min_stock }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Máximo</label>
                  <p class="mt-1 text-sm text-gray-900">{{ item.max_stock }}</p>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Costo de Compra</label>
                  <p class="mt-1 text-sm text-gray-900">${{ item.purchase_cost || '0.00' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Precio de Venta</label>
                  <p class="mt-1 text-sm text-gray-900">${{ item.sale_price || '0.00' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Elementos Asignados (para kits y componentes) -->
          <div v-if="assignedElements && assignedElements.length > 0" class="bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Elementos Asignados</h3>
            </div>
            <div class="p-6">
              <div class="space-y-3">
                <div v-for="element in assignedElements" :key="element.id" 
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <h4 class="text-sm font-medium text-gray-900">{{ element.name }}</h4>
                      <span class="text-xs text-gray-500">({{ element.type }})</span>
                    </div>
                    <p class="text-xs text-gray-500">{{ element.categoryName }}</p>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      {{ element.quantity }} {{ element.unit }}
                    </div>
                    <div class="text-xs text-gray-500">
                      Stock: {{ element.current_stock }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Usado en (para elementos) -->
          <div v-if="usedInItems && usedInItems.length > 0" class="bg-white shadow-xl rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Usado en</h3>
            </div>
            <div class="p-6">
              <div class="space-y-3">
                <div v-for="parentItem in usedInItems" :key="parentItem.id" 
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <h4 class="text-sm font-medium text-gray-900">{{ parentItem.name }}</h4>
                      <span class="text-xs text-gray-500">({{ parentItem.type }})</span>
                    </div>
                    <p class="text-xs text-gray-500">{{ parentItem.categoryName }}</p>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      {{ parentItem.quantity }} {{ item.unit }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Historial de Movimientos -->
        <div class="bg-white shadow-xl rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Historial de Movimientos</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Fecha
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cantidad
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Usuario
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Observaciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="movement in movements.data" :key="movement.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(movement.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getMovementTypeClass(movement.type)" 
                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ getMovementTypeText(movement.type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm" :class="getQuantityColor(movement.type, movement.quantity)">
                    {{ getQuantityDisplay(movement.type, movement.quantity) }} {{ item.unit }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ movement.user?.name || 'Sistema' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ movement.notes || '-' }}
                  </td>
                </tr>
                <tr v-if="movements.data.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    No hay movimientos registrados para este item
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Paginación -->
          <div v-if="movements.links && movements.links.length > 3" class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Mostrando {{ movements.from }} a {{ movements.to }} de {{ movements.total }} resultados
              </div>
              <div class="flex space-x-1">
                <Link v-for="link in movements.links" :key="link.label" 
                      :href="link.url" :class="[
                        'px-3 py-2 text-sm rounded-md',
                        link.active 
                          ? 'bg-blue-500 text-white' 
                          : link.url 
                            ? 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50' 
                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      ]" v-html="link.label" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { 
  ArrowLeft, 
  Package, 
  TrendingUp, 
  TrendingDown, 
  Activity 
} from 'lucide-vue-next'

const props = defineProps({
  item: Object,
  movements: Object,
  assignedElements: Array,
  usedInItems: Array,
  stats: Object
})

// Helper functions
const getItemTypeLabel = (type) => {
  const labels = {
    element: 'Elemento',
    component: 'Componente',
    kit: 'Kit'
  }
  return labels[type] || type
}

const getItemStatusClass = (item) => {
  return item.active
    ? 'bg-green-100 text-green-800'
    : 'bg-red-100 text-red-800'
}

const getStockStatusClass = (item) => {
  const current = parseFloat(item.current_stock)
  const min = parseFloat(item.min_stock)
  const max = parseFloat(item.max_stock)

  if (current < min) {
    return 'bg-red-100 text-red-800'
  } else if (current === min) {
    return 'bg-yellow-100 text-yellow-800'
  } else if (current > max) {
    return 'bg-blue-100 text-blue-800'
  } else {
    return 'bg-green-100 text-green-800'
  }
}

const getStockStatusText = (item) => {
  const current = parseFloat(item.current_stock)
  const min = parseFloat(item.min_stock)
  const max = parseFloat(item.max_stock)

  if (current < min) {
    return 'Bajo Stock'
  } else if (current === min) {
    return 'En el Mínimo'
  } else if (current > max) {
    return 'Sobre Stock'
  } else {
    return 'Stock Normal'
  }
}

const getStockColor = (item) => {
  const current = parseFloat(item.current_stock)
  const min = parseFloat(item.min_stock)
  const max = parseFloat(item.max_stock)

  if (current < min) {
    return 'text-red-600'
  } else if (current === min) {
    return 'text-yellow-600'
  } else if (current > max) {
    return 'text-blue-600'
  } else {
    return 'text-green-600'
  }
}

const getMovementTypeClass = (type) => {
  const classes = {
    entry: 'bg-green-100 text-green-800',
    exit: 'bg-red-100 text-red-800',
    out: 'bg-red-100 text-red-800',
    adjustment: 'bg-yellow-100 text-yellow-800',
    production: 'bg-blue-100 text-blue-800',
    damage: 'bg-red-100 text-red-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getMovementTypeText = (type) => {
  const texts = {
    entry: 'Entrada',
    exit: 'Salida',
    out: 'Salida',
    adjustment: 'Ajuste',
    production: 'Producción',
    damage: 'Daño/Pérdida'
  }
  return texts[type] || type
}

const getQuantityColor = (type, quantity) => {
  if (type === 'entry' || (type === 'adjustment' && quantity > 0)) {
    return 'text-green-600 font-medium'
  } else if (type === 'exit' || type === 'damage' || (type === 'adjustment' && quantity < 0)) {
    return 'text-red-600 font-medium'
  }
  return 'text-gray-900'
}

const getQuantityDisplay = (type, quantity) => {
  const formatNumber = (num) => parseFloat(num).toFixed(2)
  
  if (type === 'entry' || (type === 'adjustment' && quantity > 0)) {
    return `+${formatNumber(quantity)}`
  } else if (type === 'exit' || type === 'damage') {
    return `-${formatNumber(Math.abs(quantity))}`
  }
  return formatNumber(quantity)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

