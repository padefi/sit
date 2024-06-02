<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import { usePermissions } from '@/composables/permissions';
import { useConfirm } from "primevue/useconfirm";
import { toastService } from '@/composables/toastService'

toastService();

const props = defineProps({
    voucherSubtypes: {
        type: Object,
        default: () => ({}),
    },
});

const { hasPermission } = usePermissions();
const voucherSubtypesArray = ref([]);
const originalVoucherSubtypesArray = ref([]);
const toast = useToast();
const newRow = ref([]);
const editingRows = ref([]);
const rules = 'Debe completar el campo'
const editing = ref(false);
const confirm = useConfirm();

voucherSubtypesArray.value = props.voucherSubtypes;

const statuses = ref([
    { label: 'ACTIVO', value: 'ACTIVO' },
    { label: 'INACTIVO', value: 'INACTIVO' }
]);

const getStatusLabel = (status) => {
    switch (status) {
        case 'ACTIVO':
            return 'success';

        case 'INACTIVO':
            return 'danger';

        default:
            return null;
    }
};

const addNewVoucherSubtype = () => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de agregar un subtipo.',
            life: 3000,
        });

        return;
    }

    originalVoucherSubtypesArray.value = [...voucherSubtypesArray.value];

    const newVoucherSubtype = {
        id: createId(),
        name: newRow.value?.name,
        status: newRow.value?.status,
        condition: 'newVoucherSubtype',
    };

    voucherSubtypesArray.value.unshift(newVoucherSubtype);
    editing.value = true;
    editingRows.value = [newVoucherSubtype];
};

const createId = () => {
    let id = '';
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for (var i = 0; i < 5; i++) {
        id += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    return id;
}

const onRowEditInit = (event) => {
    originalVoucherSubtypesArray.value = [...voucherSubtypesArray.value];
    editingRows.value = [event.data];
}

const disabledEditButtons = (callback, event) => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de modificar un sutipo.',
            life: 3000,
        });

        return;
    }

    editing.value = true;
    callback(event);
}

const enabledEditButtons = (callback, event) => {
    editing.value = false;
    editingRows.value = [];
    callback(event);
}

const validate = (event, saveCallback, data) => {
    if (!data.name || !data.status) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });

        return;
    }

    if (data.condition === 'newVoucherSubtype') {
        confirm.require({
            target: event.currentTarget,
            message: '¿Está seguro de agrear el subtipo?',
            rejectClass: 'bg-red-500 text-white hover:bg-red-600',
            accept: () => {
                newRow.value = data;
                saveCallback(event);
            },
        });

        return;
    }

    confirm.require({
        target: event.currentTarget,
        message: '¿Está seguro de modificar el subtipo?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            saveCallback(event);
        },
    });
}

const onRowEditSave = (event) => {
    let { newData, index } = event;

    const form = useForm({
        name: newData.name,
        status: newData.status === 'ACTIVO' ? 1 : 0,
    })

    if (newData.condition === 'newVoucherSubtype') {
        form.post(route("voucher-subtypes.store", newData.id), {
            onSuccess: (result) => {
                editing.value = false;
                newData.condition = 'editVoucherSubtype';
                newData.id = result.props.flash.info.voucherSubtype.id;
                newData.name = result.props.flash.info.voucherSubtype.name;
            },
            onError: () => {
                voucherSubtypesArray.value = [...originalVoucherSubtypesArray.value];
                editing.value = false;
                addNewVoucherSubtype();
            }
        });

        return;
    }
    
    form.put(route("voucher-subtypes.update", newData.id), {
        onSuccess: () => {
            editing.value = false;
            voucherSubtypesArray.value[index] = newData;
        },
        onError: () => {
            editing.value = true;
            editingRows.value = [newData];
        }
    });
};

const onRowEditCancel = () => {
    voucherSubtypesArray.value = [...originalVoucherSubtypesArray.value];
    editing.value = false;
    newRow.value = [];
    editingRows.value = [];
};

onMounted(() => {
    props.voucherSubtypes.map((voucherSubtype) => {
        voucherSubtype.status = voucherSubtype.status === 1 ? 'ACTIVO' : 'INACTIVO';
    });
});

/*  */
import infoModal from '@/Components/InfoModal.vue';
import { useDialog } from 'primevue/usedialog';

const dialog = useDialog();

const info = (data) => {
    dialog.open(infoModal, {
        props: {
            header: `Información del subtipo ${data.name.toUpperCase()}`,
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
}
/*  */
</script>

<template>
    <AuthenticatedLayout>
        <Card class="mt-5 mx-4 uppercase">
            <template #title>
                <div class="flex justify-between items-center mx-4">
                    <div class="align-left">
                        <h3 class="uppercase">Subtipos</h3>
                    </div>
                    <template v-if="hasPermission('create voucher subtypes')">
                        <div class="align-right">
                            <Button label="Agregar subtipo" severity="info" outlined icon="pi pi-folder-plus" size="large"
                                @click="addNewVoucherSubtype($event)" />
                        </div>
                    </template>
                </div>
            </template>
            <template #content>
                <DataTable v-model:editingRows="editingRows" :value="voucherSubtypesArray" editMode="row" dataKey="id"
                    @row-edit-init="onRowEditInit($event)" @row-edit-save="onRowEditSave"
                    @row-edit-cancel="onRowEditCancel" :pt="{
                        table: { style: 'min-width: 50rem' },
                        paginator: {
                            root: { class: 'p-paginator-custom'},
                            current: { class: 'p-paginator-current' },
                        }
                    }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="{first} - {last} de {totalRecords}"
                    class="data-table">
                    <Column field="name" header="Descripción" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" :invalid="!data[field]"
                                placeholder="Descripcion" style="width: 100%;" />
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="subtypeExpenseRelationship" header="Gastos relacionados" style="width: 10%;">
                    </Column>
                    <Column field="status" header="Estado" style="width: 10%;">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.status" class="!text-sm uppercase"
                                :severity="getStatusLabel(slotProps.data.status)" />
                        </template>
                        <template #editor="{ data, field }">
                            <Dropdown v-model="data[field]" :options="statuses" optionLabel="label" optionValue="value"
                                placeholder="Seleccione un estado">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value"
                                        :severity="getStatusLabel(slotProps.option.value)" class="!text-sm uppercase" />
                                </template>
                            </Dropdown>
                        </template>
                    </Column>
                    <Column header="Acciones" style="width: 5%; min-width: 8rem;" :rowEditor="true">
                        <template #body="{ editorInitCallback, data }">
                            <div class="space-x-4 flex pl-6">
                                <template v-if="hasPermission('edit voucher subtypes')">
                                    <button v-tooltip="'Editar'"><i
                                            class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                            @click="disabledEditButtons(editorInitCallback, $event)"></i></button>
                                </template>
                                <template v-if="hasPermission('permission users')">
                                    <button v-tooltip="'+Info'"><i
                                            class="pi pi-id-card text-cyan-500 text-2xl"
                                            @click="info(data)"></i></button>
                                </template>
                            </div>
                        </template>
                        <template #editor="{ data, editorSaveCallback, editorCancelCallback }">
                            <div class="space-x-4 flex pl-7">
                                <ConfirmPopup></ConfirmPopup>
                                <button><i class="pi pi-check text-primary-500 text-lg font-extrabold"
                                        @click="validate($event, editorSaveCallback, data)"></i></button>
                                <button><i class="pi pi-times text-red-500 text-lg font-extrabold"
                                        @click="enabledEditButtons(editorCancelCallback, $event, data)"></i></button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>