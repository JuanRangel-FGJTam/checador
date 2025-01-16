<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { formatDatetime } from '@/utils/date';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PreviewDocument from '@/Components/PreviewDocument.vue';
import NavLink from '@/Components/NavLink.vue';
import SearchInput from '@/Components/SearchInput.vue';
import BadgeBlue from '@/Components/BadgeBlue.vue';
import BadgeRed from '@/Components/BadgeRed.vue';
import BadgeGreen from '@/Components/BadgeGreen.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import ChevronRightIcon from '@/Components/Icons/ChevronRightIcon.vue';
import PdfIcon from '@/Components/Icons/PdfIcon.vue';

const props = defineProps({
    title: String,
    data: Array
});

const toast = useToast();

const form = useForm({
    search: ""
});

const activeStatus = ['alta', '1', 'activo' ];
const inactiveStatus = ['baja', '0', 'inactive' ];

const loading = ref(false);

const previewDocumentModal = ref({
    show: false,
    title: "",
    subtitle: "",
    src: ""
});

function handleInputSearch(search)
{
    form.search = search;
    reloadData();
}

function reloadData(){
    // loading.value = true;
    // debounce(()=>{
    //     // * prepare the query params
    //     var params = [];
    //     if(form.gd){
    //         params.push(`gd=${form.gd}`);
    //     }

    //     if(form.search){
    //         params.push(`se=${form.search}`);
    //     }

    //     if(form.page && form.page > 1){
    //         params.push(`p=${form.page}`);
    //     }
        
    //     // * reload the view
    //     router.visit("?" + params.join("&"), {
    //         method: 'get',
    //         only: ['employees', 'directions', 'subdirectorate', 'showPaginator', 'paginator'],
    //         preserveState: true,
    //         onError:(err)=>{
    //             toast.error("Error al obtener los datos");
    //         }
    //     });
        
    // loading.value = false;
    // }, 500);
}

function employeeIsInactive(status)
{
    if(status == null) return false;

    return inactiveStatus.includes(status.toLowerCase());
}

function employeeIsActive(status)
{
    if(status == null) return false;
    
    return activeStatus.includes(status.toLowerCase());
}

function handleShowPdfClick(id)
{
    var item = props.data.find( i => i.id == id);
    previewDocumentModal.value.title = `Justificante cambio de estatus de ${item.employee.computed_employee_number}`;
    previewDocumentModal.value.subtitle = `${formatDatetime(item.created_at)}`;
    previewDocumentModal.value.src = `/inactive-history/${item.id}/file`;
    previewDocumentModal.value.show = true;
}

</script>

<template>

    <Head title="Historial de bajas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Historial de bajas</h2>
        </template>

        <div class="px-4 py-4 rounded-lg min-h-screen max-w-screen-2xl mx-auto">
            
            <!-- filter data area -->
            <!-- <div class="grid grid-cols-2 gap-2 px-2 pt-2 pb-4 bg-white border-x border-t dark:bg-gray-700 dark:border-gray-500">
                <div role="form-group" class="flex flex-col justify-end">
                    <SearchInput placeHolder="Nombre, curp, numero de empleado" v-on:search="handleInputSearch" />
                </div>
            </div> -->

            <!-- data table -->
            <table class="table-fixed w-full shadow text-sm text-left border rtl:text-right text-gray-500 dark:text-gray-400 dark:border-gray-500">
                <thead class="sticky top-0 z-20 text-xs uppercase text-gray-700 border bg-gradient-to-b from-gray-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 dark:text-gray-200 dark:border-gray-500">
                    <AnimateSpin v-if="loading" class="w-4 h-4 mx-2 absolute top-2.5" />
                    <tr>
                        <th scope="col" class="w-2/8 text-center px-6 py-3">
                            Empleado
                        </th>
                        <th scope="col" class="w-2/8 text-center px-6 py-3">
                            Unidad
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Estatus
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Fecha
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Comentarios
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody id="table-body" class="bg-white dark:bg-gray-800 dark:border-gray-500">
                    <template v-if="data && data.length > 0">
                        <tr v-for="hrecord in data" :key="hrecord.id" class="border-b">
                            <td class="p-2 text-center">
                                <div class="flex gap-2">
                                    <img :src="hrecord.employee.photo" class="h-8" alt="user"/>
                                    <div class="flex flex-col items-start">
                                        <div class="text-sm truncate">
                                            {{ hrecord.employee.name}}
                                        </div>
                                        <div v-if="hrecord.employee.computed_employee_number" class="text-xs">
                                            {{ hrecord.employee.computed_employee_number}}
                                        </div>
                                        <div v-else class="text-xs">Número de empleado desconocido</div>
                                    </div>
                                </div>
                            </td>

                            <td class="p-2 text-center">
                                <div v-if="hrecord.employee.general_direction" class="text-sm text-gray-900">{{ hrecord.employee.general_direction.abbreviation }} </div>
                                <div v-else class="text-sm text-gray-900">Desconocido</div>

                                <div v-if="hrecord.employee.direction" class="text-xs text-gray-400">{{ hrecord.employee.direction.name }}</div>
                                <div v-else class="text-xs text-gray-400">Desconocido</div>
                            </td>

                            <td class="p-2 text-center">
                                <BadgeGreen v-if="employeeIsActive(hrecord.status)" text="Alta" />
                                <BadgeRed v-else-if="employeeIsInactive(hrecord.status)" text="Baja" />
                                <BadgeBlue v-else :text="hrecord.status" class="mx-auto" />
                            </td>

                            <td class="p-2 text-center">
                                {{ formatDatetime(hrecord.created_at) }}
                            </td>

                            <td class="p-2 text-center">
                                {{ hrecord.comments }}
                            </td>

                            <td class="p-2 text-center">
                                <div class="flex gap-1 items-center justify-end">
                                    <div v-if="hrecord.file" class="flex items-center justify-center gap-1 shadow bg-slate-200 px-2 py-1 text-xs cursor-pointer" v-on:click="handleShowPdfClick(hrecord.id)">
                                        <PdfIcon class="w-4 h-4"/>
                                        <span>Justificante</span>
                                    </div>
                                    <NavLink v-if="hrecord.employee.computed_employee_number" :href="route('employees.show', hrecord.employee.computed_employee_number)">
                                        <div class="flex items-center justify-center gap-1 shadow bg-slate-200 px-2 py-1 text-xs">
                                            <span>Empleado</span>
                                            <ChevronRightIcon class="w-4 h-4"/>
                                        </div>
                                    </NavLink>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center font-medium whitespace-nowrap dark:text-white">
                                No hay registros de Empleados.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

        </div>

        <PreviewDocument v-if="previewDocumentModal.show"
            :title="previewDocumentModal.title"
            :subtitle="previewDocumentModal.subtitle"
            :src="previewDocumentModal.src"
            v-on:close="previewDocumentModal.show = false"
        />


    </AuthenticatedLayout>
</template>
