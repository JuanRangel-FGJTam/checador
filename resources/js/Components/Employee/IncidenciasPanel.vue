<script setup>

import VerticalTimeLine from '@/Components/VerticalTimeLine.vue';
import TimeLineItemCustom from '@/Components/TimeLineItemCustom.vue';
import CalendarExclamationIcon from '@/Components/Icons/CalendarExclamationIcon.vue';

const props = defineProps({
    employee: Object,
    incidences: {
        type: Array,
        default: []
    }
});

</script>

<template>
    <div class="flex flex-col h-screen overflow-y-auto">
        <h2 class="text-lg font-bold uppercase sticky top-0 backdrop-blur-md p-2 z-20">Incidencias</h2>

        <div class="border-t mt-2 pb-2"/>

        <div v-if="incidences && incidences.length > 0" class="p-4 z-10">

            <VerticalTimeLine>
                <timeLineItemCustom v-for="item in incidences">
                    <template #icon>
                        <CalendarExclamationIcon class="w-4 h-4 text-amber-600" />
                    </template>
                    <template #content>
                        <div class="-translate-y-1 -translate-x-2 flex flex-col gap-2 text-gray-600 dark:text-gray-200">
                            <h2 class="text-bold uppercase">{{ item.title }}</h2>
                            <div class="pl-1 text-xs">{{ item.start }}</div>
                        </div>
                    </template>
                </timeLineItemCustom>
            </VerticalTimeLine>

        </div>

        <div v-else class="text-emerald-600 mx-auto py-2 text-center text-sm">
            No hay incidencias del día viernes, 15 de agosto de 2024.
        </div>

    </div>
</template>