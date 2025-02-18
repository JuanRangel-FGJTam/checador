<script setup>
import { ref, onMounted, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import Card from '@/Components/Card.vue';
import CardText from '@/Components/CardText.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputSelect from '@/Components/InputSelect.vue';
import InputError from '@/Components/InputError.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import WhiteButton from '@/Components/WhiteButton.vue';
import FileCheckIcon from '@/Components/Icons/FileCheckIcon.vue';
import UploadIcon from '@/Components/Icons/UploadIcon.vue';


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
    canCheck:1
});

const formStatus = useForm({
    status_id: undefined,
    comments: "",
    file: undefined
});

const loading = ref(false);

const statusButtonText = computed(()=>{
    return (props.employee.active) ?"Desactivar empleado" : "Activar empleado";
});

onMounted(()=>{
    if(props.employee){
        form.general_direction_id = props.employee.generalDirectionId;
        form.direction_id = props.employee.directionId;
        form.subdirectorate_id = props.employee.subDirectionId;
        form.department_id = props.employee.departmentId;
        form.canCheck = props.employee.checa;

        formStatus.status_id = props.employee.active === true ?1:0;
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

function submitFormEmployeeStatus()
{
    formStatus.status_id = (props.employee.active) ?0: 1;
    formStatus.post(route('employees.update.status', props.employeeNumber), {
        replace: true,
        onError:(res)=>{
            const { message } = res;
            toast.error( message ?? "Error al actualizar el estatus");
        }
    });
}

function reloadCatalogs(queryParamsString){
    loading.value = true;
    router.visit('?' + queryParamsString, {
        only: ['generalDirections','directions', 'subdirectorates', 'deparments'],
        preserveState: true,
        onFinish: (()=>{
            loading.value = false;
        })
    });
}

function handleSelectChanged(e){
    var doomElementId = e.target.id;
    var queryParams = [];

    if(doomElementId == "general_direction_id"){
        queryParams.push(`gd=${form.general_direction_id}`);
        form.direction_id=undefined;
        form.subdirectorate_id=undefined;
        form.department_id=undefined;
        reloadCatalogs( queryParams.join('&') );
    }

    if(doomElementId == "direction_id"){
        queryParams.push(`gd=${form.general_direction_id}`);
        queryParams.push(`di=${form.direction_id}`);
        form.subdirectorate_id=undefined;
        form.department_id=undefined;
        reloadCatalogs( queryParams.join('&') );
    }

    if(doomElementId == "subdirectorate_id"){
        queryParams.push(`gd=${form.general_direction_id}`);
        queryParams.push(`di=${form.direction_id}`);
        queryParams.push(`sd=${form.subdirectorate_id}`);
        form.department_id=undefined;
        reloadCatalogs( queryParams.join('&') );
    }

}

</script>

<template>

    <Head title="Empleado - Horario Laboral" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <div class="h-fit overflow-y-auto pb-4">
            <Card class="max-w-screen-md mx-auto mt-4">
                <template #content>
                    <form class="flex flex-col gap-2" @submit.prevent="submitForm">
                        <div class="flex flex-col gap-2">

                            <div role="form-group">
                                <InputLabel for="general_direction_id">Nivel 1 (Fiscalía, Dirección General, ...)</InputLabel>
                                <InputSelect id="general_direction_id" v-model="form.general_direction_id" v-on:change="handleSelectChanged">
                                    <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                    <option v-for="item in generalDirections" :value="item.id" :key="item.id"> {{item.name}}</option>
                                </InputSelect>
                                <InputError :message="form.errors.general_direction_id" />
                            </div>

                            <div role="form-group">
                                <InputLabel for="direction_id">Nivel 2 (Dirección, Vicefiscalía, ...)</InputLabel>
                                <InputSelect id="direction_id" v-model="form.direction_id" v-on:change="handleSelectChanged">
                                    <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                    <option v-for="item in directions" :value="item.id" :key="item.id"> {{item.name}}</option>
                                </InputSelect>
                                <InputError :message="form.errors.direction_id" />
                            </div>

                            <div role="form-group">
                                <InputLabel for="subdirectorate_id">Nivel 3 (Subdirección, Agencia, ...)</InputLabel>
                                <InputSelect id="subdirectorate_id" v-model="form.subdirectorate_id" v-on:change="handleSelectChanged">
                                    <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                    <option v-for="item in subdirectorates" :value="item.id" :key="item.id"> {{item.name}}</option>
                                </InputSelect>
                                <InputError :message="form.errors.subdirectorate_id" />
                            </div>

                            <div role="form-group">
                                <InputLabel for="department_id">Nivel 4 (Departamento...) </InputLabel>
                                <InputSelect id="department_id" v-model="form.department_id" v-on:change="handleSelectChanged">
                                    <option value="" class="text-gray-600" >Seleccione un elemento</option>
                                    <option v-for="item in deparments" :value="item.id" :key="item.id"> {{item.name}}</option>
                                </InputSelect>
                                <InputError :message="form.errors.department_id" />
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

                            <SuccessButton v-if="!loading" type="submit">
                                Actualizar
                            </SuccessButton>
                            <WhiteButton v-else type="button">
                                <AnimateSpin class='w-4 h-4' />
                            </WhiteButton>
                        </div>
                    </form>
                </template>
            </Card>

            <Card class="max-w-screen-md mx-auto mt-4 border-2 border-red-600" v-if="$page.props.auth.user.level_id == 1">
                <template #content>
                    <form class="flex flex-col gap-2" @submit.prevent="submitFormEmployeeStatus">
                        <div class="flex flex-col gap-2">
                            <h2 class="text-gray-700 dark:text-gray-300 uppercase font-semibold border-b pb-1">Modificar Estatus del empleado</h2>
                            <div role="form-group">
                                <InputLabel for="general_direction_id">Comentarios</InputLabel>
                                <textarea id="comments" v-model="formStatus.comments" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-24 resize-none" />
                                <InputError :message="formStatus.errors.comments" />
                            </div>

                            <div role="form-group">
                                <InputLabel for="comments">Adjuntar archivo de justificación (Opcional)</InputLabel>
                                <InputLabel for="file" class="border rounded-lg p-2 text-white items-center justify-center cursor-pointer "
                                        :class="[ formStatus.file ?'bg-emerald-600 hover:bg-emerald-500' :'bg-slate-500 hover:bg-slate-400']"
                                    >
                                        <div class="flex items-center justify-center gap-1">
                                            <FileCheckIcon v-if="formStatus.file"  class="w-4 h-5 mx-1 inline-block" />
                                            <UploadIcon v-else class="w-4 h-5 mx-1 inline-block" />
                                            <div v-if="formStatus.file" class="inline-block text-xs truncate"> {{formStatus.file.name}} </div>
                                            <div v-else class="inline-block"> Seleccione el archivo</div>
                                            <input type="file" id="file" @input="formStatus.file = $event.target.files[0]" class="hidden"/>
                                        </div>
                                    </InputLabel>
                                <InputError :message="formStatus.errors.file" />
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-4 p-4 justify-center">
                            <DangerButton type="submit">
                                {{statusButtonText}}
                            </DangerButton>
                        </div>

                    </form>
                </template>
            </Card>
        </div>

    </AuthenticatedLayout>
</template>
