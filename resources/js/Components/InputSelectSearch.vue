<script setup >

import {ref, computed, onMounted, watch} from 'vue';

import { debounce } from '@/Utils/debounce';
import axios from 'axios';

const props = defineProps({
    modelValue: {
        type: [String, Number]
    },
    endpoint:{
        type: String,
        required:true,
    },
    defaultValues: {
        type: Array,
        default: [
            { label: "Elemento prueba 1", id: 1 },
            { label: "Elemento prueba 2", id: 2 },
        ]
    },
    valueProp: {
        type:String,
        default:"label"
    },
    labelProp: {
        type: String,
        default: "value"
    },
    placeholder: {
        type: String,
        default: "Seleccine un elemento"
    }
});

const emit = defineEmits(['update:modelValue', 'change']);

const endpointApi = ref( props.endpoint );

const dataElements = ref( props.defaultValues ?? [] );

const searchInput = ref(null);

const searchString = ref(null);

const listItemsWrap = ref(null);

const listItems = ref(null);

const selectedValue = ref(props.modelValue);

const showSpinner = ref(false);

const labelElementSelected = computed(()=>{
    if( selectedValue.value == null){
        return null;
    }
    var element = dataElements.value.find( item => item[props.valueProp] == selectedValue.value );
    if( element != null){
        return element[props.labelProp];
    }else {
        return null;
    }
})

onMounted(()=>{
    
    // display the list element on search focus
    searchInput.value.addEventListener('focus', (e)=>{
        if (listItemsWrap.value && listItemsWrap.value.classList.contains('hidden')) {
            listItemsWrap.value.classList.remove('hidden');
        }
    });

    searchInput.value.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (listItemsWrap.value && !listItemsWrap.value.classList.contains('hidden')) {
                listItemsWrap.value.classList.add('hidden');
            }
            searchInput.value.blur();
        }
    });

    // hidden list on lost focus
    searchInput.value.addEventListener('focusout', (e)=>{
        debounce( ()=>{
            if (listItemsWrap.value && !listItemsWrap.value.classList.contains('hidden')) {
                listItemsWrap.value.classList.add('hidden');
            }
        },5000);
    });

    // get the data when attempting to search
    searchInput.value.addEventListener( 'input',(e)=> getData() );

});

watch(() => props.modelValue, (newValue) => {
    selectedValue.value = newValue;
});

function getData(){
    // call to the api
    showSpinner.value = true;
    debounce(()=>{
        var __url = `${endpointApi.value}?search=${searchString.value}`;
        axios.get(__url)
        .then( (res)=>{
            try {
                dataElements.value = Object.values(res.data);
            } catch (error) {
                dataElements.value = null;
            }
        })
        .finally(()=>{
            showSpinner.value = false;
        });
    }, 1500);
}

function handleDataElementClick(elemnt){
    
    // Update the selection
    selectedValue.value = elemnt[props.valueProp];

    // Hidden the list element 
    if (listItemsWrap.value && !listItemsWrap.value.classList.contains('hidden')) {
        listItemsWrap.value.classList.add('hidden');
    }

    // Clear the search string
    searchString.value = null;

    // Emit the update:modelValue event
    emit('update:modelValue', selectedValue.value);
    setTimeout(() => {
        emit('change');
    }, 200);
}

function clearSelection(){
    selectedValue.value = null;
    emit('update:modelValue', selectedValue.value);
    setTimeout(() => {
        emit('change');
    }, 200);
}


</script>

<template>

