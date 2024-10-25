<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { debounce } from '@/utils/debounce.js';
import { useToast } from 'vue-toastification';
import { formatDate, formatDatetime } from '@/utils/date';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

import DeviceOnIcon from '@/Components/Icons/DeviceOnIcon.vue';
import DeviceOffIcon from '@/Components/Icons/DeviceOffIcon.vue';
import axios from 'axios';

const props = defineProps({
    title: String,
    devices: Array,
});

const toast = useToast();
const deviceLogs = ref([...props.devices]);
const currentTimmerId = ref(0);

onMounted(()=>{
    currentTimmerId.value = setInterval(() => {
        fetchDeviceLogs();
    }, 30000);
});

onBeforeUnmount(()=>{
    clearTimeout(currentTimmerId.value);
})

async function fetchDeviceLogs(){
    try {
        var response = await axios.get(route('devices.logs'));
        if(response.status == 200){
            deviceLogs.value = response.data;
            console.debug('Logs updated');
        }
        else{
            console.debug('Bad staus code of the response ' + response.status);
        }

    } catch (error) {
        toast.warning('Fallo al obtener los registros');
        console.error(error);
    }
}

</script>

<template>

    <Head title="Administrador" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dispositivos</h2>
        </template>

        <div class="px-4 py-[2rem] rounded-lg max-w-screen-2xl mx-auto dark:text-gray-200">
            <ul class="grid grid-cols-1 lg:grid-cols-3 2xl:grid-cols-4 gap-2">
                <li v-for="item in deviceLogs" :key="item.id">

                    <div class="p-1 flex gap-4 items-center border rounded shadow-lg bg-white">
                        <DeviceOnIcon v-if="item['status'] ==1" class="w-14 h-14" />
                        <DeviceOffIcon v-else class="w-14 h-14" />
                        <div class="flex flex-col ml-2 text-lg text-gray-700 dark:text-gray-100">
                            <div class="uppercase overflow-clip line-clamp-1 text-lg text-gray-600 dark:text-gary-200">{{ item.name }}</div>
                            <div class="text-xl">{{ item.address }}</div>
                            <div class="uppercase text-sm">{{ formatDatetime(item['last-connection']).split(',')[1]}}</div>
                            <div class="uppercase text-sm">{{ formatDatetime(item['last-connection']).split(',')[0]}}</div>
                        </div>

                    </div>

                </li>
            </ul>
        </div>

    </AuthenticatedLayout>
</template>
