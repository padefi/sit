<script setup>
import { onMounted, ref, defineExpose } from 'vue';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { usePermissions } from '@/composables/permissions';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { useForm } from '@inertiajs/vue3';
import { percentNumber, addDate, currencyNumber, dateFormat } from "@/utils/formatterFunctions";
import { v4 as uuidv4 } from 'uuid';
import InputError from '@/Components/InputError.vue';

const { hasPermission, hasPermissionColumn } = usePermissions();
const toast = useToast();
const categoriesArray = ref([]);
const originalCategoriesArray = ref([]);
const newRow = ref([]);
const editing = ref(false);
const editingRows = ref([]);
const rules = 'Debe completar el campo'
const expandedRows = ref([]);
const confirm = useConfirm();

const fetchSocialSecurityWithholdings = async () => {
    try {
        const response = await fetch('/socialSecurityTaxWithholdings');

        if (!response.ok) {
            throw new Error('Error al obtener datos de suss');
        }

        const data = await response.json();
        categorySecuritySocialTaxWithholdings(data.categories, data.socialSecurityTaxWithholdings);
    } catch (error) {
        console.error(error);
    }
}

const categorySecuritySocialTaxWithholdings = (categories, socialSecurityTaxWithholdings) => {
    categoriesArray.value = [];

    categories.map((category, index) => {
        const tax = socialSecurityTaxWithholdings
            .filter(tax => tax.idCat === category.id)
            .map(t => {
                const startAt = addDate(t.startAt, 1);
                const endAt = addDate(t.endAt, 1);

                return {
                    ...t,
                    rate: parseFloat(t.rate),
                    minAmount: parseFloat(t.minAmount),
                    fixedAmount: parseFloat(t.fixedAmount),
                    startAt,
                    endAt,
                    categoryIndex: index
                }
            });

        const data = {
            ...category,
            categoryIndex: index,
            socialSecurityTax: tax,
        };

        categoriesArray.value.push(data);
    });
}

const filters = ref({
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

const onRowExpand = (data) => {
    const originalExpandedRows = { ...expandedRows.value };
    const newExpandedRows = categoriesArray.value.reduce((acc) => (acc[data.id] = true) && acc, {});
    expandedRows.value = { ...originalExpandedRows, ...newExpandedRows };
}

const onRowCollapse = (data) => {
    delete expandedRows.value[data.id];
}

const disabledEditButtons = (callback, event) => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de modificar una retención.',
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

/* Add new social security tax withholdings */
const addNewSocialSecurityTaxWithholding = async (data) => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de modificar una retención.',
            life: 3000,
        });

        return;
    }

    const originalExpandedRows = { ...expandedRows.value };
    const newExpandedRows = categoriesArray.value.reduce((acc) => (acc[data.id] = true) && acc, {});
    expandedRows.value = { ...originalExpandedRows, ...newExpandedRows };
    originalCategoriesArray.value = [...categoriesArray.value[data.categoryIndex].socialSecurityTax];

    const newSocialSecurityTax = {
        id: uuidv4(),
        idCat: data.id,
        categoryIndex: data.categoryIndex,
        rate: newRow.value?.rate,
        minAmount: newRow.value?.minAmount,
        fixedAmount: newRow.value?.fixedAmount,
        startAt: newRow.value?.startAt,
        endAt: newRow.value?.endAt,
        condition: 'newSocialSecurityTaxWithholding',
    };

    categoriesArray.value[data.categoryIndex].socialSecurityTax.unshift(newSocialSecurityTax);
    editing.value = true;
    editingRows.value = [newSocialSecurityTax];
    onRowExpand(data);
}
/* End add new social security tax withholdings */

/* Editing social security tax withholdings  */
const onRowEditInitSocialSecurityTaxWithholding = (event) => {
    originalCategoriesArray.value = [...categoriesArray.value[event.data.categoryIndex].socialSecurityTax];
    editingRows.value = [event.data];
}

