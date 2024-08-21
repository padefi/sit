<script setup>
import { inject, onMounted, ref } from "vue";
import { usePermissions } from '@/composables/permissions';
import { useDialog } from 'primevue/usedialog';
import { currencyNumber, addDate, dateFormat, percentNumber, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { compareDates } from "@/utils/validateFunctions";
import { FilterMatchMode, FilterOperator } from "primevue/api";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import { useForm } from "@inertiajs/vue3";
import voucherModal from './VoucherModal.vue';
import treasuryModal from './TreasuryModal.vue';

const { hasPermission, hasPermissionColumn } = usePermissions();
const vouchersArray = ref([]);
const expandedRows = ref([]);
const invoiceTypesSelect = ref([]);
const invoiceTypeCodesSelect = ref([]);
const payConditionsSelect = ref([]);
const toast = useToast();
const confirm = useConfirm();

const filters = ref({
    invoiceTypeName: { value: null, matchMode: FilterMatchMode.EQUALS },
    invoiceTypeCodeName: { value: null, matchMode: FilterMatchMode.EQUALS },
    invoiceFullNumber: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    invoicePaymentDate: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.DATE_IS }] },
    payConditionName: { value: null, matchMode: FilterMatchMode.EQUALS },
    totalAmount: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
    pendingToPay: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
});

const getInvoiceTypeData = async () => {
    try {
        const response = await fetch('/invoice-types');

        if (!response.ok) {
            throw new Error('Error al obtener los datos de los tipos de comprobantes');
        }

        const data = await response.json();

        invoiceTypesSelect.value = data.invoiceTypes.map((invoiceType) => {
            return { label: invoiceType.name, value: invoiceType.name };
        });

        invoiceTypeCodesSelect.value = data.invoiceTypeCodes.map((invoiceTypeCode) => {
            return { label: invoiceTypeCode.name, value: invoiceTypeCode.name };
        });
    } catch (error) {
        console.error(error);
    }
}

const getVouchers = async () => {
    try {
        const response = await fetch(`/show-vouchers/${dialogRef.value.data.supplierId}`);

        if (!response.ok) {
            throw new Error('Error al obtener los comprobantes del proveedor');
        }

        const data = await response.json();
        vouchersArray.value = data.vouchers.map(voucher => voucherDataStructure(voucher));
    } catch (error) {
        console.error(error);
    }
};

const voucherDataStructure = (voucher) => {
    return {
        ...voucher,
        invoiceTypeName: voucher.invoiceType.name,
        invoiceTypeCodeName: voucher.invoiceTypeCode.name,
        invoiceFullNumber: invoiceNumberFormat(voucher.pointOfNumber, 5) + '-' + invoiceNumberFormat(voucher.invoiceNumber, 8),
        payConditionName: voucher.payCondition.name,
        invoicePaymentDate: addDate(voucher.invoicePaymentDate, 1),
    }
}

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
            supplierId: dialogRef.value.data.supplierId,
            payConditions: dialogRef.value.data.payConditions,
            voucherTypes: dialogRef.value.data.voucherTypes,
            vatRates: dialogRef.value.data.vatRates,
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
            supplierId: dialogRef.value.data.supplierId,
            voucherId: data.id,
            payConditions: dialogRef.value.data.payConditions,
            voucherTypes: dialogRef.value.data.voucherTypes,
            vatRates: dialogRef.value.data.vatRates,
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

const treasuryVoucher = () => {
    dialog.open(treasuryModal, {
        props: {
            header: 'Comprobantes',
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
            supplierId: dialogRef.value.data.supplierId,
            voucherTypes: dialogRef.value.data.voucherTypes,
        }
    });
}

