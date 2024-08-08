<script setup>
import { inject, onMounted, ref, computed } from "vue";
import { usePermissions } from '@/composables/permissions';
import { useDialog } from 'primevue/usedialog';
import voucherModal from './VoucherModal.vue';
import { FilterMatchMode, FilterOperator } from "primevue/api";

const { hasPermission } = usePermissions();
const vouchersArray = ref([]);
const expandedRows = ref([]);

const filters = ref({
    invoiceDate: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    invoiceType: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    invoiceTypeCode: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    invoiceNumber: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    debit: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    credit: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    balanceDue: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

const dialog = useDialog();

const addNewVoucher = (data) => {
    dialog.open(voucherModal, {
        props: {
            header: `Nuevo comprobantes - ${data.businessName}`,
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
            payConditions: dialogRef.value.data.payConditions,
            voucherTypes: dialogRef.value.data.voucherTypes,
            vatRates: dialogRef.value.data.vatRates,
        }
    });
};

const dialogRef = inject("dialogRef");

onMounted(async () => {
    vouchersArray.value = dialogRef.value.data.vouchersData;
});
</script>
<template>
    <Card class="mt-5 mx-4 uppercase">
        <template #title>
            <div class="flex justify-between items-center mx-4">
                <div class="align-left">
                    <h3 class="uppercase">Comprobantes</h3>
                </div>
                <template v-if="hasPermission('create vouchers')">
                    <div class="align-right">
                        <Button label="Agregar comprobante" severity="info" outlined icon="pi pi-shopping-cart" size="large"
                            @click="addNewVoucher($event)" />
                    </div>
                </template>
            </div>
        </template>
        <template #content>
            <DataTable :value="vouchersArray" v-model:filters="filters" v-model:expandedRows="expandedRows" scrollable scrollHeight="70vh"
                dataKey="id" filterDisplay="menu" :pt="{
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
                <Column expander style="width: 1%" />
                <Column field="invoiceDate" header="F. emisión">
                    <template #body="{ data }">
                        {{ data.invoiceDate }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="invoiceDate" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por F. emisión" />
                    </template>
                </Column>
                <Column field="invoiceType" header="T. comp.">
                    <template #body="{ data }">
                        {{ data.invoiceType }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="invoiceType" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por T. comp." />
                    </template>
                </Column>
                <Column field="invoiceTypeCode" header="T. fac.">
                    <template #body="{ data }">
                        {{ data.invoiceTypeCode }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="invoiceTypeCode" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por T. fac." />
                    </template>
                </Column>
                <Column field="invoiceNumber" header="Número">
                    <template #body="{ data }">
                        {{ data.invoiceNumber }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="invoiceNumber" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por número" />
                    </template>
                </Column>
                <Column field="debit" header="Débito">
                    <template #body="{ data }">
                        {{ data.debit }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="debit" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por débito" />
                    </template>
                </Column>
                <Column field="credit" header="Crédito">
                    <template #body="{ data }">
                        {{ data.credit }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="credit" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por crédito" />
                    </template>
                </Column>
                <Column field="balanceDue" header="Saldo adeudado">
                    <template #body="{ data }">
                        {{ data.balanceDue }}
                    </template>
                    <template #filter="{ filterModel, filterCallback }">
                        <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="balanceDue" autocomplete="off"
                            class="p-column-filter" placeholder="Buscar por saldo adeudado" />
                    </template>
                </Column>
                <Column header="Acciones" style="width: 5%; min-width: 8rem;">
                    <template #body="{ data }">
                        <!-- <div class="space-x-2 flex pl-2">
                            <template v-if="hasPermission('create vouchers')">
                                <button v-tooltip="'Comprobantes'"><i class="pi pi-book text-green-500 text-lg font-extrabold"
                                        @click="Vouchers(data)"></i></button>
                            </template>
                            <template v-if="hasPermission('edit suppliers')">
                                <button v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                        @click="editSupplier(data)"></i></button>
                            </template>
                            <template v-if="hasPermission('view users')">
                                <button v-tooltip="'+Info'"><i class="pi pi-id-card text-cyan-500 text-2xl" @click="info(data, data.id)"></i></button>
                            </template>
                        </div> -->
                    </template>
                </Column>
            </DataTable>
        </template>
    </Card>
</template>