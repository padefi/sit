<script setup>
import { ref, onMounted } from 'vue';
import { dropdownClasses } from '@/utils/cssUtils';
import { currencyNumber, roundedNumber, dateFormat, dateTimeFormat, invoiceNumberFormat, addDate } from "@/utils/formatterFunctions";
import { compareDates } from "@/utils/validateFunctions";
import { usePermissions } from '@/composables/permissions';
import { useDialog } from 'primevue/usedialog';
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import treasuryVoucherModal from './TreasuryVoucherModal.vue';
import incomeTreasuryVoucherModalConfirm from './IncomeTreasuryVoucherModalConfirm.vue';

const form = useForm({
    vouchers: [],
    totalIncomeAmount: 0,
});

const { hasPermission, hasPermissionColumn } = usePermissions();
const loading = ref(true);
const selectStatus = ref(0);
const treasuryVouchersArray = ref([]);
const voidNotesIncome = ref('');
const expandedRows = ref([]);
const paymentMethodPanel = ref();
const dataPaymentMethodArray = ref([]);
const voidedVoucherPanel = ref();
const dataVoidedVoucherArray = ref([]);
const paymentMethodsSelect = ref([]);
const banksSelect = ref([]);
const isProcessing = ref(false);
const toast = useToast();
const confirm = useConfirm();

const filters = ref({
    cuit: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    businessName: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    amount: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
    paymentMethod: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
    bank: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
    paymentDate: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.DATE_IS }] },
});

const fetchIncomeTreasuryVouchers = async (type, status) => {
    selectStatus.value = status;
    treasuryVouchersArray.value = [];
    loading.value = true;

    if (type && status) {
        try {
            const response = await fetch(`/treasury-vouchers/${type}/${status}`);

            if (!response.ok) {
                throw new Error('Error al obtener datos de comprobates a ingresar');
            }

            const data = await response.json();
            treasuryVouchersArray.value = data.treasuryVouchers.map(treasuryVoucher => treasuryVoucherDataStructure(treasuryVoucher));
        } catch (error) {
            console.error(error);
        }
    }

    loading.value = false;
}

const onRowExpand = (data) => {
    const originalExpandedRows = { ...expandedRows.value };
    const newExpandedRows = treasuryVouchersArray.value.reduce((acc) => (acc[data.id] = true) && acc, {});
    expandedRows.value = { ...originalExpandedRows, ...newExpandedRows };
}

const onRowCollapse = (data) => {
    delete expandedRows.value[data.id];
}

const treasuryVoucherDataStructure = (treasuryVoucher) => {
    const data = treasuryVoucher;

    return {
        id: data.id,
        supplierId: data.supplier.id,
        cuit: data.supplier.cuit,
        businessName: data.supplier.businessName,
        status: data.voucherStatus.id,
        amount: data.totalAmount,
        paymentMethod: data.paymentMethod ? data.paymentMethod.name : '',
        bank: data.bankAccount.bank ? data.bankAccount.bank.name : '',
        bankAccount: data.bankAccount ? data.bankAccount.accountNumber : '',
        transactionNumber: data.bankTransaction ? data.bankTransaction.number : data.checkTransaction ? data.checkTransaction.number : '',
        paymentDate: addDate(data.paymentDate, 1) ?? null,
        withholdings: {
            incomeTax: 0,
            socialTax: 0,
            vatTax: 0,
        },
        totalAmount: data.totalAmount,
        vouchers: data.voucherToTreasury.map(voucher => voucherDataStructure(voucher)),
        customVoucher: data.treasuryCustomVoucher ? customVoucherDataStructure(data.treasuryCustomVoucher) : null,
        voidedVoucher: data.voidedTreasuryVoucher ? voidedVoucherDataStructure(data.voidedTreasuryVoucher) : null,
    }
}

const voucherDataStructure = (voucherToTreasury) => {
    const data = voucherToTreasury.voucher;

    return {
        invoiceTypeName: data.invoiceType.name,
        invoiceTypeCodeName: data.invoiceTypeCode.name,
        pointOfNumber: data.pointOfNumber,
        invoiceNumber: data.invoiceNumber,
        invoiceDueDate: data.invoiceDueDate,
        amount: voucherToTreasury.amount,
    }
}

const customVoucherDataStructure = (customVoucher) => {
    return [{
        id: customVoucher.id,
        voucherSubtype: customVoucher.voucherSubtype.name,
        voucherExpense: customVoucher.voucherExpense ? customVoucher.voucherExpense.name : 'SIN DATOS',
        amount: customVoucher.amount,
        notes: customVoucher.notes,
        voucherDate: customVoucher.voucherDate,
    }];
}

