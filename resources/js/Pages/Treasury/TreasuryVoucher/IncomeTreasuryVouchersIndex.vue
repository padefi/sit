<script setup>
import { ref, onMounted } from 'vue';
import { currencyNumber, dateFormat, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { compareDates } from "@/utils/validateFunctions";
import { usePermissions } from '@/composables/permissions';
import { useDialog } from 'primevue/usedialog';
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { useForm } from "@inertiajs/vue3";
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import treasuryVoucherModal from './TreasuryVoucherModal.vue';

const { hasPermission, hasPermissionColumn } = usePermissions();
const loading = ref(true);
const selectStatus = ref(0);
const treasuryVouchersArray = ref([]);
const expandedRows = ref([]);
const toast = useToast();
const confirm = useConfirm();

const filters = ref({
    cuit: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    businessName: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
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
        vouchers: data.voucherToTreasury.map(voucher => voucherDataStructure(voucher)),
        customVoucher: data.treasuryCustomVoucher ? customVoucherDataStructure(data.treasuryCustomVoucher) : null,
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

const editTreasuryVoucher = (data) => {
    dialogInfo.open(treasuryVoucherModal, {
        props: {
            header: 'Nuevo comprobante',
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

const voidTreasuryVoucher = (event, data) => {
    confirm.require({
        target: event.currentTarget,
        message: '¿Está seguro de anular el comprobante?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            const form = useForm({
                id: data.id
            });

            form.put(route("treasury-vouchers.void", data.id), {
                onError: (error) => {
                },
            });
        },
    });
}

onMounted(() => {
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
                        width: '50vw',
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
        </Column>
        <Column field="businessName" header="Proveedor" sortable>
            <template #body="{ data }">
                {{ data.businessName }}
            </template>
        </Column>
        <Column field="amount" header="Importe" sortable>
            <template #body="{ data }">
                {{ currencyNumber(data.amount) }}
            </template>
        </Column>
        <Column header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28" v-if="hasPermissionColumn(['view users'])">
            <template #body="{ data }">
                <div class="space-x-2 flex justify-center">
                    <template v-if="data.status === 1">
                        <Checkbox v-model="data.checked" binary />
                    </template>
                    <template v-if="hasPermission('view users')">
                        <button v-tooltip="'+Info'" class="bottom-[0.2rem] relative"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                @click="info(data.id)"></i></button>
                    </template>
                    <template v-if="hasPermission('edit treasury vouchers')">
                        <template v-if="data.customVoucher">
                            <button v-tooltip="'Editar'" class="bottom-[0.2rem] relative"><i
                                    class="pi pi-pencil text-orange-500 text-lg font-extrabold" @click="editTreasuryVoucher(data)"></i></button>
                        </template>
                        <template v-if="data.status === 1">
                            <ConfirmPopup></ConfirmPopup>
                            <button v-tooltip="'Anular'" class="bottom-[0.2rem] relative"><i class="pi pi-ban text-red-500 text-lg font-extrabold"
                                    @click="voidTreasuryVoucher($event, data)"></i></button>
                        </template>
                    </template>
                </div>
            </template>
        </Column>
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
</template>