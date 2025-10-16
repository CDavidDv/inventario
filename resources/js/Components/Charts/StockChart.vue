<template>
  <div class="bg-white p-6 rounded-lg shadow-sm">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
      <div class="flex space-x-2">
        <select
          v-model="selectedPeriod"
          @change="updateData"
          class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="7">Últimos 7 días</option>
          <option value="30">Últimos 30 días</option>
          <option value="90">Últimos 90 días</option>
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
        type="line"
        :data="chartData"
        :options="chartOptions"
        :loading="loading"
      />
    </div>

    <!-- Estadísticas resumidas -->
    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
      <div class="text-center">
        <div class="text-gray-500">Total Movimientos</div>
        <div class="font-semibold text-blue-600">{{ stats.totalMovements }}</div>
      </div>
      <div class="text-center">
        <div class="text-gray-500">Entradas</div>
        <div class="font-semibold text-green-600">+{{ stats.totalIncoming }}</div>
      </div>
      <div class="text-center">
        <div class="text-gray-500">Salidas</div>
        <div class="font-semibold text-red-600">-{{ stats.totalOutgoing }}</div>
      </div>
      <div class="text-center">
        <div class="text-gray-500">Balance</div>
        <div class="font-semibold" :class="stats.balance >= 0 ? 'text-green-600' : 'text-red-600'">
          {{ stats.balance >= 0 ? '+' : '' }}{{ stats.balance }}
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
    default: 'Movimientos de Stock'
  },
  elementId: {
    type: Number,
    default: null
  }
})

const selectedPeriod = ref(30)
const loading = ref(false)
const movementData = ref([])

const chartData = computed(() => {
  if (!movementData.value.length) {
    return {
      labels: [],
      datasets: []
    }
  }

  return {
    labels: movementData.value.map(item => {
      const date = new Date(item.date)
      return date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' })
    }),
    datasets: [
      {
        label: 'Entradas',
        data: movementData.value.map(item => item.incoming),
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Salidas',
        data: movementData.value.map(item => Math.abs(item.outgoing)),
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Stock Acumulado',
        data: movementData.value.map(item => item.cumulative),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        yAxisID: 'y1'
      }
    ]
  }
})

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
    },
    tooltip: {
      mode: 'index',
      intersect: false,
      callbacks: {
        title: function(context) {
          const date = new Date(movementData.value[context[0].dataIndex].date)
          return date.toLocaleDateString('es-ES', { 
            weekday: 'long',
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
          })
        },
        label: function(context) {
          const value = context.parsed.y
          if (context.dataset.label === 'Salidas') {
            return `${context.dataset.label}: -${value}`
          }
          return `${context.dataset.label}: ${value}`
        }
      }
    },
  },
  scales: {
    x: {
      display: true,
      title: {
        display: true,
        text: 'Fecha'
      }
    },
    y: {
      type: 'linear',
      display: true,
      position: 'left',
      title: {
        display: true,
        text: 'Cantidad'
      },
      beginAtZero: true
    },
    y1: {
      type: 'linear',
      display: true,
      position: 'right',
      title: {
        display: true,
        text: 'Stock Acumulado'
      },
      grid: {
        drawOnChartArea: false,
      },
    }
  },
  interaction: {
    mode: 'nearest',
    axis: 'x',
    intersect: false,
  },
}))

const stats = computed(() => {
  if (!movementData.value.length) {
    return {
      totalMovements: 0,
      totalIncoming: 0,
      totalOutgoing: 0,
      balance: 0
    }
  }

  const totalIncoming = movementData.value.reduce((sum, item) => sum + item.incoming, 0)
  const totalOutgoing = movementData.value.reduce((sum, item) => sum + Math.abs(item.outgoing), 0)
  
  return {
    totalMovements: movementData.value.length,
    totalIncoming,
    totalOutgoing,
    balance: totalIncoming - totalOutgoing
  }
})

const fetchData = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      period: selectedPeriod.value,
      ...(props.elementId && { element_id: props.elementId })
    })
    
    const response = await fetch(`/api/inventory/stock-movements-chart?${params}`)
    const data = await response.json()
    
    movementData.value = data.movements || []
  } catch (error) {
    console.error('Error al cargar datos del gráfico:', error)
    movementData.value = []
  } finally {
    loading.value = false
  }
}

const updateData = () => {
  fetchData()
}

const refreshData = () => {
  fetchData()
}

onMounted(() => {
  fetchData()
})
</script>
