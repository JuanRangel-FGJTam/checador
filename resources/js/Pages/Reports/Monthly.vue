<script setup>
import { onMounted, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import NavLink from '@/Components/NavLink.vue';
import PageTitle from '@/Components/PageTitle.vue';
import Card from '@/Components/Card.vue';
import CardTitle from '@/Components/CardTitle.vue';
import CardText from '@/Components/CardText.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputDate from '@/Components/InputDate.vue';
import InputSelect from "@/Components/InputSelect.vue";
import InputError from '@/Components/InputError.vue';
import BadgeBlue from "@/Components/BadgeBlue.vue";
import BadgeGreen from "@/Components/BadgeGreen.vue";
import BadgeYellow from "@/Components/BadgeYellow.vue";
import BadgeRed from "@/Components/BadgeRed.vue";
import SuccessButton from '@/Components/SuccessButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import ExcelIcon from '@/Components/Icons/ExcelIcon.vue';

const props = defineProps({
    breadcrumbs: Object,
    generalDirections: Array,
    title: {
        type: String,
        default: "Generando reporte"
    },
    report: {
        type: Object,
        default: undefined
    }
});

const toast = useToast();

onMounted(()=>{
    // setTimeout(() => {
    //     router.reload({
    //         only: ['report'],
    //         onSuccess:()=>{
    //             toast.success("Reporte generado");
    //         }
    //     })
    // }, 1000);
});

function handleDownloadReport() {

    const url = route('reports.monthly.download', props.report.fileName );
    const link = document.createElement('a');
    link.href = url;
    document.body.appendChild(link);
    link.click();

    // Clean up
    window.URL.revokeObjectURL(url);
}

</script>

<template>

    <Head title="Generar Reportes" />

    <AuthenticatedLayout>

        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <div class="flex flex-col gap-6 pt-12 pb-4 py-4 rounded-lg max-w-screen-lg mx-auto select-none">

            <!-- employee data -->
            <Card class="outline outline-1 outline-gray-300 dark:outline-gray-500" :shadow="false">
                <template #header>
                    <PageTitle class="mx-auto">{{ title }}</PageTitle>
                </template>
                <template #content>
                    <div class="flex flex-col gap-4 items-center pt-4 pb-6">
                        
                        <BadgeYellow v-if="!report" class="py-4 w-[24rem] flex justify-center">
                            <div class="inline-block">Generando reporte...</div>
                            <AnimateSpin class="inline-block w-4 h-4 mx-1" />
                        </BadgeYellow>

                        
                        <div v-if="report" v-on:click="handleDownloadReport" class="flex gap-2 items-center p-2 text-xs uppercase rounded-xl bg-emerald-200 text-emerald-600 mx-auto dark:bg-emerald-500 dark:text-emerald-100 hover:bg-emerald-400 border-2 border-white outline outline-0 hover:outline-2 cursor-pointer">
                            
                            <ExcelIcon class="h-24 mx-4" />

                            <div class="flex flex-col gap-1 w-[20rem]">
                                <div class="flex flex-col gap-1 p-1 items-start">
                                    <CardTitle>Archivo</CardTitle>
                                    <CardText class="pl-2 overflow-auto"> {{ report.fileName }}</CardText>
                                </div>

                                <div class="flex gap-1 p-1 items-end">
                                    <CardTitle>Fecha</CardTitle>
                                    <CardText class="pl-2"> {{ report.date }}</CardText>
                                </div>

                                <div class="flex gap-1 p-1 items-end">
                                    <CardTitle>Genero</CardTitle>
                                    <CardText class="pl-2">{{ report.userName }}</CardText>
                                </div>

                                <div class="flex gap-1 p-1 items-end">
                                    <CardTitle>Tamaño</CardTitle>
                                    <CardText class="pl-2">{{ report.size }}</CardText>
                                </div>
                            </div>

                        </div>
                        

                    </div>
                </template>
            </Card>

        </div>

    </AuthenticatedLayout>
</template>
