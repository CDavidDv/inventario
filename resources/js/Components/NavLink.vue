<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
    class: String,
});

const classes = computed(() => {
    const baseClasses = props.active
        ? 'px-3 py-2 text-white w-full hover:rounded-lg  inline-flex items-center text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-orange-700 transition duration-150 ease-in-out bg-gray-700 rounded-lg overflow-hidden'
        : 'px-3 py-2 text-white w-full hover:rounded-xl inline-flex items-center text-sm font-medium leading-5 hover:bg-slate-100/30 hover:text-white hover:text-gray-700 hover:border-orange-300 focus:outline-none focus:border-yellow-300 transition overflow-hidden duration-900 ease-in-out';
    
    const additionalClasses = props.class ? props.class : '';
    return `${baseClasses} ${additionalClasses}`.trim();
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
