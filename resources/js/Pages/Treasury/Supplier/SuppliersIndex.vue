<script setup>
import { ref, onMounted, nextTick } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePermissions } from '@/composables/permissions';
import { toastService } from '@/composables/toastService'
import { useToast } from "primevue/usetoast";
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import stepperModal from './StepperModal.vue';
import supplierDataModal from './SupplierDataModal.vue';
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
    categories: {
        type: Object,
        default: () => ({}),
    },
});

const { hasPermission } = usePermissions();
const toast = useToast();
const suppliersArray = ref([]);
const OverlayPanelMap = ref();

const filters = ref({
    cuit: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    businessName: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

const addNewSupplier = () => {
    dialog.open(stepperModal, {
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
    dialog.open(stepperModal, {
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

const infoSupplier = (data) => {
    dialog.open(supplierDataModal, {
        props: {
            header: `${data.name.toUpperCase()}`,
            style: {
                width: '50vw',
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

onMounted(async () => {
    suppliersArray.value = props.suppliers;

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
                <DataTable :value="suppliersArray" v-model:filters="filters" scrollable scrollHeight="70vh" dataKey="id" class="data-table"
                    filterDisplay="menu">
                    <template #empty>
                        <div class="text-center text-lg text-red-500">
                            Sin proveedores cargados
                        </div>
                    </template>
                    <Column field="cuit" header="Cuit">
                        <template #body="{ data }">
                            {{ data.cuit }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="cuit" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por CUIT" />
                        </template>
                    </Column>
                    <Column field="name" header="Razón social">
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="name" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por razón social" />
                        </template>
                    </Column>
                    <Column field="businessName" header="Nombre fantasía">
                        <template #body="{ data }">
                            {{ data.businessName }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="businessName" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por nombre fantasía" />
                        </template>
                    </Column>
                    <Column header="Dirección">
                        <template #body="{ data }">
                            {{ data.street }} {{ data.streetNumber }} {{ data.floor }} {{ data.apartment }}
                            - {{ data.postalCode }},
                            {{ data.city }}, {{ data.state }} - {{ data.country }}
                            <Button icon="pi pi-map-marker"
                                class="!p-0 !text-cyan-500 text-lg hover:!bg-transparent focus:!bg-transparent focus:!ring-transparent" text rounded
                                v-tooltip="'Ver en mapa'" @click="viewOnMap(data, $event)" />
                        </template>
                    </Column>
                    <Column header="Datos contacto">
                        <template #body="{ data }">
                            <div class="flex flex-col gap-2">
                                <div class="flex">
                                    <i class="pi pi-at text-amber-500"></i>
                                    <span class="ml-2 bottom-0.5 relative text-sm" :class="{ 'text-red-500': data.email === '' }">
                                        {{ data.email !== '' ? data.email : 'Sin email' }}</span>
                                </div>

                                <div class="flex">
                                    <i class="pi pi-phone text-emerald-500"></i>
                                    <span class="ml-2 bottom-0.5 relative text-sm" :class="{ 'text-red-500': data.phone === '' }">
                                        {{ data.phone !== '' ? data.phone : 'Sin telefono' }}</span>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column header="Acciones" style="width: 5%; min-width: 8rem;">
                        <template #body="{ data }">
                            <div class="space-x-4 flex pl-6">
                                <button v-tooltip="'+Info'"><i class="pi pi-search-plus text-green-500 text-xl font-extrabold"
                                        @click="infoSupplier(data)"></i></button>
                                <template v-if="hasPermission('view users')">
                                    <button v-tooltip="'Info usuarios'"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                            @click="info(data, data.id)"></i></button>
                                </template>
                            </div>
                        </template>
                    </Column>
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