<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { formatDate } from '@/utils/date';

import PageTitle from '@/Components/PageTitle.vue';
import Card from '@/Components/Card.vue';
import CardText from '@/Components/CardText.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputSelect from "@/Components/InputSelect.vue";
import InputDate from '@/Components/InputDate.vue';
import InputError from '@/Components/InputError.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import PreviewDocument from '@/Components/PreviewDocument.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import UploadIcon from "@/Components/Icons/UploadIcon.vue";
import FileCheckIcon from "@/Components/Icons/FileCheckIcon.vue";

const props = defineProps({
    employeeNumber: String,
    employee: Object,
    justificationsType: Array,
    justify: Object,
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
    multipleDays: props.justify.date_finish != null,
    initialDay: undefined,
    endDay: undefined,
    type_id: props.justify.type_justify_id,
    comments: props.justify.details,
    file: undefined,
    __old_file_name: props.justify.file
});

const loading = ref(false);

const previewDocumentModal = ref({
    show: false,
    title: `Justification ${props.justify.type.name}`,
    subtitle: `${formatDate(props.justify.date_start)} - ${formatDate(props.justify.date_finish)}`,
    src: `/justifications/${props.justify.id}/file`
});

onMounted(()=>{
    try {
        var start = new Date(props.justify.date_start);
        form.initialDay = start.toISOString().split("T")[0];
    }catch(err){}

    if(props.justify.date_finish){
        try{
            var end = new Date(props.justify.date_finish);
            form.endDay = end.toISOString().split("T")[0];
        }catch(err){}
    }

    // load preview modal
    if(props.justify){
        previewDocumentModal.value.title = `Justification ${props.justify.type.name}`;
        previewDocumentModal.value.subtitle = `${formatDate(props.justify.date_start)} - ${formatDate(props.justify.date_finish)}`;
        previewDocumentModal.value.src = `/justifications/${props.justify.id}/file`;
    }

});

function redirectBack(){
    router.visit( route('employees.show', props.employeeNumber), {
        replace: true
    } );
}

function submitForm(){
    form.post( route('justifications.update', props.justify.id), {
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

function handleShowDocument(){
    previewDocumentModal.value.show = true;
}

</script>

<template>

    <Head title="Empleado - Editar justificante" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        
        <PageTitle class="px-4 mt-4 text-center">
            Editando justificante del empleado '{{ employee.name }}'
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
                            <InputError :message="form.errors.type_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="comments">Observaciones</InputLabel>
                            <textarea id="comments" v-model="form.comments" class="bg-gray-50 border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 h-24 resize-none" />
                            <InputError :message="form.errors.comments" />
                        </div>

                        <div role="form-group" class="grid grid-cols-2 gap-2">
                            <div role="form-group" class="border-r pr-1">
                                <InputLabel for="file" value="Nuevo documento"/>
                                <InputLabel for="file" class="border rounded-lg p-2 text-white items-center justify-center cursor-pointer " 
                                    :class="[ form.file ?'bg-emerald-600 hover:bg-emerald-500' :'bg-slate-500 hover:bg-slate-400']"
                                >
                                    <div class="flex items-center justify-center gap-1">
                                        <FileCheckIcon v-if="form.file" class="w-4 h-5 mx-1 pb-1 inline-block" />
                                        <UploadIcon v-else class="w-4 h-5 mx-1 pb-1 inline-block" />
                                        <div v-if="form.file" class="inline-block text-xs truncate"> {{form.file.name}} </div>
                                        <div v-else class="inline-block text-xs"> Adjuntar nuevo archivo de justificación </div>
                                        <input type="file" id="file" @input="form.file = $event.target.files[0]" class="hidden"/>
                                    </div>
                                </InputLabel>
                                <InputError :message="form.errors.file" />
                            </div>

                            <div role="form-group">
                                <div role="form-group" class="border-r pr-1">
                                    <InputLabel for="file2" value="Documento actual"/>
                                    <InputLabel for="file2" class="border rounded-lg p-2 text-white items-center justify-center cursor-pointer bg-emerald-600 hover:bg-emerald-500" v-on:click="handleShowDocument">
                                        <div class="flex items-center justify-center gap-1">
                                            <FileCheckIcon class="w-4 h-5 mx-1 pb-1 inline-block" />
                                            <div class="inline-block text-xs truncate"> {{ form.__old_file_name}} </div>
                                            <input type="file" id="file" @input="form.file = $event.target.files[0]" class="hidden"/>
                                        </div>
                                    </InputLabel>
                                    <InputError :message="form.errors.file" />
                                </div>

                            </div>
                        </div>

                    </div>

                    <div class="bg-amber-500 px-12 py-4 my-6 text-white border rounded-lg">
                        Antes de continuar, por favor, verifique las fechas seleccionadas
                    </div>
                    
                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton type="button" v-on:click="redirectBack">Cancelar</DangerButton>
                        <SuccessButton type="submit">Actualizar</SuccessButton>
                    </div>

                </form>
            </template>
        </Card>

        <PreviewDocument v-if="previewDocumentModal.show"
            :title="previewDocumentModal.title"
            :subtitle="previewDocumentModal.subtitle"
            :src="previewDocumentModal.src"
            v-on:close="previewDocumentModal.show = false"
        />
 
    </AuthenticatedLayout>
</template>
