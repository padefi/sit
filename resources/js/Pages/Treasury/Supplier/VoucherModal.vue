<script setup>
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, ref, computed, watch } from "vue";
import { dropdownClasses } from '@/utils/cssUtils';
import { percentNumber, addDate, currencyNumber, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { v4 as uuidv4 } from 'uuid';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    id: uuidv4(),
    idSupplier: undefined,
    voucherType: undefined,
    voucherSubtype: undefined,
    voucherExpense: undefined,
    invoiceType: undefined,
    invoiceTypeCode: undefined,
    pointOfNumber: undefined,
    invoiceNumber: undefined,
    invoiceDate: undefined,
    invoiceDueDate: undefined,
    payCondition: undefined,
    notes: '',
    voucherItems: [],
    netAmount: 0,
    vatAmount: 0,
    totalAmount: 0,
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

const disabledEditButtons = (callback, event) => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de modificar un item.',
            life: 3000,
        });

        return;
    }

    editing.value = true;
    callback(event);
}
const enabledEditButtons = (callback, event, data) => {
    if (data.condition === 'newItem') {
        if (voucherItems.value.length <= 1) {
            toast.add({
                severity: 'error',
                detail: 'Debe completar todos los cambios.',
                life: 3000,
            });
        } else {
            editing.value = false;
            editingRows.value = [];
            voucherItems.value = voucherItems.value.filter((item) => item.id !== data.id);
            recalculateAmounts();
        }

        return;
    }

    editing.value = false;
    editingRows.value = [];
    callback(event);
}

/* Add new voucher item */
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
        id: uuidv4(),
        description: undefined,
        vat: undefined,
        amount: undefined,
        subtotalAmount: ref(0),
        condition: 'newItem',
    };

    voucherItems.value.unshift(newItem);
    editing.value = true;
    editingRows.value = [newItem];
};


const validate = (event, saveCallback, data) => {
    if (data.description === undefined || !data.description.trim() || !data.amount || !data.vat) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });

        return;
    }

    data.condition = 'editItem';
    editing.value = false;
    editingRows.value = [];
    saveCallback(event);
}
/* End add new voucher item */

/* Editing voucher item   */
const onRowEditInit = (event) => {
    originalVoucherItems.value = [...voucherItems.value];
    editingRows.value = [event.data];
}

const onRowEditSave = (event) => {
    if (event.data.condition === 'editItem') {
        let { newData, index } = event;
        voucherItems.value[index] = newData;
    }
}

const onRowEditCancel = () => {
    voucherItems.value = [...originalVoucherItems.value];
    editing.value = false;
    editingRows.value = [];
    recalculateAmounts();
};
/* End editing voucher item */

const recalculateAmounts = () => {
    form.netAmount = voucherItems.value.reduce((total, item) => {
        return total + (item.amount || 0);
    }, 0);

    form.vatAmount = voucherItems.value.reduce((total, item) => {
        return total + (item.subtotalAmount || 0) - (item.amount || 0);
    }, 0);

    form.totalAmount = voucherItems.value.reduce((total, item) => {
        return total + (item.subtotalAmount || 0);
    }, 0);
}

const removeItem = (event, data) => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de eliminar un item.',
            life: 3000,
        });

        return;
    }

    confirm.require({
        target: event.currentTarget,
        message: '¿Está seguro de eliminar el item?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            voucherItems.value = voucherItems.value.filter((item) => item.id !== data.id);
            recalculateAmounts();
        },
    });
};

const handleInvoiceNumber = (input, length) => {
    form[input] = !form[input] || form[input].padStart(length, '0') === '0'.padStart(length, '0') ? '' : form[input].padStart(length, '0');
};

