<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from '@/Components/InputText.vue';
import InputError from '@/Components/InputError.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import DiskIcon from '@/Components/Icons/DiskIcon.vue';

const props = defineProps({
    generalDirection: Object
});

const breadcrumbs = ref([
    { "name": 'Inicio', "href": '/dashboard' },
    { "name": 'Catalogos', "href": '/admin' },
    { "name": 'Direcciones Generales', "href": '/admin/catalogs/general-directions' },
    { "name": 'Editar', "href": '' }
]);

const form = useForm({
    "name": props.generalDirection.name,
    "abbreviation": props.generalDirection.abbreviation
});

function submitForm(){
    form.patch( route('admin.catalogs.general-directions.update', props.generalDirection.id),{
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

    <Head title="Catalogs General-Direction" />

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
                    <InputLabel value="Abreviación" for="abbreviation" />
                    <InputText id="abbreviation" v-model="form.abbreviation"/>
                    <InputError :message="form.errors.abbreviation" />
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
