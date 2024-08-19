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
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';
import Pagination from '@/Components/Paginator.vue';
import ChevronRightIcon from '@/Components/Icons/ChevronRightIcon.vue';

const props = defineProps({
    title: String,
    employees: Array,
    general_direction: Array,
    directions: Array,
    subdirectorate: Array,
    showPaginator: Boolean,
    filters: Object,
    paginator: {
        type: Object,
        default : {
            from: 0,
            to: 0,
            total: 0,
            pages: []
        }
    }
});

const toast = useToast();

const form = useForm({
    search: "",
    gd: 0,
    d: 0,
    sd: 0,
    page: 1
});

const loading = ref(false);

onMounted(()=>{
    form.gd = props.filters.gd ?? 0;
    form.d = props.filters.d ?? 0;
    form.sd = props.filters.sd ?? 0;
    form.p = props.filters.page ?? 1;
});

function handleInputSearch(search){
    toast.warning(`Searching ${search}, no implemented!`);
}

function reloadData(){
    loading.value = true;
    debounce(()=>{
        // * prepare the query params
        var params = [];
        if(form.gd){
            params.push(`gd=${form.gd}`);
        }
        
        if(form.d && form.d > 0){
            params.push(`d=${form.d}`);
        }
        
        if(form.sd && form.sd > 0){
            params.push(`sd=${form.sd}`);
        }

        if(form.page && form.page > 1){
            params.push(`p=${form.page}`);
        }
        
        // * reload the view
        router.visit("?" + params.join("&"), {
            method: 'get',
            only: ['employees', 'directions', 'subdirectorate', 'showPaginator', 'paginator'],
            preserveState: true,
            onError:(err)=>{
                toast.error("Error al obtener los datos");
            }
        });
        
    loading.value = false;
    }, 500);
}

function handleGeneralDirectionSelect(){
    form.d = 0;
    form.sd = 0;
    form.page = 1;
    reloadData();
}

function handleDirectionSelect(){
    form.sd = 0;
    form.page = 1;
    reloadData();
}

function handleSubDirectionSelect(){
    form.page = 1;
    reloadData();
}

function changePage(pageNumber){
    form.page = pageNumber;
    reloadData();
}

</script>

<template>

    <Head title="Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Vista Empleados</h2>
        </template>

        <div class="px-4 py-4 rounded-lg min-h-screen max-w-screen-2xl mx-auto">
            
            <!-- filter data area -->
            <div class="grid grid-cols-3 gap-2 px-2 pt-2 pb-4 bg-white border-x border-t dark:bg-gray-700 dark:border-gray-500">

                <div role="form-group" class="flex flex-col">
                    <InputLabel value="Direccion General" for="gd" />
                    <InputSelect id="gd" v-model="form.gd" v-on:change="handleGeneralDirectionSelect">
                        <option selected value="0">Todos</option>
                        <option v-for="item in general_direction" :key="item.id" :value="item.id" > {{item.name }}</option>
                    </InputSelect>
                </div>

                <div role="form-group" class="flex flex-col">
                    <InputLabel value="Direccion" for="d"/>
                    <InputSelect id="d" v-model="form.d" v-on:change="handleDirectionSelect">
                        <option selected value="0">Todos</option>
                        <option v-for="item in directions" :key="item.id" :value="item.id" > {{item.name }}</option>
                    </InputSelect>
                </div>

                <div role="form-group" class="flex flex-col">
                    <InputLabel value="Sub direccion" for="sd" />
                    <InputSelect id="sd" v-model="form.sd" v-on:change="handleSubDirectionSelect">
                        <option value="0">Todos</option>
                        <option v-for="item in subdirectorate" :key="item.id" :value="item.id" > {{item.name }}</option>
                    </InputSelect>
                </div>

                <SearchInput class="col-span-3" placeHolder="Nombre, curp, numero de empleado" v-on:search="handleInputSearch"/>

            </div>

            <!-- paginator -->
            <Pagination v-if="showPaginator"
                :paginator="paginator"
                :currentPage="form.page"
                v-on:changePage="changePage"
            />

            <!-- data table -->
            <table class="table-fixed w-full shadow text-sm text-left border rtl:text-right text-gray-500 dark:text-gray-400 dark:border-gray-500">
                <thead class="sticky top-0 z-20 text-xs uppercase text-gray-700 border bg-gradient-to-b from-gray-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 dark:text-gray-200 dark:border-gray-500">
                    <AnimateSpin v-if="loading" class="w-4 h-4 mx-2 absolute top-2.5" />
                    <tr>
                        <th scope="col" class="w-2/8 text-center px-6 py-3">
                            Nombre
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Numero Empleado
                        </th>
                        <th scope="col" class="w-2/8 text-center px-6 py-3">
                            Unidad
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Estatus
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Horario
                        </th>
                        <th scope="col" class="w-1/8 text-center px-6 py-3">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody id="table-body" class="bg-white dark:bg-gray-800 dark:border-gray-500">
                    <template v-if="employees && employees.length > 0">
                        <tr v-for="employee in employees" :key="employee.id" class="border-b">
                            <td class="p-2 text-center">
                                <div class="flex gap-2">
                                    <img :src="employee.photo" class="h-8" alt="user"/>
                                    <div class="flex flex-col items-start">
                                        <div class="text-sm truncate">{{ employee.name}}</div>
                                        <div class="text-xs">{{ employee.curp}}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="p-2 text-center">
                                {{ employee.employeeNumber}}
                            </td>

                            <td class="p-2 text-center">
                                <div class="text-sm text-gray-900">{{ employee.abbreviation }} </div>
                            <div class="text-xs text-gray-400">{{ employee.direction }}</div>
                            </td>

                            <td class="p-2 text-center">
                                <BadgeGreen v-if="employee.checa == 1" text="Checa" />
                                <BadgeBlue v-else text="No checa" class="mx-auto" />
                            </td>

                            <td class="p-2 text-center">
                                <div class="text-sm text-gray-900">{{ employee.days }} </div>
                            <div class="text-sm text-gray-400">{{ employee.horario }}</div>
                            </td>

                            <td class="p-2 text-center">
                                <NavLink :href=" route('employees.show', employee.employeeNumber)">
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
                            <td colspan="6" class="px-6 py-4 text-center font-medium whitespace-nowrap dark:text-white">
                                No hay registros de Empleados.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- paginator -->
            <Pagination v-if="showPaginator"
                :paginator="paginator"
                :currentPage="form.page"
                v-on:changePage="changePage"
            />

        </div>

    </AuthenticatedLayout>
</template>
