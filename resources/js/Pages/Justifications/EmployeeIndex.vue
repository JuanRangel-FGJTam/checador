<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { formatDate } from '@/utils/date';

import NavLink from '@/Components/NavLink.vue';
import PageTitle from '@/Components/PageTitle.vue';
import Card from '@/Components/Card.vue';
import WhiteButton from '@/Components/WhiteButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputDate from '@/Components/InputDate.vue';
import InputError from '@/Components/InputError.vue';
import PreviewDocument from '@/Components/PreviewDocument.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import PdfIcon from '@/Components/Icons/PdfIcon.vue';
import EditIcon from '@/Components/Icons/EditIcon.vue';

const props = defineProps({
    employeeNumber: String,
    employee: Object,
    justifications: Array,
    breadcrumbs: {
        type: Array,
        default: [
            { "name": 'Inicio', "href": '/dashboard' },
            { "name": 'Justificantes', "href": '/dashboard' },
            { "name": 'Empleado', "href": '' }
        ]
    },
    dateRange: String,
    from: String,
    to: String
});

const toast = useToast();

const form = useForm({
    date: undefined,
    from: props.from,
    to: props.to
});

const loading = ref(false);

const previewDocumentModal = ref({
    show: false,
    title: "",
    subtitle: "",
    src: ""
});


function redirectBack(){
    router.visit( route('employees.show', props.employeeNumber), {
        replace: true
    } );
}

function handleUpdateJustifications(){
    loading.value = true;

    var params = [];
    params.push(`from=${form.from}`);
    params.push(`to=${form.to}`);

    var _route = "?" + params.join("&");

    // * reload the view
    router.visit( _route, {
        only: ['justifications', 'dateRange', 'from', 'to'],
        preserveState: true,
        onError: (err)=>{
            const {message} = err;
            toast.error( message?? "Fail to reload the view");
        },
        onFinish: ()=>{
            loading.value = false;
        }
    });
}

function handleShowPdfClick(id){
    var item = props.justifications.find( i => i.id == id);

    previewDocumentModal.value.title = `Justification ${item.type.name}`;
    previewDocumentModal.value.subtitle = `${formatDate(item.date_start)} - ${formatDate(item.date_finish)}`;
    previewDocumentModal.value.src = `/justifications/${item.id}/file`;
    previewDocumentModal.value.show = true;
}

function handleEditClick(id){
    router.visit( route("justifications.edit", id) );
}

</script>

<template>

    <Head title="Empleado - Justificantes" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <Card class="max-w-screen-2xl mx-auto mt-4">
            <template #header>
                <PageTitle class="px-4 mt-4 text-center">
                    Justificantes del empleado '{{ employee.name }}' {{ dateRange }}
                </PageTitle>
            </template>

            <template #content>
                <div class="flex gap-2 items-end">
                    <div role="form-group">
                        <InputLabel for="from" value="Desde" />
                        <InputDate id="from" v-model="form.from" class="px-4" />
                    </div>

                    <div role="form-group">
                        <InputLabel for="to" value="Hasta" />
                        <InputDate id="to" v-model="form.to" class="px-4" />
                    </div>

                    <SuccessButton class="py-2.5 w-32 justify-center" v-on:click="handleUpdateJustifications">
                        <div>Actualizar</div>
                        <AnimateSpin v-if="loading" class="w-4 h-4 mx-1"/>
                    </SuccessButton>
                </div>

            </template>
        </Card>

        <div class="py-2 rounded-lg min-h-screen max-w-screen-2xl mx-auto">
            <!-- data table -->
            <table class="table-fixed w-full shadow text-sm text-left border rtl:text-right text-gray-700 dark:text-gray-400 dark:border-gray-500">
                <thead class="sticky top-0 z-20 text-xs uppercase text-gray-700 border bg-gradient-to-b from-gray-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 dark:text-gray-200 dark:border-gray-500">
                    <AnimateSpin v-if="loading" class="w-4 h-4 mx-2 absolute top-2.5" />
                    <tr>
                        <th scope="col" class="w-2/8 text-center px-6 py-3">
                            Justificacion
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Periodo
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
                    <template v-if="justifications && justifications.length > 0">
                        <tr v-for="item in justifications" :key="item.id" :id="item.id" class="border-b">

                            <td class="p-2 text-center">
                                <div class="text-sm">
                                    {{ item.type.name }}
                                </div>
                            </td>

                            <td class="p-2 text-center uppercase">
                                <div class="d-flex items-center">
                                    <spa>{{ formatDate(item.date_start)}}</spa>
                                    <span v-if="item.date_finish && item.date_finish > '1970-01-01'">
                                        <svg aria-hidden="true" data-prefix="far" data-icon="long-arrow-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="mx-2 inline-block h-4 w-auto svg-inline--fa fa-long-arrow-right fa-w-14 fa-7x"><path fill="currentColor" d="M295.515 115.716l-19.626 19.626c-4.753 4.753-4.675 12.484.173 17.14L356.78 230H12c-6.627 0-12 5.373-12 12v28c0 6.627 5.373 12 12 12h344.78l-80.717 77.518c-4.849 4.656-4.927 12.387-.173 17.14l19.626 19.626c4.686 4.686 12.284 4.686 16.971 0l131.799-131.799c4.686-4.686 4.686-12.284 0-16.971L312.485 115.716c-4.686-4.686-12.284-4.686-16.97 0z" class=""></path>
                                        </svg>
                                        {{ formatDate(item.date_finish)}}
                                    </span>
                                </div>
                            </td>

                            <td class="p-2 text-center">
                                {{ item.details }}
                            </td>

                            <td class="p-2 text-center">
                                <div class="flex gap-2">
                                    <WhiteButton v-on:click="handleShowPdfClick(item.id)">
                                        <PdfIcon class="w-4 h-4 mr-1" />
                                        <span>PDF</span>
                                    </WhiteButton>

                                    <WhiteButton v-on:click="handleEditClick(item.id)">
                                        <EditIcon class="w-4 h-4 mr-1" />
                                        <span>Editar</span>
                                    </WhiteButton>
                                </div>
                            </td>

                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center font-medium whitespace-nowrap dark:text-white text-lg text-emerald-700">
                                No hay justificantes registrados para el empleado.
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
