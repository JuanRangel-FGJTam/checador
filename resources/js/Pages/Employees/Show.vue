<script setup>
import { ref, computed } from 'vue';
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
import EmployeeGeneralData from '@/Components/Employee/EmployeeGeneralData.vue';
import EmployeeDataPanel from '@/Components/Employee/EmployeeDataPanel.vue';
import IncidenciasPanel from '@/Components/Employee/IncidenciasPanel.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';

const props = defineProps({
    employeeNumber: String,
    employee: Object,
    status: Object,
    checa: Object,
    workingHours: Array,
    breadcrumbs: Object
});

const toast = useToast();


const calendarDaySelected = ref({
    element: undefined,
    day: undefined
});

const calendarLoading = ref(false);

const fullCalenarObj = ref({});

const calendarEvents = ref([]);

const calendarOptions = {
    plugins: [
        dayGridPlugin, timeGridPlugin, multiMonthPlugin, interactionPlugin
    ],
    locales: [esLocale],
    height: "100%",
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
        var from = info.start.toISOString().split("T")[0];
        var to = info.end.toISOString().split("T")[0];
        axios.get(route('employees.raw-events', {
            "employee_number": props.employeeNumber,
            "from": from,
            "to": to,
        }))
        .then((res)=>{
            calendarEvents.value = res.data;
            successCallback(res.data);
        })
        .catch((ex)=> failureCallback(ex));
    }
}

const currentIncidences = computed(()=>{
    
    // * get the range date
    if(calendarEvents.value && calendarEvents.value.length > 0){
        var dateRange = getCurrentDateRange();
        return calendarEvents.value
            .filter(item => item.type === 'INCIDENT')
            .filter(item => {
                const eventDate = new Date(item.start);
                return eventDate >= new Date(dateRange.from) && eventDate <= new Date(dateRange.to);
            });
    }else{
        return [];
    }

});

/**
 * @typedef {Object} DateRange
 * @property {string|null} from
 * @property {string|null} to
 * @returns {DateRange} dateRange
 */
function getCurrentDateRange(){
    if( fullCalenarObj.value){
        var currentDateStart = fullCalenarObj.value.calendar.view.currentStart;
        var currentDateEnd = fullCalenarObj.value.calendar.view.currentEnd;
        var from = currentDateStart.toISOString().split("T")[0];
        var to = currentDateEnd.toISOString().split("T")[0];
        return { from, to }
    }else{
        return { from:undefined, to:undefined };
    }
}

function editCalendarClick(){
    router.visit( route('employees.schedule.edit', props.employeeNumber));
}

function editEmployeeClick(){
    router.visit( route('employees.edit', props.employeeNumber));
}

function incidencesClick(){
    router.visit( route("incidents.employee.index", props.employeeNumber));
}

/**
 * @param {Object} form
 * @param {number} form.year - year selected.
 */
function downLoadkardexClick(form){
    var a = document.createElement('a');
    a.href = route('employees.kardex', {
        employee_number: props.employeeNumber,
        year: form.year
    });
    a.target = '_blank';
    a.rel = 'noopener noreferrer';
    a.click();
    document.body.removeChild(a);
}

function makeIncidenceClick(){
    var dateSelectedString = undefined;
    if( calendarDaySelected.value.day ){
        dateSelectedString = calendarDaySelected.value.day.toISOString().split('T')[0];
    }
    router.visit( route('employees.incidents.create', {
        "employee_number": props.employeeNumber,
        "date": dateSelectedString
    }));
}

function showJustificationsClick(){

    // * get the range date
    var dateRange = getCurrentDateRange();

    // * redirect view
    router.visit( route('employees.justifications.index', {
        "employee_number": props.employeeNumber,
        "from": dateRange.from,
        "to": dateRange.to
    }));
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

        <div class="grid p-1 gap-1 justify-center w-screen max-w-screen-2xl h-full mx-auto" style="grid-template-columns: 1fr 1fr 16rem; grid-template-rows: auto 3rem 1fr">
            
            <div class="bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500" style="grid-area: 1/1/2/2;">
                <EmployeeGeneralData
                    :employee="employee"
                    :status="status"
                    :checa="checa"
                    :workingHours="workingHours"
                />
            </div>

            <div class="bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500" style="grid-area: 1/2/2/3;">
                <EmployeeDataPanel
                    :employee="employee"
                    v-on:editCalendar="editCalendarClick"
                    v-on:editEmployee="editEmployeeClick"
                    v-on:incidencesClick="incidencesClick"
                    v-on:downloadKardex="downLoadkardexClick"
                />
            </div>

            <div class="bg-white shadow border rounded-lg px-4 py-2 dark:bg-gray-800 dark:border-gray-500" style="grid-area: 2/1/3/3;">
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

            <div class="bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500 select-none" style="grid-area:3/1/4/3;">
                <FullCalendar ref="fullCalenarObj" :options="calendarOptions" />
            </div>

            <div class="bg-white h-100 shadow border rounded-lg overflow-y-hidden dark:bg-gray-800 dark:border-gray-500 select-none" style="grid-area:1/3/4/4;">
                <AnimateSpin v-if="calendarLoading" class="w-4 h-4 mx-1 "/>
                <IncidenciasPanel v-else :incidences="currentIncidences" />
            </div>

        </div>

    </AuthenticatedLayout>
</template>
