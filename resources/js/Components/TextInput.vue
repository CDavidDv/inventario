<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: [String, Number],
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        class="px-3 py-2 border border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm block w-full"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
