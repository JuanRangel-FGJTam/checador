<script setup>

</script>

<template>
	<li class="mb-3 ml-6 p-1">
		<span class="absolute flex items-center justify-center bg-blue-100 rounded-full -left-3 ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900" >
			<slot name="icon"></slot>
		</span>
		
		<h3 class="flex items-center mb-1 text-gray-700 dark:text-gray-200">
			<slot name="content"> </slot>
		</h3>
	</li>
</template>
