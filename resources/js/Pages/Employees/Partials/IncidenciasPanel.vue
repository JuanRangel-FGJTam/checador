<script setup>

const props = defineProps({
    employee: Object
});

</script>

<template>
    <div class="flex flex-col">
        <h2 class="text-lg font-bold">Incidencias</h2>
        <p class="text-gray-500">Seleccione un día para ver las incidencias</p>

        <div class="border-t mt-2 pb-2"/>

        <div class="text-emerald-600 mx-auto py-2">No hay incidencias del día viernes, 15 de agosto de 2024.</div>
    </div>
</template>