const voidedVoucherDataStructure = (voidedVoucher) => {
    return [{
        userVoided: voidedVoucher.userVoided ? voidedVoucher.userVoided.name + ' ' + voidedVoucher.userVoided.surname : 'SIN DATOS',
        notes: voidedVoucher.notes,
        voided_at: voidedVoucher.voided_at,
    }];
}

const setTotalIncomeAmount = (event, data) => {
    form.totalIncomeAmount = event.target.checked
        ? roundedNumber(form.totalIncomeAmount || 0) + roundedNumber(data.amount || 0)
        : roundedNumber(form.totalIncomeAmount || 0) - roundedNumber(data.amount || 0);
    if (!event.target.checked) data.paymentAmount = data.pendingToPay;
}

const getPaymentMethods = async () => {
    try {
        const response = await fetch('/payment-methods/1');

        if (!response.ok) {
            throw new Error('Error al obtener los datos de los tipos de pago');
        }

        const data = await response.json();
        paymentMethodsSelect.value = data.paymentMethods.map((paymentMethod) => {
            return { label: paymentMethod.name, value: paymentMethod.name };
        });
    } catch (error) {
        console.error(error);
    }
}

const getBanks = async () => {
    try {
        const response = await fetch('/banks/show-banks');

        if (!response.ok) {
            throw new Error('Error al obtener los datos de los bancos');
        }

        const data = await response.json();
        banksSelect.value = data.banks.map((bank) => {
            return { label: bank.name, value: bank.name };
        });
    } catch (error) {
        console.error(error);
    }
}

const editTreasuryVoucher = (data) => {
    dialogInfo.open(treasuryVoucherModal, {
        props: {
            header: 'Editar comprobante',
            style: {
                width: '55vw',
            },
            breakpoints: {
                '960px': '75vw',
            },
            modal: true,
            contentStyle: {
                padding: '1.25rem',
            },
        },
        data: data.customVoucher[0].id
    });
}

const voidTreasuryVoucherIncome = (event, data) => {
    voidNotesIncome.value = '';
    const isVoidNoteEmpty = () => !voidNotesIncome.value || voidNotesIncome.value.trim() === '';

    confirm.require({
        target: event.currentTarget,
        group: 'voidTreasuryVoucherIncome',
        header: 'Anular comprobante',
        message: '¿Está seguro de anular el comprobante?',
        onShow: () => voidNotesIncome.value = '',
        onHide: () => voidNotesIncome.value = '',
        accept: () => {
            if (isVoidNoteEmpty()) {
                toast.add({
                    severity: 'error',
                    detail: 'Debe completar el motivo de la anulación.',
                    life: 3000,
                });

                return false;
            }

            const form = useForm({
                idTV: data.id,
                notes: voidNotesIncome.value,
            });

            form.put(route("treasury-vouchers.void", data.id), {
                onError: (error) => {
                    toast.add({
                        severity: 'error',
                        detail: error.response.data.message,
                        life: 3000,
                    });
                },
            });
        },
        reject: () => {
            voidNotesIncome.value = '';
        },
    });
}

const handleConfirmAccept = (acceptCallback) => {
    const isVoidNoteEmpty = () => !voidNotesIncome.value || voidNotesIncome.value.trim() === '';

    if (isVoidNoteEmpty()) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar el motivo de la anulación.',
            life: 3000,
        });

        return false;
    }

    acceptCallback();
}

const confirmTreasuryVoucherModal = () => {
    if (form.totalIncomeAmount === 0) {
        toast.add({
            severity: 'error',
            detail: 'Debe seleccionar al menos un comprobante.',
            life: 3000,
        });

        return;
    }

    const filteredVouchers = treasuryVouchersArray.value.filter(voucher => voucher.checked);

    form.vouchers = filteredVouchers.map(voucher => ({
        id: voucher.id,
        supplierId: voucher.supplierId,
        businessName: voucher.businessName,
        amount: voucher.amount,
        withholdings: voucher.withholdings,
        totalAmount: voucher.totalAmount,
        paymentMethod: undefined,
        bankId: undefined,
        bankAccountId: undefined,
        transactionNumber: undefined,
        transactionNumberStatus: 0,
        paymentDate: undefined,
        vouchers: voucher.vouchers,
    }));

    dialogInfo.open(incomeTreasuryVoucherModalConfirm, {
        props: {
            header: 'Comprobantes a egresar',
            style: {
                width: '98vw',
            },
            breakpoints: {
                '960px': '75vw',
                '640px': '98vw'
            },
            modal: true
        },
        data: {
            form,
            status: selectStatus.value,
        },
        onClose: (response) => {
            if (response.data === 'confirm') {
                form.vouchers = [];
                form.totalIncomeAmount = 0;
            }
        },
    });
}

