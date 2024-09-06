<script setup>
import { onMounted, ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { debounce } from '@/utils/debounce';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import CardTitle from '@/Components/CardTitle.vue';
import CardText from '@/Components/CardText.vue';
import InputSelect from "@/Components/InputSelect.vue";
import InputError from '@/Components/InputError.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import DownloadIcon from '@/Components/Icons/DownloadIcon.vue';
import { options } from '@fullcalendar/core/preact';
import { diffDates } from '@fullcalendar/core/internal';

const props = defineProps({
    years: {
        type: Array,
        default: [2024,2023,2022,2021,2020]
    },
    incidentStatuses: Array,
    generalDirections: Array,
    employees: Array,
    reportTypes: Object,
    periods: Object,
    options: {
        type: Object,
        default: {
            generalDirecctionId: 0,
            year: 0,
            period: 0,
            type: ''
        }
    }
});

const toast = useToast();

const form = useForm({
    general_direction_id: props.options.generalDirecctionId,
    year: props.options.year,
    period: props.options.period,
    report_type: props.options.type,
});

const loading = ref(false);

watch(form, (oldValue, newValue)=>{
    debounce(()=>{
        getIncidents();
    }, 750)
});

onMounted(()=>{
    //
});

function getIncidents(){

    // prevet call the data if the general direction and the period are not selected
    if( form.general_direction_id == null || form.general_direction_id <= 0  || !form.period){
        return;
    }

    loading.value = true;

    // prepared the query params
    var params = [];
    params.push(`gdi=${form.general_direction_id}`);
    params.push(`t=${form.report_type}`);
    params.push(`y=${form.year}`);
    params.push(`p=${form.period}`);

    // call for the data
    router.visit("?" + params.join("&"), {
        replace: true,
        preserveState: true,
        only: ["options","periods","employees"],
        onError: ()=>{
            toast.error("Error al actualizar los datos, intente de nuevo o comuniquese con el administrador.")
        },
        onFinish: ()=>{
            loading.value = false;
        }
    });

}

function reportTypeChanged(){
    form.period = undefined;

    var params = [];
    params.push(`gdi=${form.general_direction_id}`);
    params.push(`t=${form.report_type}`);
    params.push(`y=${form.year}`);

    router.visit("?" + params.join("&"), {
        replace: true,
        preserveState: true,
        only: ["options","periods","employees" ],
        onError: ()=>{
            toast.error("Error al actualizar los datos, intente de nuevo o comuniquese con el administrador.")
        },
        onFinish: ()=>{
            loading.value = false;
        }
    });
}


</script>

<template>

    <Head title="Empleado - Incidencias" />

    <AuthenticatedLayout>

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Incidencias</h2>
        </template>

        <div class="px-4 py-4 h-full rounded-lg max-w-screen-xl mx-auto grid grid-rows-[5rem_1fr] ">

            <Card class="outline outline-1 outline-gray-300 dark:outline-gray-500" :shadow="false">
                <template #content>
                    <form @submit.prevent="submitFilter" class="flex pt-1 gap-1 items-center">

                        <InputSelect v-model="form.general_direction_id" id="general_direction_id" class="max-w-[24rem]">
                            <option value="" class="uppercase"> Seleccione una opcion</option>
                            <option v-for="element in generalDirections" :value="element.id" class="uppercase"> {{ element.name }}</option>
                        </InputSelect>

                        <InputSelect v-model="form.year" id="year" class="max-w-[8rem]">
                            <option v-for="y in years" :value="y"> {{ y }}</option>
                        </InputSelect>

                        <InputSelect v-model="form.report_type" id="report_type" class="max-w-[12rem]" v-on:change="reportTypeChanged">
                            <option v-for="(key, index) in Object.keys(reportTypes)" :key="index" :value="key">{{ reportTypes[key] }}</option>
                        </InputSelect>

                        <InputSelect v-model="form.period" id="period" class="max-w-[18rem]">
                            <option value=""> Seleccione una opcion</option>
                            <option v-for="(key, index) in Object.keys(periods)" :key="index" :value="key"> {{ periods[key] }}</option>
                        </InputSelect>

                        <SuccessButton type="submit" class="ml-auto">
                            <DownloadIcon class="w-5 h-5 mr-1" />
                            <span>Descargar</span>
                        </SuccessButton>

                    </form>
                </template>
            </Card>

            <div class="h-full overflow-y-auto">
                <table class="table w-full shadow text-sm text-left border rtl:text-right text-gray-500 dark:text-gray-400 dark:border-gray-500">
                    <thead class="sticky top-0 z-20 text-xs uppercase text-gray-700 border bg-gradient-to-b from-gray-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 dark:text-gray-200 dark:border-gray-500">
                        <AnimateSpin v-if="loading" class="w-5 h-5 mx-2 absolute top-2.5" />
                        <tr>
                            <th scope="col" class="w-1/8 text-center px-6 py-3 ">#</th>

                            <th scope="col" class="w-2/8 text-center px-6 py-3 tracking-wider">
                                Nombre
                            </th>
                            <th scope="col" class="w-2/8 text-center px-6 py-3 uppercase tracking-wider">
                                Unidad
                            </th>
                            <th scope="col" class="w-1/8 text-center px-6 py-3 uppercase tracking-wider">
                                # Incidencias
                            </th>
                            <th scope="col" class="w-2/8 before:relative px-6 py-3">
                                <span class="sr-only">Información</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="employees && employees.length>0" v-for="(employee, index) in employees" :key="index">
                            <td class="text-sm text-center font-medium text-gray-900 px-2">
                                {{ index + 1 }}
                            </td>

                            <td class="px-6 py-4 flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded-full" :src="employee.photo" alt="User photo">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ employee.name }}
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="text-sm text-gray-600">
                                    {{ employee.general_direction.abbreviation }}
                                </div>
                                <div class="text-sm text-gray-900">
                                    {{ employee.direction.name }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-center whitespace-normal text-sm text-gray-500">
                                {{ employee.totalIncidents }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="#" target="_blank"
                                    class="p-2 rounded-md text-blue-600 border border-blue-500 outline-none focus:ring-4 shadow-lg transform active:scale-x-75 transition-transform flex justify-center items-center hover:bg-blue-100"
                                >
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="#3b83f6"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                                    <span class="ml-2">Incidencias</span>
                                </a>
                            </td>
                        </tr>

                        <tr v-else>
                            <td colspan="5" class="text-center text-yellow-600 py-6">Sin información que mostrar</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </AuthenticatedLayout>
</template>
