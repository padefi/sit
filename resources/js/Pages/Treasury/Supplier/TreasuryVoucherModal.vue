<script setup>
import { inject, onMounted, ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { currencyNumber, dateFormat, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { compareDates } from "@/utils/validateFunctions";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import InputError from '@/Components/InputError.vue';

const form = useForm({
    vouchers: [],
    totalPaymentAmount: 0,
});

const treasuryVouchersArray = ref([]);
const originalTreasuryVouchersArray = ref([]);
const loading = ref(true);
const isProcessing = ref(false);
const editing = ref(false);
const editingRows = ref([]);
const confirm = useConfirm();
const toast = useToast();
const rules = 'Debe completar el campo'

const getVouchers = async () => {
    try {
        const response = await fetch(`/vouchers/${dialogRef.value.data.supplierId}/pending-to-pay`);

        if (!response.ok) {
            throw new Error('Error al obtener los comprobantes del proveedor pendientes de pago');
        }

        const data = await response.json();
        data.vouchers.map(voucher => {
            voucher.paymentAmount = voucher.pendingToPay;
            voucher.checked = false;
        })

        treasuryVouchersArray.value = data.vouchers;
    } catch (error) {
        console.error(error);
    }
};

const dialogRef = inject("dialogRef");

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
};

const validateAmount = (event, saveCallback, data) => {
    if (!data.paymentAmount) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar el importe.',
            life: 3000,
        });

        return;
    }

    editing.value = false;
    editingRows.value = [];
    saveCallback(event);
}

const calculatePaymentAmount = (event, data) => {
    form.totalPaymentAmount = treasuryVouchersArray.value.reduce((total, voucher) => {
        if (voucher.id === data.id) {
            const amount = event.value <= voucher.pendingToPay ? event.value || 0 : voucher.pendingToPay || 0;
            return total + amount;
        }

        if (voucher.checked && voucher.id !== data.id) {
            return total + (voucher.paymentAmount || 0);
        }

        return total;
    }, 0);
}

const recalculatePaymentAmount = () => {
    form.totalPaymentAmount = treasuryVouchersArray.value.reduce((total, voucher) => {
        if (voucher.checked) {
            return total + (voucher.paymentAmount || 0);
        }

        return total;
    }, 0);
}

const setTotalPaymentAmount = (event, data) => {
    const paymentAmount = (data.voucherType.id === 2) ? data.paymentAmount : data.paymentAmount * -1;

    form.totalPaymentAmount = event.target.checked ? form.totalPaymentAmount + paymentAmount : form.totalPaymentAmount - paymentAmount;
    if (!event.target.checked) data.paymentAmount = data.pendingToPay;
}

const enabledEditButtons = (callback, event) => {
    treasuryVouchersArray.value = [...originalTreasuryVouchersArray.value];
    editing.value = false;
    editingRows.value = [];
    callback(event);
}

const onRowEditInit = (event) => {
    originalTreasuryVouchersArray.value = [...treasuryVouchersArray.value];
    editingRows.value = [event.data];
}

const onRowEditSave = (event) => {
    let { newData, index } = event;
    treasuryVouchersArray.value[index] = newData;
    recalculatePaymentAmount();
}

const onRowEditCancel = () => {
    recalculatePaymentAmount();
}

const saveTreasuryVoucher = (event) => {
    if (form.totalPaymentAmount === 0) {
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
        message: '¿Está seguro de generar la orden de pago?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            isProcessing.value = true;
            const filteredVouchers = treasuryVouchersArray.value.filter(voucher => voucher.checked);

            form.vouchers = filteredVouchers.map(voucher => ({
                id: voucher.id,
                idSupplier: dialogRef.value.data.supplierId,
                paymentAmount: voucher.paymentAmount
            }));

            form.post(route("vouchers.voucher-to-treasury"), {
                onSuccess: () => {
                    dialogRef.value.close();
                },
            });
        },
    });
};

const closeDialog = () => {
    dialogRef.value.close();
}

