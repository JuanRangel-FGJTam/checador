<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

import Card from '@/Components/Card.vue';
import NavLink from '@/Components/NavLink.vue';

const props = defineProps({
    title: String,
    users: Array
});

</script>

<template>

    <Head title="Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Vista Administrador - {{ title }}</h2>
        </template>

        <div class="px-4 pt-4 rounded-lg min-h-screen max-w-screen-2xl mx-auto">
            <table class="w-full shadow text-sm text-left border rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="border text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-500">
                    <tr>
                        <th scope="col" class="w-1/6 text-center px-6 py-3">
                            Nombre
                        </th>
                        <th scope="col" class="w-1/6 text-center px-6 py-3">
                            Correo
                        </th>
                        <th scope="col" class="w-1/6 text-center px-6 py-3">
                            Nivel
                        </th>
                        <th scope="col" class="w-1/6 text-center px-6 py-3">
                            Direccion
                        </th>
                        <th scope="col" class="w-1/6 text-center px-6 py-3">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody id="table-body" class="bg-white dark:bg-gray-800 dark:border-gray-500">
                    <template v-if="users && users.length > 0">
                        <tr v-for="user in users" :key="user.id" class="border-b">
                            <td class="p-2 text-center">
                                <div class="flex gap-2 items-center">
                                    <div v-if="user.deleted_at" class="rounded-full w-3 h-3 bg-red-600" />
                                    <div v-else class="rounded-full w-3 h-3 bg-emerald-600" />
                                    {{ user.name}}
                                </div>
                            </td>
                            <td class="p-2">
                                {{ user.email}}
                            </td>
                            <td class="text-center p-2">
                                <span v-if="user.level_id || user.level_id == 0">
                                    {{ user.level_id }}
                                </span>
                                <span v-else>
                                    No disponible
                                </span>
                            </td>
                            <td class="text-center p-2">
                                <span v-if="user.general_direction">
                                    {{ user.general_direction.name }}
                                </span>
                                <span v-else>
                                    No disponible
                                </span>
                            </td>
                            <td class="text-center p-2">
                                <NavLink :href="route('admin.users.edit', user.id)">
                                    Editar
                                </NavLink>
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
        </div>

    </AuthenticatedLayout>
</template>
