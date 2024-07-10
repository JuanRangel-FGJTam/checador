<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
    href: {
        type: String
    },
    title: {
        type: String
    }
});
</script>
<template>
    <Link 
        :href="href" 
        class="flex flex-col items-center justify-center border border-gray-100 p-4 rounded-lg shadow transform transition scale-90 duration-500 hover:scale-100 hover:bg-gray-50 dark:hover:bg-gray-800 dark:border-gray-500">
        <slot></slot>
        <h2 class="mt-2 title-font font-medium text-xl text-gray-700 dark:text-gray-300 w-[14rem] text-center">{{ title }}</h2>
    </Link>
</template>
