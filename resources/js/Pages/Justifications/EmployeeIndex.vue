<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import NavLink from '@/Components/NavLink.vue';
import PageTitle from '@/Components/PageTitle.vue';
import Card from '@/Components/Card.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
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
    justifications: Array,
    breadcrumbs: {
        type: Array,
        default: [
            { "name": 'Inicio', "href": '/dashboard' },
            { "name": 'Justificantes', "href": '/dashboard' },
            { "name": 'Empleado', "href": '' }
        ]
    },
    dateRange: String
});

const toast = useToast();

const form = useForm({
    date: undefined
});

const loading = ref(false);

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

        
        <PageTitle class="px-4 mt-4 text-center">
            Justificantes del empleado '{{ employee.name }}' {{ dateRange }}
        </PageTitle>

        <div class="px-4 py-4 rounded-lg min-h-screen max-w-screen-2xl mx-auto">
            
            <!-- data table -->
            <table class="table-fixed w-full shadow text-sm text-left border rtl:text-right text-gray-500 dark:text-gray-400 dark:border-gray-500">
                <thead class="sticky top-0 z-20 text-xs uppercase text-gray-700 border bg-gradient-to-b from-gray-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 dark:text-gray-200 dark:border-gray-500">
                    <AnimateSpin v-if="loading" class="w-4 h-4 mx-2 absolute top-2.5" />
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
                    <template v-if="justifications && justifications.length > 0">
                        <tr v-for="item in justifications" :key="item.id" :id="item.id" class="border-b">

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
 
    </AuthenticatedLayout>
</template>
