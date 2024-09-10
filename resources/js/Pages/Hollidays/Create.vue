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
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import UploadIcon from '@/Components/Icons/UploadIcon.vue';
import FileCheckIcon from '@/Components/Icons/FileCheckIcon.vue';

const props = defineProps({
    justificationsType: Array,
    generalDirections: Array,
    breadcrumbs: Object
});

const toast = useToast();

const form = useForm({
    initialDay: undefined,
    endDay: undefined,
    type_id: undefined,
    file: undefined,
    general_direction: undefined
});

const loading = ref(false);

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

        <template #header v-if="breadcrumbs">
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        
        <PageTitle class="px-4 mt-4 text-center">
            Justificación de días inhábiles 
        </PageTitle>

        <Card class="max-w-screen-md mx-auto mt-4">
            <template #content>
                <form class="flex flex-col gap-2" @submit.prevent="submitForm">
                    <div class="flex flex-col gap-y-4">

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
                            <InputSelect id="general_direction" v-model="form.general_direction">
                                <option value="" >Seleccione un elemento</option>
                                <option v-for="item in generalDirections" :value="item.id" :key="item.id">{{ item.name }}</option>
                            </InputSelect>
                            <InputError :message="form.errors.general_direction" />
                        </div>

                    </div>
                    
                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <SuccessButton class="ml-auto" type="submit">Confirmar empleados</SuccessButton>
                    </div>

                </form>
            </template>
        </Card>
 
    </AuthenticatedLayout>
</template>
