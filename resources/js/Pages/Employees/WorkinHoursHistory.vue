<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { formatDate, formatDatetime } from '@/utils/date';

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
    workingHours: Array,
    breadcrumbs: {
        type: Array,
        default: [
            { "name": 'Inicio', "href": '/dashboard' },
            { "name": 'Justificantes', "href": '/dashboard' },
            { "name": 'Empleado', "href": '' }
        ]
    }
});

const toast = useToast();

const form = useForm({
    date: undefined,
    from: props.from,
    to: props.to
});

function redirectBack(){
    router.visit( route('employees.show', props.employeeNumber), {
        replace: true
    } );
}
</script>

<template>

    <Head title="Empleado - Justificantes" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <Card class="max-w-screen-lg mx-auto mt-4 pb-4">
            <template #header>
                <PageTitle class="px-4 mt-4 text-center">
                    Historial Modificaciones de Horario del empleado '{{ employee.name }}'
                </PageTitle>
            </template>

            <template #content>
                <table class="table-fixed w-full shadow text-sm text-left border rtl:text-right text-gray-700 dark:text-gray-400 dark:border-gray-500">
                    <thead class="sticky top-0 z-20 text-xs uppercase text-gray-700 border bg-gradient-to-b from-gray-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 dark:text-gray-200 dark:border-gray-500">
                        <AnimateSpin v-if="loading" class="w-4 h-4 mx-2 absolute top-2.5" />
                        <tr>
                            <th scope="col" class="w-2/8 text-center px-6 py-3">
                                Empleado
                            </th>
                            <th scope="col" class="w-2/8 text-center px-6 py-3 flex items-center justify-center gap-1">
                                <span>Fecha de Asignación</span>
                                <div class="inline-block h-4 w-4 opacity-50">
                                    <svg fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M151.6 469.6C145.5 476.2 137 480 128 480s-17.5-3.8-23.6-10.4l-88-96c-11.9-13-11.1-33.3 2-45.2s33.3-11.1 45.2 2L96 365.7V64c0-17.7 14.3-32 32-32s32 14.3 32 32V365.7l32.4-35.4c11.9-13 32.2-13.9 45.2-2s13.9 32.2 2 45.2l-88 96zM320 480c-17.7 0-32-14.3-32-32s14.3-32 32-32h32c17.7 0 32 14.3 32 32s-14.3 32-32 32H320zm0-128c-17.7 0-32-14.3-32-32s14.3-32 32-32h96c17.7 0 32 14.3 32 32s-14.3 32-32 32H320zm0-128c-17.7 0-32-14.3-32-32s14.3-32 32-32H480c17.7 0 32 14.3 32 32s-14.3 32-32 32H320zm0-128c-17.7 0-32-14.3-32-32s14.3-32 32-32H544c17.7 0 32 14.3 32 32s-14.3 32-32 32H320z"/></svg>
                                </div>
                            </th>
                            <th scope="col" class="w-2/8 text-center px-6 py-3">
                                Fecha Baja
                            </th>
                            <th scope="col" class="w-2/8 text-center px-6 py-3">
                                Horario
                            </th>
                        </tr>
                    </thead>
                    <tbody id="table-body" class="bg-white dark:bg-gray-800 dark:border-gray-500">
                        <template v-if="workingHours && workingHours.length > 0">
                            <tr v-for="item in workingHours" :key="item.id" :id="item.id" class="border-b">

                                <td class="p-2 text-center">
                                    <div class="text-sm">
                                        {{ employee.name }}
                                    </div>
                                </td>

                                <td class="p-2 text-center uppercase">
                                    <div class="d-flex flex-col items-center">
                                        {{ formatDatetime(item.created_at)}}
                                    </div>
                                </td>

                                <td class="p-2 text-center uppercase">
                                    <div v-if="item.deleted_at" class="d-flex flex-col items-center">
                                        {{ formatDatetime(item.deleted_at)}}
                                    </div>
                                </td>

                                <td class="p-2 text-center">
                                    <div v-if="item.toeat" class="d-flex flex-col items-center">
                                        <div>
                                            {{ item.checkin}}
                                            <svg aria-hidden="true" data-prefix="far" data-icon="long-arrow-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="mx-2 inline-block h-4 w-auto svg-inline--fa fa-long-arrow-right fa-w-14 fa-7x"><path fill="currentColor" d="M295.515 115.716l-19.626 19.626c-4.753 4.753-4.675 12.484.173 17.14L356.78 230H12c-6.627 0-12 5.373-12 12v28c0 6.627 5.373 12 12 12h344.78l-80.717 77.518c-4.849 4.656-4.927 12.387-.173 17.14l19.626 19.626c4.686 4.686 12.284 4.686 16.971 0l131.799-131.799c4.686-4.686 4.686-12.284 0-16.971L312.485 115.716c-4.686-4.686-12.284-4.686-16.97 0z" class=""></path></svg>
                                            {{ item.toeat}}
                                        </div>
                                        <div>
                                            {{ item.toarrive}}
                                            <svg aria-hidden="true" data-prefix="far" data-icon="long-arrow-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="mx-2 inline-block h-4 w-auto svg-inline--fa fa-long-arrow-right fa-w-14 fa-7x"><path fill="currentColor" d="M295.515 115.716l-19.626 19.626c-4.753 4.753-4.675 12.484.173 17.14L356.78 230H12c-6.627 0-12 5.373-12 12v28c0 6.627 5.373 12 12 12h344.78l-80.717 77.518c-4.849 4.656-4.927 12.387-.173 17.14l19.626 19.626c4.686 4.686 12.284 4.686 16.971 0l131.799-131.799c4.686-4.686 4.686-12.284 0-16.971L312.485 115.716c-4.686-4.686-12.284-4.686-16.97 0z" class=""></path></svg>
                                            {{ item.checkout}}
                                        </div>
                                    </div>

                                    <div v-else class="d-flex flex-col items-center">
                                        <div>
                                            {{ item.checkin}}
                                            <svg aria-hidden="true" data-prefix="far" data-icon="long-arrow-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="mx-2 inline-block h-4 w-auto svg-inline--fa fa-long-arrow-right fa-w-14 fa-7x"><path fill="currentColor" d="M295.515 115.716l-19.626 19.626c-4.753 4.753-4.675 12.484.173 17.14L356.78 230H12c-6.627 0-12 5.373-12 12v28c0 6.627 5.373 12 12 12h344.78l-80.717 77.518c-4.849 4.656-4.927 12.387-.173 17.14l19.626 19.626c4.686 4.686 12.284 4.686 16.971 0l131.799-131.799c4.686-4.686 4.686-12.284 0-16.971L312.485 115.716c-4.686-4.686-12.284-4.686-16.97 0z" class=""></path></svg>
                                            {{ item.checkout}}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center font-medium whitespace-nowrap dark:text-white text-lg text-emerald-700">
                                    No hay datos registrados para el empleado.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </template>
        </Card>

    </AuthenticatedLayout>
</template>