const onRowEditCancelSocialSecurityTaxWithholding = (event) => {
    onRowCollapse(categoriesArray.value[event.data.categoryIndex]);

    setTimeout(() => {
        onRowExpand(categoriesArray.value[event.data.categoryIndex]);
    }, 100);

    categoriesArray.value[event.data.categoryIndex].socialSecurityTax = [...originalCategoriesArray.value];
    editing.value = false;
    newRow.value = [];
    editingRows.value = [];
};

const validateSocialSecurityTaxWithholding = (event, saveCallback, data) => {
    if (!data.rate || data.minAmount === null || data.fixedAmount === null || !data.startAt || !data.endAt) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });

        return;
    }

    if (data.startAt > data.endAt) {
        toast.add({
            severity: 'error',
            detail: 'La fecha desde no puede ser mayor a la fecha hasta.',
            life: 3000,
        });

        return;
    }

    if (data.condition === 'newSocialSecurityTaxWithholding') {
        confirm.require({
            target: event.currentTarget,
            message: '¿Está seguro de agregar la retención?',
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
        message: '¿Está seguro de modificar la retención?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            saveCallback(event);
        },
    });
}

const onRowEditSaveSocialSecurityTaxWithholding = (event) => {
    let { newData, index } = event;

    const form = useForm({
        idCat: newData.idCat,
        rate: newData.rate,
        minAmount: newData.minAmount,
        fixedAmount: newData.fixedAmount,
        startAt: newData.startAt,
        endAt: newData.endAt,
    });

    if (newData.condition === 'newSocialSecurityTaxWithholding') {
        const routeUrl = "socialSecurityTaxWithholdings.store";

        form.post(route(routeUrl, newData.id), {
            onSuccess: (result) => {
                const data = result.props.flash.info.socialSecurityTaxWithholding;
                editing.value = false;
                newData.condition = 'editSocialSecurityTaxWithholding';
                newData.id = data.id;
                newRow.value = [];
            },
            onError: () => {
                categoriesArray.value[event.data.categoryIndex].socialSecurityTax = [...originalCategoriesArray.value];
                editing.value = false;
                addNewSocialSecurityTaxWithholding(categoriesArray.value[event.data.categoryIndex]);
            }
        });

        return;
    }

    const routeUrl = "socialSecurityTaxWithholdings.update";

    form.put(route(routeUrl, newData.id), {
        onSuccess: () => {
            editing.value = false;
            categoriesArray.value[newData.categoryIndex].socialSecurityTax[index] = newData;
        },
        onError: () => {
            editing.value = true;
            editingRows.value = [event.data];
        }
    });
}
/* End editing social security tax withholdings */
onMounted(() => {
    fetchSocialSecurityWithholdings();

    Echo.channel('socialSecurityTaxWithholdings')
        .listen('Treasury\\Taxes\\SocialSecurityTaxWithholdingEvent', (e) => {
            const indexCategory = categoriesArray.value.findIndex(category => category.id === e.socialSecurityTaxWithholding.category.id);

            const socialSecurityTaxEventDataStructure = (indexCategory, socialSecurityTaxWithholding) => {
                const startAt = addDate(socialSecurityTaxWithholding.startAt, 1);
                const endAt = addDate(socialSecurityTaxWithholding.endAt, 1);

                return {
                    ...socialSecurityTaxWithholding,
                    rate: parseFloat(socialSecurityTaxWithholding.rate),
                    minAmount: parseFloat(socialSecurityTaxWithholding.minAmount),
                    fixedAmount: parseFloat(socialSecurityTaxWithholding.fixedAmount),
                    startAt,
                    endAt,
                    categoryIndex: indexCategory,
                }
            }

            if (indexCategory !== -1) {
                if (e.type === 'create') {
                    setTimeout(() => {
                        if (!categoriesArray.value[indexCategory].socialSecurityTax.some(tax => tax.id === e.socialSecurityTaxWithholding.id)) {
                            categoriesArray.value[indexCategory].socialSecurityTax.unshift(socialSecurityTaxEventDataStructure(indexCategory, e.socialSecurityTaxWithholding));
                        }
                    }, 500);
                } else if (e.type === 'update') {
                    const indexSocialSecurityTax = categoriesArray.value[indexCategory].socialSecurityTax.findIndex(tax => tax.id === e.socialSecurityTaxWithholding.id);

                    if (indexSocialSecurityTax !== -1) {
                        categoriesArray.value[indexCategory].socialSecurityTax[indexSocialSecurityTax] = socialSecurityTaxEventDataStructure(indexCategory, e.socialSecurityTaxWithholding);
                    }
                }
            }

        });
});

