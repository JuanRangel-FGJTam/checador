<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import Card from '@/Components/Card.vue';
import CardTitle from '@/Components/CardTitle.vue';
import CardText from '@/Components/CardText.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputText from '@/Components/InputText.vue';
import InputSelect from '@/Components/InputSelect.vue';
import InputError from '@/Components/InputError.vue';
import InputDate from '@/Components/InputDate.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    employee: Object,
    employeeNumber: String,
    employeePhoto: String,
    generalDirections: Array,
    directions: Array,
    subdirectorates: Array,
    deparments: Array,
    statusEmployee: {
        type: Array,
        default: [ {id:1, name:"Activo"}, {id:0, name:"Baja"} ]
    },
    scheduleType: {
        type: Array,
        default: [
            {label:"Horario Corrido", value: 1},
            {label:"Horario Quebrado", value: 2},
        ]
    },
});

const toast = useToast();

const breadcrumbs = ref([
    { "name": 'Inicio', "href": '/dashboard' },
    { "name": 'Nuevos Empleados', "href": route('newEmployees.index')},
    { "name": 'Registrar Nuevo', "href": '' }
]);

const form = useForm({
    name: "",
    
    general_direction_id: undefined,
    direction_id: undefined,
    subdirectorate_id: undefined,
    department_id: undefined,

    scheduleType: 0,
    checkin: undefined,
    toeat: undefined,
    toarrive: undefined,
    checkout: undefined,
    midweek: true,
    weekend: false
});

onMounted(()=>{
    if(props.employee)
    {
        form.name =`${props.employee.NOMBRE} ${props.employee.APELLIDO}`;
    }
});

function redirectBack(){
    router.visit( route('newEmployees.index'), {
        replace: true
    } );
}

function submitForm(){
    form.post(route('newEmployees.store', props.employeeNumber), {
        replace: true,
        onError:(res)=>{
            const { message } = res;
            toast.error( message ?? "Error al actualizar el horario");
        }
    });
}

</script>

<template>
    <Head title="Registrar nuevo empleado" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>
        
        <form class="w-full" @submit.prevent="submitForm">
            <Card class="max-w-screen-md mx-auto mt-4">
                <template #header>
                    <CardTitle>
                        Datos Generales
                    </CardTitle>
                </template>

                <template #content>
                    <div class="grid grid-cols-3 gap-2 pb-[1rem]">
                        <div class="flex items-center justify-center row-span-3">
                            <img class="block w-44 h-44 aspect-auto rounded-lg shadow-lg object-cover" alt="Foto empleado" :src="props.employeePhoto" />
                        </div>
                        
                        <div class="col-span-2" role="form-group">
                            <InputLabel for="name">Nombre del empleado</InputLabel>
                            <InputText id="name" v-model="form.name" />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="col-span-1" role="form-group">
                            <InputLabel for="name">Numero de empleado</InputLabel>
                            <CardText class="px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"> {{ employee.NUMEMP }} </CardText>
                        </div>

                        <div class="col-span-1" role="form-group">
                            <InputLabel for="name">CURP</InputLabel>
                            <CardText class="px-3 py-2 bg-gray-100 border border-gray-300 text-gray-800 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"> {{ employee.CURP }} </CardText>
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="max-w-screen-md mx-auto mt-4">
                <template #header>
                    <CardTitle> Area asignada </CardTitle>
                </template>
                <template #content>
                    <div class="flex flex-col gap-2 pb-[1rem]">
                        <div role="form-group">
                            <InputLabel for="general_direction_id">Dirección General</InputLabel>
                            <InputSelect id="general_direction_id" v-model="form.general_direction_id">
                                <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                <option v-for="item in generalDirections" :value="item.id" :key="item.id"> {{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.general_direction_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="direction_id">Dirección, Vicefiscalía</InputLabel>
                            <InputSelect id="direction_id" v-model="form.direction_id">
                                <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                <option v-for="item in directions" :value="item.id" :key="item.id"> {{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.direction_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="subdirectorate_id">Subdirección, Agencia</InputLabel>
                            <InputSelect id="subdirectorate_id" v-model="form.subdirectorate_id">
                                <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                <option v-for="item in subdirectorates" :value="item.id" :key="item.id"> {{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.subdirectorate_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="department_id">Departamento</InputLabel>
                            <InputSelect id="subdirectorate_id" v-model="form.department_id">
                                <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                <option v-for="item in deparments" :value="item.id" :key="item.id"> {{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.department_id" />
                        </div>
                    </div>
                </template>
            </Card>

            <Card class="max-w-screen-md mx-auto mt-4 pb-[1rem]">
                <template #header>
                    <CardTitle> Horario </CardTitle>
                </template>
                <template #content>
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
                </template>
            </Card>

            <Card class="max-w-screen-md mx-auto my-4">
                <template #content>
                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton type="button" v-on:click="redirectBack">Cancelar</DangerButton>
                        <SuccessButton type="submit">Registrar empleado</SuccessButton>
                    </div>
                </template>
            </Card>
        </form>

    </AuthenticatedLayout>
</template>
