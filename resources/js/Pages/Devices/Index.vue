<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useToast } from 'vue-toastification';
import DeviceOnIcon from '@/Components/Icons/DeviceOnIcon.vue';
import DeviceOffIcon from '@/Components/Icons/DeviceOffIcon.vue';
import axios from 'axios';

const props = defineProps({
    title: String,
    devices: Array,
    lastCheckout: String
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

            document.getElementById('lastCheckoutDiv').innerText = getDateTimeNowUTCPlus6();
        }
        else{
            console.debug('Bad staus code of the response ' + response.status);
        }

    } catch (error) {
        toast.warning('Fallo al obtener los registros');
        console.error(error);
    }
}

function getDateTimeNowUTCPlus6() {
    const now = new Date();
    const formatter = new Intl.DateTimeFormat('en-US', {
        timeZone: 'America/Monterrey',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    });
    const formattedDate = formatter.formatToParts(now);

    // Reformat to 'YYYY-MM-DD HH:MM:SS'
    const year = formattedDate.find(part => part.type === 'year').value;
    const month = formattedDate.find(part => part.type === 'month').value;
    const day = formattedDate.find(part => part.type === 'day').value;
    const hour = formattedDate.find(part => part.type === 'hour').value;
    const minute = formattedDate.find(part => part.type === 'minute').value;
    const second = formattedDate.find(part => part.type === 'second').value;

    return `${year}-${month}-${day} ${hour}:${minute}:${second}`;
}

</script>

<template>
    <div class="min-h-screen flex flex-col items-center bg-gray-100 dark:bg-gray-900">
        <div class="bg-white dark:bg-gray-600 shadow mb-4 w-full p-2">
            <div class=" max-w-screen-lg mx-auto">
                <h1 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">Estatus de dispositivos</h1>
                <h5 class="font-semibold text-sm text-gray-800 dark:text-gray-200 leading-tight">Ultima actualizacion: <span id="lastCheckoutDiv">{{ props.lastCheckout }}</span></h5>
            </div>
        </div>

        <div class="px-4 py-[2rem] rounded-lg max-w-screen-2xl mx-auto dark:text-gray-200">
            <ul class="grid grid-cols-1 lg:grid-cols-3 2xl:grid-cols-4 gap-2">
                <li v-for="item in deviceLogs" :key="item.id">

                    <div class="p-1 flex gap-4 items-center border rounded shadow-lg bg-white">
                        <DeviceOnIcon v-if="item['status'] ==1" class="w-14 h-14" />
                        <DeviceOffIcon v-else class="w-14 h-14" />
                        <div class="flex flex-col ml-2 text-lg text-gray-700 dark:text-gray-100">
                            <div class="uppercase overflow-clip line-clamp-1 text-lg text-gray-600 dark:text-gary-200">{{ item.name }}</div>
                            <div class="text-xl">{{ item.address }}</div>
                            <div class="uppercase text-sm">{{ item['last-connection'].split(' ')[1]}}</div>
                            <div class="uppercase text-sm">{{ item['last-connection'].split(' ')[0]}}</div>
                        </div>

                    </div>

                </li>
            </ul>
        </div>
    </div>
</template>
