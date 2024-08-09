<script setup>
    import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
</script>

<template>
    <div class="animate-pulse flex flex-col items-center space-y-4 py-6">
        <div class="flex-1 ">
            <div class="text-lg text-gray-500 uppercase">Cargando datos</div>
        </div>
        <AnimateSpin class="w-6 h-6"/>
    </div>
</template>