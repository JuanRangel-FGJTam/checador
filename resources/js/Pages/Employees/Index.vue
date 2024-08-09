<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { debounce } from '@/utils/debounce.js';
import { useToast } from 'vue-toastification';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import NavLink from '@/Components/NavLink.vue';
import SearchInput from '@/Components/SearchInput.vue';
import InputSelect from '@/Components/InputSelect.vue';
import BadgeBlue from '@/Components/BadgeBlue.vue';
import BadgeGreen from '@/Components/BadgeGreen.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AnimateSpin from '@/Components/Icons/AnimateSpin.vue';

const props = defineProps({
    title: String,
    employees: Array,
    general_direction: Array,
    directions: Array,
    subdirectorate: Array,
    showMoreButton: Boolean,
    filters: Object,
});

const toast = useToast();

const form = useForm({
    search: "",
    gd: undefined,
    d: undefined,
    sd: undefined,
});

const loading = ref(false);

onMounted(()=>{
    form.gd = props.filters.gd ?? undefined;
    form.d = props.filters.d ?? undefined;
    form.sd = props.filters.sd ?? undefined;
});

function handleInputSearch(search){
    toast.info("Input search changed!");
}

function reloadData(){
    loading.value = true;
    debounce(()=>{
        // * prepare the query params
        var params = [];
        if(form.gd){
            params.push(`gd=${form.gd}`);
        }
        
        if(form.d){
            params.push(`d=${form.d}`);
        }
        
        if(form.sd){
            params.push(`sd=${form.sd}`);
        }
        
        // * reload the view
        router.visit("?" + params.join("&"), {
            method: 'get',
            only: ['employees', 'directions', 'subdirectorate'],
            preserveState: true,
            onError:(err)=>{
                toast.error("Error al obtener los datos");
            }
        });
        
    loading.value = false;
    }, 1000);
}

function handleGeneralDirectionSelect(){
    form.d = undefined;
    form.sd = undefined;
    reloadData();
}

function handleDirectionSelect(){
    form.sd = undefined;
    reloadData();
}

function handleSubDirectionSelect(){
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
                <InputSelect v-model="form.gd" v-on:change="handleGeneralDirectionSelect">
                    <option disabled selected value="" >Direccion General</option>
                    <option v-for="item in general_direction" :key="item.id" :value="item.id" > {{item.name }}</option>
                </InputSelect>

                <InputSelect v-model="form.d" v-on:change="handleDirectionSelect">
                    <option disabled selected value="">Direccion</option>
                    <option v-for="item in directions" :key="item.id" :value="item.id" > {{item.name }}</option>
                </InputSelect> 

                <InputSelect v-model="form.sd" v-on:change="handleSubDirectionSelect">
                    <option disabled selected value="">Subdireccion</option>
                    <option v-for="item in subdirectorate" :key="item.id" :value="item.id" > {{item.name }}</option>
                </InputSelect>

                <SearchInput class="col-span-3" placeHolder="Nombre, curp, numero de empleado" v-on:search="handleInputSearch"/>

            </div>

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
						        <div class="text-xs text-gray-400">{{ employee.generalDirection }}</div>
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
                                <div class="flex gap-2">
                                    <NavLink href="/dashboard" >Accion 1</NavLink>
                                    <NavLink href="/dashboard">Accion 2</NavLink>
                                </div>
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

            <div v-if="showMoreButton" class="flex justify-center my-2">
                <PrimaryButton>
                    Cargar mas datos
                </PrimaryButton>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
