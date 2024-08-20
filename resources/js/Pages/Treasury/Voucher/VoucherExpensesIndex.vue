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
    voucherExpenses: {
        type: Object,
        default: () => ({}),
    },
});

const { hasPermission } = usePermissions();
const voucherExpensesArray = ref([]);
const originalVoucherExpensesArray = ref([]);
const toast = useToast();
const newRow = ref([]);
const editingRows = ref([]);
const rules = 'Debe completar el campo'
const editing = ref(false);
const confirm = useConfirm();

voucherExpensesArray.value = props.voucherExpenses;

const statuses = ref([
    { label: 'ACTIVO', value: 'ACTIVO' },
    { label: 'INACTIVO', value: 'INACTIVO' }
]);

const filters = ref({
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    status: { operator: FilterOperator.OR, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
});

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

const addNewVoucherExpense = () => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de agregar un gasto.',
            life: 3000,
        });

        return;
    }

    originalVoucherExpensesArray.value = [...voucherExpensesArray.value];

    const newVoucherExpense = {
        id: crypto.randomUUID(),
        name: newRow.value?.name,
        status: newRow.value?.status,
        condition: 'newVoucherExpense',
    };

    voucherExpensesArray.value.unshift(newVoucherExpense);
    editing.value = true;
    editingRows.value = [newVoucherExpense];
};

const onRowEditInit = (event) => {
    originalVoucherExpensesArray.value = [...voucherExpensesArray.value];
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
    if (!data.name.trim() || !data.status) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });

        return;
    }

    if (data.condition === 'newVoucherExpense') {
        confirm.require({
            target: event.currentTarget,
            message: '¿Está seguro de agregar el gasto?',
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
        message: '¿Está seguro de modificar el gasto?',
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

    if (newData.condition === 'newVoucherExpense') {
        form.post(route("voucher-expenses.store", newData.id), {
            onSuccess: (result) => {
                editing.value = false;
                newData.condition = 'editVoucherExpense';
                newData.id = result.props.flash.info.voucherExpense.id;
                newData.name = result.props.flash.info.voucherExpense.name;
                newRow.value = [];
            },
            onError: () => {
                voucherExpensesArray.value = [...originalVoucherExpensesArray.value];
                editing.value = false;
                addNewVoucherExpense();
            }
        });

        return;
    }

    form.put(route("voucher-expenses.update", newData.id), {
        onSuccess: () => {
            editing.value = false;
            voucherExpensesArray.value[index] = newData;
        },
        onError: () => {
            editing.value = true;
            editingRows.value = [newData];
        }
    });
};

const onRowEditCancel = () => {
    voucherExpensesArray.value = [...originalVoucherExpensesArray.value];
    editing.value = false;
    newRow.value = [];
    editingRows.value = [];
};

onMounted(() => {
    props.voucherExpenses.map((voucherExpense) => {
        voucherExpense.status = voucherExpense.status === 1 ? 'ACTIVO' : 'INACTIVO';
    });

    Echo.channel('expenses')
        .listen('Treasury\\Voucher\\VoucherExpenseEvent', (e) => {
            e.voucherExpense.status = e.voucherExpense.status === 1 ? 'ACTIVO' : 'INACTIVO';

            if (e.type === 'create') {
                if (!voucherExpensesArray.value.some(voucherExpense => voucherExpense.id === e.voucherExpenseId)) {
                    voucherExpensesArray.value.unshift(e.voucherExpense);
                }
            } else if (e.type === 'update') {
                const index = voucherExpensesArray.value.findIndex(voucherExpense => voucherExpense.id === e.voucherExpense.id);

                if (index !== -1) {
                    voucherExpensesArray.value[index] = e.voucherExpense;
                }
            }
        });
});

/*  */
import infoModal from '@/Components/InfoModal.vue';
import { useDialog } from 'primevue/usedialog';
import { FilterMatchMode, FilterOperator } from 'primevue/api';

const dialog = useDialog();

const info = (data) => {
    axios.get(`/voucher-expenses/${data.id}/info`)
        .then((response) => {
            dialog.open(infoModal, {
                props: {
                    header: `Información del gasto ${data.name.toUpperCase()}`,
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
                        <h3 class="uppercase">Gastos</h3>
                    </div>
                    <template v-if="hasPermission('create voucher expenses')">
                        <div class="align-right">
                            <Button label="Agregar gasto" severity="info" outlined icon="pi pi-folder-plus" size="large"
                                @click="addNewVoucherExpense($event)" />
                        </div>
                    </template>
                </div>
            </template>
            <template #content>
                <DataTable v-model:editingRows="editingRows" v-model:filters="filters" :value="voucherExpensesArray" scrollable scrollHeight="70vh"
                    editMode="row" dataKey="id" filterDisplay="menu" :globalFilterFields="['name', 'status']" @row-edit-init="onRowEditInit($event)"
                    @row-edit-save="onRowEditSave" @row-edit-cancel="onRowEditCancel" :pt="{
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
                            Sin gastos cargados
                        </div>
                    </template>
                    <Column field="name" header="Descripción" sortable>
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="name" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por descripción" />
                        </template>
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" name="name" autocomplete="off"
                                :invalid="!data[field] || data[field].trim() === ''" placeholder="Descripcion" style="width: 100%;" maxlength="100" />
                            <InputError :message="!data[field] || data[field].trim() === '' ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="status" header="Estado">
                        <template #body="{ data }">
                            <Tag :value="data.status" class="!text-sm uppercase" :severity="getStatusLabel(data.status)" />
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="statuses" placeholder="Estado"
                                class="p-column-filter" optionLabel="label" optionValue="value" style="min-width: 12rem" :showClear="true">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value" name="is_active" :severity="getStatusLabel(slotProps.option.value)"
                                        class="!text-sm uppercase" />
                                </template>
                            </Dropdown>
                        </template>
                        <template #editor="{ data, field }">
                            <Dropdown v-model="data[field]" :invalid="!data[field]" :options="statuses" optionLabel="label" optionValue="value"
                                placeholder="Seleccione un estado">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value" :severity="getStatusLabel(slotProps.option.value)"
                                        class="!text-sm uppercase" />
                                </template>
                            </Dropdown>
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28">
                        <template #body="{ editorInitCallback, data }">
                            <div class="space-x-2">
                                <template v-if="hasPermission('edit voucher expenses')">
                                    <button v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                            @click="disabledEditButtons(editorInitCallback, $event)"></i></button>
                                </template>
                                <template v-if="hasPermission('view users')">
                                    <button v-tooltip="'+Info'" class="btn-info"><i class="pi pi-id-card text-cyan-500 text-2xl" @click="info(data)"></i></button>
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