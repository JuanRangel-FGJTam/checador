<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import PageTitle from '@/Components/PageTitle.vue';
import Card from '@/Components/Card.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputDate from '@/Components/InputDate.vue';
import InputError from '@/Components/InputError.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    employeeNumber: String,
    employee: Object,
    options: Object
});

const toast = useToast();

const breadcrumbs = ref([
    { "name": 'Inicio', "href": '/dashboard' },
    { "name": 'Vista Empleados', "href": route('employees.index')},
    { "name": `Empleado: ${props.employeeNumber}`, "href": route('employees.show', props.employeeNumber) },
    { "name": 'Crear incidencia', "href": '' }
]);

const form = useForm({
    date: undefined,
    comments: undefined
});

function redirectBack(){
    router.visit( route('employees.show', props.employeeNumber), {
        replace: true
    } );
}

function submitForm(){
    toast.warning("Not implemented");
    return;

    form.patch( route('employees.schedule.update', props.employeeNumber), {
        replace: true,
        onError:(res)=>{
            const { message } = res;
            toast.error( message ?? "Error al actualizar el horario");
        }
    });
}

</script>

<template>

    <Head title="Empleado - Creare incidencia" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <Card class="max-w-screen-md mx-auto mt-4">

            <template #content>
                <form class="flex flex-col gap-2" @submit.prevent="submitForm">

                    <h1 class="py-4 font-semibold uppercase text-gray-600 dark:text-gray-200 leading-tight text-lg">Generar incidencias del empleado '{{ employee.name }}'</h1>

                    <div role="form-group" class="mt-4">
                        <InputLabel for="date">Selecciona el día a calcular las incidencias</InputLabel>
                        <InputDate id="date" v-model="form.date" />
                        <InputError :message="form.errors.date" />
                    </div>

                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton type="button" v-on:click="redirectBack">Cancelar</DangerButton>
                        <SuccessButton type="submit">Calcular</SuccessButton>
                    </div>

                </form>
            </template>
        </Card>

    </AuthenticatedLayout>
</template>