/*  */
import infoModal from '@/Components/InfoModal.vue';
import { useDialog } from 'primevue/usedialog';

const dialog = useDialog();

const info = (data) => {
    axios.get(`/socialSecurityTaxWithholdings/${data.id}/info`)
        .then((response) => {
            dialog.open(infoModal, {
                props: {
                    header: `Información de la retención de la categoría ${data.category.toUpperCase()}`,
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

defineExpose({ fetchSocialSecurityWithholdings });
</script>
<style>
div[data-pc-section="columnfilter"] {
    margin-left: 0 !important;
}
</style>

<template>
    <DataTable :value="categoriesArray" v-model:filters="filters" dataKey="id" filterDisplay="menu" :globalFilterFields="['name']"
        v-model:expandedRows="expandedRows" scrollable scrollHeight="60vh" :pt="{
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
                Sin rubros cargados
            </div>
        </template>
        <Column expander class="min-w-2 w-2 !px-0" v-if="hasPermission('view social security tax withholdings')" />
        <Column field="name" header="Rubro">
            <template #body="{ data }">
                {{ data.name }}
            </template>
            <template #filter="{ filterModel, filterCallback }">
                <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="name" autocomplete="off" class="p-column-filter"
                    placeholder="Buscar por rubro" />
            </template>
        </Column>
        <Column header="Cant.">
            <template #body="{ data }">
                <Badge :value="data.socialSecurityTax.length" size="large" :severity="data.socialSecurityTax.length === 0 ? 'danger' : 'success'"
                    class="rounded-full" @click="onRowExpand(data)"></Badge>
            </template>
        </Column>
        <Column header="Acciones" class="action-column text-center" headerClass="min-w-32 w-32"
            v-if="hasPermissionColumn(['create social security tax withholdings', 'view users'])">
            <template #body="{ data }">
                <div class="text-center">
                    <template
                        v-if="hasPermission('view social security tax withholdings') && hasPermission('create social security tax withholdings') && data.socialSecurityTax.length === 0">
                        <ConfirmPopup></ConfirmPopup>
                        <button v-tooltip="'Agregar retención'"><i class="pi pi-plus-circle text-green-500 text-2xl"
                                @click="addNewSocialSecurityTaxWithholding(data)"></i></button>
                    </template>
                </div>
            </template>
        </Column>
        <template #expansion="{ data }">
            <Divider v-if="data.socialSecurityTax.some(tax => tax.maxAmount)" align="center" type="solid" class="text-lg text-primary-700 !m-0 !mb-2">
                <b>Sobre escala</b>
            </Divider>
            <DataTable v-model:editingRows="editingRows" :value="data.socialSecurityTax" editMode="row" class="data-table-expanded"
                @row-edit-init="onRowEditInitSocialSecurityTaxWithholding($event)" @row-edit-save="onRowEditSaveSocialSecurityTaxWithholding"
                @row-edit-cancel="onRowEditCancelSocialSecurityTaxWithholding($event)">
                <template #empty>
                    <div class="text-center text-lg text-red-500">
                        Sin retenciones cargadas
                    </div>
                </template>
                <Column field="rate" header="Tasa">
                    <template #body="{ data }">
                        {{ percentNumber(data.rate) }}
                    </template>
                    <template #editor="{ data, field }">
                        <FloatLabel>
                            <InputNumber v-model="data[field]" placeholder="% 0,00" inputId="rate" prefix="%" id="rate"
                                class="w-full :not(:focus)::placeholder:text-transparent" :class="data[field] !== null ? 'filled' : ''" :min="0.01"
                                :max="100" :minFractionDigits="2" :invalid="data[field] === null || data[field] === 0"
                                :pt="{ input: { root: { autocomplete: 'off' } } }" />
                            <label for="rate">Tasa</label>
                        </FloatLabel>
                        <InputError :message="data[field] === null || data[field] === 0 ? rules : ''" />
                    </template>
                </Column>
                <Column field="minAmount" header="Monto mínimo">
                    <template #body="{ data }">
                        {{ currencyNumber(data.minAmount) }}
                    </template>
                    <template #editor="{ data, field }">
                        <FloatLabel>
                            <InputNumber v-model="data[field]" placeholder="$ 0,00" inputId="minAmount" mode="currency" currency="ARS" locale="es-AR"
                                id="minAmount" class="w-full" :class="data[field] !== null ? 'filled' : ''" :min="0" :max="99999999"
                                :minFractionDigits="2" :invalid="data[field] === null" :pt="{ input: { root: { autocomplete: 'off' } } }" />
                            <label for="minAmount">Monto mínino</label>
                        </FloatLabel>
                        <InputError :message="data[field] === null ? rules : ''" />
                    </template>
                </Column>
                <Column field="fixedAmount" header="Monto fijo">
                    <template #body="{ data }">
                        {{ currencyNumber(data.fixedAmount) }}
                    </template>
                    <template #editor="{ data, field }">
                        <FloatLabel>
                            <InputNumber v-model="data[field]" placeholder="$ 0,00" inputId="fixedAmount" mode="currency" currency="ARS"
                                locale="es-AR" id="fixedAmount" class="w-full" :class="data[field] !== null ? 'filled' : ''" :min="0" :max="99999999"
                                :minFractionDigits="2" :invalid="data[field] === null" :pt="{ input: { root: { autocomplete: 'off' } } }" />
                            <label for="fixedAmount">Monto fijo</label>
                        </FloatLabel>
                        <InputError :message="data[field] === null ? rules : ''" />
                    </template>
                </Column>
                <Column field="startAt" header="F. inicio">
                    <template #body="{ data }">
                        {{ dateFormat(data.startAt) }}
                    </template>
                    <template #editor="{ data, field }">
                        <FloatLabel>
                            <Calendar v-model="data[field]" placeholder="DD/MM/AAAA" showButtonBar id="startAt" class="w-full"
                                :class="data[field] !== null ? 'filled' : ''" :invalid="data[field] === null" :maxDate="data.endAt" />
                            <label for="startAt">F. inicio</label>
                        </FloatLabel>
                        <InputError :message="data[field] === null ? rules : ''" />
                    </template>
                </Column>
                <Column field="endAt" header="F. fin">
                    <template #body="{ data }">
                        {{ dateFormat(data.endAt) }}
                    </template>
                    <template #editor="{ data, field }">
                        <FloatLabel>
                            <Calendar v-model="data[field]" placeholder="DD/MM/AAAA" showButtonBar id="endAt" class="w-full"
                                :class="data[field] !== null ? 'filled' : ''" :invalid="data[field] === null" :minDate="data.startAt" />
                            <label for="endAt">F. fin</label>
                        </FloatLabel>
                        <InputError :message="data[field] === null ? rules : ''" />
                    </template>
                </Column>
                <Column header="Acciones" class="action-column text-center" headerClass="min-w-32 w-32"
                    v-if="hasPermissionColumn(['edit social security tax withholdings', 'view users'])">
                    <template #body="{ editorInitCallback, data }">
                        <div class="space-x-2">
                            <template v-if="hasPermission('edit social security tax withholdings')">
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
                                    @click="validateSocialSecurityTaxWithholding($event, editorSaveCallback, data)"></i></button>
                            <button><i class="pi pi-times text-red-500 text-lg font-extrabold"
                                    @click="enabledEditButtons(editorCancelCallback, $event, data)"></i></button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </template>
    </DataTable>
</template>
