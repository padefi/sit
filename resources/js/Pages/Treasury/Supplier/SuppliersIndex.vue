<script setup>
import { ref, onMounted, nextTick } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePermissions } from '@/composables/permissions';
import { toastService } from '@/composables/toastService'
import { currencyNumber } from "@/utils/formatterFunctions";
import { useToast } from "primevue/usetoast";
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import supplierModal from './SupplierModal.vue';
import supplierVoucherModal from './SupplierVoucherModal.vue';
import { useDialog } from 'primevue/usedialog';
import L from 'leaflet';

toastService();
const dialog = useDialog();

const props = defineProps({
    suppliers: {
        type: Object,
        default: () => ({}),
    },
    vatConditions: {
        type: Object,
        default: () => ({}),
    },
    vatRates: {
        type: Object,
        default: () => ({}),
    },
    categories: {
        type: Object,
        default: () => ({}),
    },
    payConditions: {
        type: Object,
        default: () => ({}),
    },
    voucherTypes: {
        type: Object,
        default: () => ({}),
    },
});

const { hasPermission, hasPermissionColumn } = usePermissions();
const toast = useToast();
const suppliersArray = ref([]);
const loading = ref(true);
const expandedRows = ref([]);
const OverlayPanelMap = ref();

const filters = ref({
    cuit: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    businessName: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    pendingToPay: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
});

const Vouchers = (data) => {
    dialog.open(supplierVoucherModal, {
        props: {
            header: data.businessName,
            style: {
                width: '95vw',
                minHeight: '60vh',
            },
            breakpoints: {
                '960px': '75vw',
                '640px': '90vw'
            },
            modal: true,
            contentStyle: {
                padding: '1.25rem',
                minHeight: '60vh',
                backgroundColor: 'rgb(var(--surface-50))',
            },
        },
        data: {
            supplierId: data.id,
            payConditions: props.payConditions,
            voucherTypes: props.voucherTypes,
            vatRates: props.vatRates,
        }
    });
};

const addNewSupplier = () => {
    dialog.open(supplierModal, {
        props: {
            header: 'Nuevo proveedor',
            style: {
                width: '60vw',
            },
            breakpoints: {
                '960px': '75vw',
                '640px': '90vw'
            },
            modal: true,
            contentStyle: {
                padding: '1.25rem'
            },
        },
        data: {
            vatConditions: props.vatConditions,
            categories: props.categories,
        }
    });
};

const editSupplier = (data) => {
    dialog.open(supplierModal, {
        props: {
            header: `Editar proveedor ${data.name.toUpperCase()}`,
            style: {
                width: '60vw',
            },
            breakpoints: {
                '960px': '75vw',
                '640px': '90vw'
            },
            modal: true,
            contentStyle: {
                padding: '1.25rem'
            },
        },
        data: {
            vatConditions: props.vatConditions,
            categories: props.categories,
            supplierData: data,
        }
    });
};

