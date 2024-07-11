<script setup>
import { ref, onMounted, nextTick } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePermissions } from '@/composables/permissions';
import { toastService } from '@/composables/toastService'
import { useForm } from '@inertiajs/vue3';
import { format } from "@formkit/tempo"
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useConfirm } from 'primevue/useconfirm';
import stepperModal from '@/Components/StepperModal.vue';
import infoModal from '@/Components/InfoModal.vue';
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
const OverlayPanelMap = ref();
const editingRows = ref([]);
const rules = 'Debe completar el campo'
const editing = ref(false);
const confirm = useConfirm();

const suppliersArray = ref([]);
const originalSuppliersArray = ref([]);

const filters = ref({
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

const addNewSupplier = () => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de agregar un proveedor.',
            life: 3000,
        });

        return;
    }

    originalSuppliersArray.value = [...suppliersArray.value];

    /* const newSupplier = {
        id: crypto.randomUUID(),
        name: newRow.value?.name,
        address: newRow.value?.address,
        phone: newRow.value?.phone,
        email: newRow.value?.email,
        condition: 'newBank',
        accounts: [],
    };

    banksArray.value.unshift(newBank);
    editing.value = true;
    editingRows.value = [newBank]; */

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
const info = (data, id) => {
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
                            <Button label="Agregar proveedor" severity="info" outlined icon="pi pi-address-book"
                                size="large" @click="addNewSupplier($event)" />
                        </div>
                    </template>
                </div>
            </template>
            <template #content>
                <DataTable :value="suppliersArray" scrollable scrollHeight="70vh" dataKey="id" class="data-table">
                    <template #empty>
                        <div class="text-center text-lg text-red-500">
                            Sin proveedores cargados
                        </div>
                    </template>
                    <Column field="cuit" header="Cuit">
                        <template #body="{ data }">
                            {{ data.cuit }}
                        </template>
                    </Column>
                    <Column field="name" header="Razón social">
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                    </Column>
                    <Column field="businessName" header="Nombre fantasía">
                        <template #body="{ data }">
                            {{ data.businessName }}
                        </template>
                    </Column>
                    <Column header="Dirección">
                        <template #body="{ data }">
                            {{ data.street }} {{ data.streetNumber }} {{ data.floor }} {{ data.apartment }}
                            - {{ data.postalCode }},
                            {{ data.city }}, {{ data.state }} - {{ data.country }}
                            <Button icon="pi pi-map-marker"
                                class="!p-0 !text-cyan-500 text-lg hover:!bg-transparent focus:!bg-transparent focus:!ring-transparent"
                                text rounded v-tooltip="'Ver en mapa'" @click="viewOnMap(data, $event)" />
                        </template>
                    </Column>
                    <Column header="Datos contacto">
                        <template #body="{ data }">
                            <div class="flex flex-col gap-2">
                                <div class="flex">
                                    <i class="pi pi-at text-amber-500"></i>
                                    <span class="ml-2 bottom-0.5 relative text-sm"
                                        :class="{ 'text-red-500': data.email === '' }">{{ data.email !== '' ? data.email
                        :
                        'Sin email' }}</span>
                                </div>

                                <div class="flex">
                                    <i class="pi pi-phone text-emerald-500"></i>
                                    <span class="ml-2 bottom-0.5 relative text-sm"
                                        :class="{ 'text-red-500': data.phone === '' }">{{ data.phone !== '' ? data.phone
                        :
                        'Sin telefono' }}</span>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column header="Acciones" style="width: 5%; min-width: 8rem;" :rowEditor="true">
                        <template #body="{ editorInitCallback, data }">
                            <div class="space-x-4 flex pl-6">
                                <template v-if="hasPermission('edit suppliers')">
                                    <button v-tooltip="'Editar'"><i
                                            class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                            @click="disabledEditButtons(editorInitCallback, $event, 'banks')"></i></button>
                                </template>
                                <template v-if="hasPermission('view users')">
                                    <button v-tooltip="'+Info'"><i class="pi pi-id-card text-cyan-500 text-2xl"
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