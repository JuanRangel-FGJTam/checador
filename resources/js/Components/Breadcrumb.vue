<script setup>

import { Link } from '@inertiajs/vue3';

const props = defineProps({
    breadcrumbs: {
        type: Array,
        required: true
    }
});

</script>

<template>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li v-for="(crumb, index) in breadcrumbs" :key="index" class="inline-flex items-center">

                <Link v-if="index !== breadcrumbs.length - 1" :href="crumb.href" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <span>{{ crumb.name }}</span>                    
                </Link>
                <span v-else  class="text-sm font-medium text-gray-500 dark:text-gray-400" >
                    {{ crumb.name }}
                </span>

                <svg v-if="index !== breadcrumbs.length - 1" class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L15.586 10 10.293 4.707a1 1 0 010-1.414z" />
                </svg>
            </li>
        </ol>
    </nav>
</template>