<script setup>
import { ref } from 'vue';
import Card from '@/Components/Card.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import WhiteButton from '@/Components/WhiteButton.vue';
import WarningButton from '@/Components/WarningButton.vue';
import CalendarEditIcon from '@/Components/Icons/CalendarEditIcon.vue';
import UserEditIcon from '@/Components/Icons/UserEditIcon.vue';
import FlagIcon from '@/Components/Icons/FlagIcon.vue';

const props = defineProps({
    employee: Object,
    years: {
        type: Array,
        default: [2024,2023,2022,2021,2020]
    }
});

const emit = defineEmits(['editCalendar', 'editEmployee', 'incidencesClick', 'downloadKardex']);

const kardexForm = ref({ year: props.years[0] });

function editCalendarClick(){
    emit('editCalendar');
}

function editEmployeeClick(){
    emit('editEmployee');
}

function incidencesClick(){
    emit('incidencesClick');
}

function downloadKardexClick(){
    emit('downloadKardex', kardexForm.value);
}


</script>

<template>
    <div class="flex flex-col items-start gap-1">
        <p class="text-gray-700 dark:text-gray-300 uppercase font-semibold">
            {{ employee.generalDirection }}
        </p>

        <p class="text-gray-700 dark:text-gray-300 uppercase font-semibold">
            {{ employee.direction }}
        </p>

        <p v-if="employee.subDirection" class="text-gray-600 dark:text-gray-300 uppercase font-semibold text-sm">
            {{ employee.subDirection}}
        </p>

        <p v-if="employee.department" class="text-gray-600 dark:text-gray-300 uppercase font-semibold text-sm">
            {{ employee.department}}
        </p>
        
        <div class="w-full flex justify-around mt-2">
            <WhiteButton class="border-1 border-blue-400" v-on:click="editCalendarClick">
                <CalendarEditIcon class="w-4 h-4 mx-1" />
                <span>Horiario</span>
            </WhiteButton>
            <WhiteButton class="border-1 border-blue-400" v-on:click="editEmployeeClick">
                <UserEditIcon class="w-4 -h4 mx-1" />
                <span>Editar Empleado</span>
            </WhiteButton>
            <WarningButton v-on:click="incidencesClick">
                <FlagIcon class="w-4 h-4 mx-1"/>
                <span>Incidencias</span>
            </WarningButton>
        </div>

        <div class="w-full flex justify-end gap-2 mt-2 pt-2 border-t dark:border-gray-500">
            <select class="w-32 border rounded" v-model="kardexForm.year">
                <option v-for="y in props.years" :key="y" :value="y"> {{y}}</option>
            </select>
            <PrimaryButton v-on:click="downloadKardexClick">
                Descargar Kardex
            </PrimaryButton>
        </div>
    </div>
</template>