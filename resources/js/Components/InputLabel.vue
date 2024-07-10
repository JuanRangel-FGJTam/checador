<script setup>
const props = defineProps({
    value: {
        type: String,
    },
    forValue: String
});
</script>

<template>
    <label class="block mb-1 text-gray-500 dark:text-gray-300" :for="forValue">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
