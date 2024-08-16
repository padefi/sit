<script setup>
import { inject, onMounted, ref } from "vue";
import { usePermissions } from '@/composables/permissions';
import { useDialog } from 'primevue/usedialog';
import { currencyNumber, dateFormat, percentNumber, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { compareDates } from "@/utils/validateFunctions";
import { FilterMatchMode, FilterOperator } from "primevue/api";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { useForm } from "@inertiajs/vue3";
import voucherModal from './VoucherModal.vue';
import treasuryModal from './TreasuryModal.vue';

const { hasPermission } = usePermissions();
const vouchersArray = ref([]);
const expandedRows = ref([]);
const toast = useToast();
const confirm = useConfirm();

const filters = ref({
    invoiceDate: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    invoiceType: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    invoiceTypeCode: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    invoiceNumber: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    debit: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    credit: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    pendingToPay: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

const getVouchers = async () => {
    try {
        const response = await fetch(`/vouchers/${dialogRef.value.data.voucher.id}`);

        if (!response.ok) {
            throw new Error('Error al obtener los comprobantes del proveedor');
        }

        const data = await response.json();
        vouchersArray.value = data.vouchers;
    } catch (error) {
        console.error(error);
    }
};

const onRowExpand = (data) => {
    const originalExpandedRows = { ...expandedRows.value };
    const newExpandedRows = vouchersArray.value.reduce((acc) => (acc[data.id] = true) && acc, {});
    expandedRows.value = { ...originalExpandedRows, ...newExpandedRows };
}

const onRowCollapse = (data) => {
    delete expandedRows.value[data.id];
}

const rowClassVoid = (data) => {
    return data.status === 0 ? '!text-red-500' : '';
};

const dialog = useDialog();
const dialogRef = inject("dialogRef");

const addNewVoucher = () => {
    dialog.open(voucherModal, {
        props: {
            header: 'Nuevo comprobante',
            style: {
                width: '55vw',
                height: '90vh',
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
            voucher: dialogRef.value.data.voucher,
            payConditions: dialogRef.value.data.payConditions,
            voucherTypes: dialogRef.value.data.voucherTypes,
            vatRates: dialogRef.value.data.vatRates,
        }
    });
};

const treasuryVoucher = () => {
    dialog.open(treasuryModal, {
        props: {
            header: dialogRef.value.data.voucher.businessName,
            style: {
                width: '80vw',
                height: '55vh',
            },
            breakpoints: {
                '960px': '75vw',
                '640px': '90vw'
            },
            modal: true,
            contentStyle: {
                padding: '1.25rem',
                height: '85vh',
                backgroundColor: 'rgb(var(--surface-50))',
            },
        },
        data: {
            voucher: dialogRef.value.data.voucher,
            /* payConditions: dialogRef.value.data.payConditions,
            voucherTypes: dialogRef.value.data.voucherTypes,
            vatRates: dialogRef.value.data.vatRates,
            voucherData: data, */
        }
    });
}

const editVoucher = (data) => {
    dialog.open(voucherModal, {
        props: {
            header: 'Editar comprobante',
            style: {
                width: '55vw',
                height: '90vh',
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
            voucher: dialogRef.value.data.voucher,
            payConditions: dialogRef.value.data.payConditions,
            voucherTypes: dialogRef.value.data.voucherTypes,
            vatRates: dialogRef.value.data.vatRates,
            voucherData: data,
        }
    });
}

const voidVoucher = (event, data) => {
    confirm.require({
        target: event.currentTarget,
        message: '¿Está seguro de anular el comprobante?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            const form = useForm({
                id: data.id
            });

            form.put(route("vouchers.void", data.id), {
                onError: (error) => {
                    toast.add({
                        severity: 'error',
                        detail: error.response.data.message,
                        life: 3000,
                    });
                },
            });
        },
    });
}

onMounted(async () => {
    await getVouchers();

    Echo.channel('vouchers')
        .listen('Treasury\\Voucher\\VoucherEvent', (e) => {
            if (e.type === 'create') {
                if (!vouchersArray.value.some(voucher => voucher.id === e.voucherId)) {
                    e.voucher.voucherIndex = 0;
                    e.voucher.accounts = [];
                    vouchersArray.value.unshift(e.voucher);
                }
            } else if (e.type === 'update') {
                const index = vouchersArray.value.findIndex(voucher => voucher.id === e.voucher.id);

                if (index !== -1) {
                    vouchersArray.value[index] = e.voucher;
                }
            } else if (e.type === 'voucherToTreasury') {
                setTimeout(async () => {
                    await getVouchers();
                }, 200);
            }
        });
});

/*  */
import infoModal from '@/Components/InfoModal.vue';

const dialogInfo = useDialog();

const info = (id) => {
    axios.get(`/vouchers/${id}/info`)
        .then((response) => {
            const data = response.data;
            const voucherData = data.invoiceType.name + '  ' + data.invoiceTypeCode.name + ' ' + invoiceNumberFormat(data.pointOfNumber, 5) + '-' + invoiceNumberFormat(data.invoiceNumber, 8);
            const header = `Información del comprobante ${voucherData.toUpperCase()}`;

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
    <Card class="mt-5 mx-4 uppercase">
        <template #title>
            <div class="flex justify-between items-center mx-4">
                <div class="align-left">
                    <h3 class="uppercase">Comprobantes</h3>
                </div>
                <template v-if="hasPermission('create vouchers')">
                    <div class="align-right space-x-2">
                        <Button label="Comprobante" severity="info" outlined icon="pi pi-shopping-cart" size="large" @click="addNewVoucher($event)" />
                        <template v-if="hasPermission('view treasury vouchers')">
                            <Button label="Tesorería" severity="success" outlined icon="pi pi-dollar" size="large" @click="treasuryVoucher($event)" />
                        </template>
                    </div>
                </template>
            </div>
        </template>
        <template #content>
            <DataTable :value="vouchersArray" v-model:filters="filters" v-model:expandedRows="expandedRows" scrollable scrollHeight="70vh"
                dataKey="id" filterDisplay="menu" @row-expand="onRowExpand($event)" @row-collapse="onRowCollapse($event)" :pt="{
                    table: { style: 'min-width: 50rem' },
                    paginator: {
                        root: { class: 'p-paginator-custom' },
                        current: { class: 'p-paginator-current' },
                    }
                }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table" :row-class="rowClassVoid">
                <template #empty>
                    <div class="text-center text-lg text-red-500">
                        Sin comprobantes cargados
                    </div>
                </template>
                <Column expander style="width: 1%" />
                <Column field="invoiceType" header="T. comp.">
                    <template #body="{ data }">
                        {{ data.invoiceType.name }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="invoiceType" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por T. comp." />
                    </template>
                </Column>
                <Column field="invoiceTypeCode" header="T. fac.">
                    <template #body="{ data }">
                        {{ data.invoiceTypeCode.name }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="invoiceTypeCode" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por T. fac." />
                    </template>
                </Column>
                <Column field="invoiceNumber" header="Número">
                    <template #body="{ data }">
                        {{ invoiceNumberFormat(data.pointOfNumber, 5) + '-' + invoiceNumberFormat(data.invoiceNumber, 8) }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="invoiceNumber" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por número" />
                    </template>
                </Column>
                <Column field="invoicePaymentDate" header="F. vencimiento">
                    <template #body="{ data }">
                        <span :class="{ 'text-red-500': compareDates(data.invoicePaymentDate, '', 'before') }">
                            {{ dateFormat(data.invoicePaymentDate) }}
                        </span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="invoicePaymentDate" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por F. emisión" />
                    </template>
                </Column>
                <Column field="payCondition" header="Cond. Pago">
                    <template #body="{ data }">
                        {{ data.payCondition.name }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="payCondition" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por cond. pago" />
                    </template>
                </Column>
                <Column field="totalAmount" header="Importe">
                    <template #body="{ data }">
                        {{ currencyNumber(data.totalAmount) }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="totalAmount" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por importe" />
                    </template>
                </Column>
                <Column field="pendingToPay" header="Saldo">
                    <template #body="{ data }">
                        {{ currencyNumber(data.pendingToPay) }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="pendingToPay" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por saldo" />
                    </template>
                </Column>
                <Column header="Acciones" style="width: 5%; min-width: 8rem;">
                    <template #body="{ data }">
                        <div class="space-x-2 flex pl-2">
                            <template v-if="hasPermission('edit vouchers')">
                                <button v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                        @click="editVoucher(data)"></i></button>
                            </template>
                            <template v-if="hasPermission('view users')">
                                <button v-tooltip="'+Info'"><i class="pi pi-id-card text-cyan-500 text-2xl" @click="info(data.id)"></i></button>
                            </template>
                            <template v-if="hasPermission('edit vouchers') && data.status === 1 && data.pendingToPay === data.totalAmount">
                                <ConfirmPopup></ConfirmPopup>
                                <button v-tooltip="'Anular'"><i class="pi pi-ban text-red-500 text-lg font-extrabold"
                                        @click="voidVoucher($event, data)"></i></button>
                            </template>
                        </div>
                    </template>
                </Column>
                <template #expansion="{ data }">
                    <DataTable :value="data.items" scrollable class="m-3 data-table-expanded">
                        <template #empty>
                            <div class="text-center text-lg text-red-500">
                                Sin items
                            </div>
                        </template>
                        <Column field="description" header="Descripción">
                            <template #body="{ data }">
                                {{ data.description }}
                            </template>
                        </Column>
                        <Column field="amount" header="Importe">
                            <template #body="{ data }">
                                {{ currencyNumber(data.amount) }}
                            </template>
                        </Column>
                        <Column field="vat" header="I.V.A.">
                            <template #body="{ data }">
                                {{ percentNumber(data.VatRate.rate) }}
                            </template>
                        </Column>
                        <Column field="amount" header="Subtotal">
                            <template #body="{ data }">
                                {{ currencyNumber(data.subtotalAmount) }}
                            </template>
                        </Column>
                    </DataTable>
                </template>
            </DataTable>
        </template>
    </Card>
</template>