<div class="py-0.5 relative mx-auto w-full flex items-center justify-end box-border cursor-pointer outline-zero transition-input duration-200 border-solid form-border-width-input form-shadow-input form-text-type form-text-sm form-radius-input-sm form-min-h-input-height-sm form-bg-input form-color-input form-border-color-input hover:form-bg-input-hover hover:form-color-input-hover hover:form-border-color-input-hover hover:form-shadow-input-hover focused:form-bg-input-focus focused:form-color-input-focus focused:form-border-color-input-focus focused:form-shadow-input-focus focused:form-ring focused-hover:form-shadow-input-hover" >
    <div class="relative mx-auto w-full flex items-center justify-end box-border cursor-pointer outline-zero form-min-h-input-height-inner-sm"
    tabindex="-1" >

        <!-- Search -->
        <input ref="searchInput"
            type="search"
            class="w-full h-full absolute inset-0 outline-zero appearance-none box-border border-0 bg-transparent form-color-input form-text-sm form-radius-input-sm form-pl-input-sm form-pr-select-no-clear-sm with-floating:form-p-input-floating-sm rtl:form-pl-select-no-clear-sm rtl:form-pr-input-sm"
            autocomplete="off"
            id="input-search-select"
            aria-expanded="false"
            aria-multiselectable="true"
            role="combobox"
            aria-invalid="false"
            aria-disabled="false"
            aria-busy="false"
            v-model="searchString"
        />
        
        <!-- Single label -->
        <div v-if="selectedValue && !searchString" class="flex items-center h-full max-w-full absolute left-0 top-0 pointer-events-none bg-transparent box-border rtl:left-auto rtl:right-0 rtl:pl-0 form-py-input-sm form-pl-input-sm form-pr-select-no-clear-sm form-radius-input-sm form-text-sm form-min-h-input-height-inner-sm with-floating:form-p-input-floating-sm rtl:form-pr-input-sm rtl:form-pl-select-no-clear-sm form-pl-input-sm form-pr-select-sm with-floating:form-p-input-floating-sm rtl:form-pr-input-sm rtl:form-pl-select-sm">
            <span class="overflow-hidden block whitespace-nowrap max-w-full overflow-ellipsis">{{ labelElementSelected }}</span>
        </div>
        <!--v-if-->
        
        <!-- Placeholder -->
        <div v-if="placeholder && !selectedValue && !searchString" class="flex items-center h-full absolute left-0 top-0 pointer-events-none bg-transparent form-color-placeholder rtl:left-auto rtl:right-0 rtl:pl-0 form-pl-input-sm rtl:form-pr-input-sm" aria-hidden="true">{{placeholder}}</div>
        <!--v-if-->
        
        <!-- Spinner -->
        <span v-if="showSpinner" class="mask-bg mask-form-spinner form-bg-primary w-4 h-4 animate-spin flex-shrink-0 flex-grow-0 rtl:mr-0 form-mr-input-sm rtl:form-ml-input-sm" aria-hidden="true"></span>
        <!--v-if-->
        
        <!--Clear -->
        <span v-if="selectedValue" aria-hidden="true" tabindex="0" role="button" data-clear="" aria-roledescription="❎" class="relative opacity-50 transition duration-300 flex-shrink-0 flex-grow-0 flex hover:opacity-100 rtl:pr-0 rtl:pl-3.5 form-pr-input-sm rtl:form-pl-input-sm" v-on:click="clearSelection">
            <span class="mask-bg mask-form-remove form-bg-icon w-2.5 h-4 py-px box-content inline-block"></span>
        </span>
        <!--endclear-->
        
        <!-- Caret -->
        <span class="mask-bg mask-form-caret form-bg-icon w-2.5 h-4 py-px box-content relative flex-shrink-0 flex-grow-0 transition-transform transform pointer-events-none rtl:mr-0 form-mr-input-sm rtl:form-ml-input-sm"
        aria-hidden="true"
        ></span>
    </div>

    <!-- Options -->
    <div ref="listItemsWrap" id="input-search-select-list-wrap" class="max-h-60 absolute z-999 -left-px -right-px bottom-0 transform form-shadow-dropdown form-border-width-dropdown border-solid form-border-color-input form-bg-input -mt-px overflow-y-scroll flex flex-col form-radius-input-b-sm translate-y-full hidden" tabindex="-1" >
        <ul ref="listItems" class="flex flex-col p-0 m-0 list-none form-color-input" id="input-search-select-list" role="listbox">            
            <li v-if="dataElements && dataElements.length > 0"
                v-for="elemnt in dataElements" :key="elemnt[valueProp]" :id="elemnt[valueProp]"
                v-on:click="handleDataElementClick(elemnt)"
                class="flex items-center justify-start box-border text-left cursor-pointer form-px-input-sm form-py-input-border-sm form-color-input form-bg-selected"
                data-pointed="true"
                aria-selected="false"
                aria-label="Convocatoria prueba documentos adjuntos v23"
                role="option"
            >
                <span>{{elemnt[labelProp]}}</span>
            </li>
            <li v-else
                class="flex items-center justify-start box-border text-left cursor-pointer form-px-input-sm form-py-input-border-sm form-color-input form-bg-selected"
                data-pointed="true"
                aria-selected="false"
                aria-label="Convocatoria prueba documentos adjuntos v23"
                role="option"
            >
                <span>No hay datos disponibles</span>
            </li>
        </ul>
    </div>
    <!--List options-->
  
    <!-- Create height for empty input -->

  <div class="box-content form-h-input-height-inner-sm"></div>
</div>
    
</template>