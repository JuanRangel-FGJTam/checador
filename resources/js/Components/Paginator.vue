<script setup>

const props = defineProps({
    paginator: {
        type: Object,
        default : {
            from: 0,
            to: 0,
            total: 0,
            pages: []
        },
    },
    currentPage: Number
});

const emit = defineEmits(['changePage']);

function handleShowPage(pageNumber){
    emit('changePage', pageNumber );
}

</script>

<template>
    <div class="sm:px-6 flex items-center justify-between px-4 py-3 text-gray-500 dark:text-gray-100 bg-white border border-gray-200 dark:bg-gray-800 dark:border-gray-500">
        <div class="sm:flex-1 sm:flex sm:items-center sm:justify-between hidden">
            <div>
                <p class="text-sm">
                    Mostrando
                    <span class="font-medium">{{ paginator.from }}</span>
                    a
                    <span class="font-medium">{{ paginator.to }}</span>
                    elementos
                    de
                    <span class="font-medium">{{ paginator.total }}</span>
                </p>
            </div>

            <div class="">
                <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <a @click.prevent="handleShowPage(currentPage - 1)"
                        href="#previous"
                        class="
                            rounded-l-md
                            relative
                            inline-flex
                            items-center
                            px-2
                            py-2
                            text-sm
                            font-medium
                            border
                            hover:bg-gray-50
                            dark:hover:bg-gray-700
                            bg-white
                            dark:bg-gray-600
                            border-gray-300
                            dark:border-gray-500
                        "
                    >
                        <span class="sr-only">Anterior</span>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                    <a
                        v-for="page in paginator.pages" :key="page"
                        @click.prevent="handleShowPage(page)"
                        :href="`#to-page-${page}`"
                        aria-current="page"
                        class="
                            relative
                            inline-flex
                            items-center
                            px-4
                            py-2
                            text-sm
                            font-medium
                            border
                            hover:bg-gray-50
                            dark:hover:bg-gray-700
                            bg-white
                            dark:bg-gray-600
                            border-gray-300
                            dark:border-gray-500
                        "
                    >
                        {{ page }}
                    </a>
                    <a
                        @click.prevent="handleShowPage(currentPage + 1)"
                        href="#next-page"
                        class="
                            rounded-r-md
                            relative
                            inline-flex
                            items-center
                            px-2
                            py-2
                            text-sm
                            font-medium
                            border
                            hover:bg-gray-50
                            dark:hover:bg-gray-700
                            bg-white
                            dark:bg-gray-600
                            border-gray-300
                            dark:border-gray-500
                        "
                    >
                        <span class="sr-only">Siguiente</span>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" >
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </a>
                </nav>
            </div>
        </div>
    </div>
</template>