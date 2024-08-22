<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { formatDate } from '@/utils/date.js';
import axios from 'axios';

import FullCalendar from '@fullcalendar/vue3';
import esLocale from '@fullcalendar/core/locales/es';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import dayGridPlugin from '@fullcalendar/daygrid';
import multiMonthPlugin from '@fullcalendar/multimonth'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import WarningButton from '@/Components/WarningButton.vue';
import WhiteButton from '@/Components/WhiteButton.vue';
import DisabledButton from '@/Components/DisabledButton.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import EmployeeGeneralData from './Partials/EmployeeGeneralData.vue';
import EmployeeDataPanel from './Partials/EmployeeDataPanel.vue';
import IncidenciasPanel from './Partials/IncidenciasPanel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';

const props = defineProps({
    employeeNumber: String,
    employee: Object,
    status: Object,
    checa: Object,
    workingHours: Array,
});

const toast = useToast();

const breadcrumbs = ref([
    { "name": 'Inicio', "href": '/dashboard' },
    { "name": 'Vista Empleados', "href": '/employees' },
    { "name": `Empleado: ${props.employeeNumber}`, "href": '' }
]);

const calendarDaySelected = ref({
    element: undefined,
    day: undefined
});

const calendarLoading = ref(false);

const calendarOptions = {
    plugins: [
        dayGridPlugin, timeGridPlugin, multiMonthPlugin, interactionPlugin
    ],
    locales: [esLocale],
    height: 650,
    locale: 'es',
    initialView: 'dayGridMonth',
    headerToolbar: {
        start: 'multiMonthYear,dayGridMonth,timeGridWeek',
        center: 'title',
        end: 'today prev,next'
    },
    loading: (isLoading) => calendarLoading.value = isLoading,
    dateClick: (info)=> calendarDayClick(info),
    events: function(info, successCallback, failureCallback) {
        const startDate = info.start.valueOf();
        const endDate = info.end.valueOf();
        axios.get(route('employees.raw-events', {
            "employee_number": props.employeeNumber,
            "start": startDate,
            "end": endDate,
        }))
        .then((res)=> successCallback(res.data))
        .catch((ex)=> failureCallback(ex));
    }
}

function editCalendarClick(){
    router.visit( route('employees.schedule.edit', props.employeeNumber));
}

function editEmployeeClick(){
    router.visit( route('employees.edit', props.employeeNumber));
}

function incidencesClick(){
    toast.info("incidences click!!");
}

/**
 * @param {Object} form
 * @param {number} form.year - year selected.
 */
function downLoadkardexClick(form){
    toast.info(`download kardex ${form.year} click!!`);
}

function makeIncidenceClick(){
    router.visit(route('employees.incidents.create', props.employeeNumber));
}

function showJustificationsClick(){
    router.visit(route('employees.justifications.index', props.employeeNumber));
}

function justifyDayClick(){

    var day = calendarDaySelected.value.day;

    const formattedDate = day.getFullYear() + '-' +
                      String(day.getMonth() + 1).padStart(2, '0') + '-' +
                      String(day.getDate()).padStart(2, '0');

    router.visit(route('employees.justifications.justify-day', {
        "employee_number": props.employeeNumber,
        "day" : formattedDate
    }));

}

/**
 *
 * @param {Object} info
 * @param {Date} info.date
 * @param {string} info.dateStr
 * @param {any} info.dayEl
 */
function calendarDayClick(info){
    // clear selection
    if( calendarDaySelected.value.element != undefined){
        calendarDaySelected.value.element.style.backgroundColor = 'inherit';
    }
    calendarDaySelected.value.element = info.dayEl;
    calendarDaySelected.value.element.style.backgroundColor = '#a9cce3';
    calendarDaySelected.value.day = info.date;

    // TODO: validate if the day selected exist a incident
}

</script>

<template>

    <Head title="Empleado - Mostrar" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <div class="grid grid-cols-12 my-4 p-4 gap-2 mx-auto w-screen max-w-screen-xl">

            <div class="col-span-7 bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500">
                <EmployeeGeneralData
                    :employee="employee"
                    :status="status"
                    :checa="checa"
                    :workingHours="workingHours"
                />
            </div>

            <div class="col-span-5 bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500">
                <EmployeeDataPanel
                    :employee="employee"
                    v-on:editCalendar="editCalendarClick"
                    v-on:editEmployee="editEmployeeClick"
                    v-on:incidencesClick="incidencesClick"
                    v-on:downloadKardex="downLoadkardexClick"
                />
            </div>

            <div class="col-span-12 bg-white shadow border rounded-lg px-4 py-2 dark:bg-gray-800 dark:border-gray-500">
                <div class="flex gap-4 justify-center">

                    <WarningButton v-on:click="makeIncidenceClick">
                        Generar Incidencias
                    </WarningButton>

                    <WhiteButton v-on:click="showJustificationsClick" class=" outline outline-1">
                        Ver justificaciones
                    </WhiteButton>

                    <WhiteButton v-if="calendarDaySelected.day" v-on:click="justifyDayClick" class=" outline outline-1">
                        Justificar ({{ formatDate(calendarDaySelected.day) }} )
                    </WhiteButton>
                    <DisabledButton v-else>
                        Justificar (seleccione un dia)
                    </DisabledButton>
                </div>
            </div>

            <div class="col-span-12 bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500 select-none">
                <FullCalendar :options="calendarOptions" />
            </div>

            <div class="col-span-12 bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500 select-none">
                <AnimateSpin v-if="calendarLoading" class="w-4 h-4 mx-1 "/>
                <IncidenciasPanel />
            </div>
        </div>

    </AuthenticatedLayout>
</template>
