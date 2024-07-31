<script setup>
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, ref, watch } from "vue";
import { dropdownClasses } from '@/utils/cssUtils';
import { percentNumber, currencyNumber, dateFormat } from "@/utils/formatterFunctions";
import InputError from '@/Components/InputError.vue';

const form = useForm({
    invoiceType: undefined,
    invoiceTypeCode: undefined,
    pointOfNumber: undefined,
    invoiceNumber: undefined,
    invoiceDate: undefined,
    invocePaymentDate: undefined,
    payCondition: undefined,
    voucherType: undefined,
    voucherSubtype: undefined,
    voucherExpense: undefined,
    notes: ref(''),
});

const rules = 'Debe completar el campo'
const invoiceTypes = ref([]);
const invoiceTypeCodes = ref([]);
const payConditions = ref([]);
const voucherTypes = ref([]);
const voucherSubtypes = ref([]);
const voucherExpenses = ref([]);
const vatRates = ref([]);

const voucherItems = ref([]);
const originalVoucherItems = ref([]);
const newRow = ref([]);
const editingRows = ref([]);
const editing = ref(false);

const handleInvoiceNumber = (input, length) => {
    // form[input] = !form[input] ? null : form[input].padStart(length, '0') !== '0'.padStart(length, '0') ? form[input].padStart(length, '0') : '';
    form[input] = !form[input] || form[input].padStart(length, '0') === '0'.padStart(length, '0') ? '' : form[input].padStart(length, '0');
};

const addNewItem = () => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de agregar un nuevo item.',
            life: 3000,
        });

        return;
    }

    originalVoucherItems.value = [...voucherItems.value];

    const newItem = {
        id: crypto.randomUUID(),
        description: undefined,
        vat: undefined,
        amount: undefined,
        subtotalAmount: 0,
        condition: 'newItem',
    };

    voucherItems.value.unshift(newItem);
    editing.value = true;
    editingRows.value = [newItem];
};

const calculateSubtotalAmount = (data, amountValue, rateValue) => {
    if (amountValue > 99999999) return;

    const rate = data.vat ? vatRates.value.find((rate) => rate.id === rateValue).rate : 0;
    data.subtotalAmount = rate > 0 ? amountValue * (1 + rate / 100) || 0 : amountValue || 0;
    /* form.totalAmount = voucherItems.value.reduce((total, item) => {
        return total + (item.subtotalAmount || 0);
    }, 0); */
};

const dialogRef = inject("dialogRef");

onMounted(async () => {
    invoiceTypes.value = dialogRef.value.data.invoiceTypes;
    invoiceTypeCodes.value = dialogRef.value.data.invoiceTypeCodes;
    payConditions.value = dialogRef.value.data.payConditions;
    voucherTypes.value = dialogRef.value.data.voucherTypes;
    vatRates.value = dialogRef.value.data.vatRates.map((vatRate) => {
        return { label: percentNumber(vatRate.rate), rate: vatRate.rate, id: vatRate.id };
    });
    addNewItem();
});

