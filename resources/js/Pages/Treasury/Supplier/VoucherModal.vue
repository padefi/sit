<script setup>
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, ref, computed } from "vue";
import { dropdownClasses } from '@/utils/cssUtils';
import { validateCBU } from '@/utils/validateFunctions';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    invoiceType: undefined,
    invoiceTypeCode: undefined,
    pointOfNumber: undefined,
    invoiceNumber: undefined,
    invoiceDate: undefined,
    invocePaymentDate: undefined,
    saleCondition: undefined,
});

const rules = 'Debe completar el campo'
const invoiceTypes = ref([]);
const invoiceTypeCodes = ref([]);

const dialogRef = inject("dialogRef");

onMounted(async () => {
    invoiceTypes.value = dialogRef.value.data.invoiceTypes;
    invoiceTypeCodes.value = dialogRef.value.data.invoiceTypeCodes;
});
</script>
<template>
    <div class="card flex flex-col">
        <div class="flex w-6/6 space-x-2">
            <div class="flex md:w-2/6 space-x-2">
                <div>
                    <FloatLabel>
                        <InputNumber v-model="form.pointOfNumber" placeholder="0000" inputId="pointOfNumber" id="pointOfNumber"
                            class=":not(:focus)::placeholder:text-transparent"
                            :class="form.pointOfNumber !== null && form.pointOfNumber !== undefined ? 'filled' : ''" :min="1" :max="5"
                            :invalid="form.pointOfNumber === null || form.pointOfNumber === 0" />
                        <label for="pointOfNumber">Pto. Vta.</label>
                    </FloatLabel>
                    <InputError :message="form.pointOfNumber === null || form.pointOfNumber === 0 ? rules : ''" />
                </div>
                <div>
                    <FloatLabel>
                        <InputNumber v-model="form.invoiceNumber" placeholder="0000000" inputId="invoiceNumber" id="invoiceNumber"
                            class=":not(:focus)::placeholder:text-transparent"
                            :class="form.invoiceNumber !== null && form.invoiceNumber !== undefined ? 'filled' : ''" :min="1" :max="8"
                            :invalid="form.invoiceNumber === null || form.invoiceNumber === 0" />
                        <label for="invoiceNumber">Número</label>
                    </FloatLabel>
                    <InputError :message="form.invoiceNumber === null || form.invoiceNumber === 0 ? rules : ''" />
                </div>
            </div>

            <div class="w-1/6">
                <FloatLabel class="!top-[2px]">
                    <Dropdown inputId="invoiceType" v-model="form.invoiceType" :options="invoiceTypes" filter showClear resetFilterOnHide
                        class="!focus:border-primary-500 w-full" :class="dropdownClasses(form.invoiceType)" optionLabel="name" optionValue="id" />
                    <template #option="slotProps">
                        <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                    </template>
                    <label for="invoiceType">T. comprobante</label>
                </FloatLabel>
            </div>

            <div class="w-1/6">
                <FloatLabel class="!top-[2px]">
                    <Dropdown inputId="invoiceTypeCode" v-model="form.invoiceTypeCode" :options="invoiceTypeCodes" filter showClear resetFilterOnHide
                        class="!focus:border-primary-500 w-full" :class="dropdownClasses(form.invoiceTypeCode)" optionLabel="name" optionValue="id" />
                    <template #option="slotProps">
                        <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                    </template>
                    <label for="invoiceTypeCode">T. factura</label>
                </FloatLabel>
            </div>
        </div>
    </div>
</template>