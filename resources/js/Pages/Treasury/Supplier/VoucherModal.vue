<script setup>
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, ref, watch } from "vue";
import { dropdownClasses } from '@/utils/cssUtils';
import { validateCBU } from '@/utils/validateFunctions';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    invoiceType: null,
    invoiceTypeCode: null,
    pointOfNumber: null,
    invoiceNumber: null,
    invoiceDate: undefined,
    invocePaymentDate: undefined,
    saleCondition: null,
    voucherType: null,
    voucherSubtype: null,
    voucherExpense: null,
});

const rules = 'Debe completar el campo'
const invoiceTypes = ref([]);
const invoiceTypeCodes = ref([]);
const saleConditions = ref([]);
const voucherTypes = ref([]);
const voucherSubtypes = ref([]);
const voucherExpenses = ref([]);

const handleInvoiceNumber = (input, length) => {
    form[input] = !form[input] ? null : form[input].padStart(length, '0') !== '0'.padStart(length, '0') ? form[input].padStart(length, '0') : '';
};

const dialogRef = inject("dialogRef");

onMounted(async () => {
    invoiceTypes.value = dialogRef.value.data.invoiceTypes;
    invoiceTypeCodes.value = dialogRef.value.data.invoiceTypeCodes;
    saleConditions.value = dialogRef.value.data.saleConditions;
    voucherTypes.value = dialogRef.value.data.voucherTypes;
});

watch(() => form.voucherType, async (voucherTypeId) => {
    voucherSubtypes.value = [];
    voucherExpenses.value = [];
    try {
        const response = await fetch(`/voucher-subtypes/${voucherTypeId}/data-related`);

        if (!response.ok) {
            throw new Error('Error al obtener los subtipos relacionados');
        }

        const data = await response.json();
        voucherSubtypes.value = data.voucherSubtypes;
    } catch (error) {
        console.error(error);
    }
});

watch(() => form.voucherSubtype, (data) => {
    console.log(data);
});
</script>
<template>
    <div class="card flex !p-2 justify-center">
        <div class="flex flex-col w-full">
            <div class="flex flex-col justify-center font-medium">
                <div class="flex gap-3 m-3 flex-wrap justify-center">
                    <div class="min-w-52">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="invoiceType" v-model="form.invoiceType" :options="invoiceTypes" filter showClear resetFilterOnHide
                                class="w-full !focus:border-primary-500" :class="dropdownClasses(form.invoiceType)" optionLabel="name"
                                optionValue="id" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="invoiceType">T. comp.</label>
                        </FloatLabel>
                    </div>

                    <div class="min-w-32">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="invoiceTypeCode" v-model="form.invoiceTypeCode" :options="invoiceTypeCodes" filter showClear
                                resetFilterOnHide class="w-full !focus:border-primary-500" :class="dropdownClasses(form.invoiceTypeCode)"
                                optionLabel="name" optionValue="id" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="invoiceTypeCode">T. fac.</label>
                        </FloatLabel>
                    </div>

                    <div class="flex min-w-48 gap-3">
                        <div class="max-w-20">
                            <FloatLabel>
                                <InputText v-model="form.pointOfNumber" autocomplete="off" inputId="pointOfNumber" id="pointOfNumber"
                                    class="w-full :not(:focus)::placeholder:text-transparent" minlength="5" maxlength="5"
                                    :invalid="form.pointOfNumber && (form.pointOfNumber.trim() === '' || form.pointOfNumber === '')"
                                    @blur="handleInvoiceNumber('pointOfNumber', 5)" />
                                <label for="pointOfNumber">Pto Vta</label>
                            </FloatLabel>
                            <InputError :message="form.pointOfNumber && form.pointOfNumber.trim() === '' || form.pointOfNumber === '' ? rules : ''" />
                        </div>


                        <div class="flex gap-3 flex-wrap justify-center">
                            <div class="max-w-28">
                                <FloatLabel>
                                    <InputText v-model="form.invoiceNumber" autocomplete="off" inputId="invoiceNumber" id="invoiceNumber"
                                        class="w-full :not(:focus)::placeholder:text-transparent" minlength="8" maxlength="8"
                                        :invalid="form.invoiceNumber && (form.invoiceNumber.trim() === '' || form.invoiceNumber === '')"
                                        @blur="handleInvoiceNumber('invoiceNumber', 8)" />
                                    <label for="invoiceNumber">Número</label>
                                </FloatLabel>
                                <InputError
                                    :message="form.invoiceNumber && form.invoiceNumber.trim() === '' || form.invoiceNumber === '' ? rules : ''" />
                            </div>
                        </div>
                    </div>
                </div>

                <Divider align="center" type="dashed" class="text-lg text-primary-700 !m-0" />

                <div class="flex gap-3 m-3 flex-wrap justify-center">
                    <div>
                        <FloatLabel>
                            <Calendar v-model="form.invoiceDate" placeholder="DD/MM/AAAA" showButtonBar id="invoiceDate" class="w-full"
                                :class="form.invoiceDate !== null && form.invoiceDate !== undefined ? 'filled' : ''"
                                :invalid="form.invoiceDate === null" :maxDate="new Date()" />
                            <label for="invoiceDate">F. emisión</label>
                        </FloatLabel>
                        <InputError :message="form.invoiceDate === null ? rules : ''" />
                    </div>

                    <div>
                        <FloatLabel>
                            <Calendar v-model="form.invocePaymentDate" placeholder="DD/MM/AAAA" showButtonBar id="invocePaymentDate" class="w-full"
                                :class="form.invocePaymentDate !== null && form.invocePaymentDate !== undefined ? 'filled' : ''"
                                :invalid="form.invocePaymentDate === null" :minDate="form.invoiceDate" />
                            <label for="invocePaymentDate">F. vencimiento</label>
                        </FloatLabel>
                        <InputError :message="form.invocePaymentDate === null ? rules : ''" />
                    </div>

                    <div class="min-w-60">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="saleCondition" v-model="form.saleCondition" :options="saleConditions" filter showClear
                                resetFilterOnHide class="w-full !focus:border-primary-500" :class="dropdownClasses(form.saleCondition)"
                                optionLabel="name" optionValue="id" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="saleCondition">Cond. venta</label>
                        </FloatLabel>
                    </div>
                </div>

                <Divider align="center" type="dashed" class="text-lg text-primary-700 !m-0" />

                <div class="flex gap-3 m-3 flex-wrap justify-center">
                    <div class="min-w-60">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="voucherType" v-model="form.voucherType" :options="voucherTypes" filter showClear resetFilterOnHide
                                class="w-full !focus:border-primary-500" :class="dropdownClasses(form.voucherType)" optionLabel="name"
                                optionValue="id" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="voucherType">Tipo</label>
                        </FloatLabel>
                    </div>

                    <div class="min-w-60">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="voucherSubtype" v-model="form.voucherSubtype" :options="voucherSubtypes" filter showClear
                                resetFilterOnHide class="w-full !focus:border-primary-500" :class="dropdownClasses(form.voucherSubtype)"
                                optionLabel="name" optionValue="id" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="voucherSubtype">Subtipo</label>
                        </FloatLabel>
                    </div>

                    <div class="min-w-60">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="voucherExpense" v-model="form.voucherExpense" :options="voucherExpenses" filter showClear
                                resetFilterOnHide class="w-full !focus:border-primary-500" :class="dropdownClasses(form.voucherExpense)"
                                optionLabel="name" optionValue="id" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="voucherExpense">Gasto</label>
                        </FloatLabel>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>