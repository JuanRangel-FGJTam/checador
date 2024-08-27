<script setup>
import Modal from '@/Components/Modal.vue';
import WhiteButton from '@/Components/WhiteButton.vue';

var props = defineProps({
    title: String,
    subtitle: String,
    src: String,
});
const emit = defineEmits(['close']);

const handleCanceledClick = ()=> emit("close");

</script>

<template>
    <Modal :show="true" maxWidth="4xl">
        <div class="bg-white border p-4 rounded dark:dark:text-white dark:bg-gray-700 dark:border-gray-600">
            <div>
                <h2 class="uppercase"> {{ title }} </h2>
                <div v-if="subtitle" class="uppercase text-sm"> {{ subtitle }} </div>
            </div>

            <div class="wrap p-2 mb-6" style="height: 75vh;">
                <iframe :src="src" class="w-full h-full block" />
            </div>
            
            <div class="flex gap-4 justify-center">
                <WhiteButton @click="handleCanceledClick" class="outline outline-1 px-4">
                    <span>Cerrar</span>
                </WhiteButton>
            </div>
        </div>
    </Modal>
</template>