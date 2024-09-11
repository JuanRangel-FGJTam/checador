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
    justification: Object,
    employeesResult: Array
});

const toast = useToast();

const loading = ref(false);

onMounted(()=>{
    if(props.employees){
        form.employees.push(...props.employees.map(element => element.id));
    }
});

</script>

<template>

    <Head title="Empleado - Justificar dia" />

    <AuthenticatedLayout>

        <PageTitle class="px-4 mt-4 text-center">
            Justificación aplicada
        </PageTitle>

        <Card class="max-w-screen-md mx-auto mt-4">
            <template #content>
                <form class="flex flex-col gap-2 select-none" @submit.prevent="submitForm">
                    
                    <p class="rounded bg-emerald-100 text-emerald-600 text-lg p-2 mb-4 text-center">Empleados afectados por la justificación.</p>

                    <div class="flex flex-col gap-y-4">
                        <ul class="list-outside max-h-[24rem] overflow-y-auto space-y-2">
                            <li v-for="employee in employeesResult" class="flex gap-2 items-center">
                                <label class="flex gap-1" :class="[ employee.ok ? 'text-gray-700': 'text-red-700' ]" >
                                    
                                    <svg v-if="employee.ok" class="w-4 h-4 m-1" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/></svg>
                                    
                                    <svg v-else class="w-4 h-4 m-1" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512"><path d="M96 64c0-17.7-14.3-32-32-32S32 46.3 32 64V320c0 17.7 14.3 32 32 32s32-14.3 32-32V64zM64 480c22.1 0 40-17.9 40-40s-17.9-40-40-40s-40 17.9-40 40s17.9 40 40 40z"/></svg>

                                    {{ employee.name }}
                                </label>
                            </li>
                        </ul>
                    </div>
                </form>
            </template>
        </Card>

    </AuthenticatedLayout>
</template>
