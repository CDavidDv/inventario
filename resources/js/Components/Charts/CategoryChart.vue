<template>
  <div class="bg-white p-6 rounded-lg shadow-sm">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
      <div class="flex space-x-2">
        <select
          v-model="chartType"
          @change="updateChartType"
          class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="doughnut">Dona</option>
          <option value="pie">Pastel</option>
          <option value="bar">Barras</option>
        </select>
        <button
          @click="refreshData"
          class="text-gray-400 hover:text-gray-600"
          :disabled="loading"
        >
          <svg class="w-5 h-5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
        </button>
      </div>
    </div>
    
    <div class="h-64">
      <BaseChart
        :type="chartType"
        :data="chartData"
        :options="chartOptions"
        :loading="loading"
      />
    </div>

    <!-- Lista de categorías con estadísticas -->
    <div class="mt-4 space-y-2">
      <div 
        v-for="(category, index) in categoryStats" 
        :key="category.name"
        class="flex items-center justify-between p-2 rounded hover:bg-gray-50"
      >
        <div class="flex items-center space-x-3">
          <div 
            class="w-4 h-4 rounded-full"
            :style="{ backgroundColor: getColor(index) }"
          ></div>
          <span class="font-medium text-gray-900">{{ category.name }}</span>
        </div>
        <div class="flex items-center space-x-4 text-sm text-gray-500">
          <span>{{ category.count }} elementos</span>
          <span>{{ category.totalStock }} unidades</span>
          <span class="font-medium" :class="getValueColor(category.totalValue)">
            ${{ formatNumber(category.totalValue) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import BaseChart from './BaseChart.vue'

const props = defineProps({
  title: {
    type: String,
    default: 'Distribución por Categorías'
  },
  metric: {
    type: String,
    default: 'count', // count, stock, value
    validator: value => ['count', 'stock', 'value'].includes(value)
  }
})

const chartType = ref('doughnut')
const loading = ref(false)
const categoryData = ref([])

// Colores predefinidos para las categorías
const colors = [
  '#3B82F6', // blue
  '#10B981', // emerald
  '#F59E0B', // amber
  '#EF4444', // red
  '#8B5CF6', // violet
  '#06B6D4', // cyan
  '#84CC16', // lime
  '#F97316', // orange
  '#EC4899', // pink
  '#6B7280', // gray
]

const getColor = (index) => {
  return colors[index % colors.length]
}

const chartData = computed(() => {
  if (!categoryData.value.length) {
    return {
      labels: [],
      datasets: []
    }
  }

  const labels = categoryData.value.map(item => item.name)
  let data = []
  
  switch (props.metric) {
    case 'count':
      data = categoryData.value.map(item => item.count)
      break
    case 'stock':
      data = categoryData.value.map(item => item.totalStock)
      break
    case 'value':
      data = categoryData.value.map(item => item.totalValue)
      break
  }

  return {
    labels,
    datasets: [{
      data,
      backgroundColor: categoryData.value.map((_, index) => getColor(index)),
      borderColor: categoryData.value.map((_, index) => getColor(index)),
      borderWidth: chartType.value === 'bar' ? 0 : 2,
      hoverBorderWidth: 3
    }]
  }
})

const chartOptions = computed(() => {
  const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: chartType.value !== 'bar',
        position: 'right',
        labels: {
          usePointStyle: true,
          padding: 20
        }
      },
      tooltip: {
        callbacks: {
          label: function(context) {
            const category = categoryData.value[context.dataIndex]
            const value = context.parsed || context.parsed.y
            
            let label = context.label + ': '
            
            switch (props.metric) {
              case 'count':
                label += `${value} elementos`
                break
              case 'stock':
                label += `${value} unidades`
                break
              case 'value':
                label += `$${formatNumber(value)}`
                break
            }
            
            const total = context.dataset.data.reduce((sum, val) => sum + val, 0)
            const percentage = ((value / total) * 100).toFixed(1)
            label += ` (${percentage}%)`
            
            return label
          }
        }
      }
    }
  }

  if (chartType.value === 'bar') {
    baseOptions.scales = {
      x: {
        display: true,
        title: {
          display: true,
          text: 'Categorías'
        }
      },
      y: {
        display: true,
        beginAtZero: true,
        title: {
          display: true,
          text: getYAxisLabel()
        }
      }
    }
  }

  return baseOptions
})

const categoryStats = computed(() => {
  return categoryData.value.sort((a, b) => {
    switch (props.metric) {
      case 'count':
        return b.count - a.count
      case 'stock':
        return b.totalStock - a.totalStock
      case 'value':
        return b.totalValue - a.totalValue
      default:
        return 0
    }
  })
})

const getYAxisLabel = () => {
  switch (props.metric) {
    case 'count':
      return 'Número de Elementos'
    case 'stock':
      return 'Stock Total'
    case 'value':
      return 'Valor Total ($)'
    default:
      return 'Cantidad'
  }
}

const getValueColor = (value) => {
  if (value > 10000) return 'text-green-600'
  if (value > 5000) return 'text-blue-600'
  if (value > 1000) return 'text-yellow-600'
  return 'text-gray-600'
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('es-ES', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(num)
}

const fetchData = async () => {
  loading.value = true
  try {
    const response = await fetch(`/api/inventory/category-stats?metric=${props.metric}`)
    const data = await response.json()
    
    categoryData.value = data.categories || []
  } catch (error) {
    console.error('Error al cargar datos de categorías:', error)
    categoryData.value = []
  } finally {
    loading.value = false
  }
}

const updateChartType = () => {
  // El gráfico se actualiza automáticamente por el computed
}

const refreshData = () => {
  fetchData()
}

onMounted(() => {
  fetchData()
})
</script>
