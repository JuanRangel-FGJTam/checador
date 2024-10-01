<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import axios from 'axios';

import FullCalendar from '@fullcalendar/vue3';
import esLocale from '@fullcalendar/core/locales/es';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import dayGridPlugin from '@fullcalendar/daygrid';
import multiMonthPlugin from '@fullcalendar/multimonth'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
    employeePhoto: String,
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
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
        meridiem: false
    },
    lazyFetching: false,
    loading: (isLoading) => calendarLoading.value = isLoading,
    dateClick: (info)=> calendarDayClick(info),
    events: function(info, successCallback, failureCallback) {
        var from = info.start.toISOString().split("T")[0];
        var to = info.end.toISOString().split("T")[0];

        // * get number of days for determining if the view is monthly
        const diffDays = getdiffDays(info.start, info.end);
        if(diffDays == 42 )/* is a monthly view */{
            // get the current month
            var date = getFirstDayOrNextMonth(info.start, info.end);
            from = new Date(date.getFullYear(), date.getMonth(), 1).toISOString().split("T")[0];
            to = new Date(date.getFullYear(), date.getMonth() + 1, 0).toISOString().split("T")[0];
        }

        // * get the events
        axios.get(route('staff.raw-events', {
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
    a.href = route('staff.kardex', {
        employee_number: props.employeeNumber,
        year: form.year
    });
    a.target = '_blank';
    a.rel = 'noopener noreferrer';
    a.click();
    document.body.removeChild(a);
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

function getdiffDays(startDate, endDate){
    // * get number of days
    const diffTime = Math.abs(startDate - endDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
}

function getFirstDayOrNextMonth(startDate, endDate) {
  const start = new Date(startDate);
  const end = new Date(endDate);

  // If the end date is before the start date, return null or handle accordingly
  if (end < start) {
    return null; // Or throw an error, depending on your use case
  }

  // Check if the start date is the first day of the month
  if (start.getDate() === 1) {
    return start; // Return the start date if it's already the first day of the month
  }

  // Otherwise, return the first day of the next month
  const firstDayNextMonth = new Date(start.getFullYear(), start.getMonth() + 1, 1);

  return firstDayNextMonth;
}

</script>

<template>

    <Head title="Empleado - Mostrar" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <div class="grid gap-1 p-1 justify-center w-screen max-w-screen-2xl mx-auto h-full" style="grid-template-columns: 1fr 1fr 16rem; grid-template-rows: auto 1fr">
            
            <div class="col-span-7 bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500" style="grid-area: 1/1/2/2;">
                <EmployeeGeneralData
                    :employee="employee"
                    :status="status"
                    :checa="checa"
                    :showStatus="false"
                    :workingHours="workingHours"
                    :employeePhoto="employeePhoto"
                />
            </div>

            <div class="col-span-5 bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500" style="grid-area: 1/2/1/3;">
                <EmployeeDataPanel
                    :employee="employee"
                    :showButtons="false"
                    v-on:editCalendar="editCalendarClick"
                    v-on:editEmployee="editEmployeeClick"
                    v-on:incidencesClick="incidencesClick"
                    v-on:downloadKardex="downLoadkardexClick"
                />
            </div>

            <div class="col-span-12 bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500 select-none" style="grid-area: 2/1/3/3;">
                <FullCalendar ref="fullCalenarObj" :options="calendarOptions" />
            </div>

            <div class="bg-white shadow border rounded-lg p-4 dark:bg-gray-800 dark:border-gray-500 select-none" style="grid-area: 1/3/3/4;">
                <AnimateSpin v-if="calendarLoading" class="w-4 h-4 mx-1 "/>
                <IncidenciasPanel v-else :incidences="currentIncidences" />
            </div>

        </div>

    </AuthenticatedLayout>
</template>
