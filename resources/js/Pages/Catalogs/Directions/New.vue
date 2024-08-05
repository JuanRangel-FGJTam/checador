<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from '@/Components/InputText.vue';
import InputSelect from '@/Components/InputSelect.vue';
import InputError from '@/Components/InputError.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import DiskIcon from '@/Components/Icons/DiskIcon.vue';

const props = defineProps({
    generalDirections: Array
});

const breadcrumbs = ref([
    { "name": 'Inicio', "href": '/dashboard' },
    { "name": 'Catalogos', "href": '/admin' },
    { "name": 'Direcciones', "href": '/admin/catalogs/directions' },
    { "name": 'Nuevo', "href": '' }
]);

const form = useForm({
    "name": undefined,
    "general_direction_id": undefined
});

function submitForm(){
    form.post( route('admin.catalogs.directions.store'),{
        replace: true,
        onSuccess: ()=>{
            // Todo: handle sucess update
        },
        onError: (err)=>{
            const {message} = err;
            if( message)
                alert(message);
            console.error(err);
        }
    });
}

</script>

<template>

    <Head title="Catalogo Direccion General - Nuevo" />

    <AuthenticatedLayout>
        <template #header>
            <Breadcrumb :breadcrumbs="breadcrumbs" />
        </template>

        <div class="mx-auto my-4 p-4 rounded-lg max-w-xl shadow bg-white dark:bg-gray-600 dark:border-gray-500">

            <form @submit.prevent="submitForm" class="flex flex-col gap-4">

                <div role="form-group">
                    <InputLabel value="Nombre" for="name" />
                    <InputText id="name" v-model="form.name"/>
                    <InputError :message="form.errors.name" />
                </div>

                <div role="form-group">
                    <InputLabel value="Dirección general asociado" for="general_direction_id" />
                    <InputSelect id="general_direction_id" v-model="form.general_direction_id">
                        <option v-for="item in generalDirections" :value="item.id" :key="item.id"> ({{item.abbreviation}}) {{ item.name }} </option>
                    </InputSelect>
                    <InputError :message="form.errors.general_direction_id" />
                </div>

                <div role="form-group" class="flex justify-center">
                    <SuccessButton type="submit">
                        <DiskIcon class="w-4 h-4 mx-1"/>
                        <span>Guardar</span>
                    </SuccessButton>
                </div>
            </form>

        </div>

    </AuthenticatedLayout>
</template>
