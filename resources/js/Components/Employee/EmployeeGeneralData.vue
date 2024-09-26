<script setup>
import CardText from '@/Components/CardText.vue';
import CardTitle from '@/Components/CardTitle.vue';

const props = defineProps({
    employee: Object,
    status: Object,
    checa: Object,
    workingHours: Array,
    employeePhoto: String,
    showStatus: {
        type: Boolean,
        default: true
    }
});

console.log('Photo URL:', props.employeePhoto);
</script>

<template>
    <div class="grid grid-cols-3 items-center">
        <img 
            class="mr-1 w-48 h-48 aspect-auto rounded-lg border bg-slate-400 object-cover" 
            alt="Foto empleado" 
            :src="employeePhoto"
        />

        <div class="col-span-2 flex flex-col items-start gap-1">

            <h1 class="font-bold text-lg text-gray-800 uppercase">{{ employee.name }}</h1>

            <div>
                <CardTitle>Número de empleado</CardTitle>
                <CardText> {{ employee.employeeNumber }}</CardText>
            </div>

            <div>
                <CardTitle>CURP</CardTitle>
                <CardText> {{ employee.curp }}</CardText>
            </div>

            <div class="flex items-center gap-2 justify-between w-full border-t">
                <div>
                    <CardTitle>Horario</CardTitle>
                    <CardText v-for="item in workingHours">{{ item }}</CardText>
                </div>
                <div>
                    <CardTitle>Dias laborales</CardTitle>
                    <CardText> {{ employee.days }}</CardText>
                </div>
            </div>

            <div v-if="showStatus" class="flex gap-2">
                <div class="border rounded-lg px-2 text-sm" :class="status.class">
                    {{status.name}}
                </div>
                <div class="border rounded-lg px-2 text-sm" :class="checa.class">
                    {{checa.name}}
                </div>
            </div>

        </div>
    </div>
</template>