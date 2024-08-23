<script setup>
import CalendarIcon from '@/Components/Icons/CalendarIcon.vue';
import BadgeBlue from "@/Components/BadgeBlue.vue";
import BadgeRed from "@/Components/BadgeRed.vue";
import BadgeGreen from "@/Components/BadgeGreen.vue";

import { computed } from 'vue';

const props = defineProps({
	title: String,
	subtitle: String,
	status_text: String,
	status_color: {
		type: String,
		default: 'blue'
	},
	description: {
		type: String,
		default: 'Sin descripcion'
	},
	date: String
});


const text_color = computed(()=>{
	return `text-${props.status_color}-800`;

})
const background_color = computed(()=>{
	return `bg-${props.status_color}-100`;
})


</script>

<template>
	<li class="mb-5 ml-6 py-1 px-2 rounded cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800">

		<span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900" >
			<CalendarIcon class="w-3 h-3" />
		</span>

		<h3 class="flex items-center mb-0 text-md font-semibold text-gray-700 dark:text-white">
			<span>{{ title }}</span>
			<BadgeGreen v-if="status_text == 'acreditado'" class="ml-2">
				{{ status_text }}
			</BadgeGreen>
			<BadgeRed v-else-if="status_text == 'no acreditado'" class="ml-2">
				{{ status_text }}
			</BadgeRed>
			<BadgeBlue v-else class="ml-2">
				{{ status_text }}
			</BadgeBlue>
		</h3>

		<div v-if="subtitle" class="-translate-y-1"> {{ subtitle }}</div>

		<time class="block mt-0 mb-1 text-sm font-normal leading-none text-gray-500 dark:text-gray-500 uppercase"> {{ date }}</time>
		
		<p class="line-clamp-2 mb-4 text-base font-normal text-gray-500 dark:text-gray-400">
			<div class="whitespace-pre-line" v-html="description"></div>
		</p>
	</li>
</template>
