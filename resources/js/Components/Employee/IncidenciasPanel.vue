<script setup>

import VerticalTimeLine from '@/Components/VerticalTimeLine.vue';
import TimeLineItemCustom from '@/Components/TimeLineItemCustom.vue';
import CalendarExclamationIcon from '@/Components/Icons/CalendarExclamationIcon.vue';
import WhiteButton from '@/Components/WhiteButton.vue';
import TrashcanIcon from '@/Components/Icons/TrashcanIcon.vue';

const props = defineProps({
    employee: Object,
    incidences: {
        type: Array,
        default: []
    }
});

const emit = defineEmits(['incidentClick', 'removeIncidentClick']);

function handleIncidentClick(incident)
{
    const incidentDate = incident.start;
    emit("incidentClick", incidentDate);
}

function handleRemoveIncident(incident)
{
    emit("removeIncidentClick", incident);
}

function handleMouseEnter(type, incident)
{
    var ele = document.getElementById(`rm${incident.id}`);
    if(!ele)
    {
        return;
    }

    if( type=='enter')
    {
        if(ele.classList.contains('hidden'))
        {
            ele.classList.remove('hidden');
        }
    }
    else
    {
        if(!ele.classList.contains('hidden'))
        {
            document.getElementById(`rm${incident.id}`).classList.add('hidden');
        }
    }
}

</script>

<template>
    <div class="flex flex-col h-full overflow-y-auto">

        <h2 class="text-lg font-bold uppercase sticky top-0 backdrop-blur-md p-2 z-20">
            <span class="mx-2 text-gray-400">{{ incidences.length }}</span>
            Incidencias 
        </h2>

        <div class="border-t mt-2 pb-2"/>

        <div v-if="incidences && incidences.length > 0" class="p-4 z-10">

            <VerticalTimeLine>
                <timeLineItemCustom v-for="item in incidences">
                    <template #icon>
                        <CalendarExclamationIcon class="w-4 h-4 text-orange-400" />
                    </template>
                    <template #content>
                        <div class="-translate-y-1 -translate-x-2 cursor-pointer w-full rounded px-2 py-1 flex gap-2 text-gray-600 dark:text-gray-200 hover:bg-amber-50 hover:outline hover:outline-amber-400"
                            v-on:click="handleIncidentClick(item)"
                            v-on:mouseenter="handleMouseEnter('enter', item)"
                            v-on:mouseleave="handleMouseEnter('leave', item)"
                        >
                            <div class="flex flex-col">
                                <h2 class="text-bold uppercase">{{ item.title }}</h2>
                                <div class="pl-1 text-xs">{{ item.start }}</div>
                            </div>
                            <WhiteButton :id="`rm${item.id}`" v-if="$page.props.auth.user.level_id <= 1" class="ml-auto text-red-500 hover:bg-red-100 hidden focus:bg-red-200 active:bg-red-200 focus:outline-red-400" v-on:click="handleRemoveIncident(item)">
                                <TrashcanIcon class="w-4 h-4"/>
                            </WhiteButton>
                        </div>
                    </template>
                </timeLineItemCustom>
            </VerticalTimeLine>

        </div>

        <div v-else class="text-emerald-600 mx-auto p-2 text-center text-sm">
            No hay incidencias para este periodo.
        </div>

    </div>
</template>