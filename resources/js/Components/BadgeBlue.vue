<script setup>

defineProps({
    text: {
        type: String
    },
});
</script>

<template>
    <div class="text-xs rounded px-2 py-1 bg-blue-200 text-blue-600 w-fit dark:bg-blue-500 dark:text-blue-100">
        <span v-if="text">{{ text }}</span>
        <span v-else><slot /></span>
    </div>
</template>