const exportTreasuryVouchers = async () => {
    window.location.href = route('treasury-vouchers.export', [1, selectStatus.value]);
}

const paymentMethodInfo = (data, event) => {
    dataPaymentMethodArray.value = [];

    const dataArray = [{
        bankAccount: data.bankAccount,
        transactionNumber: data.transactionNumber,
    }];

    dataPaymentMethodArray.value = dataArray;
    paymentMethodPanel.value.toggle(event);
}

const voidedTreasuryVoucherIncomeInfo = (data, event) => {
    dataVoidedVoucherArray.value = data;
    voidedVoucherPanel.value.toggle(event);
}

onMounted(async () => {
    await getPaymentMethods();
    await getBanks();

    Echo.channel('treasuryVouchers')
        .listen('Treasury\\TreasuryVoucher\\TreasuryVoucherEvent', (e) => {
            if (e.type === 'create') {
                if (!treasuryVouchersArray.value.some(treasuryVoucher => treasuryVoucher.id === e.treasuryVoucherId)
                    && e.treasuryVoucher.voucherStatus.id === selectStatus.value
                    && e.treasuryVoucher.voucherType.id === 1) {
                    const dataTreasuryVoucher = treasuryVoucherDataStructure(e.treasuryVoucher);
                    treasuryVouchersArray.value.unshift(dataTreasuryVoucher);
                }
            } else if (e.type === 'update') {
                const index = treasuryVouchersArray.value.findIndex(treasuryVoucher => treasuryVoucher.id === e.treasuryVoucher.id);

                if (e.treasuryVoucher.voucherStatus.id === selectStatus.value
                    && e.treasuryVoucher.voucherType.id === 1) {
                    const dataTreasuryVoucher = treasuryVoucherDataStructure(e.treasuryVoucher);

                    if (index !== -1) {
                        treasuryVouchersArray.value[index] = dataTreasuryVoucher;
                    } else {
                        treasuryVouchersArray.value.unshift(dataTreasuryVoucher);
                    }
                } else {
                    treasuryVouchersArray.value.splice(index, 1);
                }
            }
        });
});

/*  */
import infoModal from '@/Components/InfoModal.vue';

const dialogInfo = useDialog();

const info = (id) => {
    axios.get(`/treasury-voucher/${id}/info`)
        .then((response) => {
            const data = response.data;
            const header = 'Información del comprobante';

            dialogInfo.open(infoModal, {
                props: {
                    header: header,
                    style: {
                        width: '75vw',
                    },
                    breakpoints: {
                        '960px': '75vw',
                        '640px': '90vw'
                    },
                    modal: true
                },
                data: data
            });
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                detail: error.response.data.message,
                life: 3000,
            });
        });
}
/*  */

