<script setup>
import SearchIcon from '@/Components/Icons/SearchIcon.vue';
defineProps({
	placeHolder: String
});
const emit = defineEmits(['search']);
function onChanged(e) {
	emit('search', e.target.value);
}
</script>

<template>
	<div class="relative mt-1 border bg-gray-100 flex px-2 rounded-md text-gray-600 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-600" >
		<SearchIcon class="w-3" />
		<input type="search" class="remove-focus-outline w-full border-transparent bg-transparent p-1 focus:outline-0" :placeholder="placeHolder" v-on:change="onChanged">
	</div>
</template>