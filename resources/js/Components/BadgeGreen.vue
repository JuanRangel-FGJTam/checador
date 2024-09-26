<script setup>

defineProps({
    text: {
        type: String
    },
});
</script>

<template>
    <div class="text-xs uppercase rounded-md px-2 py-1 bg-green-200 text-green-600 w-fit mx-auto dark:bg-green-500 dark:text-green-100">
        <span v-if="text">{{ text }}</span>
        <span v-else><slot /></span>
    </div>
</template>