const viewOnMap = async (data, event) => {
    OverlayPanelMap.value.toggle(event);

    await nextTick();

    const map = L.map('map').setView([data.latitude, data.longitude], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    L.marker([data.latitude, data.longitude]).addTo(map).openPopup();
}

onMounted(() => {
    suppliersArray.value = props.suppliers;
    loading.value = false;

    Echo.channel('suppliers')
        .listen('Treasury\\Supplier\\SupplierEvent', (e) => {
            if (e.type === 'create') {
                if (!suppliersArray.value.some(supplier => supplier.id === e.supplierId)) {
                    e.supplier.supplierIndex = 0;
                    e.supplier.accounts = [];
                    suppliersArray.value.unshift(e.supplier);
                }
            } else if (e.type === 'update') {
                const index = suppliersArray.value.findIndex(supplier => supplier.id === e.supplier.id);

                if (index !== -1) {
                    suppliersArray.value[index] = e.supplier;
                }
            }
        });
});

/*  */
import infoModal from '@/Components/InfoModal.vue';

const dialogInfo = useDialog();

const info = (data, id) => {
    axios.get(`/suppliers/${id}/info`)
        .then((response) => {
            const header = `Información del proveedor ${data.name.toUpperCase()}`;

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
    <AuthenticatedLayout>
        <Card class="mt-5 mx-4 uppercase">
            <template #title>
                <div class="flex justify-between items-center mx-4">
                    <div class="align-left">
                        <h3 class="uppercase">Proveedores</h3>
                    </div>
                    <template v-if="hasPermission('create suppliers')">
                        <div class="align-right">
                            <Button label="Agregar proveedor" severity="info" outlined icon="pi pi-address-book" size="large"
                                @click="addNewSupplier($event)" />
                        </div>
                    </template>
                </div>
            </template>
            <template #content>
                <DataTable :value="suppliersArray" v-model:filters="filters" v-model:expandedRows="expandedRows" :loading="loading" scrollable
                    scrollHeight="60vh" dataKey="id" filterDisplay="menu" :pt="{
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
                    <Column expander class="min-w-2 w-2 !px-0" v-if="hasPermission('view suppliers')" />
                    <Column field="cuit" header="Cuit" sortable class="w-1/6">
                        <template #body="{ data }">
                            {{ data.cuit }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="cuit" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por CUIT" />
                        </template>
                    </Column>
                    <Column field="name" header="Razón social" sortable class="w-2/6">
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="name" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por razón social" />
                        </template>
                    </Column>
                    <Column field="businessName" header="Nombre fantasía" sortable class="w-2/6">
                        <template #body="{ data }">
                            {{ data.businessName }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="businessName" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por nombre fantasía" />
                        </template>
                    </Column>
                    <Column field="pendingToPay" header="Saldo" dataType="numeric" sortable class="w-1/6">
                        <template #body="{ data }">
                            <span :class="data.pendingToPay < 0 ? 'text-red-500' : ''">
                                {{ Number(data.pendingToPay) ? currencyNumber(data.pendingToPay) : currencyNumber(0) }}
                            </span>
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="pendingToPay" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por saldo" />
                        </template>
                    </Column>
                    <Column header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28"
                        v-if="hasPermissionColumn(['view vouchers', 'edit suppliers', 'view users'])">
                        <template #body="{ data }">
                            <div class="space-x-2 flex pl-2">
                                <template v-if="hasPermission('view vouchers')">
                                    <button v-tooltip="'Comprobantes'"><i class="pi pi-book text-green-500 text-lg font-extrabold"
                                            @click="Vouchers(data)"></i></button>
                                </template>
                                <template v-if="hasPermission('edit suppliers')">
                                    <button v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                            @click="editSupplier(data)"></i></button>
                                </template>
                                <template v-if="hasPermission('view users')">
                                    <button v-tooltip="'+Info'"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                            @click="info(data, data.id)"></i></button>
                                </template>
                            </div>
                        </template>
                    </Column>
                    <template #expansion="{ data }">
                        <div class="data-table-expanded">
                            <div class="flex w-6/6 gap-3 mx-1 my-0 text-sm uppercase tracking-tight">
                                <div class="w-full md:w-5/12">
                                    {{ data.street }} {{ data.streetNumber }} {{ data.floor }} {{ data.apartment }}
                                    - {{ data.postalCode }},
                                    {{ data.city }}, {{ data.state }} - {{ data.country }}
                                    <Button icon="pi pi-map-marker"
                                        class="!p-0 !text-cyan-500 text-lg hover:!bg-transparent focus:!bg-transparent focus:!ring-transparent" text
                                        rounded v-tooltip="'Ver en mapa'" @click="viewOnMap(data, $event)" />
                                </div>
                                <div class="w-full md:w-1/6 min-w-48 break-all">
                                    <div class="flex flex-col top-0.5 relative">
                                        <div class="flex">
                                            <i class="pi pi-at text-amber-500"></i>
                                            <span class="ml-1 bottom-0.5 relative" :class="{ 'text-red-500': data.email === '' }">
                                                {{ data.email !== '' ? data.email : 'Sin email' }}</span>
                                        </div>

                                        <div class="flex">
                                            <i class="pi pi-phone text-emerald-500"></i>
                                            <span class="ml-1 bottom-0.5 relative" :class="{ 'text-red-500': data.phone === '' }">
                                                {{ data.phone !== '' ? data.phone : 'Sin telefono' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full md:w-1/6 break-all">
                                    <div class="w-full text-left text-surface-900/60 font-bold">CBU</div>
                                    <div class="" :class="{ 'text-red-500': !data.cbu }">
                                        {{ (data.cbu) ? data.cbu : 'Sin CBU' }}
                                    </div>
                                </div>

                                <div class="w-full md:w-1/6">
                                    <div class="w-full text-left text-surface-900/60 font-bold">Condición de I.V.A.</div>
                                    <div class="">
                                        {{ (data.idVC) && vatConditions.filter(v => v.id === data.idVC)[0].name }}
                                    </div>
                                </div>

                                <div class="w-full md:w-1/6">
                                    <div class="w-full text-left text-surface-900/60 font-bold">Rubro</div>
                                    <div class="">
                                        {{ (data.idCat) && categories.filter(c => c.id === data.idCat)[0].name }}
                                    </div>
                                </div>

                                <div class="w-full md:w-48">
                                    <div class="flex w-full flex-col align-items-center">
                                        <div class="flex w-full">
                                            <div class="flex align-items-center w-1/3">
                                                <div class="w-full">
                                                    <div class="w-full text-surface-900/60 font-bold">
                                                        Gcias.
                                                    </div>
                                                    <div :class="{ 'text-red-500': !data.incomeTaxWithholding }">
                                                        {{ (data.incomeTaxWithholding) ? 'SI' : 'NO' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex align-items-center w-1/3">
                                                <div class="w-full">
                                                    <div class="w-full text-surface-900/60 font-bold">
                                                        Suss
                                                    </div>
                                                    <div :class="{ 'text-red-500': !data.socialTax }">
                                                        {{ (data.socialTax) ? 'SI' : 'NO' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex align-items-center w-1/3">
                                                <div class="w-full">
                                                    <div class="w-full text-surface-900/60 font-bold">
                                                        I.V.A.
                                                    </div>
                                                    <div :class="{ 'text-red-500': !data.vatTax }">
                                                        {{ (data.vatTax) ? 'SI' : 'NO' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </DataTable>

                <OverlayPanel ref="OverlayPanelMap">
                    <div class="flex flex-col gap-3 w-[25rem]">
                        <div id="map" style="height: 400px;"></div>
                    </div>
                </OverlayPanel>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>