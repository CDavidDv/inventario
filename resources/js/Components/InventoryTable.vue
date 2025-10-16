<template>
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Contador de items -->
        <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Mostrando {{ filteredCount }} de {{ totalItems }} items
                </div>
                <div v-if="sortField" class="text-sm text-gray-500">
                    Ordenado por: {{ getSortFieldName(sortField) }} 
                    <span class="font-medium">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th @click="$emit('sort', 'name')"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-1">
                                <span>Item</span>
                                <component :is="getSortIcon('name')" class="w-4 h-4" />
                            </div>
                        </th>
                        <th @click="$emit('sort', 'current_stock')"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-1">
                                <span>Cantidad</span>
                                <component :is="getSortIcon('current_stock')" class="w-4 h-4" />
                            </div>
                        </th>
                        <th @click="$emit('sort', 'min_stock')"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-1">
                                <span>Límites</span>
                                <component :is="getSortIcon('min_stock')" class="w-4 h-4" />
                            </div>
                        </th>
                        <th @click="$emit('sort', 'categoryName')"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-1">
                                <span>Estado</span>
                                <component :is="getSortIcon('categoryName')" class="w-4 h-4" />
                            </div>
                        </th>
                        <th v-if="typeItem !== 'element'" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Elementos Asignados
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Loading Skeleton -->
                    <template v-if="isLoading">
                        <tr v-for="n in 5" :key="'skeleton-' + n">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-10 h-10 bg-gray-200 rounded-lg"></div>
                                    <div class="ml-4 space-y-2">
                                        <div class="h-4 bg-gray-200 rounded w-32"></div>
                                        <div class="h-3 bg-gray-200 rounded w-24"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div class="h-4 bg-gray-200 rounded w-16"></div>
                                    <div class="h-3 bg-gray-200 rounded w-12"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-2">
                                    <div class="h-3 bg-gray-200 rounded w-28"></div>
                                    <div class="w-full bg-gray-200 rounded-full h-2"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="h-4 bg-gray-200 rounded w-20"></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="h-3 bg-gray-200 rounded w-16"></div>
                                    <div class="h-3 bg-gray-200 rounded w-12"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-gray-200 rounded"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded"></div>
                                    <div class="w-8 h-8 bg-gray-200 rounded"></div>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <!-- Datos reales -->
                    <template v-else>
                        <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <Package class="w-5 h-5 text-blue-600" />
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                                        <div class="text-sm text-gray-500">{{ item.categoryName }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ item.current_stock }}</div>
                                <div class="text-xs text-gray-500">{{ item.unit }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs text-gray-500">
                                    Min: {{ item.min_stock }} | Max: {{ item.max_stock }}
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="h-2 rounded-full transition-all duration-300"
                                        :class="getQuantityBarColor(item)"
                                        :style="{ width: getQuantityPercentage(item) + '%' }"></div>
                                </div>
                            </td>                            
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                                                         <!-- Icono del estado -->
                                     <div class="flex-shrink-0">
                                         <div v-if="getStatusText(item) === 'Bajo Stock'" 
                                              class="w-3 h-3 bg-red-500 rounded-full"></div>
                                         <div v-else-if="getStatusText(item) === 'En el Mínimo'" 
                                              class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                         <div v-else-if="getStatusText(item) === 'Normal'" 
                                              class="w-3 h-3 bg-green-500 rounded-full"></div>
                                         <div v-else-if="getStatusText(item) === 'Sobre Stock'" 
                                              class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                         <div v-else class="w-3 h-3 bg-gray-500 rounded-full"></div>
                                     </div>
                                    
                                    <!-- Texto del estado -->
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="getStatusClass(item)">
                                        {{ getStatusText(item) }}
                                    </span>
                                </div>
                            </td>
                            <td v-if="typeItem !== 'element'" class="px-6 py-4">
                                <div v-if="item.assignedElements && item.assignedElements.length > 0" class="text-xs">
                                    <div class="text-gray-600 mb-1">{{ item.assignedElements.length }} elemento(s)</div>
                                    <div class="space-y-1">
                                        <div v-for="element in item.assignedElements.slice(0, 2)" :key="element.id" 
                                            class="flex items-center justify-between bg-gray-50 px-2 py-1 rounded">
                                            <span class="font-medium text-gray-700 truncate">{{ element.name }}</span>
                                            <span class="text-gray-600 ml-2">{{ element.quantity }}</span>
                                        </div>
                                        <div v-if="item.assignedElements.length > 2" class="text-gray-500 text-center">
                                            +{{ item.assignedElements.length - 2 }} más
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-xs text-gray-500">
                                    Sin elementos
                                </div>
                            </td>
                                                         <td class="px-6 py-4">
                                 <div class="flex items-center gap-2">
                                     <!-- Botón Activo/Inactivo -->
                                     <button @click="$emit('toggle-active', item)"
                                         :class="item.active ? 'text-green-600 hover:text-green-800' : 'text-gray-400 hover:text-gray-600'"
                                         class="p-1 rounded transition-colors"
                                         :title="item.active ? 'Desactivar' : 'Activar'">
                                         <div v-if="item.active" class="w-4 h-4 bg-green-500 rounded-full"></div>
                                         <div v-else class="w-4 h-4 bg-gray-300 rounded-full"></div>
                                     </button>

                                     <button @click="$emit('view', item)"
                                         class="text-gray-600 hover:text-gray-800 p-1 rounded transition-colors"
                                         title="Ver detalles">
                                         <Eye class="w-4 h-4" />
                                     </button>
                                     
                                     <button @click="$emit('edit', item)"
                                         class="text-blue-600 hover:text-blue-800 p-1 rounded transition-colors"
                                         title="Editar">
                                         <Edit class="w-4 h-4" />
                                     </button>
                                     
                                     <button @click="$emit('delete', item)"
                                         class="text-red-600 hover:text-red-800 p-1 rounded transition-colors"
                                         title="Eliminar">
                                         <Trash2 class="w-4 h-4" />
                                     </button>
                                 </div>
                             </td>
                        </tr>
                        
                        <!-- Estado vacío -->
                        <tr v-if="items.length === 0">
                            <td :colspan="type === 'element' ? 5 : 6" class="px-6 py-12 text-center text-gray-500">
                                <Package class="w-12 h-12 mx-auto text-gray-300 mb-4" />
                                <div class="text-lg font-medium mb-2">No hay items disponibles</div>
                                <div class="text-sm">Agrega algún item para comenzar.</div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
  </template>
  
  <script setup>
import { computed } from 'vue'
import { Package, Edit, Trash2, Link, ChevronUp, ChevronDown, ChevronsUpDown, Eye } from 'lucide-vue-next'
import { type } from 'jquery'

// Props
const props = defineProps({
    items: {
      type: Array,
      required: true
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    totalItems: {
        type: Number,
        required: true
    },
    filteredCount: {
        type: Number,
        required: true
    },
    sortField: {
        type: String,
        default: ''
    },
    sortDirection: {
      type: String,
        default: 'asc'
    },
    typeItem: {
        type: String,
        default: 'all'  // 'all', 'elements', 'products', etc.
    }
})

// Emits
const emit = defineEmits(['sort', 'edit', 'delete', 'assign', 'toggle-active', 'view'])

// Computed
const getSortIcon = (field) => {
    if (props.sortField !== field) {
        return ChevronsUpDown
    }
    return props.sortDirection === 'asc' ? ChevronUp : ChevronDown
}

const getSortFieldName = (field) => {
    const fieldNames = {
        'name': 'Nombre',
        'current_stock': 'Cantidad',
        'min_stock': 'Límites',
        'categoryName': 'Categoría',
    }
    return fieldNames[field] || field
}

// Métodos
const getQuantityPercentage = (item) => {
    const range = parseFloat(item.max_stock)
    const current = Math.max(0, Math.min(parseFloat(item.current_stock), range))
    return range > 0 ? (current / range) * 100 : 0
}

const getQuantityBarColor = (item) => {
    // Convertir a números para comparaciones correctas
    const current = parseFloat(item.current_stock)
    const min = parseFloat(item.min_stock)
    const max = parseFloat(item.max_stock)
    
    if (current < min) {
        return 'bg-red-500'  // Bajo Stock
    } else if (current === min) {
        return 'bg-yellow-500'  // En el Mínimo
    } else if (current > max) {
        return 'bg-blue-500'  // Sobre Stock
    } else {
        // min < current <= max
        return 'bg-green-500'  // Normal
    }
}

const getStatusClass = (item) => {
    // Convertir a números para comparaciones correctas
    const current = parseFloat(item.current_stock)
    const min = parseFloat(item.min_stock)
    const max = parseFloat(item.max_stock)
    
    if (current < min) {
        return 'bg-red-100 text-red-800'  // Bajo Stock
    } else if (current === min) {
        return 'bg-yellow-100 text-yellow-800'  // En el Mínimo
    } else if (current > max) {
        return 'bg-blue-100 text-blue-800'  // Sobre Stock
    } else {
        // min < current <= max
        return 'bg-green-100 text-green-800'  // Normal
    }
}

const getStatusText = (item) => {   
    // Convertir a números para comparaciones correctas
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
        // min < current <= max
        return 'Normal'
    }
}

const getItemName = (itemId) => {
    // Esta función debería recibir la lista completa de items para buscar el nombre
    // Por ahora retornamos un placeholder
    return `Item ${itemId}`
}
  </script>