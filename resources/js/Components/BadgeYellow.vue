<script setup>

defineProps({
    text: {
        type: String
    },
});
</script>

<template>
    <div class="text-xs uppercase rounded-xl px-2 py-1 bg-amber-200 text-amber-600 w-fit mx-auto dark:bg-amber-500 dark:text-amber-100">
        <span v-if="text">{{ text }}</span>
        <span v-else><slot /></span>
    </div>
</template>