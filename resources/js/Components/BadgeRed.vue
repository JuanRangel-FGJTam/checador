<script setup>

defineProps({
    text: {
        type: String
    },
});
</script>

<template>
    <div class="text-xs uppercase rounded-xl px-2 py-1 bg-red-200 text-red-600 w-fit mx-auto dark:bg-red-500 dark:text-red-100">
        <span v-if="text">{{ text }}</span>
        <span v-else><slot /></span>
    </div>
</template>