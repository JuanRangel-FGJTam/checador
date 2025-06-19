<script setup>
import { ref, onMounted,computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { useToast } from 'vue-toastification';
import { formatDate } from '@/utils/date';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PreviewDocument from '@/Components/PreviewDocument.vue';

import WhiteButton from '@/Components/WhiteButton.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import PdfIcon from '@/Components/Icons/PdfIcon.vue';

const props = defineProps({
    title: String,
    justifications: Array,
    paginator: Object
});

const toast = useToast();

const loading = ref(false);

const previewDocumentModal = ref({
    show: false,
    title: "",
    subtitle: "",
    src: ""
});

const previousUrl = computed(() => {
    var page = Number(props.paginator.page) - 1;
    return `?p=${page}`;
});

const nextUrl = computed(() => {
    var page = Number(props.paginator.page) + 1;
    return `?p=${page}`;
});

onMounted(()=>{
    //
});

function handleShowPdfClick(id){
    var item = props.justifications.find( i => i.id == id);

    previewDocumentModal.value.title = `Justification ${item.type_name}`;
    previewDocumentModal.value.subtitle = `${formatDate(item.date_start)} - ${formatDate(item.date_finish)}`;
    previewDocumentModal.value.src = `/justifications/${item.id}/file`;
    previewDocumentModal.value.show = true;
}

</script>

<template>

    <Head title="Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Ultimos Justificantes</h2>
        </template>

        <div class="px-4 py-4 rounded-lg min-h-screen max-w-screen-2xl mx-auto">
            <table class="table-fixed w-full shadow text-sm text-left border rtl:text-right text-gray-700 dark:text-gray-400 dark:border-gray-500">
                <thead class="sticky top-0 z-20 text-xs uppercase text-gray-700 border bg-gradient-to-b from-gray-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 dark:text-gray-200 dark:border-gray-500">
                    <AnimateSpin v-if="loading" class="w-4 h-4 mx-2 absolute top-2.5" />
                    <tr>
                        <th scope="col" class="w-3/8 text-center px-6 py-3">
                            Empleado
                        </th>
                        <th scope="col" class="w-2/8 text-center px-6 py-3">
                            Justificacion
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Fecha Justificacion
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Fecha Registro
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Observaciones
                        </th>
                        <th scope="col" class="w-32 text-center px-6 py-3">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody id="table-body" class="bg-white dark:bg-gray-800 dark:border-gray-500">
                    <template v-if="justifications && justifications.length > 0">
                        <tr v-for="item in justifications" :key="item.id" :id="item.id" class="border-b">

                            <td class="p-2">
                                <div class="text-sm pl-1">
                                    {{ item.employee_name }}
                                </div>
                            </td>

                            <td class="p-2 text-center">
                                <div class="text-sm">
                                    {{ item.type_name }}
                                </div>
                            </td>

                            <td class="p-2 text-center uppercase">
                                <div class="flex items-center gap-1 justify-center">
                                    <span>{{formatDate(item.date_start)}}</span>
                                    <span v-if="item.date_finish && item.date_finish > '1970-01-01'">
                                        <svg aria-hidden="true" data-prefix="far" data-icon="long-arrow-right" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" class="mx-1 inline-block h-4 w-auto svg-inline--fa fa-long-arrow-right fa-w-14 fa-7x"><path fill="currentColor" d="M295.515 115.716l-19.626 19.626c-4.753 4.753-4.675 12.484.173 17.14L356.78 230H12c-6.627 0-12 5.373-12 12v28c0 6.627 5.373 12 12 12h344.78l-80.717 77.518c-4.849 4.656-4.927 12.387-.173 17.14l19.626 19.626c4.686 4.686 12.284 4.686 16.971 0l131.799-131.799c4.686-4.686 4.686-12.284 0-16.971L312.485 115.716c-4.686-4.686-12.284-4.686-16.97 0z" class=""></path>
                                        </svg>
                                        {{ formatDate(item.date_finish)}}
                                    </span>
                                </div>
                            </td>

                            <td class="p-2 text-center uppercase">
                                {{ formatDate(item.date_register)}}
                            </td>

                            <td class="p-2 text-center">
                                {{ item.details }}
                            </td>

                            <td class="p-2 text-center">
                                <div class="flex gap-2">
                                    <WhiteButton v-on:click="handleShowPdfClick(item.id)">
                                        <PdfIcon class="w-4 h-4 mr-1" />
                                        <span>Mostrar</span>
                                    </WhiteButton>
                                </div>
                            </td>

                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center font-medium whitespace-nowrap dark:text-white text-lg text-emerald-700">
                                No hay justificantes registrados para el empleado.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex justify-center items-center gap-4 mt-2 pb-[1rem]">
            <button
                class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition disabled:opacity-50"
                :disabled="!paginator.previous"
                @click="router.visit( previousUrl )"
            >
                Anterior
            </button>
            <span class="text-gray-700 dark:text-gray-200">
                Página {{ paginator.page }}
            </span>
            <button
                class="px-4 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition disabled:opacity-50"
                :disabled="!paginator.next"
                @click="router.visit( nextUrl )"
            >
                Siguiente
            </button>
        </div>

        <PreviewDocument v-if="previewDocumentModal.show"
            :title="previewDocumentModal.title"
            :subtitle="previewDocumentModal.subtitle"
            :src="previewDocumentModal.src"
            v-on:close="previewDocumentModal.show = false"
        />

    </AuthenticatedLayout>
</template>
