<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { formatDate } from '@/utils/date.js';
import { useToast } from 'vue-toastification';

import FullCalendar from '@fullcalendar/vue3';
import esLocale from '@fullcalendar/core/locales/es';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import dayGridPlugin from '@fullcalendar/daygrid';
import multiMonthPlugin from '@fullcalendar/multimonth'
import Modal from '@/Components/Modal.vue';
import Card from '@/Components/Card.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import WarningButton from '@/Components/WarningButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import WhiteButton from '@/Components/WhiteButton.vue';
import DisabledButton from '@/Components/DisabledButton.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import EmployeeGeneralData from '@/Components/Employee/EmployeeGeneralData.vue';
import EmployeeDataPanel from '@/Components/Employee/EmployeeDataPanel.vue';
import IncidenciasPanel from '@/Components/Employee/IncidenciasPanel.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';

const toast = useToast();

const props = defineProps({
    employeeNumber: String,
    employee: Object,
    status: Object,
    checa: Object,
    workingHours: Array,
    breadcrumbs: Object,
    employeePhoto: String,
    auth: Object
});

const calendarDaySelected = ref({
    element: undefined,
    day: undefined
});

const calendarLoading = ref(false);

const fullCalenarObj = ref({});

const calendarEvents = ref([]);

const yearsAvailables = ref([]);

const calendarOptions = {
    plugins: [
        dayGridPlugin, timeGridPlugin, multiMonthPlugin, interactionPlugin
    ],
    locales: [esLocale],
    height: "90%",
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

const confirmationModal = ref({
    show: false,
    data: undefined
});

onMounted(()=>{
    for (let i = 0; i < 6; i++) {
        yearsAvailables.value.push(new Date().getFullYear() - i);
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

function showHistoryWorkingHoursClick()
{
    router.visit( route('employees.workinghours-history',
        {
            "employee_number": props.employeeNumber
        }
    ));
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

function handleIncidentClick(incidentDate)
{
    fullCalenarObj.value.calendar.select(incidentDate);
}

function handleRemoveIncidentClick(incident)
{
    confirmationModal.value.data = incident;
    confirmationModal.value.show = true;
}

function removeIncident(incident)
{
    // toast.warning('Remove the incident');
    var incidentId = incident.id.slice(1);
    router.delete(route('employees.incidents.delete', { "employee_number": props.employeeNumber, "incidentId": incidentId }), {
        onSuccess: (res) =>
        {
            toast.success('Incidencia eliminada');
            confirmationModal.value.show = false;
            fullCalenarObj.value.calendar.refetchEvents();
        },
        onError: (err) => {
            const {message} = err;
            if(message)
            {
                toast.error(message);
            }
            toast.error("Error al eliminar la incidencia, intente de nuevo.");
        }
    });
}

</script>

<template>
    <Head title="Empleado" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <div class="mx-auto max-w-screen-2xl grid grid-employee">

            <div class="bg-white rounded p-4">
                <EmployeeGeneralData
                    :employee="employee"
                    :employeePhoto="employeePhoto"
                    :status="status"
                    :checa="checa"
                    :workingHours="workingHours"
                />

                <EmployeeDataPanel
                    :employee="employee"
                    :years="yearsAvailables"
                    v-on:editCalendar="editCalendarClick"
                    v-on:editEmployee="editEmployeeClick"
                    v-on:incidencesClick="incidencesClick"
                    v-on:downloadKardex="downLoadkardexClick"
                />

                <div class="pb-4 overflow-y: auto; dark:bg-gray-800 h-80 mt-2">
                    <AnimateSpin v-if="calendarLoading" class="w-4 h-4 mx-1 "/>
                    <IncidenciasPanel v-else 
                        :incidences="currentIncidences"
                        v-on:incidentClick="handleIncidentClick"
                        v-on:removeIncidentClick="handleRemoveIncidentClick"
                    />
                </div>
            </div>

            <div class="select-none p-4 bg-white">
                <div class="flex gap-4 justify-end border-b pb-2 mb-2">
                    <WarningButton 
                        v-on:click="makeIncidenceClick"
                        v-if="auth.user.level_id == 1"
                    >
                        Generar Incidencias
                    </WarningButton>

                    <WhiteButton v-on:click="showHistoryWorkingHoursClick">
                        Historial Horarios
                    </WhiteButton>

                    <WhiteButton v-on:click="showJustificationsClick">
                        Ver justificantes
                    </WhiteButton>

                    <WhiteButton v-if="calendarDaySelected.day" v-on:click="justifyDayClick">
                        Justificar día {{ formatDate(calendarDaySelected.day) }}
                    </WhiteButton>
                    <DisabledButton v-else>
                        Seleccione un día para justificar
                    </DisabledButton>
                </div>

                <FullCalendar ref="fullCalenarObj" :options="calendarOptions" />
            </div>

            <Modal :show="confirmationModal.show && confirmationModal.data != null" v-on:close="confirmationModal.show = false">
                <Card :shadow="false" :whitOutBorder="true">
                    <template #header>
                        <h2 class="uppercase">Eliminando incidencia</h2>
                    </template>
                    <template #content>
                        <div class="flex flex-col p-2">
                            <p>Esta por eliminar la incidencia <b>"{{ confirmationModal.data.title}}"</b> del dia <b>{{ confirmationModal.data.start}}</b>, esta es una accion irreversible, ¿desea continuar?</p>

                            <div class="flex mt-5 justify-between">
                                <PrimaryButton v-on:click="confirmationModal.show = false">Cancelar </PrimaryButton>
                                <DangerButton v-on:click="removeIncident(confirmationModal.data)"> Eliminar incidencia </DangerButton>
                            </div>
                        </div>
                    </template>
                </Card>
            </Modal>
        </div>

    </AuthenticatedLayout>
</template>

<style>
.grid-employee {
    grid-template-columns: 30rem 1fr;
    grid-template-rows: calc(100vh - 9rem);
    gap: 1rem;
    padding: 0.5rem;
    overflow-y: hidden;
}
.border-yellow-500 {
    border-color: #fbbf24;
}
</style>