const calculateSubtotalAmount = (data, amountValue, rateValue) => {
    if (amountValue > 99999999) return;

    // const rate = vatRates.value.find((rate) => rate.id === rateValue).rate || 0;
    const rate = data.vat ? parseFloat(vatRates.value.find((rate) => rate.id === rateValue).rate) : 0;
    data.subtotalAmount = rate > 0 ? amountValue * (1 + rate / 100) || 0 : amountValue || 0;

    form.netAmount = voucherItems.value.reduce((total, item) => {
        const amount = (data.id === item.id) ? amountValue : item.amount || 0;
        return total + amount;
    }, 0);

    form.vatAmount = voucherItems.value.reduce((total, item) => {
        const amount = (data.id === item.id) ? amountValue : item.amount || 0;
        const subtotalAmount = (data.id === item.id) ? data.subtotalAmount : item.subtotalAmount || 0;
        return total + subtotalAmount - amount;
    }, 0);

    form.totalAmount = voucherItems.value.reduce((total, item) => {
        const subtotalAmount = (data.id === item.id) ? data.subtotalAmount : item.subtotalAmount || 0;
        return total + subtotalAmount;
    }, 0);
};

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
    if (voucherItems.value.length === 0) {
        toast.add({
            severity: 'error',
            detail: 'Debe agregar al menos un item.',
            life: 3000,
        });

        return;
    }

    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de agregar un nuevo item.',
            life: 3000,
        });

        return;
    }

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
    form.idSupplier = dialogRef.value.data.supplierId;
    payConditions.value = dialogRef.value.data.payConditions;
    voucherTypes.value = dialogRef.value.data.voucherTypes;
    vatRates.value = await dialogRef.value.data.vatRates.map((vatRate) => {
        return { label: percentNumber(vatRate.rate), rate: vatRate.rate, id: vatRate.id };
    });

    if (dialogRef.value.data.voucherId) {
        const data = await getVoucher(dialogRef.value.data.voucherId);

        editingVoucher.value = true;
        form.id = data.id;
        form.voucherType = data.voucherType.id;

        await loadVoucherSubtypeData(data.voucherType.id);
        form.voucherSubtype = voucherSubtypes.value.find((subtype) => subtype.id === data.voucherSubtype.id) ? data.voucherSubtype.id : undefined;

        await loadVoucherExpenseData(data.voucherSubtype.id);
        const voucherExpense = data.voucherExpense ? data.voucherExpense.id : 0;
        form.voucherExpense = voucherExpenses.value.find((expense) => expense.id === voucherExpense) ? voucherExpense : undefined;
        form.invoiceType = data.invoiceType.id;

        await loadInvoiceTypeData(data.invoiceType.id);
        form.invoiceTypeCode = invoiceTypeCodes.value.find((invoiceTypeCode) => invoiceTypeCode.id === data.invoiceTypeCode.id) ? data.invoiceTypeCode.id : undefined;

        form.pointOfNumber = invoiceNumberFormat(data.pointOfNumber, 5);
        form.invoiceNumber = invoiceNumberFormat(data.invoiceNumber, 8);
        form.invoiceDate = addDate(data.invoiceDate, 1);
        form.invoiceDueDate = addDate(data.invoiceDueDate, 1);

        form.payCondition = data.payCondition.id;
        form.notes = data.notes || '';
        voucherItems.value = data.items.map((item) => {
            return { id: item.id, description: item.description, amount: item.amount, vat: item.VatRate.id, subtotalAmount: item.subtotalAmount, condition: 'editItem' };
        });

        recalculateAmounts();
        loading.value = false;
        return;
    }

    // setTimeout(async () => {
    addNewItem();
    // }, 100);
    loading.value = false;
});

const getVoucher = async (voucherId) => {
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
    if (loading.value) return;
    await loadVoucherSubtypeData(voucherTypeId);
});

watch(() => form.voucherSubtype, async (voucherSubtype) => {
    if (loading.value) return;
    await loadVoucherExpenseData(voucherSubtype);
});

