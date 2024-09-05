<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import Card from '@/Components/Card.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputText from '@/Components/InputText.vue';
import InputSelect from '@/Components/InputSelect.vue';
import InputError from '@/Components/InputError.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';

const props = defineProps({
    employeeNumber: String,
    employee: Object,
    generalDirections: Array,
    directions: Array,
    subdirectorates: Array,
    deparments: Array,
    statusEmployee: {
        type: Array,
        default: [ {id:1, name:"Activo"}, {id:0, name:"Baja"} ]
    }
});

const toast = useToast();

const breadcrumbs = ref([
    { "name": 'Inicio', "href": '/dashboard' },
    { "name": 'Nuevos Empleados', "href": route('newEmployees.index')},
    { "name": 'Asignar Area', "href": '' }
]);

const form = useForm({
    name: "",
    general_direction_id: undefined,
    direction_id: undefined,
    subdirectorate_id: undefined,
    department_id: undefined
});

onMounted(()=>{
    if(props.employee){
        form.name = props.employee.name;
        form.general_direction_id = props.employee.generalDirectionId;
        form.direction_id = props.employee.directionId;
        form.subdirectorate_id = props.employee.subDirectionId;
        form.department_id = props.employee.departmentId;
    }
});

function redirectBack(){
    router.visit( route('newEmployees.index'), {
        replace: true
    } );
}

function submitForm(){
    form.patch( route('newEmployees.update', props.employeeNumber), {
        replace: true,
        onError:(res)=>{
            const { message } = res;
            toast.error( message ?? "Error al actualizar el horario");
        }
    });
}

</script>

<template>

    <Head title="Asignar area" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <Card class="max-w-screen-md mx-auto mt-4">

            <template #content>
                <form class="flex flex-col gap-2" @submit.prevent="submitForm">
                    <div class="flex flex-col gap-2">

                        <div role="form-group">
                            <InputLabel for="name">Nombre del empleado</InputLabel>
                            <InputText id="name" v-model="form.name" />
                            <InputError :message="form.errors.name" />
                        </div>

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

                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton type="button" v-on:click="redirectBack">Cancelar</DangerButton>
                        <SuccessButton type="submit">Asignar area</SuccessButton>
                    </div>
                </form>
            </template>
        </Card>

    </AuthenticatedLayout>
</template>
