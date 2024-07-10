<script setup>
defineProps({
	modelValue: {
        type: String
    },
	padding_class: "p-2"
});
defineEmits(['update:modelValue']);
</script>

<template>
	<input type="text" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"
	class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" :class="[padding_class]" >
</template>
	