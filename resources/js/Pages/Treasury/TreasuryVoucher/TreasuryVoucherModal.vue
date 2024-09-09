<script setup>
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, ref, computed, watch } from "vue";
import { dropdownClasses } from '@/utils/cssUtils';
import { percentNumber, addDate, currencyNumber, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import InputError from '@/Components/InputError.vue';

const form = useForm({
    id: crypto.randomUUID(),
    idSupplier: undefined,
    voucherType: undefined,
    voucherSubtype: undefined,
    voucherExpense: undefined,
    amount: 0,
    notes: '',
});

const rules = 'Debe completar el campo';
const loading = ref(true);
const voucherTypes = ref([]);
const voucherSubtypes = ref([]);
const voucherExpenses = ref([]);
const invoiceTypes = ref([]);
const invoiceTypeCodes = ref([]);
const payConditions = ref([]);
const vatRates = ref([]);

const voucherItems = ref([]);
const originalVoucherItems = ref([]);
const editingRows = ref([]);
const editing = ref(false);
const editingVoucher = ref(false);
const confirm = useConfirm();
const toast = useToast();

const isFormInvalid = computed(() => {
    if (!form.voucherType) return true;
    if (!form.voucherSubtype) return true;
    if (form.voucherExpense === undefined || form.voucherExpense === null) return true;
    if (!form.invoiceType) return true;
    if (!form.invoiceTypeCode) return true;
    if (!form.pointOfNumber) return true;
    if (!form.invoiceNumber) return true;
    if (!form.invoiceDate) return true;
    if (!form.invoiceDueDate) return true;
    if (!form.payCondition) return true;
    if (form.netAmount < 0) return true;
    if (form.vatAmount < 0) return true;
    if (form.totalAmount < 0) return true;
    if (voucherItems.value.length === 0) return true;

    return false;
});

const saveVoucher = (event) => {
    confirm.require({
        target: event.currentTarget,
        message: '¿Está seguro de ingresar el comprobante?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            form.voucherItems = voucherItems.value;

            if (!editingVoucher.value) {
                form.post(route("vouchers.store"), {
                    onSuccess: () => {
                        dialogRef.value.close();
                    },
                });
            } else {
                form.put(route("vouchers.update", form.id), {
                    onSuccess: () => {
                        dialogRef.value.close();
                    },
                });
            }
        },
    });
};

const dialogRef = inject("dialogRef");

const closeDialog = () => {
    dialogRef.value.close();
}

onMounted(async () => {
    /* form.idSupplier = dialogRef.value.data.supplierId;
    payConditions.value = dialogRef.value.data.payConditions;
    voucherTypes.value = dialogRef.value.data.voucherTypes; */
    loading.value = false;
});

const getVhoucher = async (voucherId) => {
    try {
        const response = await fetch(`/vouchers/${voucherId}`);

        if (!response.ok) {
            throw new Error('Error al obtener el comprobante');
        }

        const data = await response.json();
        return data.voucher[0];
    } catch (error) {
        console.error(error);
    }
}

const loadVoucherSubtypeData = async (voucherTypeId) => {
    form.voucherSubtype = undefined;
    form.voucherExpense = undefined;
    form.invoiceType = undefined;
    form.invoiceTypeCode = undefined;
    voucherSubtypes.value = [];
    voucherExpenses.value = [];
    invoiceTypes.value = [];
    invoiceTypeCodes.value = [];

    if (!voucherTypeId) return;

    try {
        const [subtypeResponse, invoiceTypeResponse] = await Promise.all([
            fetch(`/voucher-subtypes/${voucherTypeId}/data-related`),
            fetch(`/vouchers/${voucherTypeId}/types-related`)
        ]);

        if (!subtypeResponse.ok || !invoiceTypeResponse.ok) {
            throw new Error('Error al obtener los datos relacionados');
        }

        const [dataSubtype, dataInvoiceType] = await Promise.all([
            subtypeResponse.json(),
            invoiceTypeResponse.json()
        ]);

        voucherSubtypes.value = dataSubtype.voucherSubtypes;
        invoiceTypes.value = dataInvoiceType.invoiceTypes;
    } catch (error) {
        console.error(error);
    }
};

const loadVoucherExpenseData = async (voucherSubtype) => {
    form.voucherExpense = undefined;
    voucherExpenses.value = [];

    if (!voucherSubtype) return;

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
}

const loadInvoiceTypeData = async (invoiceTypeId) => {
    form.invoiceTypeCode = undefined;
    invoiceTypeCodes.value = [];

    if (!invoiceTypeId) return;

    try {
        const response = await fetch(`/vouchers/${invoiceTypeId}/invoice-types-related`);

        if (!response.ok) {
            throw new Error('Error al obtener los tipos de factura relacionados');
        }

        const data = await response.json();
        invoiceTypeCodes.value = data.invoiceTypeCodes;
    } catch (error) {
        console.error(error);
    }
}

