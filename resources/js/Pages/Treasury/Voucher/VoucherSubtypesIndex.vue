<script setup>
import { ref, onMounted } from 'vue';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import { usePermissions } from '@/composables/permissions';
import { useConfirm } from "primevue/useconfirm";
import { toastService } from '@/composables/toastService'
import { format } from "@formkit/tempo"

toastService();

const props = defineProps({
    voucherSubtypes: {
        type: Object,
        default: () => ({}),
    },
    voucherExpenses: {
        type: Object,
        default: () => ({}),
    },
    suppliers: {
        type: Object,
        default: () => ({}),
    },
});

const { hasPermission, hasPermissionColumn } = usePermissions();
const voucherSubtypesArray = ref([]);
const originalVoucherSubtypesArray = ref([]);
const voucherExpensesArray = ref([]);
const dataVoucherExpensesArray = ref([]);
const suppliersArray = ref([]);
const dataSuppliersArray = ref([]);
const expensesPanel = ref();
const suppliersPanel = ref();
const toast = useToast();
const newRow = ref([]);
const editingRows = ref([]);
const rules = 'Debe completar el campo'
const editing = ref(false);
const confirm = useConfirm();
const voucherSubtypeRelated = ref();

voucherSubtypesArray.value = props.voucherSubtypes;
dataVoucherExpensesArray.value = props.voucherExpenses;
dataSuppliersArray.value = props.suppliers;

