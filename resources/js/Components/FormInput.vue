<script setup>
import { ref } from 'vue'

const props = defineProps({
    type: {
        type: String,
    },
    name: {
        type: String,
    },
    value: {
        type: String,
    },
    maxlength: {
        type: String,
    },
    uppercase: {
        type: Boolean,
    },
    blurFunction: {
        type: Function,
    },
    colorInput: {
        type: String,
    },
    colorLabel: {
        type: String,
    }
})

const model = defineModel({
    type: String,
    required: true,
})

const inputName = props.name + '_' + (new Date()).getTime()
const uppercase = props.uppercase ? 'uppercase' : '';
const input = ref(null)
</script>

<style>
.alert-input {
    border-color: #dc2626 !important;
}

.alert-label {
    color: #f87171 !important;
}

.normal-input {
    border-color: #030712 !important;
}

.normal-label {
    color: #9ca3af !important;
}
</style>

<template>
    <input :id="name" :name="inputName" :type="type" placeholder=" " v-model="model" ref="input"
        :autocomplete="inputName" :maxlength="maxlength" @blur="blurFunction" @keyup="blurFunction"
        :class="colorInput, uppercase"
        class="block py-2.5 px-0 w-full text-lg text-gray-900 bg-transparent border-0 border-b appearance-none focus:outline-none focus:ring-0 peer" />
    <label :for="name" :class="colorLabel"
        class="peer-focus:font-medium absolute text-lg duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-5">
        {{ value }}
    </label>
</template>
