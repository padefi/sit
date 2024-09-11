<script setup>
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, ref, computed, watch } from "vue";
import { dropdownClasses } from '@/utils/cssUtils';
import { percentNumber, addDate, currencyNumber } from "@/utils/formatterFunctions";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import InputError from '@/Components/InputError.vue';

const form = useForm({
    id: crypto.randomUUID(),
    voucherDate: undefined,
    voucherType: undefined,
    voucherSubtype: undefined,
    voucherExpense: undefined,
    supplier: undefined,
    amount: undefined,
    notes: '',
});

const rules = 'Debe completar el campo';
const loading = ref(true);
const voucherTypes = ref([]);
const voucherSubtypes = ref([]);
const voucherExpenses = ref([]);
const suppliers = ref([]);
const editingVoucher = ref(false);
const confirm = useConfirm();
const toast = useToast();

const isFormInvalid = computed(() => {
    if (!form.voucherDate) return true;
    if (!form.voucherType) return true;
    if (!form.voucherSubtype) return true;
    if (form.voucherExpense === undefined || form.voucherExpense === null) return true;
    if (!form.supplier) return true;
    if (!form.amount || form.amount < 0) return true;

    return false;
});

const saveVoucher = (event) => {
    confirm.require({
        target: event.currentTarget,
        message: '¿Está seguro de ingresar el comprobante?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            if (!editingVoucher.value) {
                form.post(route("treasury-vouchers.store"), {
                    onSuccess: () => {
                        dialogRef.value.close();
                    },
                });
            } else {
                form.put(route("treasury-custom-vouchers.update", form.id), {
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
    await loadVoucherTypeData();

    if (dialogRef.value.data) {
        const data = await getVoucher(dialogRef.value.data);

        form.id = data.id;
        form.voucherDate = addDate(data.voucherDate, 0);
        form.voucherType = data.voucherType.id;

        await loadVoucherSubtypeData(data.voucherType.id);
        form.voucherSubtype = voucherSubtypes.value.find((subtype) => subtype.id === data.voucherSubtype.id) ? data.voucherSubtype.id : undefined;

        await loadVoucherExpenseData(data.voucherSubtype.id);
        await loadSupplierData(data.voucherSubtype.id);        
        const voucherExpense = data.voucherExpense ? data.voucherExpense.id : 0;
        form.voucherExpense = voucherExpenses.value.find((expense) => expense.id === voucherExpense) ? voucherExpense : undefined;
        form.supplier = suppliers.value.find((supplier) => supplier.id === data.supplier) ? data.supplier : undefined;
        form.amount = data.amount;
        form.notes = data.notes ? data.notes : '';
        editingVoucher.value = true;
    }

    loading.value = false;
});

const getVoucher = async (voucher) => {
    try {
        const response = await fetch(`/treasury-custom-vouchers/${voucher}`);

        if (!response.ok) {
            throw new Error('Error al obtener el comprobante');
        }

        const data = await response.json();
        return data.treasuryCustomVouchers[0];
    } catch (error) {
        console.error(error);
    }
}

const loadVoucherTypeData = async () => {
    form.voucherType = undefined;
    form.voucherSubtype = undefined;
    form.voucherExpense = undefined;
    voucherTypes.value = [];
    voucherSubtypes.value = [];
    voucherExpenses.value = [];

    try {
        const response = await fetch('/voucher-types/data');

        if (!response.ok) {
            throw new Error('Error al obtener los tipos de comprobantes');
        }

        const data = await response.json();
        voucherTypes.value = data.voucherTypes;
    } catch (error) {
        console.error(error);
    }
};

const loadVoucherSubtypeData = async (voucherTypeId) => {
    form.voucherSubtype = undefined;
    form.voucherExpense = undefined;
    voucherSubtypes.value = [];
    voucherExpenses.value = [];

    if (!voucherTypeId) return;

    try {
        const response = await fetch(`/voucher-subtypes/${voucherTypeId}/data-related`);

        if (!response.ok) {
            throw new Error('Error al obtener los datos relacionados');
        }

        const data = await response.json();
        voucherSubtypes.value = data.voucherSubtypes;
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
        form.voucherExpense = data.voucherExpenses.length === 0 ? 0 : undefined;
    } catch (error) {
        console.error(error);
    }
}

const loadSupplierData = async (voucherSubtype) => {
    form.supplier = undefined;
    suppliers.value = [];

    if (!voucherSubtype) return;

    try {
        const response = await fetch(`/suppliers/${voucherSubtype}/subtype-related`);

        if (!response.ok) {
            throw new Error('Error al obtener los proveedores');
        }

        const data = await response.json();
        suppliers.value = data.suppliers;
    } catch (error) {
        console.error(error);
    }
}

watch(() => form.voucherType, async (voucherTypeId) => {
    await loadVoucherSubtypeData(voucherTypeId);
});

watch(() => form.voucherSubtype, async (voucherSubtype) => {
    await loadVoucherExpenseData(voucherSubtype);
    await loadSupplierData(voucherSubtype);
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
                            <Calendar v-model="form.voucherDate" placeholder="DD/MM/AAAA" showButtonBar id="voucherDate" class="w-full"
                                :class="form.voucherDate !== null && form.voucherDate !== undefined ? 'filled' : ''" inputClass="w-full"
                                :invalid="form.voucherDate === null" :maxDate="new Date()" />
                            <label for="voucherDate">F. Comprobante</label>
                        </FloatLabel>
                        <InputError :message="form.voucherDate === null ? rules : ''" />
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
                            <Dropdown inputId="supplier" v-model="form.supplier" :options="suppliers" filter showClear resetFilterOnHide
                                :invalid="form.supplier === null" optionLabel="businessName" optionValue="id"
                                class="w-full !focus:border-primary-500 uppercase" :class="dropdownClasses(form.supplier)" />
                            <template #option="slotProps">
                                <Tag :value="slotProps.option.businessName" class="bg-transparent uppercase" />
                            </template>
                            <label for="supplier">Proveedor</label>
                        </FloatLabel>
                        <InputError :message="form.supplier === null ? rules : ''" />
                    </template>
                </div>

                <div class="w-1/4">
                    <template v-if="loading">
                        <Skeleton class="mb-2"></Skeleton>
                    </template>
                    <template v-if="!loading">
                        <FloatLabel>
                            <InputNumber v-model="form.amount" placeholder="$ 0,00" :inputId="'amount' + '_' + (new Date()).getTime()" mode="currency"
                                currency="ARS" locale="es-AR" id="amount" inputClass="w-full px-1" class=":not(:focus)::placeholder:text-transparent"
                                style="width: -webkit-fill-available;" :class="form.amount !== null && form.amount !== undefined ? 'filled' : ''"
                                :min="0" :minFractionDigits="2" :max="99999999" :invalid="form.amount === null" />
                            <label for="amount">Importe</label>
                        </FloatLabel>
                        <InputError :message="form.amount === null ? rules : ''" />
                    </template>
                </div>
            </div>

            <Divider align="center" type="dashed" class="text-lg text-primary-700 !m-0" />

            <div class="flex my-3 mx-3 ">
                <template v-if="loading">
                    <Skeleton class="mb-2"></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel class="w-full !top-[2px]">
                        <Textarea v-model="form.notes" rows="5" autocomplete="off" inputId="notes" id="notes"
                            class="w-full resize-none peer uppercase" :class="dropdownClasses(form.notes)" />
                        <label for="notes" class="peer-focus:!top-[-0.75rem]"
                            :class="{ '!top-5': form.notes.trim() === '', '!top-[-0.75rem]': form.notes.trim() !== '' }">Observación</label>
                    </FloatLabel>
                </template>
            </div>

            <Divider class="!my-0" type="dashed" />

            <div class="flex p-3 justify-between">
                <Button label="Cancelar" severity="danger" icon="pi pi-times" @click="closeDialog" />
                <ConfirmPopup></ConfirmPopup>
                <Button label="Finalizar" icon="pi pi-save" iconPos="right" :disabled="isFormInvalid" @click="saveVoucher($event)" />
            </div>
        </div>
    </div>
</template>