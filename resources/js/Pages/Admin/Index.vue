<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

import Card from '@/Components/Card.vue';
import PageTitle from '@/Components/PageTitle.vue';
import CatalogOptionCard from './Partials/CatalogOptionCard.vue';
import UserIconPlus from '@/Components/Icons/UserPlusIcon.vue';
import Users from '@/Components/Icons/Users.vue';
import UsersSlash from '@/Components/Icons/UsersSlash.vue';
import Landmark from '@/Components/Icons/Landmark.vue';

</script>

<template>

    <Head title="Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Vista Administrador</h2>
        </template>


        <Card class=" max-w-screen-xl mx-auto mt-4">
            <template #header>
                <PageTitle>Usuarios</PageTitle>
            </template>
            <template #content>
                <div class="flex flex-wrap gap-4 p-4 justify-around">
                    <CatalogOptionCard :href="route('admin.users.create')" title="Agregar Usuario">
                        <UserIconPlus class="w-12 h-12" />
                    </CatalogOptionCard>

                    <CatalogOptionCard :href="route('admin.users.index')" title="Usuarios Activos">
                        <Users class="w-12 h-12" />
                    </CatalogOptionCard>

                    <CatalogOptionCard href="/dashboard" title="Bajas">
                        <UsersSlash class="w-12 h-12" />
                    </CatalogOptionCard>
                </div>
            </template>
        </Card>

        <Card class=" max-w-screen-xl mx-auto mt-4">
            <template #header>
                <PageTitle>Catalogos</PageTitle>
            </template>
            <template #content>
                <div class="flex flex-wrap gap-4 p-4 justify-around">
                    <CatalogOptionCard :href="route('admin.catalogs.general-directions.index')" title="Direcciones Generales">
                        <Landmark class="w-12 h-12" />
                    </CatalogOptionCard>

                    <CatalogOptionCard href="/dashboard" title="Direcciones">
                        <Landmark class="w-12 h-12" />
                    </CatalogOptionCard>

                    <CatalogOptionCard href="/dashboard" title="Subdirecciones">
                        <Landmark class="w-12 h-12" />
                    </CatalogOptionCard>

                    <CatalogOptionCard href="/dashboard" title="Departamentos">
                        <Landmark class="w-12 h-12" />
                    </CatalogOptionCard>
                </div>
            </template>
        </Card>
        

    </AuthenticatedLayout>
</template>