watch(() => form.voucherType, async (voucherTypeId) => {
    await loadVoucherSubtypeData(voucherTypeId);
});

watch(() => form.voucherSubtype, async (voucherSubtype) => {
    await loadVoucherExpenseData(voucherSubtype);
});

watch(() => form.invoiceType, async (invoiceTypeId) => {
    await loadInvoiceTypeData(invoiceTypeId);
});
</script>
<style>
.thin-td tr td {
    padding: 0.5rem 0.25rem !important;
}

.iva_dropdown div[data-pc-section="trigger"] {
    /* padding: 0.5rem 0.25rem !important; */
    width: 1rem;
    right: 0.5rem;
    position: relative;
}
</style>
<template>
    <div class="card flex !p-2 !m-0 justify-center">
        <div class="flex flex-col w-full">
            <div class="flex gap-3 m-3 w-auto justify-center">
                <div class="w-1/4">
                    <template v-if="loading">
                        <Skeleton class="mb-2"></Skeleton>
                    </template>
                    <template v-if="!loading">
                        <FloatLabel>
                            <Calendar v-model="form.invoiceDate" placeholder="DD/MM/AAAA" showButtonBar id="invoiceDate" class="w-full"
                                :class="form.invoiceDate !== null && form.invoiceDate !== undefined ? 'filled' : ''" inputClass="w-full"
                                :invalid="form.invoiceDate === null" :maxDate="new Date()" />
                            <label for="invoiceDate">F. Comprobante</label>
                        </FloatLabel>
                        <InputError :message="form.invoiceDate === null ? rules : ''" />
                    </template>
                </div>

                <div class="w-1/4">
                    <template v-if="loading">
                        <Skeleton class="mb-2"></Skeleton>
                    </template>
                    <template v-if="!loading">
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
                    </template>
                </div>

                <div class="w-2/4">
                    <template v-if="loading">
                        <Skeleton class="mb-2"></Skeleton>
                    </template>
                    <template v-if="!loading">
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
                    </template>
                </div>
            </div>

            <div class="flex gap-3 m-3 w-auto justify-center">
                <div class="w-1/4">
                    <template v-if="loading">
                        <Skeleton class="mb-2"></Skeleton>
                    </template>
                    <template v-if="!loading">
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
                    </template>
                </div>

                <div class="w-2/4">
                    <template v-if="loading">
                        <Skeleton class="mb-2"></Skeleton>
                    </template>
                    <template v-if="!loading">
                        <FloatLabel class="!top-[2px]">
                            <Dropdown inputId="voucherExpense" v-model="form.voucherExpense" :options="voucherExpenses" filter showClear
                                resetFilterOnHide :invalid="form.voucherExpense === null" optionLabel="name" optionValue="id"
                                class="w-full !focus:border-primary-500" :class="dropdownClasses(form.voucherExpense)" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                            </template>
                            <label for="voucherExpense">Proveedor</label>
                        </FloatLabel>
                        <InputError :message="form.voucherExpense === null ? rules : ''" />
                    </template>
                </div>

                <div class="w-1/4">
                    <template v-if="loading">
                        <Skeleton class="mb-2"></Skeleton>
                    </template>
                    <template v-if="!loading"></template>
                    <FloatLabel>
                        <InputNumber v-model="form.amount" placeholder="$ 0,00" :inputId="'amount' + '_' + (new Date()).getTime()" mode="currency"
                            currency="ARS" locale="es-AR" id="amount" inputClass="w-full px-1" class=":not(:focus)::placeholder:text-transparent"
                            :class="form.amount !== null ? 'filled' : ''" :min="0" :minFractionDigits="2" :max="99999999"
                            :invalid="form.amount === null" />
                        <label for="amount">Importe</label>
                    </FloatLabel>
                    <InputError :message="form.amount === null ? rules : ''" />
                </div>
            </div>

            <Divider align="center" type="dashed" class="text-lg text-primary-700 !m-0" />

            <div class="flex my-3 mx-3 ">
                <template v-if="loading">
                    <Skeleton class="mb-2"></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel class="w-full !top-[2px]">
                        <Textarea v-model="form.notes" autocomplete="off" inputId="notes" id="notes" class="w-full resize-none peer uppercase"
                            :class="dropdownClasses(form.notes)" />
                        <label for="notes" class="peer-focus:!top-[-0.75rem]"
                            :class="{ '!top-5': form.notes.trim() === '', '!top-[-0.75rem]': form.notes.trim() !== '' }">Observación</label>
                    </FloatLabel>
                </template>
            </div>

            <Divider class="!my-0" type="dashed" />

            <div class="flex p-3 justify-between">
                <Button label="Cancelar" severity="danger" icon="pi pi-times" @click="closeDialog" />
                <ConfirmPopup></ConfirmPopup>
                <Button label="Finalizar" icon="pi pi-save" iconPos="right" :disabled="editing || isFormInvalid" @click="saveVoucher($event)" />
            </div>
        </div>
    </div>
</template>