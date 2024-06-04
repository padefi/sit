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
    banks: {
        type: Object,
        default: () => ({}),
    },
});

const { hasPermission } = usePermissions();
const banksArray = ref([]);
const originalBanksArray = ref([]);
const toast = useToast();
const newRow = ref([]);
const editingRows = ref([]);
const rules = 'Debe completar el campo'
const editing = ref(false);
const confirm = useConfirm();

banksArray.value = props.banks;

const validateEmail = value => {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(value)) {
        return true
    } else {
        return false
    }
}

const addNewBank = () => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de agregar un banco.',
            life: 3000,
        });

        return;
    }

    originalBanksArray.value = [...banksArray.value];

    const newBank = {
        id: crypto.randomUUID(),
        name: newRow.value?.name,
        address: newRow.value?.address,
        phone: newRow.value?.phone,
        email: newRow.value?.email,
        condition: 'newBank',
    };

    banksArray.value.unshift(newBank);
    editing.value = true;
    editingRows.value = [newBank];
};

const onRowEditInit = (event) => {
    originalBanksArray.value = [...banksArray.value];
    editingRows.value = [event.data];
}

const disabledEditButtons = (callback, event) => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de modificar un banco.',
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
    if (!data.name || !data.address || !data.phone || !data.email) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });

        return;
    }

    if (data.condition === 'newBank') {
        confirm.require({
            target: event.currentTarget,
            message: '¿Está seguro de agrear el banco?',
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
        message: '¿Está seguro de modificar el banco?',
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
        address: newData.address,
        phone: newData.phone,
        email: newData.email,
        notes: newData.notes,
    })

    if (newData.condition === 'newBank') {
        form.post(route("banks.store", newData.id), {
            onSuccess: (result) => {
                editing.value = false;
                newData.condition = 'editBank';
                newData.id = result.props.flash.info.bank.id;
                newData.name = result.props.flash.info.bank.name;
                newData.address = result.props.flash.info.bank.address;
                newData.phone = result.props.flash.info.bank.phone;
                newData.email = result.props.flash.info.bank.email;
                newData.notes = result.props.flash.info.bank.notes;
                newRow.value = [];
            },
            onError: () => {
                banksArray.value = [...originalBanksArray.value];
                editing.value = false;
                addNewBank();
            }
        });

        return;
    }

    form.put(route("banks.update", newData.id), {
        onSuccess: () => {
            editing.value = false;
            banksArray.value[index] = newData;
        },
        onError: () => {
            editing.value = true;
            editingRows.value = [newData];
        }
    });
};

const onRowEditCancel = () => {
    banksArray.value = [...originalBanksArray.value];
    editing.value = false;
    newRow.value = [];
    editingRows.value = [];
};

onMounted(() => {
    console.log(props.banks);
});

/*  */
import infoModal from '@/Components/InfoModal.vue';
import { useDialog } from 'primevue/usedialog';

const dialog = useDialog();

const info = (data) => {
    axios.get(`/banks/${data.id}/info`)
        .then((response) => {
            dialog.open(infoModal, {
                props: {
                    header: `Información del banco ${data.name.toUpperCase()}`,
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
                        <h3 class="uppercase">Bancos</h3>
                    </div>
                    <template v-if="hasPermission('create banks')">
                        <div class="align-right">
                            <Button label="Agregar banco" severity="info" outlined icon="pi pi-building-columns"
                                size="large" @click="addNewBank($event)" />
                        </div>
                    </template>
                </div>
            </template>
            <template #content>
                <DataTable v-model:editingRows="editingRows" :value="banksArray" editMode="row" dataKey="id"
                    @row-edit-init="onRowEditInit($event)" @row-edit-save="onRowEditSave"
                    @row-edit-cancel="onRowEditCancel" :pt="{
                        table: { style: 'min-width: 50rem' },
                        paginator: {
                            root: { class: 'p-paginator-custom' },
                            current: { class: 'p-paginator-current' },
                        }
                    }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table">
                    <Column field="name" header="Nombre" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" :invalid="!data[field]"
                                placeholder="Nombre" style="width: 100%;" />
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="address" header="Dirección" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" :invalid="!data[field]"
                                placeholder="Dirección" style="width: 100%;" />
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="phone" header="Teléfono" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" :invalid="!data[field]"
                                placeholder="Teléfono" style="width: 100%;" />
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="email" header="Email" style="width: 15%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]"
                                :invalid="!data[field] || !validateEmail(data[field])" placeholder="Email"  style="width: 100%;" />
                            <InputError
                                :message="!data[field] ? rules : validateEmail(data[field]) ? '' : 'Dirección de mail invalida'" />
                        </template>
                    </Column>
                    <Column field="bankAccountRelationship" header="Cuentas asociadas" style="width: 10%;">
                    </Column>
                    <Column header="Acciones" style="width: 5%; min-width: 8rem;" :rowEditor="true">
                        <template #body="{ editorInitCallback, data }">
                            <div class="space-x-4 flex pl-6">
                                <template v-if="hasPermission('edit banks')">
                                    <button v-tooltip="'Editar'"><i
                                            class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                            @click="disabledEditButtons(editorInitCallback, $event)"></i></button>
                                </template>
                                <template v-if="hasPermission('view users')">
                                    <button v-tooltip="'+Info'"><i class="pi pi-id-card text-cyan-500 text-2xl"
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