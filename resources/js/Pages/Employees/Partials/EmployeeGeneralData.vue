<script setup>

import Card from '@/Components/Card.vue';
import CardText from '@/Components/CardText.vue';
import CardTitle from '@/Components/CardTitle.vue';
import BadgeBlue from '@/Components/BadgeBlue.vue';
import BadgeGreen from '@/Components/BadgeGreen.vue';
import BadgeYellow from '@/Components/BadgeYellow.vue';

const props = defineProps({
    employee: Object
});

</script>

<template>
    <div class="grid grid-cols-3">
        <div class="flex items-center justify-start">
            <img class="mr-1 w-48 h-48 aspect-auto rounded-lg border bg-slate-400 text-center" alt="Foto empleado" :src="employee.photo"/>
        </div>

        <div class=" col-span-2 flex flex-col items-start gap-1">

            <h1 class="font-bold text-lg uppercase">{{ employee.name }}</h1>
            
            <div class="flex gap-2">
                <BadgeYellow>{estatus}</BadgeYellow>
                <BadgeGreen v-if="employee.checa == 1">Checa</BadgeGreen>
                <BadgeBlue v-else>No Checa</BadgeBlue>
            </div>
            
            <div class="flex gap-2 justify-end">
                <CardTitle class="pt-0.5">Numero de empleado: </CardTitle>
                <CardText> {{ employee.employeeNumber }}</CardText>
            </div>

            <div class="flex gap-2 justify-end">
                <CardTitle class="pt-0.5">Curp: </CardTitle>
                <CardText> {{ employee.curp }}</CardText>
            </div>

            <div class="flex gap-2 justify-end">
                <CardTitle class="pt-0.5">Horario: </CardTitle>
                <CardText> {{ employee.horario }}</CardText>
            </div>

            <div class="flex gap-2 justify-end">
                <CardTitle class="pt-0.5">Dias laborales: </CardTitle>
                <CardText> {{ employee.days }}</CardText>
            </div>

        </div>
    </div>
</template>