<script setup>
import { ref, onMounted } from 'vue';
import { currencyNumber, dateFormat, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { compareDates } from "@/utils/validateFunctions";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePermissions } from '@/composables/permissions';
import { useDialog } from 'primevue/usedialog';
import { toastService } from '@/composables/toastService'
import { useToast } from "primevue/usetoast";
import { FilterMatchMode, FilterOperator } from 'primevue/api';

toastService();

const props = defineProps({
    treasuryVoucher: {
        type: Object,
        default: () => ({}),
    },
});

const { hasPermission, hasPermissionColumn } = usePermissions();
const treasuryVouchersArray = ref([]);
const expandedRows = ref([]);
const toast = useToast();

const filters = ref({
    cuit: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    businessName: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

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
        voucherTypeName: data.voucherType.name,
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

onMounted(() => {
    treasuryVouchersArray.value = props.treasuryVoucher.map(treasuryVoucher => treasuryVoucherDataStructure(treasuryVoucher));
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
</script>
<template>
    <AuthenticatedLayout>
        <Card class="mt-5 mx-4 uppercase">
            <template #title>
                <div class="flex justify-between items-center mx-4">
                    <div class="align-left">
                        <h3 class="uppercase">Comprobantes</h3>
                    </div>
                </div>
            </template>
            <template #content>
                <DataTable :value="treasuryVouchersArray" v-model:filters="filters" v-model:expandedRows="expandedRows" scrollable scrollHeight="60vh"
                    dataKey="id" filterDisplay="menu" @row-expand="onRowExpand($event)" @row-collapse="onRowCollapse($event)" :pt="{
                    table: { style: 'min-width: 50rem' }, tbody: { class: 'thin-td' }, wrapper: { class: 'datatable-scrollbar' },
                    paginator: {
                        root: { class: 'p-paginator-custom' },
                        current: { class: 'p-paginator-current' },
                    }
                }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table">
                    <Column expander class="min-w-2 w-2 !px-0" />
                    <Column field="voucherTypeName" header="Tipo" sortable>
                        <template #body="{ data }">
                            {{ data.voucherTypeName }}
                        </template>
                    </Column>
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
                    <Column header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28"
                        v-if="hasPermissionColumn(['view users'])">
                        <template #body="{ data }">
                            <div class="space-x-4 flex justify-center">
                                <Checkbox v-model="data.checked" binary />
                                <template v-if="hasPermission('view users')">
                                    <button v-tooltip="'+Info'" class="bottom-[0.2rem] relative"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                            @click="info(data.id)"></i></button>
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
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>