const related = (data, event) => {
    voucherSubtypeRelated.value = data;
    voucherExpensesArray.value = [];

    dataVoucherExpensesArray.value.map((voucherExpense) => {
        const matchingExpense = data.expenses.find(expense => expense.id === voucherExpense.id);
        if (matchingExpense) {
            voucherExpense.related = true;
            voucherExpense.relatedData = {
                related_at: matchingExpense.related_at ? format(matchingExpense.related_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00',
                userRelated: {
                    name: matchingExpense.userRelated.name,
                    surname: matchingExpense.userRelated.surname
                }
            }
        } else {
            voucherExpense.related = false;
        }

        voucherExpensesArray.value.push(voucherExpense);
    });

    expensesPanel.value.toggle(event);
};

const supplierRelated = (data, event) => {
    voucherSubtypeRelated.value = data;
    suppliersArray.value = [];

    dataSuppliersArray.value.map((supplier) => {
        const matchingSupplier = data.suppliers.find(s => s.id === supplier.id);
        if (matchingSupplier) {
            supplier.related = true;
            supplier.relatedData = {
                related_at: matchingSupplier.related_at ? format(matchingSupplier.related_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00',
                userRelated: {
                    name: matchingSupplier.userRelated.name,
                    surname: matchingSupplier.userRelated.surname
                }
            }
        } else {
            supplier.related = false;
        }

        suppliersArray.value.push(supplier);
    });

    suppliersPanel.value.toggle(event);
};

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

const subtypesFilters = ref({
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    status: { operator: FilterOperator.OR, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
});

const expensesFilters = ref({
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

const suppliersFilters = ref({
    businessName: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

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
        id: crypto.randomUUID(),
        name: newRow.value?.name,
        expenses: [],
        suppliers: [],
        status: newRow.value?.status,
        condition: 'newVoucherSubtype',
    };

    voucherSubtypesArray.value.unshift(newVoucherSubtype);
    editing.value = true;
    editingRows.value = [newVoucherSubtype];
};

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
    if (!data.name.trim() || !data.status) {
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
            message: '¿Está seguro de agregar el subtipo?',
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
                newRow.value = [];
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

const allowHide = ref(true);

const handleHide = (event) => {
    if (!allowHide.value) {
        allowHide.value = true;
    }
};

const onContentMouseDown = () => {
    allowHide.value = false;
};

const relateButton = (event, data, status, type) => {
    if (voucherSubtypeRelated.value.condition === 'newVoucherSubtype') return;
    const descStatus = status ? 'relacionar el' : 'quitar la relación del';
    const desc = type === 'supplier' ? 'proveedor' : 'gasto';

    confirm.require({
        target: event.currentTarget,
        message: `¿Está seguro de ${descStatus} ${desc} al subipo?`,
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            const form =
                type === 'supplier' ?
                    useForm({
                        supplier: data.id
                    }) :
                    useForm({
                        voucherExpense: data.id
                    });

            const routeUrl = type === 'supplier' ? 'voucher-subtypes.supplier-relate' : 'voucher-subtypes.relate';
            form.post(route(routeUrl, voucherSubtypeRelated.value.id), {
                onSuccess: () => {
                },
                onError: () => {
                }
            });
        },
    });
}

onMounted(() => {
    props.voucherSubtypes.map((voucherSubtype) => {
        voucherSubtype.status = voucherSubtype.status === 1 ? 'ACTIVO' : 'INACTIVO';
    });

    Echo.channel('subtypes')
        .listen('Treasury\\Voucher\\VoucherSubtypeEvent', (e) => {
            e.voucherSubtype.status = e.voucherSubtype.status === 1 ? 'ACTIVO' : 'INACTIVO';

            if (e.type === 'create') {
                if (!voucherSubtypesArray.value.some(voucherSubtype => voucherSubtype.id === e.voucherSubtypeId)) {
                    voucherSubtypesArray.value.unshift(e.voucherSubtype);
                }
            } else if (e.type === 'update') {
                const index = voucherSubtypesArray.value.findIndex(voucherSubtype => voucherSubtype.id === e.voucherSubtype.id);

                if (index !== -1) {
                    voucherSubtypesArray.value[index] = e.voucherSubtype;
                }
            } else if (e.type === 'relate') {
                const index = voucherSubtypesArray.value.findIndex(voucherSubtype => voucherSubtype.id === e.voucherSubtype.id);

                if (index !== -1) {
                    voucherSubtypesArray.value[index] = e.voucherSubtype;
                }

                if (voucherSubtypeRelated.value.id != e.voucherSubtype.id) return;

                voucherExpensesArray.value.map((voucherExpense) => {
                    const data = e.voucherSubtype.expenses.find(expenseRelated => expenseRelated.id === voucherExpense.id);

                    if (data) {
                        voucherExpense.related = true;
                        voucherExpense.relatedData = {
                            related_at: data.related_at ? format(data.related_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00',
                            userRelated: {
                                name: data.userRelated.name,
                                surname: data.userRelated.surname
                            }
                        }
                    } else {
                        voucherExpense.related = false;
                    }
                });
            } else if (e.type === 'supplierRelate') {
                const index = voucherSubtypesArray.value.findIndex(voucherSubtype => voucherSubtype.id === e.voucherSubtype.id);

                if (index !== -1) {
                    voucherSubtypesArray.value[index] = e.voucherSubtype;
                }

                if (voucherSubtypeRelated.value.id != e.voucherSubtype.id) return;

                suppliersArray.value.map((supplier) => {
                    const data = e.voucherSubtype.suppliers.find(supplierRelated => supplierRelated.id === supplier.id);

                    if (data) {
                        supplier.related = true;
                        supplier.relatedData = {
                            related_at: data.related_at ? format(data.related_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00',
                            userRelated: {
                                name: data.userRelated.name,
                                surname: data.userRelated.surname
                            }
                        }
                    } else {
                        supplier.related = false;
                    }
                });
            }
        });

    Echo.channel('expenses')
        .listen('Treasury\\Voucher\\VoucherExpenseEvent', (e) => {
            e.voucherExpense.status = e.voucherExpense.status === 1 ? 'ACTIVO' : 'INACTIVO';

            if (e.type === 'create') {
                if (!dataVoucherExpensesArray.value.some(voucherExpense => voucherExpense.id === e.voucherExpenseId)) {
                    dataVoucherExpensesArray.value.unshift(e.voucherExpense);
                }

                if (!voucherExpensesArray.value.some(voucherExpense => voucherExpense.id === e.voucherExpenseId)) {
                    voucherExpensesArray.value.unshift(e.voucherExpense);
                }
            } else if (e.type === 'update') {
                const dataIndex = dataVoucherExpensesArray.value.findIndex(voucherExpense => voucherExpense.id === e.voucherExpense.id);
                const index = voucherExpensesArray.value.findIndex(voucherExpense => voucherExpense.id === e.voucherExpense.id);

                if (dataIndex !== -1) {
                    dataVoucherExpensesArray.value[dataIndex].name = e.voucherExpense.name;
                    dataVoucherExpensesArray.value[dataIndex].status = e.voucherExpense.status;
                    /* voucherExpensesArray.value[index].name = e.voucherExpense.name;
                    voucherExpensesArray.value[index].status = e.voucherExpense.status; */
                }
            }
        });

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
import { useDialog } from 'primevue/usedialog';

const dialog = useDialog();

const info = (data) => {
    axios.get(`/voucher-subtypes/${data.id}/info`)
        .then((response) => {
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
                <DataTable v-model:editingRows="editingRows" v-model:filters="subtypesFilters" :value="voucherSubtypesArray" scrollable
                    scrollHeight="70vh" editMode="row" dataKey="id" filterDisplay="menu" :globalFilterFields="['name', 'status']"
                    @row-edit-init="onRowEditInit($event)" @row-edit-save="onRowEditSave" @row-edit-cancel="onRowEditCancel" :pt="{
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
                            Sin subtipos cargados
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
                    <Column header="Gastos relacionados">
                        <template #body="{ data }">
                            <Button severity="info" raised rounded outlined @click="related(data, $event)"
                                :disabled="data.condition === 'newVoucherSubtype'">
                                {{ data.expenses.length }}
                            </Button>
                        </template>
                    </Column>
                    <Column header="Proveedores relacionados">
                        <template #body="{ data }">
                            <Button severity="info" raised rounded outlined @click="supplierRelated(data, $event)"
                                :disabled="data.condition === 'newVoucherSubtype'">
                                {{ data.suppliers.length }}
                            </Button>
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
                    <Column header="Acciones" class="action-column text-center" headerClass="min-w-32 w-32"
                        v-if="hasPermissionColumn(['edit voucher subtypes', 'view users'])">
                        <template #body="{ editorInitCallback, data }">
                            <div class="space-x-4 flex pl-6">
                                <template v-if="hasPermission('edit voucher subtypes')">
                                    <button v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                            @click="disabledEditButtons(editorInitCallback, $event)"></i></button>
                                </template>
                                <template v-if="hasPermission('view users')">
                                    <button v-tooltip="'+Info'" class="btn-info"><i class="pi pi-id-card text-cyan-500 text-2xl"
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

                <OverlayPanel ref="expensesPanel" appendTo="body" :dismissable="false" @hide="handleHide">
                    <div @mousedown.stop="onContentMouseDown">
                        <DataTable v-model:filters="expensesFilters" :value="voucherExpensesArray" paginator :rows="5" dataKey="id"
                            filterDisplay="menu" :globalFilterFields="['name']">
                            <template #empty>
                                <div class="text-center text-lg text-red-500">
                                    Sin gastos cargados
                                </div>
                            </template>
                            <Column field="name" header="Gasto" style="min-width: 12rem" class="uppercase" sortable>
                                <template #body="{ data }">
                                    <span :class="{ 'text-red-500': data.status === 'INACTIVO' || data.status === 0 }">
                                        {{ data.name }}
                                    </span>
                                </template>
                                <template #filter="{ filterModel, filterCallback }">
                                    <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="expenseName" autocomplete="off"
                                        class="p-column-filter" placeholder="Buscar por gasto" />
                                </template>
                            </Column>
                            <Column field="related" header="RELACIONADO" dataType="boolean" class="action-column text-center"
                                headerClass="min-w-28 w-28">
                                <template #body="{ data }">
                                    <div class="space-x-4">
                                        <template v-if="!hasPermission('relationship voucher subtypes')">
                                            <i class="pi top-1 relative"
                                                :class="{ 'pi-check-circle text-green-500': data.related, 'pi-times-circle text-red-400': !data.related }"
                                                v-tooltip="data.related ? 'Relacionado' : 'No relacionado'">
                                            </i>
                                        </template>
                                        <template v-if="hasPermission('relationship voucher subtypes')">
                                            <ConfirmPopup></ConfirmPopup>
                                            <button v-tooltip="data.related ? 'Quitar relación' : 'Relacionar'"><i class="pi text-blue-300"
                                                    :class="{ 'pi-plus-circle text-green-500': !data.related, 'pi-minus-circle text-red-400': data.related }"
                                                    @click="relateButton($event, data, !data.related, 'expense')"></i></button>
                                        </template>
                                        <template v-if="hasPermission('view users') && data.related">
                                            <i class="pi pi-id-card text-cyan-500 text-2xl top-1 relative"
                                                v-tooltip="`Usuario: ${data.relatedData.userRelated.surname} ${data.relatedData.userRelated.name} \n Fecha: ${data.relatedData.related_at}`"></i>
                                        </template>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </OverlayPanel>

                <OverlayPanel ref="suppliersPanel" appendTo="body" :dismissable="false" @hide="handleHide">
                    <div @mousedown.stop="onContentMouseDown">
                        <DataTable v-model:filters="suppliersFilters" :value="suppliersArray" paginator :rows="5" dataKey="id" filterDisplay="menu"
                            :globalFilterFields="['name']">
                            <template #empty>
                                <div class="text-center text-lg text-red-500">
                                    Sin proveedores cargados
                                </div>
                            </template>
                            <Column field="businessName" header="Proveedor" style="min-width: 12rem" class="uppercase" sortable>
                                <template #body="{ data }">
                                    <span :class="{ 'text-red-500': data.status === 'INACTIVO' || data.status === 0 }">
                                        {{ data.businessName }}
                                    </span>
                                </template>
                                <template #filter="{ filterModel, filterCallback }">
                                    <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="businessName"
                                        autocomplete="off" class="p-column-filter" placeholder="Buscar por proveedor" />
                                </template>
                            </Column>
                            <Column field="related" header="RELACIONADO" dataType="boolean" class="action-column text-center"
                                headerClass="min-w-28 w-28">
                                <template #body="{ data }">
                                    <div class="space-x-4">
                                        <template v-if="!hasPermission('relationship voucher subtypes')">
                                            <i class="pi top-1 relative"
                                                :class="{ 'pi-check-circle text-green-500': data.related, 'pi-times-circle text-red-400': !data.related }"
                                                v-tooltip="data.related ? 'Relacionado' : 'No relacionado'">
                                            </i>
                                        </template>
                                        <template v-if="hasPermission('relationship voucher subtypes')">
                                            <ConfirmPopup></ConfirmPopup>
                                            <button v-tooltip="data.related ? 'Quitar relación' : 'Relacionar'"><i class="pi text-blue-300"
                                                    :class="{ 'pi-plus-circle text-green-500': !data.related, 'pi-minus-circle text-red-400': data.related }"
                                                    @click="relateButton($event, data, !data.related, 'supplier')"></i></button>
                                        </template>
                                        <template v-if="hasPermission('view users') && data.related">
                                            <i class="pi pi-id-card text-cyan-500 text-2xl top-1 relative"
                                                v-tooltip="`Usuario: ${data.relatedData.userRelated.surname} ${data.relatedData.userRelated.name} \n Fecha: ${data.relatedData.related_at}`"></i>
                                        </template>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </OverlayPanel>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>