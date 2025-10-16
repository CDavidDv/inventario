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

    <!-- Resumen de alertas por tipo -->
    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
      <div 
        v-for="alertType in alertTypes" 
        :key="alertType.type"
        class="text-center p-3 rounded-lg"
        :class="alertType.bgClass"
      >
        <div class="flex items-center justify-center mb-2">
          <div 
            class="w-8 h-8 rounded-full flex items-center justify-center"
            :class="alertType.iconBgClass"
          >
            <svg class="w-4 h-4" :class="alertType.iconClass" fill="currentColor" viewBox="0 0 20 20">
              <path v-if="alertType.type === 'Agotado'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
              <path v-else-if="alertType.type === 'Crítico'" fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
              <path v-else-if="alertType.type === 'Mínimo'" fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
              <path v-else fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold" :class="alertType.textClass">
          {{ alertType.count }}
        </div>
        <div class="text-sm font-medium" :class="alertType.labelClass">
          {{ alertType.type }}
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
    default: 'Evolución de Alertas de Stock'
  }
})

const selectedPeriod = ref(30)
const loading = ref(false)
const alertsData = ref([])

const chartData = computed(() => {
  if (!alertsData.value.length) {
    return {
      labels: [],
      datasets: []
    }
  }

  return {
    labels: alertsData.value.map(item => {
      const date = new Date(item.date)
      return date.toLocaleDateString('es-ES', { month: 'short', day: 'numeric' })
    }),
    datasets: [
      {
        label: 'Agotado',
        data: alertsData.value.map(item => item.agotado || 0),
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Crítico',
        data: alertsData.value.map(item => item.critico || 0),
        borderColor: 'rgb(245, 158, 11)',
        backgroundColor: 'rgba(245, 158, 11, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Mínimo',
        data: alertsData.value.map(item => item.minimo || 0),
        borderColor: 'rgb(234, 179, 8)',
        backgroundColor: 'rgba(234, 179, 8, 0.1)',
        tension: 0.4,
        fill: true
      },
      {
        label: 'Máximo',
        data: alertsData.value.map(item => item.maximo || 0),
        borderColor: 'rgb(168, 85, 247)',
        backgroundColor: 'rgba(168, 85, 247, 0.1)',
        tension: 0.4,
        fill: true
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
          const date = new Date(alertsData.value[context[0].dataIndex].date)
          return date.toLocaleDateString('es-ES', { 
            weekday: 'long',
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
          })
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
      display: true,
      beginAtZero: true,
      title: {
        display: true,
        text: 'Número de Alertas'
      },
      ticks: {
        stepSize: 1
      }
    }
  },
  interaction: {
    mode: 'nearest',
    axis: 'x',
    intersect: false,
  },
}))

const alertTypes = computed(() => {
  if (!alertsData.value.length) {
    return [
      { type: 'Agotado', count: 0, bgClass: 'bg-red-50', iconBgClass: 'bg-red-100', iconClass: 'text-red-600', textClass: 'text-red-600', labelClass: 'text-red-700' },
      { type: 'Crítico', count: 0, bgClass: 'bg-orange-50', iconBgClass: 'bg-orange-100', iconClass: 'text-orange-600', textClass: 'text-orange-600', labelClass: 'text-orange-700' },
      { type: 'Mínimo', count: 0, bgClass: 'bg-yellow-50', iconBgClass: 'bg-yellow-100', iconClass: 'text-yellow-600', textClass: 'text-yellow-600', labelClass: 'text-yellow-700' },
      { type: 'Máximo', count: 0, bgClass: 'bg-purple-50', iconBgClass: 'bg-purple-100', iconClass: 'text-purple-600', textClass: 'text-purple-600', labelClass: 'text-purple-700' }
    ]
  }

  const latestData = alertsData.value[alertsData.value.length - 1] || {}
  
  return [
    { 
      type: 'Agotado', 
      count: latestData.agotado || 0, 
      bgClass: 'bg-red-50', 
      iconBgClass: 'bg-red-100', 
      iconClass: 'text-red-600', 
      textClass: 'text-red-600', 
      labelClass: 'text-red-700' 
    },
    { 
      type: 'Crítico', 
      count: latestData.critico || 0, 
      bgClass: 'bg-orange-50', 
      iconBgClass: 'bg-orange-100', 
      iconClass: 'text-orange-600', 
      textClass: 'text-orange-600', 
      labelClass: 'text-orange-700' 
    },
    { 
      type: 'Mínimo', 
      count: latestData.minimo || 0, 
      bgClass: 'bg-yellow-50', 
      iconBgClass: 'bg-yellow-100', 
      iconClass: 'text-yellow-600', 
      textClass: 'text-yellow-600', 
      labelClass: 'text-yellow-700' 
    },
    { 
      type: 'Máximo', 
      count: latestData.maximo || 0, 
      bgClass: 'bg-purple-50', 
      iconBgClass: 'bg-purple-100', 
      iconClass: 'text-purple-600', 
      textClass: 'text-purple-600', 
      labelClass: 'text-purple-700' 
    }
  ]
})

const fetchData = async () => {
  loading.value = true
  try {
    const response = await fetch(`/api/inventory/alerts-trend?period=${selectedPeriod.value}`)
    const data = await response.json()
    
    alertsData.value = data.alerts || []
  } catch (error) {
    console.error('Error al cargar datos de alertas:', error)
    alertsData.value = []
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
