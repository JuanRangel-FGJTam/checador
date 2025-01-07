<script setup>
import { ref } from 'vue';
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
    },
    showButtons: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['editCalendar', 'editEmployee', 'incidencesClick', 'downloadKardex']);

const kardexForm = ref({ year: new Date().getFullYear() });

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
    <div class="flex flex-col items-start gap-2">
        <p class="text-gray-800 dark:text-gray-300">
            {{ employee.generalDirection }}
        </p>

        <p class="text-gray-500 dark:text-gray-300 text-sm">
            {{ employee.direction }}
        </p>

        <p v-if="employee.subDirection" class="text-gray-500 dark:text-gray-300 text-sm">
            {{ employee.subDirection}}
        </p>

        <!--
        <p v-if="employee.department" class="text-gray-600 dark:text-gray-300 uppercase font-semibold text-sm">
            {{ employee.department}}
        </p>
        -->

        <div v-if="showButtons" class="w-full flex justify-between">
            <WhiteButton v-on:click="editCalendarClick">
                <CalendarEditIcon class="w-4 h-4 mx-1" />
                <span>Horiario</span>
            </WhiteButton>
            <WhiteButton v-on:click="editEmployeeClick">
                <UserEditIcon class="w-4 -h4 mx-1" />
                <span>Editar Empleado</span>
            </WhiteButton>
            <WarningButton v-on:click="incidencesClick">
                <FlagIcon class="w-4 h-4 mx-1"/>
                <span>Incidencias</span>
            </WarningButton>
        </div>

        <div class="w-full h-8 flex justify-starts gap-4 mt-1">
            <select class="w-32 border px-2 py-1 rounded text-sm" v-model="kardexForm.year">
                <option v-for="y in props.years" :key="y" :value="y">{{y}}</option>
            </select>
            <PrimaryButton v-on:click="downloadKardexClick">
                Descargar Kardex
            </PrimaryButton>
        </div>
    </div>
</template>