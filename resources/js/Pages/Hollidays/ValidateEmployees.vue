<script setup>
import { ref, onMounted } from 'vue';
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
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import UploadIcon from '@/Components/Icons/UploadIcon.vue';
import FileCheckIcon from '@/Components/Icons/FileCheckIcon.vue';

const props = defineProps({
    justificationsType: Array,
    generalDirections: Array,
    employees: Array,
});

const toast = useToast();

const form = useForm({
    comments: "Put some comment here",
    employees: [ ]
});

const loading = ref(false);

onMounted(()=>{
    
    console.dir( props.employees.map(element => element.id) );

})

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

</script>

<template>

    <Head title="Empleado - Justificar dia" />

    <AuthenticatedLayout>

        <PageTitle class="px-4 mt-4 text-center">
            Justificación de días inhábiles | empleados a justificar 
        </PageTitle>

        <Card class="max-w-screen-md mx-auto mt-4">
            <template #content>
                <form class="flex flex-col gap-2" @submit.prevent="submitForm">
                    <div class="flex flex-col gap-y-4">

                        <p class="mt-2 p-2 text-center text-lg">Para continuar, por favor, confirme a los empleados que desea que se justifique.</p>

                        <p class="bg-yellow-100 text-yellow-600 text-lg p-2 text-center">Todo empleado que <strong>NO</strong> sea seleccionado, mantendra las incidencias (faltas y retardos) generadas durante el periodo de fechas seleccionado.</p>

                        <ul class="list-outside">
                            <li class="flex gap-2 items-center pb-3">
                                <input id="cb_todos" type="checkbox" class="rounded" />
                                <label for="cb_todos" class="uppercase font-bold"> Seleccionar Todos</label>
                            </li>
                            <li v-for="employee in employees" class="flex gap-2 items-center">
                                <input :id="`cb_${employee.id}`" type="checkbox" class="rounded" />
                                <label :for="`cb_${employee.id}`">{{ employee.name }}</label>
                            </li>
                        </ul>

                        <div role="form-group">
                            <InputLabel for="comments">Observaciones</InputLabel>
                            <textarea id="comments" v-model="form.comments" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-24 resize-none" />
                            <InputError :message="form.errors.comments" />
                        </div>

                    </div>

                    <div class="w-full text-center p-2 text-blue-700">
                        ¿Esta seguro que desea continuar con la Justificación del día 12/08/2024 al 13/08/2024?
                    </div>
                    
                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton class="ml-auto" type="button">Cancelar</DangerButton>
                        <SuccessButton class="ml-auto" type="submit">Confirmar empleados</SuccessButton>
                    </div>

                </form>
            </template>
        </Card>
 
    </AuthenticatedLayout>
</template>
