<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import PageTitle from '@/Components/PageTitle.vue';
import Card from '@/Components/Card.vue';
import CardText from '@/Components/CardText.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputSelect from "@/Components/InputSelect.vue";
import InputText from "@/Components/InputText.vue";
import InputDate from '@/Components/InputDate.vue';
import InputError from '@/Components/InputError.vue';
import BadgeGreen from "@/Components/BadgeGreen.vue";
import BadgeBlue from "@/Components/BadgeBlue.vue";
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';

const props = defineProps({
    employeeNumber: String,
    employee: Object,
    justificationsType: Array,
    initialDay: String,
    breadcrumbs: {
        type: Array,
        default: [
            { "name": 'Inicio', "href": '/dashboard' },
            { "name": 'Justificantes', "href": '/dashboard' },
            { "name": 'Justificar dia', "href": '' }
        ]
    }
});

const toast = useToast();

const form = useForm({
    employee_id: props.employee.id,
    multipleDays: false,
    initialDay: props.initialDay,
    endDay: undefined,
    type_id: undefined,
    comments: undefined,
    file: undefined
});

const loading = ref(false);

function redirectBack(){
    router.visit( route('employees.show', props.employeeNumber), {
        replace: true
    } );
}

function submitForm(){
    toast.warning("Not implemented");
}

</script>

<template>

    <Head title="Empleado - Justificar dia" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        
        <PageTitle class="px-4 mt-4 text-center">
            Justificar dia al empleado '{{ employee.name }}'
        </PageTitle>

        <Card class="max-w-screen-md mx-auto mt-4">
            <template #content>
                <form class="flex flex-col gap-2" @submit.prevent="submitForm">
                    <div class="flex flex-col gap-y-4">
                        
                        <div role="form-group" class="mt-4 select-none">
                            <InputLabel for="initialDay">¿Desea justificar varios días?</InputLabel>
                            <InputError :message="form.errors.multipleDays" />
                            <div class="flex flex-col gap-2">
                                <ul class="flex flex-col gap-2 bg-gray-50 p-2 border-2 border-gray-100 rounded">
                                    <li class="flex items-center gap-x-1">
                                        <input type="checkbox" id="multipleDays" v-model="form.multipleDays" class="rounded" />
                                        <label for="multipleDays">
                                            <CardText class="text-sm pl-2">Si, justificar un rango de dias.</CardText>
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div role="form-group">
                            <InputLabel for="initialDay">{{ form.multipleDays ? "Fecha inicial de justificacion" : "Fecha a justificar" }} </InputLabel>
                            <InputDate id="initialDay" v-model="form.initialDay" />
                            <InputError :message="form.errors.initialDay" />
                        </div>
                        
                        <div v-if="form.multipleDays==1" role="form-group">
                            <InputLabel for="endDay">Fecha final de justificacion</InputLabel>
                            <InputDate id="endDay" v-model="form.endDay" />
                            <InputError :message="form.errors.endDay" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="type_id">Tipo de justificacion</InputLabel>
                            <InputSelect id="type_id" v-model="form.type_id">
                                <option value="" >Seleccione un elemento</option>
                                <option v-for="item in justificationsType" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </InputSelect>
                            <InputError :message="form.errors.comments" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="comments">Observaciones</InputLabel>
                            <textarea id="comments" v-model="form.comments" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-24 resize-none" />
                            <InputError :message="form.errors.comments" />
                        </div>


                        <div role="form-group">
                            <InputLabel for="comments">Adjuntar archivo de justificación</InputLabel>
                            <input type="file" id="file" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 "/>
                            <InputError :message="form.errors.file" />
                        </div>

                    </div>

                    <div class="bg-amber-500 px-12 py-4 my-6 text-white border rounded-lg">
                        Antes de continuar, por favor, verifique las fechas seleccionadas
                    </div>
                    
                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton type="button" v-on:click="redirectBack">Cancelar</DangerButton>
                        <SuccessButton type="submit">Justificar</SuccessButton>
                    </div>

                </form>
            </template>
        </Card>
 
    </AuthenticatedLayout>
</template>
