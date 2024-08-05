<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import NavLink from '@/Components/NavLink.vue';

const props = defineProps({
    data: Array
});

const breadcrumbs = ref([
    { "name": 'Inicio', "href": '/dashboard' },
    { "name": 'Catalogos', "href": '/admin' },
    { "name": 'Sub Direcciones', "href": '' }
]);

function handleNewElementClick(){
    router.visit( route('admin.catalogs.sub-directions.create'));
}

</script>

<template>

    <Head title="Catalogo Sub Direcciones" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <div class="mx-auto my-4 p-4 rounded-lg max-w-screen-xl bg-white dark:bg-gray-600 dark:border-gray-500">

            <div class="flex gap-2 pb-2 justify-end">
                <PrimaryButton v-on:click="handleNewElementClick">
                    Agregar nuevo
                </PrimaryButton>
            </div>
            
            <table class="w-full shadow text-sm text-left border rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs border text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="w-3/6 text-center px-6 py-3">
                            Nombre
                        </th>
                        <th scope="col" class="w-2/6 text-center px-6 py-3">
                            Direccion
                        </th>
                        <th scope="col" class="w-1/6 text-center px-6 py-3">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <template v-if="data">
                        <tr v-for="item in data" :key="item.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <td class="p-2">
                                {{ item.name }}
                            </td>
                            <td class="p-2 text-center">
                                {{ item.direction.name}}
                            </td>
                            <td class="p-2 text-center">
                                <NavLink class="px-2 py-0.5 rounded" :href="route('admin.catalogs.sub-directions.edit', item.id)">
                                    Editar
                                </NavLink>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                No hay registros
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

        </div>

    </AuthenticatedLayout>
</template>
