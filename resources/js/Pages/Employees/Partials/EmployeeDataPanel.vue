<script setup>

import Card from '@/Components/Card.vue';
import CardText from '@/Components/CardText.vue';
import CardTitle from '@/Components/CardTitle.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    employee: Object
});

</script>

<template>
    <div class="flex flex-col items-start gap-1">
        <CardTitle>{{ employee.generalDirection }}</CardTitle>
        <CardTitle>{{ employee.direction }}</CardTitle>
        <CardTitle>{employee.subdirection}</CardTitle>
        <CardTitle>{employee.department}</CardTitle>
        
        <div class="w-full flex justify-around mt-2">
            <PrimaryButton>Horiario</PrimaryButton>
            <PrimaryButton>Asistencias</PrimaryButton>
            <PrimaryButton>Incidencias</PrimaryButton>
        </div>

        <div class="w-full flex justify-end gap-2 mt-2 pt-2 border-t dark:border-gray-500">
            <select class="w-32 border rounded">
                <option>2024</option>
                <option>2023</option>
                <option>2022</option>
                <option>2021</option>
            </select>
            <PrimaryButton>Descargar Kardex</PrimaryButton>
        </div>
    </div>
</template>