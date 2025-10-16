<template>
    <span 
        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
        :class="badgeClasses"
    >
        <svg 
            v-if="dot" 
            class="w-1.5 h-1.5 mr-1.5 fill-current"
            viewBox="0 0 6 6"
        >
            <circle cx="3" cy="3" r="3"/>
        </svg>
        {{ text }}
    </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    text: {
        type: String,
        required: true
    },
    variant: {
        type: String,
        default: 'secondary',
        validator: (value) => {
            return ['primary', 'secondary', 'success', 'error', 'warning'].includes(value)
        }
    },
    dot: {
        type: Boolean,
        default: false
    }
})

const badgeClasses = computed(() => {
    const variants = {
        'primary': 'bg-blue-100 text-blue-800',
        'secondary': 'bg-gray-100 text-gray-800', 
        'success': 'bg-green-100 text-green-800',
        'error': 'bg-red-100 text-red-800',
        'warning': 'bg-yellow-100 text-yellow-800'
    }
    return variants[props.variant] || variants.secondary
})
</script>