defineExpose({ fetchIncomeTreasuryVouchers });
</script>
<template>
    <DataTable :value="treasuryVouchersArray" v-model:filters="filters" v-model:expandedRows="expandedRows" :loading="loading" scrollable
        scrollHeight="45vh" dataKey="id" filterDisplay="menu" @row-expand="onRowExpand($event)" @row-collapse="onRowCollapse($event)" :pt="{
            table: { style: 'min-width: 50rem' }, tbody: { class: 'thin-td' }, wrapper: { class: 'datatable-scrollbar' },
            paginator: {
                root: { class: 'p-paginator-custom' },
                current: { class: 'p-paginator-current' },
            }
        }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table">
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
        <Column expander class="min-w-2 w-2 !px-0" />
        <Column field="cuit" header="Cuit" sortable>
            <template #body="{ data }">
                {{ data.cuit }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="cuit" autocomplete="off" class="p-column-filter"
                    placeholder="Buscar por CUIT" />
            </template>
        </Column>
        <Column field="businessName" header="Proveedor" :class="selectStatus === 2 ? 'w-[30%]' : ''" sortable>
            <template #body="{ data }">
                {{ data.businessName }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="businessName" autocomplete="off"
                    class="p-column-filter" placeholder="Buscar por nombre fantasía" />
            </template>
        </Column>
        <Column field="amount" header="Importe" sortable>
            <template #body="{ data }">
                {{ currencyNumber(data.amount) }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputNumber v-model="filterModel.value" @blur="filterCallback()" placeholder="$ 0,00" mode="currency" currency="ARS" locale="es-AR"
                    name="amount" :min="0" :max="99999999" :minFractionDigits="2" :pt="{ input: { root: { autocomplete: 'off' } } }" />
            </template>
        </Column>
        <Column field="paymentMethod" header="Forma de ingreso" v-if="selectStatus === 2" sortable>
            <template #body="{ data }">
                {{ data.paymentMethod }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="paymentMethodsSelect" placeholder="Forma de Pago"
                    class="p-column-filter" optionLabel="label" optionValue="value" style="min-width: 12rem" :showClear="true" />
            </template>
        </Column>
        <Column field="bank" header="Banco" v-if="selectStatus === 2" sortable>
            <template #body="{ data }">
                <template v-if="data.paymentMethod !== 'EFECTIVO'">
                    <Button :label="data.bank"
                        class="!p-1.5 hover:cursor-pointer hover:border-emerald-500 hover:!text-emerald-500 hover:bg-transparent focus:ring-0"
                        @click="paymentMethodInfo(data, $event)" severity="secondary" outlined />
                </template>
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="banksSelect" placeholder="Forma de Pago"
                    class="p-column-filter" optionLabel="label" optionValue="value" style="min-width: 12rem" :showClear="true" />
            </template>
        </Column>
        <Column field="paymentDate" header="F. pago" dataType="date" v-if="selectStatus === 2" sortable>
            <template #body="{ data }">
                {{ dateFormat(data.paymentDate) }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <Calendar v-model="filterModel.value" @blur="filterCallback();" dateFormat="dd/mm/yy" placeholder="dd/mm/yyyy" mask="99/99/9999"
                    name="paymentDate" class="p-column-filter" />
            </template>
        </Column>
        <Column header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28">
            <template #body="{ data }">
                <div class="space-x-2 flex justify-center">
                    <template v-if="data.status === 1">
                        <Checkbox v-model="data.checked" binary @click="setTotalIncomeAmount($event, data)" />
                    </template>
                    <template v-if="data.status === 3">
                        <button v-tooltip="'Motivo anulación'" class="bottom-[0.2rem] relative"><i class="pi pi-info-circle text-red-700 text-2xl"
                                @click="voidedTreasuryVoucherIncomeInfo(data.voidedVoucher, $event)"></i></button>
                    </template>
                    <template v-if="hasPermission('view users')">
                        <button v-tooltip="'+Info'" class="bottom-[0.2rem] relative"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                @click="info(data.id)"></i></button>
                    </template>
                    <template v-if="hasPermission('edit treasury vouchers')">
                        <template v-if="data.customVoucher && data.status === 1">
                            <button v-tooltip="'Editar'" class="bottom-[0.2rem] relative"><i
                                    class="pi pi-pencil text-orange-500 text-lg font-extrabold" @click="editTreasuryVoucher(data)"></i></button>
                        </template>
                        <template v-if="data.status === 1">
                            <ConfirmPopup group="voidTreasuryVoucherIncome">
                                <template #container="{ message, acceptCallback, rejectCallback }">
                                    <div class="flex flex-col p-5 items-center">
                                        <p>{{ message.message }}</p>
                                        <FloatLabel class="w-full !top-3">
                                            <Textarea v-model="voidNotesIncome" maxlength="250" rows="2" autocomplete="off" id="voidNotesIncome"
                                                class="w-full mt-2 resize-none peer uppercase" :class="dropdownClasses(voidNotesIncome)" autofocus />
                                            <label for="notes" class="peer-focus:!top-[-0.40rem]"
                                                :class="{ '!top-5': voidNotesIncome.trim() === '', '!top-[-0.40rem]': voidNotesIncome.trim() !== '' }">Motivo
                                                de anulación</label>
                                        </FloatLabel>
                                    </div>
                                    <div class="flex items-center gap-2 m-4 justify-end">
                                        <Button label="Cancelar" class="bg-red-500 border-red-500 text-white hover:bg-red-600 hover:border-red-600"
                                            @click="rejectCallback"></Button>
                                        <Button label="Aceptar" class="bg-primary-500 text-white hover:bg-primary-600"
                                            @click="handleConfirmAccept(acceptCallback)"
                                            :disabled="!voidNotesIncome || voidNotesIncome.trim() === ''"></Button>
                                    </div>
                                </template>
                            </ConfirmPopup>
                            <button v-tooltip="'Anular'" class="bottom-[0.2rem] relative"><i class="pi pi-ban text-red-500 text-lg font-extrabold"
                                    @click="voidTreasuryVoucherIncome($event, data)"></i></button>
                        </template>
                    </template>
                </div>
            </template>
        </Column>
        <template #paginatorend>
            <Button icon="pi pi-download" iconClass="text-xl"
                class="p-0 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-transparent focus:ring-0 focus:outline-0"
                text @click="exportTreasuryVouchers()" />
        </template>
        <template #expansion="{ data }">
            <template v-if="data.vouchers.length > 0">
                <DataTable :value="data.vouchers" scrollable class="m-3 data-table-expanded">
                    <template #empty>
                        <div class="text-center text-lg text-red-500">
                            Sin comprobantes
                        </div>
                    </template>
                    <Column header="Comprobante">
                        <template #body="{ data }">
                            {{ data.invoiceTypeName }}
                            <span class="font-bold">{{ data.invoiceTypeCodeName }}</span>
                        </template>
                    </Column>
                    <Column header="Número">
                        <template #body="{ data }">
                            {{ invoiceNumberFormat(data.pointOfNumber, 5) }} - {{ invoiceNumberFormat(data.invoiceNumber, 8) }}
                        </template>
                    </Column>
                    <Column header="F. vencimiento">
                        <template #body="{ data }">
                            <span :class="{ 'text-red-500': compareDates(data.invoiceDueDate, '', 'before') }">
                                {{ dateFormat(data.invoiceDueDate) }}
                            </span>
                        </template>
                    </Column>
                    <Column header="Importe">
                        <template #body="{ data }">
                            {{ currencyNumber(data.amount) }}
                        </template>
                    </Column>
                </DataTable>
            </template>
            <template v-else-if="data.customVoucher">
                <DataTable :value="data.customVoucher" scrollable class="m-3 data-table-expanded">
                    <template #empty>
                        <div class="text-center text-lg text-red-500">
                            Sin comprobantes
                        </div>
                    </template>
                    <Column header="Subtipo" class="w-1/3">
                        <template #body="{ data }">
                            {{ data.voucherSubtype }}
                        </template>
                    </Column>
                    <Column header="Gasto" class="w-1/3">
                        <template #body="{ data }">
                            {{ data.voucherExpense }}
                        </template>
                    </Column>
                    <Column header="F. Comprobante" class="w-1/3">
                        <template #body="{ data }">
                            {{ dateFormat(data.voucherDate) }}
                        </template>
                    </Column>
                </DataTable>
            </template>
        </template>
    </DataTable>

    <OverlayPanel ref="paymentMethodPanel" appendTo="body">
        <DataTable :value="dataPaymentMethodArray" scrollable scrollHeight="70vh" dataKey="id" class="data-table">
            <Column field="bankAccount" header="CTA. BANCARIA">
                <template #body="{ data }">
                    {{ data.bankAccount }}
                </template>
            </Column>
            <Column field="transactionNumber" header="N° OPERACIÓN">
                <template #body="{ data }">
                    <span class="uppercase">{{ data.transactionNumber }}</span>
                </template>
            </Column>
        </DataTable>
    </OverlayPanel>

    <OverlayPanel ref="voidedVoucherPanel" appendTo="body" class="voidedVoucherPanel">
        <DataTable :value="dataVoidedVoucherArray" scrollable scrollHeight="70vh" dataKey="id" class="data-table">
            <Column field="notes" header="Motivo Anulación">
                <template #body="{ data }">
                    <span class="uppercase">{{ data.notes }}</span>
                </template>
            </Column>
            <Column field="userVoided" header="Usuario anulación">
                <template #body="{ data }">
                    <span class="uppercase">{{ data.userVoided }}</span>
                </template>
            </Column>
            <Column field="voided_at" header="Fecha anulación">
                <template #body="{ data }">
                    <span class="uppercase">{{ dateTimeFormat(data.voided_at) }}</span>
                </template>
            </Column>
        </DataTable>
    </OverlayPanel>

    <div v-if="hasPermission('edit treasury vouchers')">
        <div class="flex mt-3 pb-0 items-center justify-between" v-if="selectStatus === 1">
            <div class="flex w-fit space-x-4">
                <div class="w-fit text-left text-surface-900/60 font-bold">Total a Ingresar: </div>
                <div class="w-fit text-left font-bold" :class="form.totalIncomeAmount < 0 ? 'text-red-500' : ''">
                    {{ currencyNumber(form.totalIncomeAmount) }}
                </div>
            </div>

            <div>
                <Button label="Confirmar" icon="pi pi-save" iconPos="right" :disabled="form.totalIncomeAmount <= 0 || isProcessing"
                    @click="confirmTreasuryVoucherModal()" />
            </div>
        </div>
    </div>
</template>