watch(() => form.invoiceType, async (invoiceTypeId) => {
    if (loading.value) return;
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
        <div class="flex flex-col w-fit">
            <div
                class="flex flex-col border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 justify-center font-medium">
                <div class="flex gap-3 m-3 flex-wrap justify-center">
                    <div class="min-w-40">
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

                    <div class="min-w-72">
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

                    <div class="min-w-56">
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
                </div>

                <Divider align="center" type="dashed" class="text-lg text-primary-700 !m-0" />

                <div class="flex gap-3 m-3 flex-wrap justify-center">
                    <div class="min-w-64">
                        <template v-if="loading">
                            <Skeleton class="mb-2"></Skeleton>
                        </template>
                        <template v-if="!loading">
                            <FloatLabel class="!top-[2px]">
                                <Dropdown inputId="invoiceType" v-model="form.invoiceType" :options="invoiceTypes" filter showClear resetFilterOnHide
                                    :invalid="form.invoiceType === null" optionLabel="name" optionValue="id" class="w-full !focus:border-primary-500"
                                    :class="dropdownClasses(form.invoiceType)" />
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                                </template>
                                <label for="invoiceType">T. comp.</label>
                            </FloatLabel>
                            <InputError :message="form.invoiceType === null ? rules : ''" />
                        </template>
                    </div>

                    <div class="min-w-44">
                        <template v-if="loading">
                            <Skeleton class="mb-2"></Skeleton>
                        </template>
                        <template v-if="!loading">
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
                        </template>
                    </div>

                    <div class="flex min-w-60 gap-1">
                        <template v-if="loading">
                            <Skeleton class="mb-2"></Skeleton>
                        </template>
                        <template v-if="!loading">
                            <div class="max-w-24">
                                <FloatLabel>
                                    <InputText v-model="form.pointOfNumber" autocomplete="off" inputId="pointOfNumber" id="pointOfNumber"
                                        class="w-full :not(:focus)::placeholder:text-transparent" minlength="5" maxlength="5"
                                        :invalid="form.pointOfNumber && (form.pointOfNumber.trim() === '' || form.pointOfNumber === '')"
                                        @blur="handleInvoiceNumber('pointOfNumber', 5)" />
                                    <label for="pointOfNumber">Pto Vta</label>
                                </FloatLabel>
                                <InputError
                                    :message="form.pointOfNumber && form.pointOfNumber.trim() === '' || form.pointOfNumber === '' ? rules : ''" />
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
                        </template>
                    </div>
                </div>

                <Divider align="center" type="dashed" class="text-lg text-primary-700 !m-0" />

                <div class="flex gap-3 m-3 flex-wrap justify-center">
                    <div class="min-w-48 max-w-48">
                        <template v-if="loading">
                            <Skeleton class="mb-2"></Skeleton>
                        </template>
                        <template v-if="!loading">
                            <FloatLabel>
                                <Calendar v-model="form.invoiceDate" placeholder="DD/MM/AAAA" showButtonBar id="invoiceDate" class="w-full"
                                    :class="form.invoiceDate !== null && form.invoiceDate !== undefined ? 'filled' : ''" inputClass="w-full"
                                    :invalid="form.invoiceDate === null" :maxDate="new Date()" />
                                <label for="invoiceDate">F. emisión</label>
                            </FloatLabel>
                            <InputError :message="form.invoiceDate === null ? rules : ''" />
                        </template>
                    </div>

                    <div class="min-w-48 max-w-48">
                        <template v-if="loading">
                            <Skeleton class="mb-2"></Skeleton>
                        </template>
                        <template v-if="!loading">
                            <FloatLabel>
                                <Calendar v-model="form.invoiceDueDate" placeholder="DD/MM/AAAA" showButtonBar id="invoiceDueDate" class="w-full"
                                    :class="form.invoiceDueDate !== null && form.invoiceDueDate !== undefined ? 'filled' : ''" inputClass="w-full"
                                    :invalid="form.invoiceDueDate === null" :minDate="form.invoiceDate" />
                                <label for="invoiceDueDate">F. vencimiento</label>
                            </FloatLabel>
                            <InputError :message="form.invoiceDueDate === null ? rules : ''" />
                        </template>
                    </div>

                    <div class="min-w-72">
                        <template v-if="loading">
                            <Skeleton class="mb-2"></Skeleton>
                        </template>
                        <template v-if="!loading">
                            <FloatLabel class="!top-[2px]">
                                <Dropdown inputId="payCondition" v-model="form.payCondition" :options="payConditions" filter showClear
                                    resetFilterOnHide :invalid="form.payCondition === null" optionLabel="name" optionValue="id"
                                    class="w-full !focus:border-primary-500" :class="dropdownClasses(form.payCondition)" />
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                                </template>
                                <label for="payCondition">Cond. pago</label>
                            </FloatLabel>
                            <InputError :message="form.payCondition === null ? rules : ''" />
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
                            <Textarea v-model="form.notes" autocomplete="off" inputId="notes" id="notes" class="w-full resize-none peer uppercase"
                                :class="dropdownClasses(form.notes)" />
                            <label for="notes" class="peer-focus:!top-[-0.75rem]"
                                :class="{ '!top-5': form.notes.trim() === '', '!top-[-0.75rem]': form.notes.trim() !== '' }">Observación</label>
                        </FloatLabel>
                    </template>
                </div>

                <Divider align="center" type="solid" class="text-lg text-primary-700 !m-0">
                    <b>Items del comprobante</b>
                </Divider>

                <div class="m-3 !mb-0">
                    <DataTable v-model:editingRows="editingRows" :value="voucherItems" :loading="loading" scrollable scrollHeight="200px"
                        editMode="row" dataKey="id" @row-edit-init="onRowEditInit($event)" @row-edit-save="onRowEditSave"
                        @row-edit-cancel="onRowEditCancel" :pt="{ tbody: { class: 'thin-td' }, wrapper: { class: 'datatable-scrollbar' } }">
                        <template #empty>
                            <div :class="loading ? 'py-4' : ''">
                                <template v-if="!loading">
                                    Sin item cargados
                                </template>
                            </div>
                        </template>
                        <template #loading>
                            <ProgressSpinner class="!w-10 !h-10 mt-14" />
                        </template>
                        <Column field="description" header="Descripción" class="rounded-tl-lg min-w-56 max-w-56">
                            <template #body="{ data }">
                                {{ data.description.toLocaleUpperCase() || '' }}
                            </template>
                            <template #editor="{ data, field }">
                                <FloatLabel>
                                    <InputText class="w-full px-1 uppercase" v-model="data[field]" id="description" autocomplete="off"
                                        inputId="description" :invalid="data[field] !== undefined && data[field].trim() === ''" />
                                    <label class="!left-1" for="description">Descripción</label>
                                </FloatLabel>
                                <InputError :message="data[field] !== undefined && data[field].trim() === '' ? rules : ''" />
                            </template>
                        </Column>

                        <Column field="amount" header="Importe" class="rounded-tl-lg min-w-32 max-w-32">
                            <template #body="{ data }">
                                {{ currencyNumber(data.amount) }}
                            </template>
                            <template #editor="{ data, field }">
                                <FloatLabel>
                                    <InputNumber v-model="data[field]" placeholder="$ 0,00" :inputId="'amount' + '_' + (new Date()).getTime()"
                                        inputClass="w-full px-1" prefix="$" id="amount" class=":not(:focus)::placeholder:text-transparent" :min="0.01"
                                        :max="99999999" :minFractionDigits="2" @input="calculateSubtotalAmount(data, $event.value, data['vat'])"
                                        :class="data[field] !== null && data[field] !== undefined ? 'filled' : ''" :invalid="data[field] <= 0"
                                        :pt="{ input: { root: { autocomplete: 'off' } } }" />
                                    <label for="amount">Importe</label>
                                </FloatLabel>
                                <InputError :message="data[field] <= 0 ? rules : ''" />
                            </template>
                        </Column>

                        <Column field="vat" header="I.V.A." class="rounded-tl-lg min-w-24 max-w-24">
                            <template #body="{ data }">
                                {{ percentNumber(vatRates.find((rate) => rate.id === data.vat).rate) }}
                            </template>
                            <template #editor="{ data, field }">
                                <FloatLabel>
                                    <Dropdown inputId="vatRate" inputClass="w-full flex flex-wrap pl-2 pr-0" v-model="data[field]" :options="vatRates"
                                        :invalid="data[field] === null" optionLabel="label" optionValue="id"
                                        class="w-full !focus:border-primary-500 iva_dropdown" :class="dropdownClasses(data[field])"
                                        @change="calculateSubtotalAmount(data, data['amount'] || 0, $event.value)" />
                                    <template #option="slotProps">
                                        <Tag :value="slotProps.option.rate" class="bg-transparent uppercase" />
                                    </template>
                                    <label for="rate">I.V.A.</label>
                                </FloatLabel>
                                <InputError :message="data[field] === null ? rules : ''" />
                            </template>
                        </Column>

                        <Column field="subtotalAmount" header="Subtotal" class="rounded-tl-lg min-w-28 max-w-28">
                            <template #body="{ data }">
                                {{ currencyNumber(data.subtotalAmount) }}
                            </template>
                        </Column>
                        <Column header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28">
                            <template #body="{ editorInitCallback, data }">
                                <div class="space-x-4 text-center">
                                    <button v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                            @click="disabledEditButtons(editorInitCallback, $event)"></i></button>
                                    <template v-if="voucherItems.length > 1">
                                        <ConfirmPopup></ConfirmPopup>
                                        <button v-tooltip="'Eliminar'"><i class="pi pi-trash text-red-500 text-lg font-extrabold"
                                                @click="removeItem($event, data)"></i></button>
                                    </template>
                                </div>
                            </template>
                            <template #editor="{ data, editorSaveCallback, editorCancelCallback }">
                                <div class="space-x-4 text-center">
                                    <button v-tooltip="'Confirmar'"><i class="pi pi-check text-primary-500 text-lg font-extrabold"
                                            @click="validate($event, editorSaveCallback, data)"></i></button>
                                    <button v-tooltip="'Cancelar'"><i class="pi pi-times text-red-500 text-lg font-extrabold"
                                            @click="enabledEditButtons(editorCancelCallback, $event, data)"></i></button>
                                </div>
                            </template>
                        </Column>
                    </DataTable>

                    <div class="flex py-3 w-full justify-center">
                        <Button label="Agregar item" icon="pi pi-plus-circle" iconPos="right" @click="addNewItem()" />
                    </div>

                    <Divider class="!my-0" type="dashed" />

                    <div class="flex flex-col gap-3 m-3">
                        <div class="flex md:w-2/5">
                            <div class="w-full text-left text-surface-900/60 font-bold">Neto ARS: </div>
                            <template v-if="loading">
                                <Skeleton class="mb-2"></Skeleton>
                            </template>
                            <template v-if="!loading">
                                <div class="w-full text-left font-bold">{{ currencyNumber(form.netAmount) }}</div>
                            </template>
                        </div>

                        <div class="flex md:w-2/5">
                            <div class="w-full text-left text-surface-900/60 font-bold">Total IVA: </div>
                            <template v-if="loading">
                                <Skeleton class="mb-2"></Skeleton>
                            </template>
                            <template v-if="!loading">
                                <div class="w-full text-left font-bold">{{ currencyNumber(form.vatAmount) }}</div>
                            </template>
                        </div>

                        <div class="flex md:w-2/5">
                            <div class="w-full text-left text-surface-900/60 font-bold">Total a Pagar: </div>
                            <template v-if="loading">
                                <Skeleton class="mb-2"></Skeleton>
                            </template>
                            <template v-if="!loading">
                                <div class="w-full text-left font-bold">{{ currencyNumber(form.totalAmount) }}</div>
                            </template>
                        </div>
                    </div>

                    <Divider class="!my-0" type="dashed" />

                    <div class="flex p-3 justify-between">
                        <Button label="Cancelar" severity="danger" icon="pi pi-times" @click="closeDialog" />
                        <ConfirmPopup></ConfirmPopup>
                        <Button label="Finalizar" icon="pi pi-save" iconPos="right" :disabled="editing || isFormInvalid"
                            @click="saveVoucher($event)" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>