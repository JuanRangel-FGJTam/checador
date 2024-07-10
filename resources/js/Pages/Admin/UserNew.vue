<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

import Card from '@/Components/Card.vue';
import PageTitle from '@/Components/PageTitle.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputSelect from '@/Components/InputSelect.vue';
import InputText from '@/Components/InputText.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    generalDirections: Array,
    directions: Array,
    subdirectorates: Array,
    departments: Array
});

const form = useForm({
    generalDirection_id: 1,
    direction_id: 1,
    subdirectorate_id: 1,
    departments_id: 1,
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

function redirectBack(){
    router.visit( route('admin.index'), {
        replace: true
    } );
}

function submitForm(){
    form.post( route('admin.users.store'), {
        replace: true,
        onError:(res)=>{
            const { message } = res;
            alert( message ?? "Error no controlado al almacenar la evaluacion.");
        }
    });
}

</script>

<template>

    <Head title="Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Nuevo Usuario</h2>
        </template>

        <Card class=" max-w-screen-md mx-auto mt-2">
            <template #header>
                <PageTitle>Registrar nuevo usuarios</PageTitle>
            </template>

            <template #content>
                <form class="flex flex-col gap-2" @submit.prevent="submitForm">
                    <div role="form-group">
                        <InputLabel for="generalDirection_id">Nivel 1 (Fiscalía, Dirección General, ...)</InputLabel>
                        <InputSelect id="generalDirection_id" v-model="form.generalDirection_id">
                            <option v-for="item in generalDirections" :value="item.id">{{item.name}}</option>
                        </InputSelect>
                        <InputError :message="form.errors.generalDirection_id" />
                    </div>

                    <div role="form-group">
                        <InputLabel for="direction_id">Nivel 2 (Dirección, Vicefiscalía, ...)</InputLabel>
                        <InputSelect id="direction_id" v-model="form.direction_id">
                            <option v-for="item in directions" :value="item.id">{{item.name}}</option>
                        </InputSelect>
                        <InputError :message="form.errors.direction_id" />
                    </div>

                    <div role="form-group">
                        <InputLabel for="subdirectorate_id">Nivel 3 (Subdirección, Agencia, ...)</InputLabel>
                        <InputSelect id="subdirectorate_id" v-model="form.subdirectorate_id">
                            <option v-for="item in subdirectorates" :value="item.id">{{item.name}}</option>
                        </InputSelect>
                        <InputError :message="form.errors.subdirectorate_id" />
                    </div>

                    <div role="form-group">
                        <InputLabel for="departments_id">Nivel 4 (Departamento...)</InputLabel>
                        <InputSelect id="departments_id" v-model="form.departments_id">
                            <option v-for="item in departments" :value="item.id">{{item.name}}</option>
                        </InputSelect>
                        <InputError :message="form.errors.departments_id" />
                    </div>
                    
                    <div role="form-group">
                        <InputLabel for="name">Nombre</InputLabel>
                        <InputText id="name" v-model="form.name" />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div role="form-group">
                        <InputLabel for="email">Correo Electrónico</InputLabel>
                        <InputText id="email" v-model="form.email" type="email"/>
                        <InputError :message="form.errors.email" />
                    </div>

                    <div role="form-group">
                        <InputLabel for="password">Contraseña</InputLabel>
                        <InputText id="password" v-model="form.password" type="password"/>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div role="form-group">
                        <InputLabel for="password_confirmation">Confirmar Contraseña</InputLabel>
                        <InputText id="password_confirmation" v-model="form.password_confirmation" type="password"/>
                        <InputError :message="form.errors.password_confirmation" />
                    </div>

                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton type="button" v-on:click="redirectBack">Cancelar</DangerButton>
                        <SuccessButton type="submit">Registrar</SuccessButton>
                    </div>

                </form>
            </template>
           
        </Card>
    </AuthenticatedLayout>
</template>
