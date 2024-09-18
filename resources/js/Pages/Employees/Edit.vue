<script setup>
import { ref, onMounted } from 'vue';
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
    { "name": 'Vista Empleados', "href": route('employees.index')},
    { "name": `Empleado: ${props.employeeNumber}`, "href": route('employees.show', props.employeeNumber) },
    { "name": 'Editar Empleado', "href": '' }
]);

const form = useForm({
    general_direction_id:undefined,
    direction_id:undefined,
    subdirectorate_id:undefined,
    department_id: undefined,
    canCheck:1,
    status_id:undefined
});

onMounted(()=>{
    if(props.employee){
        form.general_direction_id = props.employee.generalDirectionId;
        form.direction_id = props.employee.directionId;
        form.subdirectorate_id = props.employee.subDirectionId;
        form.department_id = props.employee.departmentId;
        form.canCheck = props.employee.checa;
        form.status_id = props.employee.active === true ?1:0;
    }
});

function redirectBack(){
    router.visit( route('employees.show', props.employeeNumber), {
        replace: true
    } );
}

function submitForm(){
    form.patch( route('employees.update', props.employeeNumber), {
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
                            <InputLabel for="general_direction_id">Nivel 1 (Fiscalía, Dirección General, ...)</InputLabel>
                            <InputSelect id="general_direction_id" v-model="form.general_direction_id">
                                <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                <option v-for="item in generalDirections" :value="item.id" :key="item.id"> {{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.general_direction_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="direction_id">Nivel 2 (Dirección, Vicefiscalía, ...)</InputLabel>
                            <InputSelect id="direction_id" v-model="form.direction_id">
                                <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                <option v-for="item in directions" :value="item.id" :key="item.id"> {{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.direction_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="subdirectorate_id">Nivel 3 (Subdirección, Agencia, ...)</InputLabel>
                            <InputSelect id="subdirectorate_id" v-model="form.subdirectorate_id">
                                <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                <option v-for="item in subdirectorates" :value="item.id" :key="item.id"> {{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.subdirectorate_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="department_id">Nivel 4 (Departamento...) </InputLabel>
                            <InputSelect id="subdirectorate_id" v-model="form.department_id">
                                <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                <option v-for="item in deparments" :value="item.id" :key="item.id"> {{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.department_id" />
                        </div>

                        <div v-if="$page.props.auth.user.level_id == 1" role="form-group">
                            <InputLabel for="status_id">Estado del empleado</InputLabel>
                            <InputSelect id="status_id" v-model="form.status_id">
                                <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                <option v-for="item in statusEmployee" :key="item.id" :value="item.id">{{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.status_id" />
                        </div>

                    </div>

                    <CardText class=" font-bold mt-6">¿El Empleado Registra asistencia?</CardText>
                    <p> Seleccione No para quitar a <span class="text-bold"> '{{ employee.name }}'' </span> de los reportes.</p>
                    <div class="flex flex-col gap-2">
                        <ul class="flex flex-col gap-2 bg-gray-50 p-4 border-2 border-gray-100 rounded">
                            <li class="flex items-center gap-x-1">
                                <input type="radio" id="cancheck-yes" name="canCheck" v-model="form.canCheck" :value="1" class="rounded" />
                                <InputLabel for="cancheck-yes">SI</InputLabel>
                            </li>
                            <li class="flex items-center gap-x-1">
                                <input type="radio" id="cancheck-no" name="canCheck" v-model="form.canCheck" :value="0" class="rounded" />
                                <InputLabel for="cancheck-no">NO</InputLabel>
                            </li>
                        </ul>
                    </div>
                    <InputError :message="form.errors.canCheck" />

                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton type="button" v-on:click="redirectBack">Cancelar</DangerButton>
                        <SuccessButton type="submit">Actualizar</SuccessButton>
                    </div>
                </form>
            </template>
        </Card>

    </AuthenticatedLayout>
</template>
