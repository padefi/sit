<script setup>
import { inject, onMounted, ref } from "vue";
import { currencyNumber, dateFormat, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { compareDates } from "@/utils/validateFunctions";
import { usePermissions } from '@/composables/permissions';
import { useDialog } from 'primevue/usedialog';
import treasuryVoucherModal from './TreasuryVoucherModal.vue';

const { hasPermission } = usePermissions();
const treasuryVouchersArray = ref([]);
const expandedRows = ref([]);
const dialog = useDialog();
const dialogRef = inject("dialogRef");

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
                width: '75vw',
                height: '45vh',
            },
            breakpoints: {
                '960px': '75vw',
                '640px': '90vw'
            },
            modal: true,
            contentStyle: {
                padding: '1.25rem',
                height: '85vh',
            },
        },
        data: {
            supplierId: dialogRef.value.data.supplierId,
        }
    });
}

onMounted(async () => {
    try {
        const response = await fetch(`/treasury-vouchers/${dialogRef.value.data.supplierId}`);

        if (!response.ok) {
            throw new Error('Error al obtener los comprobantes de tesorería del proveedor');
        }

        const data = await response.json();
        treasuryVouchersArray.value = data.treasuryVouchers;
    } catch (error) {
        console.error(error);
    }

    Echo.channel('voucherToTreasury')
        .listen('Treasury\\Voucher\\VoucherToTreasuryEvent', (e) => {
            if (e.type === 'create') {
                if (!treasuryVouchersArray.value.some(treasuryVoucher => treasuryVoucher.id === e.treasuryVoucherId)) {
                    treasuryVouchersArray.value.unshift(e.treasuryVoucher);
                }
            }
        });
});
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
            <DataTable :value="treasuryVouchersArray" v-model:expandedRows="expandedRows" scrollable scrollHeight="70vh" dataKey="id"
                filterDisplay="menu" @row-expand="onRowExpand($event)" @row-collapse="onRowCollapse($event)" :pt="{
                    table: { style: 'min-width: 50rem' },
                    paginator: {
                        root: { class: 'p-paginator-custom' },
                        current: { class: 'p-paginator-current' },
                    }
                }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table">
                <template #empty>
                    <div class="text-center text-lg text-red-500">
                        Sin comprobantes cargados
                    </div>
                </template>
                <Column expander class="min-w-2 w-2 !px-0" />
                <Column field="voucherType" header="Tipo" class="rounded-tl-lg min-w-56 max-w-56">
                    <template #body="{ data }">
                        {{ data.voucherType.name }}
                    </template>
                </Column>
                <Column field="voucherStatus" header="Estado" class="rounded-tl-lg min-w-56 max-w-56">
                    <template #body="{ data }">
                        {{ data.voucherStatus.name }}
                    </template>
                </Column>
                <Column field="totalAmount" header="Importe" class="rounded-tl-lg min-w-56 max-w-56">
                    <template #body="{ data }">
                        {{ currencyNumber(data.totalAmount) }}
                    </template>
                </Column>
                <Column header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28">
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