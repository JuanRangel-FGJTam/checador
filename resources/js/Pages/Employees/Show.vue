<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Card from '@/Components/Card.vue';
import EmployeeGeneralData from './Partials/EmployeeGeneralData.vue';
import EmployeeDataPanel from './Partials/EmployeeDataPanel.vue';
import IncidenciasPanel from './Partials/IncidenciasPanel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    employeeNumber: String,
    employee: Object
});

const breadcrumbs = ref([
    { "name": 'Inicio', "href": '/dashboard' },
    { "name": 'Vista Empleados', "href": '/employees' },
    { "name": `Empleado: ${props.employeeNumber}`, "href": '' }
]);

</script>

<template>

    <Head title="Empleado - Mostrar" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <div class="grid grid-cols-12 my-4 p-4 gap-2 mx-auto w-screen max-w-screen-xl">

            <div class="col-span-7 bg-white shadow border rounded-lg p-4">
                <EmployeeGeneralData :employee="employee" />
            </div>

            <div class="col-span-5 bg-white shadow border rounded-lg p-4">
                <EmployeeDataPanel :employee="employee"/>
            </div>

            <div class="col-span-12 bg-white shadow border rounded-lg px-4 py-2">
                <div class="flex gap-4 justify-center">
                    <PrimaryButton>Generar Incidencias</PrimaryButton>
                    <PrimaryButton>Ver justificaciones</PrimaryButton>
                    <PrimaryButton>Justificar {dia seleccionado}</PrimaryButton>
                </div>
            </div>

            <div class="col-span-12 bg-white shadow border rounded-lg p-4">
                <div class="h-64">
                    Calendario
                </div>
            </div>

            <div class="col-span-12 bg-white shadow border rounded-lg p-4">
                <IncidenciasPanel />
            </div>
        </div>

    </AuthenticatedLayout>
</template>
