<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';

import PageTitle from '@/Components/PageTitle.vue';
import Card from '@/Components/Card.vue';
import CardTitle from '@/Components/CardTitle.vue';
import CardText from '@/Components/CardText.vue';
import BadgeYellow from "@/Components/BadgeYellow.vue";
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import ExcelIcon from '@/Components/Icons/ExcelIcon.vue';
import CloudFailIcon from '@/Components/Icons/CloudFailIcon.vue';
import FileDownloadIcon from '@/Components/Icons/FileDownload.vue';

const props = defineProps({
    breadcrumbs: Object,
    generalDirections: Array,
    title: {
        type: String,
        default: "Generando reporte"
    },
    reportId: String
});

const toast = useToast();

const intervalId = ref(null);

const report = ref(null);

onMounted(()=>{
    startInterval();
});

onUnmounted(()=>{
    clearInterval(intervalId.value);
});

async function handleDownloadReport() {
    try {
        const response = await fetch(route('reports.monthly.download', report.value.fileName), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/octet-stream',
            },
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = report.value.fileName; // Ensure the download attribute is set
        document.body.appendChild(link);
        link.click();

        // Clean up
        document.body.removeChild(link); // Remove the link from the document
        URL.revokeObjectURL(url); // Release the object URL
    } catch (error) {
        toast.error("Ocurrió un error al descargar el archivo. Por favor, intente de nuevo en unos minutos.");
        console.log('Error downloading the file:', error);
    }
}

function startInterval(){
    intervalId.value = setInterval(() => {
        checkReportStatus();
    }, 2000);
}

function checkReportStatus(){

    axios.get( route("reports.monthly.verify", props.reportId))
    .then((response)=>{
        const {data} = response;

        if(data.status == "success" || data.status == "error" ){
            toast.info("Reporte generado.");
            clearInterval(intervalId.value);

            if( data.status == "success"){
                report.value = data.reportData;
            }

            if( data.status == "error"){
                report.value = {
                    error: data.message
                };
            }
        }
    }).
    catch((ex)=>{
        console.log(ex);
        clearInterval(intervalId.value);
        toast.error("Ocurrió un error al general el reporte. Por favor, intente de nuevo en unos minutos.");
        router.visit( route('reports.index'), {
            replace: true
        });
    });
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
                        
                        <div v-if="report ==null " class="py-4 flex items-center justify-center">
                            <AnimateSpin class="inline-block w-6 h-6 text-blue-500 mr-4" />
                            <div class="inline-block">
                                Su reporte se está generando, por favor espere, esto puede tardar unos minutos.
                            </div>
                        </div>

                        <div 
                            v-else-if="report && report.error" 
                            class="flex gap-2 items-center p-2 text-xs uppercase rounded-xl bg-red-100 text-red-600 mx-auto dark:bg-red-500 dark:text-red-100 border-2 border-white"
                        >
                            <CloudFailIcon class="text-red-400 w-20 h-20 mr-4" />

                            <div>
                                <CardTitle class="text-red-800">
                                    Ocurrió un error al generar el reporte. Por favor, Intente de nuevo en unos minutos.
                                </CardTitle>
                                <p class="text-xs text-red-800 mt-2">
                                    {{report.error }}
                                </p>
                            </div>
                        </div>

                        <div 
                            v-else="report" 
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