onMounted(async () => {
    await getVouchers();
    loading.value = false;

    Echo.channel('vouchers')
        .listen('Treasury\\Voucher\\VoucherEvent', (e) => {
            if (e.type === 'voucherToTreasury') {
                setTimeout(async () => {
                    await getVouchers();
                }, 200);
            }
        });
});
</script>
<template>
    <DataTable :value="treasuryVouchersArray" v-model:editingRows="editingRows" :loading="loading" editMode="row" scrollable scrollHeight="20vh"
        dataKey="id" filterDisplay="menu" :pt="{
        table: { style: 'min-width: 50rem' }, wrapper: { class: 'datatable-scrollbar' },
        paginator: {
            root: { class: 'p-paginator-custom' },
            current: { class: 'p-paginator-current' },
        }
    }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table uppercase" @row-edit-init="onRowEditInit($event)"
        @row-edit-save="onRowEditSave" @row-edit-cancel="onRowEditCancel">
        <template #empty>
            <div :class="loading ? 'py-4' : ''">
                <template v-if="!loading">
                    Sin comprobantes cargados
                </template>
            </div>
        </template>
        <template #loading>
            <ProgressSpinner class="!w-10 !h-10" />
        </template>
        <Column header="Comprobante">
            <template #body="{ data }">
                {{ data.invoiceType.name }}
                <span class="font-bold">{{ data.invoiceTypeCode.name }}</span>
            </template>
        </Column>
        <Column header="Número">
            <template #body="{ data }">
                {{ invoiceNumberFormat(data.pointOfNumber, 5) }} - {{ invoiceNumberFormat(data.invoiceNumber, 8) }}
            </template>
        </Column>
        <Column header="F. vencimiento">
            <template #body="{ data }">
                <span :class="{ 'text-red-500': compareDates(data.invoicePaymentDate, '', 'before') }">
                    {{ dateFormat(data.invoicePaymentDate) }}
                </span>
            </template>
        </Column>
        <Column header="Cond. Pago">
            <template #body="{ data }">
                {{ data.payCondition.name }}
            </template>
        </Column>
        <Column field="paymentAmount" header="Importe" class="min-w-36 max-w-36">
            <template #body="{ data }">
                {{ currencyNumber(data.paymentAmount) }}
            </template>
            <template #editor="{ data, field }">
                <FloatLabel>
                    <InputNumber v-model="data[field]" placeholder="$ 0,00" :inputId="'paymentAmount' + '_' + (new Date()).getTime()" mode="currency"
                        currency="ARS" locale="es-AR" id="paymentAmount" inputClass="w-full px-1" class=":not(:focus)::placeholder:text-transparent"
                        :class="data[field] !== null ? 'filled' : ''" :min="0" :minFractionDigits="2" @input="calculatePaymentAmount($event, data)"
                        :invalid="data[field] === null" />
                    <label for="paymentAmount">Importe</label>
                </FloatLabel>
                <InputError :message="data[field] === null ? rules : ''" />
            </template>
        </Column>
        <Column header="Saldo">
            <template #body="{ data }">
                {{ currencyNumber(data.pendingToPay) }}
            </template>
        </Column>
        <Column field="checked" header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28">
            <template #body="{ editorInitCallback, data }">
                <div class="space-x-4 flex justify-center">
                    <Checkbox v-model="data.checked" binary @click="setTotalPaymentAmount($event, data)" />
                    <template v-if="data.checked && data.payCondition.id === 2">
                        <button class="bottom-[0.2rem] relative" v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                @click="disabledEditButtons(editorInitCallback, $event)"></i></button>
                    </template>
                </div>
            </template>
            <template #editor="{ data, editorSaveCallback, editorCancelCallback }">
                <div class="space-x-4 flex justify-center">
                    <ConfirmPopup></ConfirmPopup>
                    <button><i class="pi pi-check text-primary-500 text-lg font-extrabold"
                            @click="validateAmount($event, editorSaveCallback, data)"></i></button>
                    <button><i class="pi pi-times text-red-500 text-lg font-extrabold"
                            @click="enabledEditButtons(editorCancelCallback, $event, data)"></i></button>
                </div>
            </template>
        </Column>
    </DataTable>

    <div class="flex flex-col mx-3 my-4">
        <div class="flex md:w-2/5">
            <div class="w-full text-left text-surface-900/60 font-bold">Total a Pagar: </div>
            <div class="w-full text-left font-bold">{{ currencyNumber(form.totalPaymentAmount) }}</div>
        </div>
    </div>

    <Divider class="!my-0" type="dashed" />

    <div class="flex p-3 justify-between">
        <Button label="Cancelar" severity="danger" icon="pi pi-times" @click="closeDialog" />
        <ConfirmPopup></ConfirmPopup>
        <Button label="Finalizar" icon="pi pi-save" iconPos="right" :disabled="form.totalPaymentAmount === 0 || editing || isProcessing"
            @click="saveTreasuryVoucher($event)" />
    </div>
</template>