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

</script>

<template>
    <div class="flex items-start">
        <img
            class="block mr-4 w-44 h-44 aspect-auto rounded-lg shadow-lg object-cover" 
            alt="Foto empleado"
            :src="employeePhoto"
        />

        <div>
            <h1 class="font-semibold text-blue-600 uppercase mb-2 border-b">{{ employee.name }}</h1>

            <div>
                <CardTitle>Número de empleado</CardTitle>
                <CardText> {{ employee.employeeNumber }}</CardText>
            </div>

            <div>
                <CardTitle>CURP</CardTitle>
                <CardText> {{ employee.curp }}</CardText>
            </div>

            <div v-if="showStatus" class="flex justify-between gap-2 py-2">
                <div 
                    :class="status.class"
                >
                    {{status.name}}
                </div>
                <div 
                    :class="checa.class"
                >
                    {{checa.name}}
                </div>
            </div>

        </div>
    </div>

    <div class="flex justify-between border-b">
        <div>
            <CardTitle>Horario</CardTitle>
            <CardText v-for="item in workingHours">{{ item }}</CardText>
        </div>
        <div>
            <CardTitle>Dias laborales</CardTitle>
            <CardText> {{ employee.days }}</CardText>
        </div>
    </div>
</template>