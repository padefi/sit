<script setup>
import { inject, onMounted, ref } from "vue";
import { currencyNumber, dateFormat, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { compareDates } from "@/utils/validateFunctions";
import { usePermissions } from '@/composables/permissions';
import { useDialog } from 'primevue/usedialog';
import treasuryVoucherModal from './TreasuryVoucherModal.vue';
import { FilterMatchMode, FilterOperator } from "primevue/api";

const { hasPermission, hasPermissionColumn } = usePermissions();
const treasuryVouchersArray = ref([]);
const loading = ref(true);
const expandedRows = ref([]);
const voucherTypesSelect = ref([]);
const voucherStatusesSelect = ref([]);
const dialog = useDialog();
const dialogRef = inject("dialogRef");

const filters = ref({
    voucherTypeName: { value: null, matchMode: FilterMatchMode.EQUALS },
    voucherStatusName: { value: null, matchMode: FilterMatchMode.EQUALS },
    totalAmount: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
});

const getTreasuryVoucherStatusData = async () => {
    try {
        const response = await fetch('/treasury-vouchers/status');

        if (!response.ok) {
            throw new Error('Error al obtener los datos de los tipos de comprobantes');
        }

        const data = await response.json();
        voucherStatusesSelect.value = data.treasuryVoucherStatus.map((treasuryVoucherStatus) => {
            return { label: treasuryVoucherStatus.name, value: treasuryVoucherStatus.name };
        });
    } catch (error) {
        console.error(error);
    }
}

const getTreasuryVouchers = async () => {
    try {
        const response = await fetch(`/treasury-vouchers/${dialogRef.value.data.supplierId}`);

        if (!response.ok) {
            throw new Error('Error al obtener los comprobantes de tesorería del proveedor');
        }

        const data = await response.json();
        treasuryVouchersArray.value = data.treasuryVouchers.map(treasuryVoucher => treasuryVoucherDataStructure(treasuryVoucher));
    } catch (error) {
        console.error(error);
    }
};

const treasuryVoucherDataStructure = (treasuryVoucher) => {
    return {
        ...treasuryVoucher,
        voucherTypeName: treasuryVoucher.voucherType.name,
        voucherStatusName: treasuryVoucher.voucherStatus.name,
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

const addNewTreasuryVoucher = () => {
    dialog.open(treasuryVoucherModal, {
        props: {
            header: 'Comprobantes',
            style: {
                width: '80vw',
                minHeight: '50vh',
            },
            breakpoints: {
                '960px': '75vw',
                '640px': '70vw'
            },
            modal: true,
            contentStyle: {
                padding: '1.25rem',
                minHeight: '50vh',
            },
        },
        data: {
            supplierId: dialogRef.value.data.supplierId,
        }
    });
}

onMounted(async () => {
    await getTreasuryVoucherStatusData();
    await getTreasuryVouchers();
    loading.value = false;

    voucherTypesSelect.value = dialogRef.value.data.voucherTypes.map((voucherType) => {
        return { label: voucherType.name, value: voucherType.name };
    });

    Echo.channel('voucherToTreasury')
        .listen('Treasury\\Voucher\\VoucherToTreasuryEvent', (e) => {
            if (e.type === 'create') {
                if (!treasuryVouchersArray.value.some(treasuryVoucher => treasuryVoucher.id === e.treasuryVoucherId)) {
                    const dataTreasuryVoucher = treasuryVoucherDataStructure(e.treasuryVoucher);
                    treasuryVouchersArray.value.unshift(dataTreasuryVoucher);
                }
            }
        });
});

/*  */
import infoModal from '@/Components/InfoModal.vue';

const dialogInfo = useDialog();

const info = (id) => {
    axios.get(`/treasury-vouchers/${id}/info`)
        .then((response) => {
            const header = 'Información del comprobante de tesorería';

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
                data: response.data
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
    <Card class="mt-5 mx-4 uppercase">
        <template #title>
            <div class="flex justify-between items-center mx-4">
                <div class="align-left">
                    <h3 class="uppercase">Comprobantes tesorería</h3>
                </div>
                <template v-if="hasPermission('create treasury vouchers')">
                    <div class="align-right space-x-2">
                        <Button severity="info" outlined icon="pi pi-wallet" size="large" @click="addNewTreasuryVoucher($event)" />
                    </div>
                </template>
            </div>
        </template>
        <template #content>
            <DataTable :value="treasuryVouchersArray" v-model:filters="filters" v-model:expandedRows="expandedRows" :loading="loading" scrollable
                scrollHeight="30vh" dataKey="id" filterDisplay="menu" @row-expand="onRowExpand($event)" @row-collapse="onRowCollapse($event)" :pt="{
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
                <Column field="voucherTypeName" header="Tipo" class="rounded-tl-lg min-w-56 max-w-56">
                    <template #body="{ data }">
                        {{ data.voucherTypeName }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="voucherTypesSelect" placeholder="Tipo"
                            name="voucherTypeName" class="p-column-filter" optionLabel="label" optionValue="value" :showClear="true"
                            style="min-width: 12rem" />
                    </template>
                </Column>
                <Column field="voucherStatusName" header="Estado" class="rounded-tl-lg min-w-56 max-w-56">
                    <template #body="{ data }">
                        {{ data.voucherStatusName }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="voucherStatusesSelect" placeholder="Estado"
                            name="voucherStatusName" class="p-column-filter" optionLabel="label" optionValue="value" :showClear="true"
                            style="min-width: 12rem" />
                    </template>
                </Column>
                <Column field="totalAmount" header="Importe" dataType="numeric" class="rounded-tl-lg min-w-56 max-w-56">
                    <template #body="{ data }">
                        {{ currencyNumber(data.totalAmount) }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputNumber v-model="filterModel.value" @blur="filterCallback()" placeholder="$ 0,00" mode="currency" currency="ARS"
                            locale="es-AR" name="totalAmount" :min="0" :max="99999999" :minFractionDigits="2" />
                    </template>
                </Column>
                <Column header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28" v-if="hasPermissionColumn(['view users'])">
                    <template #body="{ data }">
                        <div>
                            <template v-if="hasPermission('view users')">
                                <button v-tooltip="'+Info'" class="btn-info"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                        @click="info(data.id)"></i></button>
                            </template>
                        </div>
                    </template>
                </Column>
                <template #expansion="{ data }">
                    <DataTable :value="data.voucherToTreasury" scrollable class="m-3 data-table-expanded">
                        <template #empty>
                            <div class="text-center text-lg text-red-500">
                                Sin comprobantes
                            </div>
                        </template>
                        <Column header="Comprobante">
                            <template #body="{ data }">
                                {{ data.voucher.invoiceType.name }}
                                <span class="font-bold">{{ data.voucher.invoiceTypeCode.name }}</span>
                            </template>
                        </Column>
                        <Column header="Número">
                            <template #body="{ data }">
                                {{ invoiceNumberFormat(data.voucher.pointOfNumber, 5) }} - {{ invoiceNumberFormat(data.voucher.invoiceNumber, 8) }}
                            </template>
                        </Column>
                        <Column header="F. vencimiento">
                            <template #body="{ data }">
                                <span :class="{ 'text-red-500': compareDates(data.voucher.invoicePaymentDate, '', 'before') }">
                                    {{ dateFormat(data.voucher.invoicePaymentDate) }}
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
</template>