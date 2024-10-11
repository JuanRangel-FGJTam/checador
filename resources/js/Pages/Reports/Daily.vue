<script setup>
import { onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import PageTitle from '@/Components/PageTitle.vue';
import Card from '@/Components/Card.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import FileDownloadIcon from '@/Components/Icons/FileDownload.vue';

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
    setTimeout(() => {
        router.reload({
            only: ['report'],
            onSuccess:()=>{
                toast.success("Reporte generado");
            }
        })
    }, 1000);
});

function handleDownloadReport() {

    const url = route('reports.daily.download', props.report.fileName );
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
                        
                        <div v-if="!report" class="py-4 flex items-center justify-center">
                            <AnimateSpin class="inline-block w-6 h-6 text-blue-500 mr-4" />
                            <div class="inline-block">
                                Su reporte se está generando, por favor espere...
                            </div>
                        </div>

                        
                        <div 
                            v-if="report" 
                            v-on:click="handleDownloadReport" 
                            class="p-4 flex items-center p-2 rounded-xl bg-white border shadow-lg mx-auto hover:bg-emerald-100 outline outline-0 hover:outline-2 cursor-pointer"
                        >
                            <FileDownloadIcon class="w-20 h-20 text-emerald-500 mr-4" />

                            <div class="flex flex-col">
                                <p class="text-lg">
                                    Su reporte esta listo, haga clic aquí para descargarlo.
                                </p>
                                <p class="text-sm text-gray-400">Generado por {{ report.userName }}</p>
                                <p class="text-sm text-gray-400">{{ report.size }}</p>
                            </div>
                        </div>

                    </div>
                </template>
            </Card>

        </div>

    </AuthenticatedLayout>
</template>
