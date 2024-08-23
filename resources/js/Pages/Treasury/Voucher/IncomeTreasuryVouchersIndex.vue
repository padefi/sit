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

const { hasPermission, hasPermissionColumn } = usePermissions();
const treasuryVouchersArray = ref([]);
const loading = ref(true);
const expandedRows = ref([]);
const toast = useToast();
const confirm = useConfirm();

const filters = ref({
    cuit: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    businessName: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});


const fetchIncomeTreasuryVouchers = async () => {
    try {
        const response = await fetch('/treasury-vouchers/1/1');

        if (!response.ok) {
            throw new Error('Error al obtener datos de comprobates a ingresar');
        }

        const data = await response.json();
        treasuryVouchersArray.value = data.treasuryVouchers.map(treasuryVoucher => treasuryVoucherDataStructure(treasuryVoucher));
    } catch (error) {
        console.error(error);
    }
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
        cuit: data.supplier.cuit,
        businessName: data.supplier.businessName,
        status: data.voucherStatus.id,
        amount: data.totalAmount,
        vouchers: data.voucherToTreasury.map(voucher => voucherDataStructure(voucher)),
    }
}

const voucherDataStructure = (voucherToTreasury) => {
    const data = voucherToTreasury.voucher;

    return {
        invoiceTypeName: data.invoiceType.name,
        invoiceTypeCodeName: data.invoiceTypeCode.name,
        pointOfNumber: data.pointOfNumber,
        invoiceNumber: data.invoiceNumber,
        invoicePaymentDate: data.invoicePaymentDate,
        amount: voucherToTreasury.amount,
    }
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

onMounted(async () => {
    await fetchIncomeTreasuryVouchers();
    loading.value = false;
});

/*  */
import infoModal from '@/Components/InfoModal.vue';

const dialogInfo = useDialog();

const info = (id) => {
    axios.get(`/treasury-vouchers/${id}/info`)
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
        scrollHeight="60vh" dataKey="id" filterDisplay="menu" @row-expand="onRowExpand($event)" @row-collapse="onRowCollapse($event)" :pt="{
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
                <div class="space-x-4 flex justify-center">
                    <Checkbox v-model="data.checked" binary />
                    <template v-if="hasPermission('view users')">
                        <button v-tooltip="'+Info'" class="bottom-[0.2rem] relative"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                @click="info(data.id)"></i></button>
                    </template>
                    <template v-if="hasPermission('edit treasury vouchers') && data.status === 1">
                        <ConfirmPopup></ConfirmPopup>
                        <button v-tooltip="'Anular'"><i class="pi pi-ban text-red-500 text-lg font-extrabold"
                                @click="voidTreasuryVoucher($event, data)"></i></button>
                    </template>
                </div>
            </template>
        </Column>
        <template #expansion="{ data }">
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
                        <span :class="{ 'text-red-500': compareDates(data.invoicePaymentDate, '', 'before') }">
                            {{ dateFormat(data.invoicePaymentDate) }}
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
    </DataTable>
</template>