<script setup>
import { ref, watch } from 'vue';
import { useToast } from 'vue-toastification';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

import Card from '@/Components/Card.vue';
import CardText from '@/Components/CardText.vue';
import PageTitle from '@/Components/PageTitle.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import InputLabel from "@/Components/InputLabel.vue";
import InputSelect from '@/Components/InputSelect.vue';
import InputText from '@/Components/InputText.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    user: Object,
    title: String, 
    selectedOptions: Array,
    generalDirections: Array,
    directions: Array,
    subdirectorates: Array,
    departments: Array,
    menuOptions: Array,
    accessLevel: {
        type: Array,
        default: [
            {label:"Usuario (Consulta)", value: "0"},
            {label:"Administrador", value: "1"},
            {label:"Director General (Nivel 2)", value: "2"},
            {label:"Director (Nivel 3)", value: "3"},
            {label:"Subdirector (Nivel 4)", value: "4"},
            {label:"Jefe de Departamento (Nivel 5)", value: "5"},
        ]
    }
});

const toast = useToast();
// const directions = ref(props.directions);
// const emit = defineEmits(['update:selected_options']);

const form = useForm({
    generalDirection_id: props.user.general_direction_id ?? 1,
    direction_id: props.user.direction_id ?? '',
    subdirectorate_id: props.user.subdirectorates_id ?? '',
    departments_id: props.user.departments_id ?? '',
    name: props.user.name ?? '',
    email: props.user.email ?? '',
    options: props.selectedOptions ?? [],
    level_id: props.user.level_id,
    password: "",
});

async function fetchData(url) {
    try {
        router.visit(url, {
            method: 'get',
            only: ['directions', 'subdirectorates', 'departments'],
            preserveState: true,
        });
    } catch (error) {
        toast.error(`Error al cargar datos de ${updateField}.`);
        console.error(error);
    }
}

watch(() => form.generalDirection_id, (newVal) => {
    form.direction_id = '';
    form.subdirectorate_id = '';
    form.departments_id = '';

    fetchData(`/admin/users/${props.user.id}/edit?generalDirection_id=${newVal}`);
});

watch(() => form.direction_id, (newVal) => {
    form.subdirectorate_id = '';
    form.departments_id = '';
    fetchData(`/admin/users/${props.user.id}/edit?generalDirection_id=${form.generalDirection_id}&direction_id=${newVal}`);
});

watch(() => form.subdirectorate_id, (newVal) => {
    form.departments_id = '';
    fetchData(`/admin/users/${props.user.id}/edit?generalDirection_id=${form.generalDirection_id}&direction_id=${form.direction_id}&subdirectorate_id=${newVal}`);
});

function redirectBack(){
    router.visit( route('admin.users.index'), {
        replace: true
    } );
}

function submitForm(){
    form.patch( route('admin.users.update', props.user.id), {
        replace: true,
        onError:(res)=>{
            const { message } = res;
            if( message){
                toast.error(message);
            }else{
                toast.warning("Revise los campos e intente de nuevo.");
            }
        }
    });
}

function handleCheckboxUpdated(e) {
    try {
        const sender = e.target;
        const option_id = parseInt(sender.id);
        if(sender.checked){
            form.options.push( option_id );
        }
        else {
            form.options = form.options.filter( _option_id => _option_id != option_id );
        }
        // emit('update:selected_options',  selected_options_ref.value);
    } catch (err) {
        console.error(err);
    }
}

</script>

<template>
    <Head title="Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ title ?? "Editar usuario" }}</h2>
        </template>

        <Card class="max-w-screen-md mx-auto mt-2">
            <template #header>
                <PageTitle>Actualizar Usuario</PageTitle>
            </template>

            <template #content>
                <form class="flex flex-col gap-2" @submit.prevent="submitForm">
                    <div class="flex flex-col gap-2">
                        <div role="form-group">
                            <InputLabel for="level_id">Nivel de Acceso</InputLabel>
                            <InputSelect id="level_id" v-model="form.level_id">
                                <option value="">Selecciona un nivel</option>
                                <option v-for="level in  accessLevel" :key="level.value" :value="level.value">{{level.label}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.level_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="generalDirection_id">Nivel 1 (Fiscalía, Dirección General, ...)</InputLabel>
                            <InputSelect id="generalDirection_id" v-model="form.generalDirection_id">
                                <option value="">Selecciona una dirección</option>
                                <option v-for="item in generalDirections" :value="item.id">{{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.generalDirection_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="direction_id">Nivel 2 (Dirección, Vicefiscalía, ...)</InputLabel>
                            <InputSelect id="direction_id" v-model="form.direction_id">
                                <option value="">Selecciona una dirección</option>
                                <option v-for="item in directions" :value="item.id">{{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.direction_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="subdirectorate_id">Nivel 3 (Subdirección, Agencia, ...)</InputLabel>
                            <InputSelect id="subdirectorate_id" v-model="form.subdirectorate_id">
                                <option value="">Selecciona una subdirección</option>
                                <option v-for="item in subdirectorates" :value="item.id">{{item.name}}</option>
                            </InputSelect>
                            <InputError :message="form.errors.subdirectorate_id" />
                        </div>

                        <div role="form-group">
                            <InputLabel for="departments_id">Nivel 4 (Departamento...)</InputLabel>
                            <InputSelect id="departments_id" v-model="form.departments_id">
                                <option value="">Selecciona un departamento</option>
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
                    </div>

                    <CardText class=" font-bold text-lg mt-6">Acceso a menus</CardText>
                    <InputError :message="form.errors.options" />
                    <div class="flex flex-col gap-2">
                        <ul class="flex flex-col gap-2 bg-gray-50 p-4 border-2 border-gray-100 rounded">
                            <li v-for="option in menuOptions" :key="option.id" class="flex items-center gap-x-1">
                                <label>
                                    <input type="checkbox"
                                        :id="option.id" 
                                        :checked="( form.options.includes( option.id) )"
                                        v-on:change="handleCheckboxUpdated"
                                        class="rounded"
                                    />
                                    {{ option.name }} - <span class="text-xs">{{ option.url }}</span>
                                </label>
                            </li>
                        </ul>
                    </div>

                     <div role="form-group">
                        <InputLabel for="password">Contraseña</InputLabel>
                        <InputText id="password" v-model="form.password" type="password" placeholder="Deja en blanco para no actualizar"/>
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="flex flex-wrap gap-4 p-4 justify-between">
                        <DangerButton type="button" v-on:click="redirectBack">Cancelar</DangerButton>
                        <SuccessButton type="submit">Actualizar Usuario</SuccessButton>
                    </div>
                </form>
            </template>
        </Card>

    </AuthenticatedLayout>
</template>
