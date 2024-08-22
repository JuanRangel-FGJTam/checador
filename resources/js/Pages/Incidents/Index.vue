<script setup>
import { onMounted, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import NavLink from '@/Components/NavLink.vue';
import PageTitle from '@/Components/PageTitle.vue';
import Card from '@/Components/Card.vue';
import CardTitle from '@/Components/CardTitle.vue';
import CardText from '@/Components/CardText.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputSelect from "@/Components/InputSelect.vue";
import BadgeGreen from "@/Components/BadgeGreen.vue";
import BadgeBlue from "@/Components/BadgeBlue.vue";
import InputDate from '@/Components/InputDate.vue';
import InputError from '@/Components/InputError.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import ChevronRightIcon from '@/Components/Icons/ChevronRightIcon.vue';

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
    }
});

const toast = useToast();

const form = useForm({
    year: props.years[0],
    month: undefined
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
    const date = new Date();
    const currentMonth = date.getMonth() + 1;
    form.month = currentMonth;
});

function getIncidents(){
    toast.warning("Retriving the incidents, not implemented");

    loading.value = true;

    setTimeout(() => {
        loading.value = false;
    }, 3000);
}

</script>

<template>

    <Head title="Empleado - Incidencias" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <div class="px-4 py-4 rounded-lg min-h-screen max-w-screen-2xl mx-auto">

            <!-- employee data -->
            <Card class="mx-auto max-w-screen-lg">
                <template #header>
                    <div class="flex gap-4">
                        <h1 class="font-bold text-lg uppercase">{{ employee.name }}</h1>
                        <div class="border rounded-lg px-2" :class="status.class">
                            {{status.name}}
                        </div>
                        <div class="border rounded-lg px-2" :class="checa.class">
                            {{checa.name}}
                        </div>
                    </div>
                </template>

                <template #content>
                    <div class="flex gap-4">

                        <div class="flex items-center justify-start">
                            <img :src="employee.photo" class="mx-auto w-48 h-48 aspect-auto rounded-lg border bg-slate-400 text-center" alt="Foto empleado"/>
                        </div>

                        <div class="flex flex-col items-start gap-1">
                            
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
                        </div>

                    </div>
                </template>
            </Card>

            
            <!-- data table -->
            <div class="outline outline-1 outline-gray-300 flex flex-col gap-2 bg-white p-2 rounded dark:bg-gray-700">
                
                <!-- table options -->
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

                </div>

                <!-- table incidences -->
                <table class="table-fixed w-full shadow text-sm text-left border rtl:text-right text-gray-500 dark:text-gray-400 dark:border-gray-500">
                    <thead class="sticky top-0 z-20 text-xs uppercase text-gray-700 border bg-gradient-to-b from-gray-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 dark:text-gray-200 dark:border-gray-500">
                        <tr>
                            <th scope="col" class="w-2/8 text-center px-6 py-3">
                                Justificacion
                            </th>
                            <th scope="col" class="w-1/8 text-center px-6 py-3">
                                Fecha Inicio
                            </th>
                            <th scope="col" class="w-2/8 text-center px-6 py-3">
                                Fecha Fin
                            </th>
                            <th scope="col" class="w-1/8 text-center px-6 py-3">
                                Observaciones
                            </th>
                            <th scope="col" class="w-1/8 text-center px-6 py-3">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="bg-white dark:bg-gray-800 dark:border-gray-500">
                        <template v-if="incidents && incidents.length > 0">
                            <tr v-for="item in incidents" :key="item.id" :id="item.id" class="border-b">

                                <td class="p-2 text-center">
                                    <div class="text-sm">justify type id: {{ item.type_justify_id }}</div>
                                </td>

                                <td class="p-2 text-center">
                                    {{ item.date_start }}
                                </td>

                                <td class="p-2 text-center">
                                    {{ item.date_finish }}
                                </td>

                                <td class="p-2 text-center">
                                    {{ item.details }}
                                </td>

                                <td class="p-2 text-center">
                                    <NavLink href="/dashboard" >
                                        <div class="flex gap-2 shadow bg-slate-200 px-4 py-1">
                                            <span>Accion 1</span>
                                            <ChevronRightIcon class="w-4 h-4 ml-1" />
                                        </div>
                                    </NavLink>
                                </td>

                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center font-medium whitespace-nowrap dark:text-white text-lg text-emerald-700">
                                    No hay justificantes registrados para el empleado.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

        </div>

    </AuthenticatedLayout>
</template>
