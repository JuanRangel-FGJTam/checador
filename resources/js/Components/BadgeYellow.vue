<script setup>

defineProps({
    text: {
        type: String
    },
});
</script>

<template>
    <div class="text-xs rounded px-2 py-1 bg-amber-100 text-amber-700 w-fit mx-auto dark:bg-amber-500 dark:text-amber-100">
        <span v-if="text">{{ text }}</span>
        <span v-else><slot /></span>
    </div>
</template>