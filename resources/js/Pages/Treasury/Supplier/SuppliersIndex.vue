<script setup>
import { ref, onMounted } from 'vue';
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
const editingRows = ref([]);
const rules = 'Debe completar el campo'
const editing = ref(false);
const confirm = useConfirm();

const suppliersArray = ref([]);
const originalSuppliersArray = ref([]);

suppliersArray.value = props.suppliers;

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
            categories : props.categories,
        }
    });
};

onMounted(() => {
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
                    <Column field="name" header="Razón social" style="width: 10%;">
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                    </Column>
                    <Column field="businessName" header="Nombre fantasía" style="width: 10%;">
                        <template #body="{ data }">
                            {{ data.businessName }}
                        </template>
                    </Column>
                    <Column field="cuit" header="Cuit" style="width: 10%;">
                        <template #body="{ data }">
                            {{ data.cuit }}
                        </template>
                    </Column>
                    <Column header="Dirección" style="width: 10%;">
                        <template #body="{ data }">
                            {{ data.street }} {{ data.streetNumber }}
                        </template>
                    </Column>
                    <Column field="city" header="Ciudad" style="width: 10%;">
                        <template #body="{ data }">
                            {{ data.city }}
                        </template>
                    </Column>
                    <Column field="postalCode" header="C.P." style="width: 10%;">
                        <template #body="{ data }">
                            {{ data.postalCode }}
                        </template>
                    </Column>
                    <Column header="Datos contacto" style="width: 10%;">
                        <template #body="{ data }">
                            {{ data.phone }} {{ data.email }}
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
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>