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
    department: Object,
    subDirections: Array
});

const breadcrumbs = ref([
    { "name": 'Inicio', "href": '/dashboard' },
    { "name": 'Catalogos', "href": '/admin' },
    { "name": 'Departamentos', "href": '/admin/catalogs/departments' },
    { "name": 'Editar Departamento', "href": '' }
]);

const form = useForm({
    "name": props.department.name,
    "subdirectorate_id": props.department.subdirectorate_id
});

function submitForm(){
    form.patch( route('admin.catalogs.departments.update', props.department.id),{
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

    <Head title="Catalogo Departamentos - Editar" />

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
                    <InputLabel value="Sub direccion asociado" for="subdirectorate_id" />
                    <InputSelect id="subdirectorate_id" v-model="form.subdirectorate_id">
                        <option v-for="item in subDirections" :value="item.id" :key="item.id"> {{ item.name }} </option>
                    </InputSelect>
                    <InputError :message="form.errors.subdirectorate_id" />
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
