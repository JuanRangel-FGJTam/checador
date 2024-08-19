<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import Card from '@/Components/Card.vue';
import CardText from '@/Components/CardText.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputSelect from '@/Components/InputSelect.vue';
import InputDate from '@/Components/InputDate.vue';
import InputError from '@/Components/InputError.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    employeeNumber: String,
    employee: Object,
    scheduleType: {
        type: Array,
        default: [
            {label:"Horario Corrido", value: 1},
            {label:"Horario Quebrado", value: 2},
        ]
    },
    defaultValues: Object
});

const toast = useToast();

const breadcrumbs = ref([
    { "name": 'Inicio', "href": '/dashboard' },
    { "name": 'Vista Empleados', "href": route('employees.index')},
    { "name": `Empleado: ${props.employeeNumber}`, "href": route('employees.show', props.employeeNumber) },
    { "name": 'Editar Horario Laboral', "href": '' }
]);

const form = useForm({
    scheduleType: props.defaultValues.scheduleType,
    checkin: props.defaultValues.checkin,
    toeat: props.defaultValues.toeat,
    toarrive: props.defaultValues.toarrive,
    checkout: props.defaultValues.checkout,
    midweek: props.defaultValues.midweek==1,
    weekend: props.defaultValues.weekend==1,
});

function redirectBack(){
    router.visit( route('employees.show', props.employeeNumber), {
        replace: true
    } );
}

function submitForm(){
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

    <Head title="Empleado - Horario Laboral" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <Card class="max-w-screen-md mx-auto mt-4">

            <template #content>
                <form class="flex flex-col gap-2" @submit.prevent="submitForm">
                    <div class="flex flex-col gap-2">
                        <div role="form-group">
                            <InputLabel for="scheduleType">Tipo de Horario</InputLabel>
                            <InputSelect id="scheduleType" v-model="form.scheduleType">
                                <option v-for="t in scheduleType" :key="t.value" :value="t.value">{{t.label}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.scheduleType" />
                        </div>

                        <div role="form-group" class="mt-4">
                            <InputLabel for="checkin">Hora de entrada</InputLabel>
                            <InputDate type="time" id="checkin" v-model="form.checkin" />
                            <InputError :message="form.errors.checkin" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="toeat"> {{ form.scheduleType==2 ?"Salida a comer": "Hora de salida" }}</InputLabel>
                            <InputDate type="time" id="toeat" v-model="form.toeat" />
                            <InputError :message="form.errors.toeat" />
                        </div>

                        <div v-if="form.scheduleType==2" role="form-group" class="mt-8">
                            <InputLabel for="toarrive">Regreso de comer</InputLabel>
                            <InputDate type="time" id="toarrive" v-model="form.toarrive" />
                            <InputError :message="form.errors.toarrive" />
                        </div>

                        <div v-if="form.scheduleType==2" role="form-group">
                            <InputLabel for="checkout">Hola de salida</InputLabel>
                            <InputDate type="time" id="checkout" v-model="form.checkout" />
                            <InputError :message="form.errors.checkout" />
                        </div>

                    </div>

                    <CardText class=" font-bold text-lg mt-6">Días laborales (Puede seleccionar más de una opción)</CardText>
                    <InputError :message="form.errors.midweek_or_weekend" />
                    <div class="flex flex-col gap-2">
                        <ul class="flex flex-col gap-2 bg-gray-50 p-4 border-2 border-gray-100 rounded">
                            <li class="flex items-center gap-x-1">
                                <input type="checkbox" id="midweek" v-model="form.midweek" class="rounded" />
                                <CardText>De Lunes a Viernes</CardText>
                            </li>
                            <li class="flex items-center gap-x-1">
                                <input type="checkbox" id="weekend" v-model="form.weekend" class="rounded" />
                                <CardText>Sabados y Domingos</CardText>
                            </li>
                        </ul>
                    </div>

                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton type="button" v-on:click="redirectBack">Cancelar</DangerButton>
                        <SuccessButton type="submit">Actualizar</SuccessButton>
                    </div>
                </form>
            </template>
        </Card>

    </AuthenticatedLayout>
</template>