watch(() => form.voucherType, async (voucherTypeId) => {
    form.voucherSubtype = undefined;
    form.voucherExpense = undefined;
    voucherSubtypes.value = [];
    voucherExpenses.value = [];

    if (!voucherTypeId) {
        return;
    }

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

watch(() => form.voucherSubtype, async (voucherSubtype) => {
    form.voucherExpense = undefined;
    voucherExpenses.value = [];

    if (!voucherSubtype) {
        return;
    }

    try {
        const response = await fetch(`/voucher-expenses/${voucherSubtype}/data-related`);

        if (!response.ok) {
            throw new Error('Error al obtener los gastos relacionados');
        }

        const data = await response.json();
        voucherExpenses.value = data.voucherExpenses.length > 0 ? data.voucherExpenses : [{ id: 0, name: 'SIN GASTOS' }];
        form.voucherExpense = data.voucherExpenses.length === 0 && 0;
    } catch (error) {
        console.error(error);
    }
});
</script>
<template>
    <div class="card flex !p-2 justify-center">
        <div class="flex flex-col w-fit">
            <div
                class="flex flex-col border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 justify-center font-medium">
                <div class="flex gap-3 m-3 flex-wrap justify-center">
                    <div class="min-w-64">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="invoiceType" v-model="form.invoiceType" :options="invoiceTypes" filter showClear resetFilterOnHide
                                :invalid="!!form.invoiceType" optionLabel="name" optionValue="id" class="w-full !focus:border-primary-500"
                                :class="dropdownClasses(form.invoiceType)" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="invoiceType">T. comp.</label>
                        </FloatLabel>
                        <InputError :message="form.invoiceType === null ? rules : ''" />
                    </div>

                    <div class="min-w-40">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="invoiceTypeCode" v-model="form.invoiceTypeCode" :options="invoiceTypeCodes" filter showClear
                                resetFilterOnHide :invalid="form.invoiceTypeCode === null" optionLabel="name" optionValue="id"
                                class="w-full !focus:border-primary-500" :class="dropdownClasses(form.invoiceTypeCode)" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="invoiceTypeCode">T. fac.</label>
                        </FloatLabel>
                        <InputError :message="form.invoiceTypeCode === null ? rules : ''" />
                    </div>

                    <div class="flex min-w-52 gap-1">
                        <div class="max-w-24">
                            <FloatLabel>
                                <InputText v-model="form.pointOfNumber" autocomplete="off" inputId="pointOfNumber" id="pointOfNumber"
                                    class="w-full :not(:focus)::placeholder:text-transparent" minlength="5" maxlength="5"
                                    :invalid="form.pointOfNumber && (form.pointOfNumber.trim() === '' || form.pointOfNumber === '')"
                                    @blur="handleInvoiceNumber('pointOfNumber', 5)" />
                                <label for="pointOfNumber">Pto Vta</label>
                            </FloatLabel>
                            <InputError :message="form.pointOfNumber && form.pointOfNumber.trim() === '' || form.pointOfNumber === '' ? rules : ''" />
                        </div>

                        <div class="relative !top-2.5">-</div>

                        <div class="flex gap-3 flex-wrap justify-center">
                            <div class="max-w-32">
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
                    <div class="max-w-48">
                        <FloatLabel>
                            <Calendar v-model="form.invoiceDate" placeholder="DD/MM/AAAA" showButtonBar id="invoiceDate" class="w-full"
                                :class="form.invoiceDate !== null && form.invoiceDate !== undefined ? 'filled' : ''" inputClass="w-full"
                                :invalid="form.invoiceDate === null" :maxDate="new Date()" />
                            <label for="invoiceDate">F. emisión</label>
                        </FloatLabel>
                        <InputError :message="form.invoiceDate === null ? rules : ''" />
                    </div>

                    <div class="max-w-48">
                        <FloatLabel>
                            <Calendar v-model="form.invocePaymentDate" placeholder="DD/MM/AAAA" showButtonBar id="invocePaymentDate" class="w-full"
                                :class="form.invocePaymentDate !== null && form.invocePaymentDate !== undefined ? 'filled' : ''" inputClass="w-full"
                                :invalid="form.invocePaymentDate === null" :minDate="form.invoiceDate" />
                            <label for="invocePaymentDate">F. vencimiento</label>
                        </FloatLabel>
                        <InputError :message="form.invocePaymentDate === null ? rules : ''" />
                    </div>

                    <div class="min-w-72">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="payCondition" v-model="form.payCondition" :options="payConditions" filter showClear resetFilterOnHide
                                :invalid="form.payCondition === null" optionLabel="name" optionValue="id" class="w-full !focus:border-primary-500"
                                :class="dropdownClasses(form.payCondition)" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="payCondition">Cond. pago</label>
                        </FloatLabel>
                        <InputError :message="form.payCondition === null ? rules : ''" />
                    </div>
                </div>

                <Divider align="center" type="dashed" class="text-lg text-primary-700 !m-0" />

                <div class="flex gap-3 m-3 flex-wrap justify-center">
                    <div class="min-w-40">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="voucherType" v-model="form.voucherType" :options="voucherTypes" filter showClear resetFilterOnHide
                                :invalid="form.voucherType === null" optionLabel="name" optionValue="id" class="w-full !focus:border-primary-500"
                                :class="dropdownClasses(form.voucherType)" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="voucherType">Tipo</label>
                        </FloatLabel>
                        <InputError :message="form.voucherType === null ? rules : ''" />
                    </div>

                    <div class="min-w-72">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="voucherSubtype" v-model="form.voucherSubtype" :options="voucherSubtypes" filter showClear
                                resetFilterOnHide :invalid="form.voucherSubtype === null" optionLabel="name" optionValue="id"
                                class="w-full !focus:border-primary-500" :class="dropdownClasses(form.voucherSubtype)" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="voucherSubtype">Subtipo</label>
                        </FloatLabel>
                        <InputError :message="form.voucherSubtype === null ? rules : ''" />
                    </div>

                    <div class="min-w-56">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="voucherExpense" v-model="form.voucherExpense" :options="voucherExpenses" filter showClear
                                resetFilterOnHide :invalid="form.voucherExpense === null" optionLabel="name" optionValue="id"
                                class="w-full !focus:border-primary-500" :class="dropdownClasses(form.voucherExpense)" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="voucherExpense">Gasto</label>
                        </FloatLabel>
                        <InputError :message="form.voucherExpense === null ? rules : ''" />
                    </div>
                </div>

                <Divider align="center" type="dashed" class="text-lg text-primary-700 !m-0" />

                <div class="m-3 !mb-0">
                    <FloatLabel class="w-full !top-[2px]">
                        <Textarea v-model="form.notes" autocomplete="off" inputId="notes" id="notes" class="w-full peer"
                            :class="dropdownClasses(form.notes)" />
                        <label for="notes" class="peer-focus:!top-[-0.75rem]"
                            :class="{ '!top-5': form.notes.trim() === '', '!top-[-0.75rem]': form.notes.trim() !== '' }">Observación</label>
                    </FloatLabel>
                </div>

                <Divider align="center" type="solid" class="text-lg text-primary-700 !m-0">
                    <b>Items del comprobante</b>
                </Divider>

                <div class="m-3 !mb-0">
                    <DataTable v-model:editingRows="editingRows" :value="voucherItems" scrollable scrollHeight="70vh" editMode="row" dataKey="id">
                        <Column field="description" header="Descripción" class="rounded-tl-lg">
                            <template #body="{ data }">
                                {{ data.description }}
                            </template>
                            <template #editor="{ data, field }">
                                <FloatLabel>
                                    <InputText class="w-full uppercase" v-model="data[field]" id="description" autocomplete="off"
                                        inputId="description" :invalid="data[field] !== undefined && data[field].trim() === ''" />
                                    <label for="description">Descripción</label>
                                </FloatLabel>
                                <InputError :message="data[field] !== undefined && data[field].trim() === '' ? rules : ''" />
                            </template>
                        </Column>

                        <Column field="amount" header="Importe" class="rounded-tl-lg">
                            <template #body="{ data }">
                                {{ currencyNumber(data.amount) }}
                            </template>
                            <template #editor="{ data, field }">
                                <FloatLabel>
                                    <InputNumber v-model="data[field]" placeholder="$ 0,00" inputId="amount" prefix="$" id="amount"
                                        class=":not(:focus)::placeholder:text-transparent" :min="0.01" :max="99999999" :minFractionDigits="2"
                                        :class="data[field] !== null && data[field] !== undefined ? 'filled' : ''" :invalid="data[field] <= 0"
                                        @input="calculateSubtotalAmount(data, $event.value, data['vat'])" />
                                    <label for="amount">Importe</label>
                                </FloatLabel>
                                <InputError :message="data[field] <= 0 ? rules : ''" />
                            </template>
                        </Column>

                        <Column field="vat" header="I.V.A." class="rounded-tl-lg">
                            <template #body="{ data }">
                                {{ percentNumber(data.vat) }}
                            </template>
                            <template #editor="{ data, field }">
                                <FloatLabel class="!top-[2px]">
                                    <Dropdown inputId="vatRate" v-model="data[field]" :options="vatRates" showClear :invalid="data[field] === null"
                                        optionLabel="label" optionValue="id" class="w-full !focus:border-primary-500"
                                        :class="dropdownClasses(data[field])" @change="calculateSubtotalAmount(data, data['amount'], $event.value)" />
                                    <template #option="slotProps">
                                        <Tag :value="slotProps.option.rate" class="bg-transparent uppercase" />
                                    </template>
                                    <label for="rate">I.V.A.</label>
                                </FloatLabel>
                                <InputError :message="data[field] === null ? rules : ''" />
                            </template>
                        </Column>

                        <Column field="subtotalAmount" header="Subtotal" class="rounded-tl-lg">
                            <template #body="{ data }">
                                {{ currencyNumber(data.subtotalAmount) }}
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>
    </div>
</template>