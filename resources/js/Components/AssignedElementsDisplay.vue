<template>
    <div v-if="assignedElements && assignedElements.length > 0" class="mt-2">
        <div class="text-xs text-gray-600 mb-1">Elementos asignados:</div>
        <div class="space-y-1">
            <div v-for="element in assignedElements" :key="element.id" 
                class="flex items-center justify-between text-xs bg-gray-50 px-2 py-1 rounded">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-700">{{ element.name }}</span>
                    <span class="text-gray-500">{{ element.categoryName }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-gray-600">{{ element.quantity }}</span>
                    <span v-if="element.unit" class="text-gray-500">({{ element.unit }})</span>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="mt-2 text-xs text-gray-500">
        Sin elementos asignados
    </div>
</template>

<script setup>
// Props
const props = defineProps({
    assignedElements: {
        type: Array,
        default: () => []
    }
})
</script>
