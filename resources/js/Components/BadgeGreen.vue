<script setup>

defineProps({
    text: {
        type: String
    },
});
</script>

<template>
    <div class="text-xs rounded px-2 py-1 bg-green-100 text-green-700 w-fit mx-auto dark:bg-green-500 dark:text-green-100">
        <span v-if="text">{{ text }}</span>
        <span v-else><slot /></span>
    </div>
</template>