onMounted(async () => {
    await getInvoiceTypeData();
    await getVouchers();

    payConditionsSelect.value = dialogRef.value.data.payConditions.map((payCondition) => {
        return { label: payCondition.name, value: payCondition.name };
    });

    Echo.channel('vouchers')
        .listen('Treasury\\Voucher\\VoucherEvent', (e) => {
            if (e.type === 'create') {
                if (!vouchersArray.value.some(voucher => voucher.id === e.voucherId)) {
                    e.voucher.voucherIndex = 0;
                    e.voucher.accounts = [];
                    const dataVoucher = voucherDataStructure(e.voucher);
                    vouchersArray.value.unshift(dataVoucher);
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
                <Column expander class="min-w-2 w-2 !px-0" />
                <Column field="invoiceTypeName" header="T. comp.">
                    <template #body="{ data }">
                        {{ data.invoiceTypeName }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="invoiceTypesSelect" placeholder="T. Comp"
                            class="p-column-filter" optionLabel="label" optionValue="value" style="min-width: 12rem" :showClear="true" />
                    </template>
                </Column>
                <Column field="invoiceTypeCodeName" header="T. fac.">
                    <template #body="{ data }">
                        {{ data.invoiceTypeCodeName }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="invoiceTypeCodesSelect" placeholder="T. Fac."
                            class="p-column-filter" optionLabel="label" optionValue="value" style="min-width: 12rem" :showClear="true" />
                    </template>
                </Column>
                <Column field="invoiceFullNumber" header="Número">
                    <template #body="{ data }">
                        {{ data.invoiceFullNumber }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputMask v-model="filterModel.value" type="text" @blur="filterCallback();" name="invoiceFullNumber" autocomplete="off"
                            mask="99999-99999999" class="p-column-filter" placeholder="Buscar por número" />
                    </template>
                </Column>
                <Column field="invoicePaymentDate" header="F. vencimiento" dataType="date" sortable>
                    <template #body="{ data }">
                        <span :class="{ 'text-red-500': compareDates(data.invoicePaymentDate, '', 'before') }">
                            {{ dateFormat(data.invoicePaymentDate) }}
                        </span>
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Calendar v-model="filterModel.value" @blur="filterCallback();" dateFormat="dd/mm/yy" placeholder="dd/mm/yyyy"
                            mask="99/99/9999" name="invoicePaymentDate" class="p-column-filter" />
                    </template>
                </Column>
                <Column field="payConditionName" header="Cond. Pago">
                    <template #body="{ data }">
                        {{ data.payConditionName }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="payConditionsSelect" placeholder="Cond. pago"
                            class="p-column-filter" optionLabel="label" optionValue="value" style="min-width: 12rem" :showClear="true" />
                    </template>
                </Column>
                <Column field="totalAmount" header="Importe" dataType="numeric" sortable>
                    <template #body="{ data }">
                        {{ currencyNumber(data.totalAmount) }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputNumber v-model="filterModel.value" @blur="filterCallback()" placeholder="$ 0,00" mode="currency" currency="ARS"
                            locale="es-AR" name="totalAmount" :min="0" :max="99999999" :minFractionDigits="2" />
                    </template>
                </Column>
                <Column field="pendingToPay" header="Saldo" dataType="numeric" sortable>
                    <template #body="{ data }">
                        {{ currencyNumber(data.pendingToPay) }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputNumber v-model="filterModel.value" @blur="filterCallback()" placeholder="$ 0,00" mode="currency" currency="ARS"
                            locale="es-AR" name="pendingToPay" :min="0" :max="99999999" :minFractionDigits="2" />
                    </template>
                </Column>
                <Column header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28"
                    v-if="hasPermissionColumn(['edit vouchers', 'view users'])">
                    <template #body="{ data }">
                        <div class="space-x-2">
                            <template v-if="hasPermission('edit vouchers') && data.status === 1 && data.pendingToPay === data.totalAmount">
                                <button v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                        @click="editVoucher(data)"></i></button>
                            </template>
                            <template v-if="hasPermission('view users')">
                                <button v-tooltip="'+Info'" class="top-0.5 relative"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                        @click="info(data.id)"></i></button>
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