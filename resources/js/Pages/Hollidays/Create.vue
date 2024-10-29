<script setup>
import { onMounted, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { debounce } from '@/utils/debounce';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
import Breadcrumb from '@/Components/Breadcrumb.vue';
import UploadIcon from '@/Components/Icons/UploadIcon.vue';
import FileCheckIcon from '@/Components/Icons/FileCheckIcon.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import DisabledButton from '@/Components/DisabledButton.vue';

const props = defineProps({
    justificationsType: Array,
    generalDirections: Array,
    generalDirection: Object,
    breadcrumbs: Object,
    employees: Array
});

const toast = useToast();

const form = useForm({
    initialDay: undefined,
    endDay: undefined,
    type_id: undefined,
    file: undefined,
    general_direction: props.generalDirection != null ?props.generalDirection.id :undefined,
    employees: [],
    comments: undefined
});

const loading = ref(false);

onMounted(()=>{
    if(props.employees){
        form.employees.push(...props.employees.map(element => element.id));
    }
});

function submitForm(){
    form.post( route('hollidays.store'), {
        onError: (err)=>{
            const {message} = err;
            if( message) {
                toast.warning( message );
            }else{
                toast.warning("Campos invalidos o requeridos, Por favor, revisa los campos e intenta nuevamente.");
            }
        }
    });
}

function hangleGeneralDirectionChange(){
    loading.value = true;
    debounce(()=>{
        // * clear the employees of the form
        form.employees = [];

        // * reload and retrive the employees of the selected general direction
        router.visit(`?gd=${form.general_direction}`, {
            only: ['employees'],
            preserveState: true,
            preserveScroll: true,
            onSuccess: (res)=>{
                if(res.props.employees != null){
                    form.employees.push( ...res.props.employees.map(element => element.id));
                }
            },
            onFinish: ()=>{
                loading.value = false;
            }
        });
    },1000);
}

function handleListItemTodosChanged(e){
    const checked = e.target.checked;
    if(checked){
        form.employees = [...props.employees.map(element => element.id)];
    }else {
        form.employees = [];
    }
}

</script>

<template>

    <Head title="Empleado - Justificar dia" />

    <AuthenticatedLayout>

        <template #header v-if="breadcrumbs">
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <PageTitle class="px-4 mt-4 text-center">
            Justificación de días inhábiles
        </PageTitle>

        <Card class="max-w-screen-md mx-auto mt-4">
            <template #content>
                <form class="flex flex-col gap-2" @submit.prevent="submitForm">
                    <div class="flex flex-col gap-y-4 mb-2">

                        <p class="bg-yellow-100 text-yellow-600 text-lg my-2 p-2 text-center">Este apartado es única y exclusivamente para justificar días festivos o feriados considerados como <strong>días de descanso obligatorio</strong>.</p>

                        <div role="form-group">
                            <InputLabel for="initialDay">Fecha inicial de justificacion</InputLabel>
                            <InputDate id="initialDay" v-model="form.initialDay" />
                            <InputError :message="form.errors.initialDay" />
                        </div>
                        
                        <div role="form-group">
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
                            <InputError :message="form.errors.type_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="comments">Oficio o Cirular de autorización</InputLabel>
                            <InputLabel for="file" class="border rounded-lg p-2 text-white items-center justify-center cursor-pointer "
                                    :class="[ form.file ?'bg-emerald-600 hover:bg-emerald-500' :'bg-slate-500 hover:bg-slate-400']"
                                >
                                    <div class="flex items-center justify-center gap-1">
                                        <FileCheckIcon v-if="form.file"  class="w-4 h-5 mx-1 inline-block" />
                                        <UploadIcon v-else class="w-4 h-5 mx-1 inline-block" />
                                        <div v-if="form.file" class="inline-block text-xs truncate"> {{form.file.name}} </div>
                                        <div v-else class="inline-block"> Seleccione el archivo</div>
                                        <input type="file" id="file" @input="form.file = $event.target.files[0]" class="hidden"/>
                                    </div>
                                </InputLabel>
                            <InputError :message="form.errors.file" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="general_direction">Dirección General</InputLabel>
                            <InputSelect id="general_direction" v-model="form.general_direction" v-on:change=hangleGeneralDirectionChange
                                :disabled="$page.props.auth.user.level_id > 1"
                            >
                                <option value="" >Seleccione un elemento</option>
                                <option v-for="item in generalDirections" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </InputSelect>
                            <InputError :message="form.errors.general_direction" />
                        </div>

                    </div>
                </form>
            </template>
        </Card>

        <Card v-if="form.general_direction" class="max-w-screen-md mx-auto mt-4">
            <template v-if="loading" #content>
                <div class="flex flex-col items-center gap-y-2 pb-4">
                    <p class="mt-2 p-2 text-center text-lg">Cargando los empleados...</p>
                    <AnimateSpin class="w-6 h-6" />
                </div>
            </template>
            <template v-else #content>
                <form class="flex flex-col gap-2 select-none" @submit.prevent="submitForm">
                    <p class="mt-2 p-2 text-center text-lg">Para continuar, por favor, confirme a los empleados que desea que se justifique.</p>

                    <div class="flex flex-col gap-y-4">

                        <div v-if="employees" >
                            <p class="bg-yellow-100 text-yellow-600 text-lg p-2 mb-4 text-center">Todo empleado que <strong>NO</strong> sea seleccionado, mantendra las incidencias (faltas y retardos) generadas durante el periodo de fechas seleccionado.</p>

                            <ul class="list-outside">
                                <li class="flex gap-2 items-center pb-3">
                                    <input id="cb_todos" type="checkbox" class="rounded" checked v-on:input="handleListItemTodosChanged" />
                                    <label for="cb_todos" class="uppercase font-bold"> Seleccionar Todos ({{ props.employees.length }})</label>
                                </li>
                            </ul>
                            <ul class="list-outside max-h-[24rem] overflow-y-auto">
                                <li v-for="employee in employees" class="flex gap-2 items-center">
                                    <input :id="`${employee.id}`" type="checkbox" class="rounded" v-model="form.employees" :value="employee.id"/>
                                    <label :for="`cb_${employee.id}`">{{ employee.name }}</label>
                                </li>
                            </ul>
                            <InputError :message="form.errors.employees" />
                        </div>

                        <div v-else>
                            <p class="bg-yellow-100 text-yellow-600 text-lg p-2 text-center"><strong>NO</strong> hay empleados disponibles para justificar.</p>
                        </div>

                    </div>

                    <div role="form-group">
                        <InputLabel for="comments">Observaciones</InputLabel>
                        <textarea id="comments" v-model="form.comments" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-24 resize-none" />
                        <InputError :message="form.errors.comments" />
                    </div>

                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton class="mx-2 px-4" type="button">Cancelar</DangerButton>
                        <SuccessButton class="mx-2 px-2" type="submit">Justificar</SuccessButton>
                    </div>

                </form>
            </template>
        </Card>

    </AuthenticatedLayout>
</template>
