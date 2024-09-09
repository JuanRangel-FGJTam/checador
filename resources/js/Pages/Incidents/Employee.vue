<script setup>
import { onMounted, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import NavLink from '@/Components/NavLink.vue';
import Card from '@/Components/Card.vue';
import CardTitle from '@/Components/CardTitle.vue';
import CardText from '@/Components/CardText.vue';
import InputSelect from "@/Components/InputSelect.vue";
import InputError from '@/Components/InputError.vue';
import BadgeBlue from "@/Components/BadgeBlue.vue";
import BadgeGreen from "@/Components/BadgeGreen.vue";
import BadgeYellow from "@/Components/BadgeYellow.vue";
import BadgeRed from "@/Components/BadgeRed.vue";
import SuccessButton from '@/Components/SuccessButton.vue';
import VerticalTimeLine from '@/Components/VerticalTimeLine.vue';
import TimeLineItemCustom from '@/Components/TimeLineItemCustom.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import CalendarExclamationIcon from '@/Components/Icons/CalendarExclamationIcon.vue';

const props = defineProps({
    employeeNumber: String,
    employee: Object,
    status: Object,
    checa: Object,
    workingHours: Object,
    breadcrumbs: {
        type: Array,
        default: [
            { "name": 'Inicio', "href": '/dashboard' },
            { "name": 'Justificantes', "href": '/dashboard' },
            { "name": 'Empleado', "href": '' }
        ]
    },
    years: {
        type: Array,
        default: [2024,2023,2022,2021,2020]
    },
    months: {
        type: Array,
        default: [
            {value:1, label:"Enero"},
            {value:2, label:"Febrero"},
            {value:3, label:"Marzo"},
            {value:4, label:"Abril"},
            {value:5, label:"Mayo"},
            {value:6, label:"Junio"},
            {value:7, label:"Julio"},
            {value:8, label:"Agosto"},
            {value:9, label:"Septiembre"},
            {value:10, label:"Octubre"},
            {value:11, label:"Noviembre"},
            {value:12, label:"Diciembre"}
        ]
    },
    incidentStatuses: Array,
    options: Object
});

const toast = useToast();

const form = useForm({
    year: props.years[0],
    month: undefined
});

const formIncident = useForm({
    incident_id: undefined,
    state_id: undefined
});

const incidents = ref([]);

const loading = ref(false);

watch(form, (oldValue, newValue)=>{
    if( newValue.year && newValue.month){
        getIncidents();
    }
});

onMounted(()=>{
    // set the current month

    if( props.options != null){
        form.year = props.options.year;
        form.month = props.options.month;
    }else{
        const date = new Date();
        const currentMonth = date.getMonth() + 1;
        form.month = currentMonth;
    }

});

function getIncidents(){

    // * clear incident form
    formIncident.incident_id = undefined;
    formIncident.state_id = undefined;

    incidents.value = [];
    loading.value = true;

    // * attempt to get the incidentes of the employee
    axios.get( route('incidents.employee.raw',{
        "employee_number": props.employeeNumber,
        "onlyPendings": 1,
        "year": form.year,
        "month": form.month
    }))
    .then((response)=>{
        const {data} = response;
        if(data){
            incidents.value = data;
        }
    })
    .catch((err)=>{
        const {message} = err;
        toast.error(message??"Error at attempting to retrive the incendents of the month.");
        console.dir(err);
    })
    .finally(()=>{
        loading.value = false;
    });

}

function updateIncident(){
    formIncident.patch( route('incidents.state.update', formIncident.incident_id ), {
        onSuccess:(()=>{
            toast.info("El estado de la incidencia sé ha actualizado.")
            getIncidents();
        }),
        onError:((err)=>{
            const {message} = err;
            if( message){
                toast.warning(message);
            }
        })
    });
}

</script>

<template>

    <Head title="Empleado - Incidencias" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <div class="px-4 py-4 rounded-lg min-h-screen max-w-screen-lg mx-auto">

            <!-- employee data -->
            <Card class="outline outline-1 outline-gray-300 dark:outline-gray-500" :shadow="false">
                <template #header>
                    <div class="flex gap-4">
                        <h1 class="font-bold text-lg uppercase">{{ employee.name }}</h1>
                        <div class="border rounded-lg px-2 dark:border-gray-500" :class="status.class">
                            {{status.name}}
                        </div>
                        <div class="border rounded-lg px-2 dark:border-gray-500" :class="checa.class">
                            {{checa.name}}
                        </div>
                    </div>
                </template>

                <template #content>
                    <div class="flex gap-4">

                        <div class="flex items-center justify-start">
                            <img :src="employee.photo" class="mx-auto w-48 aspect-auto rounded-lg border bg-slate-400 text-center dark:border-gray-500" alt="Foto empleado"/>
                        </div>

                        <div class="flex gap-4 w-full">
                            <div class="flex flex-col items-start gap-1 w-2/5">

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
                                    <CardText v-for="item in workingHours">{{ item }}</CardText>
                                </div>

                                <div class="flex gap-2 justify-end">
                                    <CardTitle class="pt-0.5">Dias laborales: </CardTitle>
                                    <CardText> {{ employee.days }}</CardText>
                                </div>

                            </div>

                            <div class="flex flex-col items-start gap-1 w-3/5">
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
                            </div>
                        </div>

                    </div>
                </template>
            </Card>

            
            <!-- Incidents -->
            <div class="outline outline-1 outline-gray-300 flex flex-col gap-2 bg-white p-2 rounded dark:bg-gray-700 dark:outline-gray-500">
                
                <!-- Options -->
                <div class="w-full flex gap-2 mt-2 dark:border-gray-500">
                    <div class="w-32">
                        <InputSelect v-model="form.year">
                            <option v-for="y in props.years" :key="y" :value="y"> {{y}}</option>
                        </InputSelect>
                    </div>

                    <div class="w-32">
                        <InputSelect v-model="form.month">
                            <option v-for="y in props.months" :key="y.value" :value="y.value"> {{y.label}}</option>
                        </InputSelect>
                    </div>

                    <AnimateSpin v-if="loading" class="w-6 h-6 my-auto text-slate-800" />

                    <div v-if="formIncident.incident_id != null" class="ml-auto flex gap-1">
                        <InputSelect v-model="formIncident.state_id">
                            <option value="" selected> * Seleccione un elemento</option>
                            <option v-for="item in incidentStatuses" :key="item.id" :value="item.id"> {{ item.name }}</option>
                        </InputSelect>
                        <SuccessButton v-if="formIncident.state_id != null" v-on:click="updateIncident" class="w-fit px-1">
                            Actualzar
                        </SuccessButton>
                    </div>

                </div>
                <InputError class="ml-auto" :message="formIncident.errors.state_id" />

                <!-- timeline -->
                <div class="mt-4">
                    <fieldset class="pl-12">
                        <legend class="pb-4 text-gray-600 dark:text-gray-200">Seleccione una incidencia para actualizar el estado</legend>
                        <VerticalTimeLine>
                            <TimeLineItemCustom v-for="(item, index) in incidents" :key="item.id">
                                <template #icon>
                                    <input type="radio" :id="index" name="incident_id" :value="item.id" v-model="formIncident.incident_id" />
                                </template>

                                <template #content>
                                    <label :for="index" class="flex flex-col gap-1 border-b border-transparent hover:border-slate-200 cursor-pointer">
                                        <h3 class="flex items-center gap-2 mb-0 text-lg font-semibold text-gray-700 dark:text-white uppercase">
                                            <span>{{ item.type.name }}</span>
                                            <BadgeYellow v-if="item.state.name == 'Pendiente' ">{{ item.state.name }}</BadgeYellow>
                                            <BadgeGreen v-else-if="item.state.name == 'Autorizado' || item.state.name == 'Cancelado' ">{{ item.state.name }}</BadgeGreen>
                                            <BadgeRed v-else-if="item.state.name == 'Descontado' ">{{ item.state.name }}</BadgeRed>
                                            <BadgeBlue v-else>{{ item.state.name }}</BadgeBlue>
                                        </h3>
                                        <time class="flex items-center gap-1 mt-0 mb-1 text-sm font-normal leading-none text-gray-500 dark:text-gray-500 uppercase">
                                            <CalendarExclamationIcon class="w-6 h-6 p-1 text-slate-500" />
                                            <span>{{ item.date }}</span>
                                        </time>
                                    </label>
                                </template>
                            </TimeLineItemCustom>
                        </VerticalTimeLine>
                    </fieldset>
                </div>

            </div>

        </div>

    </AuthenticatedLayout>
</template>
