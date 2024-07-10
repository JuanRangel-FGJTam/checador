<script setup>
import { ref, onMounted } from 'vue';
const {isClosable, initCollapsed} = defineProps({
	shadow:{
		type: Boolean,
		default: true
	},
	isClosable: {
		type: Boolean,
		default: false
	},
	initCollapsed: {
		type: Boolean,
		default: true
	},
	whitOutBorder: {
		type: Boolean,
		default: false
	}
});

const hiddenContent = ref( initCollapsed );

function headerClick(sender){
	// Prevent close when the action button is clicked
	var parent = sender.target.parentNode;
	var parent2 = parent.parentNode;
	if(sender.target.tagName == "BUTTON" || parent.tagName == "BUTTON" || parent2.tagName =="BUTTON")
	{
		return;
	}

	if(isClosable){
		hiddenContent.value = !hiddenContent.value;
	}
}

</script>

<template>
	<div class="flex flex-col bg-white px-2 mb-4 rounded-md dark:bg-slate-6 dark:text-gray-200 dark:bg-gray-700 dark:border-gray-500" :class="[ shadow?'shadow':'', !whitOutBorder?'border':'' ]">
		
		<div v-if="$slots.header" class="flex items-center select-none p-2 border-b text-gray-600 font-medium sticky top-0 z-10 bg-white dark:bg-gray-700 dark:text-gray-200 dark:border-gray-500" :class="[hiddenContent && isClosable ? 'border-none':'border-b', !isClosable?'justify-between':'']" v-on:click="headerClick" >
			<div v-if="isClosable">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="transition duration-100 ease-in-out" :class="[hiddenContent?'':'rotate-[180deg]']" fill="currentColor">
					<path d="M12 14.95q-.2 0-.375-.062t-.325-.213l-4.6-4.6q-.275-.275-.275-.7t.275-.7q.275-.275.7-.275t.7.275l3.9 3.9l3.9-3.9q.275-.275.7-.275t.7.275q.275.275.275.7t-.275.7l-4.6 4.6q-.15.15-.325.213T12 14.95"/>
				</svg>
			</div>
			<slot name="header"></slot>
		</div>

		<div class="w-full h-full p-2 text-gray-600 dark:text-gray-100 transition-opacity duration-500 ease-out hover:ease-in" :class="[hiddenContent && isClosable?'opacity-0 hidden':'opacity-100']">
			<slot name="content"></slot>
		</div>

		<div v-if="$slots.footer" class="w-full p-2 min-h-[2rem]">
			<slot name="footer"></slot>
		</div>
		
	</div>
</template>
