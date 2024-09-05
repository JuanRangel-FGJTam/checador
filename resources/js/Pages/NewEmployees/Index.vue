<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { debounce } from '@/utils/debounce.js';
import { useToast } from 'vue-toastification';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import NavLink from '@/Components/NavLink.vue';
import SearchInput from '@/Components/SearchInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputSelect from '@/Components/InputSelect.vue';
import BadgeBlue from '@/Components/BadgeBlue.vue';
import BadgeGreen from '@/Components/BadgeGreen.vue';
import ChevronRightIcon from '@/Components/Icons/ChevronRightIcon.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';

const props = defineProps({
    employees: Array,
    general_direction: Array,
    directions: Array,
    subdirectorate: Array,
    searchString: {
        type: String,
        default: undefined
    }
});

const toast = useToast();

const loading = ref(false);

function handleInputSearch(search){
    loading.value = true;
    
    debounce(()=>{
        var queryParams = '';
        if(search){
            queryParams = 's=' + search;
        }
        router.visit('?'+ queryParams, {
            replace: true,
            preserveState: true,
            only: ['employees'],
            onError:()=>{
                toast.error("Error al recargar los datos.")
            },
            onFinish:()=>{
                loading.value = false;
            }
        });
    }, 100);
}

</script>

<template>

    <Head title="Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Vista Empleados</h2>
        </template>

        <div class="px-4 py-4 rounded-lg min-h-screen max-w-screen-xl mx-auto">

            <div class="bg-white border-l border-t border-r dark:border-gray-500 flex items-center p-2">
                <SearchInput v-on:search="handleInputSearch" :initialValue="props.searchString" />
                <AnimateSpin v-if="loading" class="w-5 h-5 mx-2" />
            </div>

            <!-- data table -->
            <table class="table w-full shadow text-sm text-left border rtl:text-right text-gray-500 dark:text-gray-400 dark:border-gray-500">
                <thead class="sticky top-0 z-20 text-xs uppercase text-gray-700 border bg-gradient-to-b from-gray-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 dark:text-gray-200 dark:border-gray-500">
                    <tr>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            #
                        </th>
                        <th scope="col" class="w-3/8 text-center px-6 py-3">
                            Nombre
                        </th>
                        <th scope="col" class="w-2/8 text-center px-6 py-3">
                            Numero de empleado
                        </th>
                        <th scope="col" class="w-2/8 text-center px-6 py-3">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody id="table-body" class="bg-white dark:bg-gray-800 dark:border-gray-500">
                    <template v-if="employees && employees.length > 0">
                        <tr v-for="(employee, index) in employees" :key="employee.id" class="border-b">
                            <td class="p-2 text-center">
                                {{index + 1}}
                            </td>

                            <td class="p-2">
                                {{ employee.name }}
                            </td>

                            <td class="p-2 text-center">
                                {{ employee.employeeNumber }}
                            </td>

                            <td class="p-2 text-center">
                                <NavLink :href=" route('employees.show', employee.employeeNumber )">
                                    <div class="flex gap-2 shadow bg-slate-200 px-4 py-1">
                                        <span>Asistencia</span>
                                        <ChevronRightIcon class="w-4 h-4 ml-1" />
                                    </div>
                                </NavLink>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center font-medium whitespace-nowrap dark:text-white">
                                No hay registros de Empleados.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

        </div>

    </AuthenticatedLayout>
</template>
