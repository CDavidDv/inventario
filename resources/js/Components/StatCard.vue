<template>
    <div class="bg-white overflow-hidden shadow-md rounded-lg border border-gray-200">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div 
                        class="w-10 h-10 rounded-lg flex items-center justify-center"
                        :class="iconBgClass"
                    >
                        <svg 
                            class="w-6 h-6" 
                            :class="iconTextClass"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path 
                                v-if="icon === 'document-text'"
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                            />
                            <path 
                                v-else-if="icon === 'arrows-up-down'"
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"
                            />
                            <path 
                                v-else-if="icon === 'calendar'"
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                            />
                            <path 
                                v-else-if="icon === 'clock'"
                                stroke-linecap="round" 
                                stroke-linejoin="round" 
                                stroke-width="2" 
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                            <circle 
                                v-else
                                cx="12" 
                                cy="12" 
                                r="10"
                            />
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <div class="text-sm font-medium text-gray-600">
                        {{ title }}
                    </div>
                    <div class="text-2xl font-bold text-gray-900">
                        {{ value }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    icon: {
        type: String,
        default: 'document-text'
    },
    color: {
        type: String,
        default: 'blue'
    }
})

const iconBgClass = computed(() => {
    const colors = {
        'blue': 'bg-blue-100',
        'green': 'bg-green-100', 
        'purple': 'bg-purple-100',
        'orange': 'bg-orange-100',
        'red': 'bg-red-100',
        'yellow': 'bg-yellow-100'
    }
    return colors[props.color] || colors.blue
})

const iconTextClass = computed(() => {
    const colors = {
        'blue': 'text-blue-600',
        'green': 'text-green-600',
        'purple': 'text-purple-600', 
        'orange': 'text-orange-600',
        'red': 'text-red-600',
        'yellow': 'text-yellow-600'
    }
    return colors[props.color] || colors.blue
})
</script>