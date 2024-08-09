<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

import NavLink from '@/Components/NavLink.vue';
import SearchInput from '@/Components/SearchInput.vue';
import InputSelect from '@/Components/InputSelect.vue';
import BadgeBlue from '@/Components/BadgeBlue.vue';
import BadgeGreen from '@/Components/BadgeGreen.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    title: String,
    employees: Array
});

function handleInputSearch(search){

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
                <InputSelect>
                    <option disabled selected value="" >Direccion General</option>
                </InputSelect>

                <InputSelect>
                    <option disabled selected value="" >Subdireccion</option>
                </InputSelect>

                <InputSelect>
                    <option disabled selected value="" >Departamento</option>
                </InputSelect> 

                <SearchInput class="col-span-3" placeHolder="Nombre, curp, numero de empleado" v-on:search="handleInputSearch"/>
            </div>

            <table class="table-fixed w-full shadow text-sm text-left border rtl:text-right text-gray-500 dark:text-gray-400 dark:border-gray-500">
                <thead class="sticky top-0 z-20 text-xs uppercase text-gray-700 border bg-gradient-to-b from-gray-50 to-slate-100 dark:from-gray-800 dark:to-gray-700 dark:text-gray-200 dark:border-gray-500">
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
                            <td colspan="5" class="px-6 py-4 text-center font-medium whitespace-nowrap dark:text-white">
                                No hay registros de Empleados.
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <div class="w-100 flex justify-center my-2">
                <PrimaryButton>Cargar mas</PrimaryButton>
            </div>

        </div>

    </AuthenticatedLayout>
</template>
