<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

import Card from '@/Components/Card.vue';
import NavLink from '@/Components/NavLink.vue';

const props = defineProps({
    title: String,
    users: Array
});

const showDeleteModal = ref(false);
const userToDelete = ref(null);

const openDeleteModal = (user) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    userToDelete.value = null;
};

const confirmDelete = () => {
    if (userToDelete.value) {
        router.delete(route('admin.users.destroy', userToDelete.value.id), {
            onSuccess: () => {
                closeDeleteModal();
            },
            onError: (errors) => {
                console.error('Error deleting user:', errors);
            }
        });
    }
};

const showRestoreModal = ref(false);
const userToRestore = ref(null);
const openRestoreModal = (user) => {
    userToRestore.value = user;
    showRestoreModal.value = true;
};
const closeRestoreModal = () => {
    showRestoreModal.value = false;
    userToRestore.value = null;
};
const confirmRestore = () => {
    if (userToRestore.value) {
        router.post(route('admin.users.restore', userToRestore.value.id), {
            onSuccess: () => {
                closeRestoreModal();
            },
            onError: (errors) => {
                console.error('Error restoring user:', errors);
            }
        });
    }
};
</script>

<template>

    <Head title="Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ title }}
            </h2>
        </template>

        <div class="px-4 py-4 rounded-lg min-h-screen max-w-screen-xl mx-auto">
            <div class="flex justify-end items-center gap-4 mb-4">
                <Link 
                    :href="route('admin.users.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white font-semibold rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                >
                    Usuarios Activos
                </Link>
                <Link 
                    :href="route('admin.users.index', { inactives: true })"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white font-semibold rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                >
                    Usuarios de baja
                </Link>
                <Link 
                    :href="route('admin.users.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                >
                    Agregar Usuario
                </Link>
            </div>

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
                            <td class="text-center">
                                <div class="flex justify-center items-center gap-4">
                                    <button 
                                        @click="openDeleteModal(user)"
                                        v-if="!user.deleted_at"
                                        class="inline-flex items-center px-2 py-2 bg-red-600 text-white font-semibold rounded-md ml-2 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                                        type="button"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-1 14H6l-1-14m3-4h8m-4 0v4m0 0h4m-4 0H9m0 0V3m0 0H5a2 2 0 00-2 2v1a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2h-4z" />
                                        </svg>
                                    </button>
    
                                    <button
                                        v-else
                                        @click="openRestoreModal(user)"
                                        class="inline-flex items-center px-2 py-2 bg-green-600 text-white font-semibold rounded-md ml-2 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition duration-150 ease-in-out"
                                        type="button"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m6-6a9 9 0 11-18 0 9 9 0 0118 0zM12 3v1m0 16v1m-6.364-2.636l.707.707M4.929 4.929l.707.707M19.071 4.929l-.707.707M19.071 19.071l-.707-.707" />
                                        </svg>
                                    </button>
    
                                    <NavLink 
                                        v-show="!user.deleted_at"
                                        :href="route('admin.users.edit', user.id)"
                                    >
                                        Editar
                                    </NavLink>
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
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeDeleteModal"></div>

                <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg dark:bg-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                            Confirmar eliminación
                        </h3>
                        <button @click="closeDeleteModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            ¿Estás seguro de que quieres eliminar al usuario 
                            <span class="font-semibold">{{ userToDelete?.name }}</span>?
                            Esta acción no se puede deshacer.
                        </p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            @click="closeDeleteModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Cancelar
                        </button>
                        <button 
                            @click="confirmDelete"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Restore Confirmation Modal -->
        <div v-if="showRestoreModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeRestoreModal"></div>

                <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg dark:bg-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                            Confirmar restauración
                        </h3>
                        <button @click="closeRestoreModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            ¿Estás seguro de que quieres restaurar al usuario 
                            <span class="font-semibold">{{ userToRestore?.name }}</span>?
                            Esta acción no se puede deshacer.
                        </p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button 
                            @click="closeRestoreModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Cancelar
                        </button>
                        <button 
                            @click="confirmRestore"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            Restaurar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
