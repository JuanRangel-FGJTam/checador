<script setup>
defineProps({
	modelValue: {
        type: String
    }
});
defineEmits(['update:modelValue']);
</script>

<template>
	<input type="date" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"
	class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
</template>
