<script setup>
import { onMounted, ref, defineExpose } from 'vue';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { usePermissions } from '@/composables/permissions';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { percentNumber, currencyNumber, dateFormat } from "@/utils/formatterFunctions";
import InputError from '@/Components/InputError.vue';

const { hasPermission } = usePermissions();
const toast = useToast();
const categoriesArray = ref([]);
const originalCategoriesArray = ref([]);
const newRow = ref([]);
const editing = ref(false);
const editingRows = ref([]);
const rules = 'Debe completar el campo'
const expandedRows = ref([]);
const confirm = useConfirm();

const fetchIncomeTaxWithholdings = async () => {
    try {
        const response = await fetch('/income-tax-witholdings');

        if (!response.ok) {
            throw new Error('Error al obtener datos de impuestos a las ganancias');
        }

        const data = await response.json();
        categoryIncomeTaxWithholdings(data.categories, data.incomeTaxWithholdings, data.incomeTaxWithholdingsScales);
    } catch (error) {
        console.error(error);
    }
}

const categoryIncomeTaxWithholdings = (categories, incomeTaxWithholdings, incomeTaxWithholdingsScales) => {
    categoriesArray.value = [];

    categories.map((category, index) => {
        const tax = incomeTaxWithholdings
            .filter(tax => tax.idCat === category.id)
            .map(t => ({
                ...t,
                rate: parseFloat(t.rate),
                minAmount: parseFloat(t.minAmount),
                fixedAmount: parseFloat(t.fixedAmount),
                categoryIndex: index
            }));

        const taxScale = incomeTaxWithholdingsScales
            .filter(tax => tax.idCat === category.id)
            .map(t => ({
                ...t,
                rate: parseFloat(t.rate),
                minAmount: parseFloat(t.minAmount),
                maxAmount: parseFloat(t.maxAmount),
                fixedAmount: parseFloat(t.fixedAmount),
                categoryIndex: index
            }));

        const data = {
            ...category,
            categoryIndex: index,
            incomeTax: tax.length > 0 ? tax : taxScale,
            scale: tax.length > 0 ? 0 : 1,
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

/* Add new income tax withholdings */
const addNewIncomeTaxWithholding = (data) => {
    console.log(data);
    console.log(categoriesArray.value[data.categoryIndex]);
    if (data.scale === 0 && data.incomeTax.length > 0) {
        confirm.require({
            message: 'Ya posee una retención ¿Desea aplicar escala?',
            rejectClass: 'bg-red-500 text-white hover:bg-red-600',
            accept: () => {
                // data.scale = 1;
                onRowExpand(data);
                originalCategoriesArray.value = [...categoriesArray.value[data.categoryIndex].incomeTax];

                console.log(originalCategoriesArray.value);
            },
            reject: () => {
                // data.scale = 0;
            },
        });
    }

}
/* End add new income tax withholdings */

/* Editing income tax withholdings  */
const onRowEditInitIncomeTaxWithholding = (event) => {
    originalCategoriesArray.value = [...categoriesArray.value[event.data.categoryIndex].incomeTax];
    editingRows.value = [event.data];
}

const onRowEditCancelIncomeTaxWithholding = (event) => {
    categoriesArray.value[event.data.categoryIndex].incomeTax = [...originalCategoriesArray.value];
    editing.value = false;
    newRow.value = [];
    editingRows.value = [];
};

const onRowEditSaveIncomeTaxWithholding = (event) => {
}
/* End editing income tax withholdings */

onMounted(() => {
    fetchIncomeTaxWithholdings();
});

defineExpose({ fetchIncomeTaxWithholdings });
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
        <Column expander style="width: 1%" v-if="hasPermission('view bank accounts')" />
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
                <Badge :value="data.incomeTax.length" size="large" :severity="data.incomeTax.length === 0 ? 'danger' : 'success'" class="rounded-full"
                    v-tooltip="data.scale === 1 ? 'Sobre escala' : 'Sin escala'" @click="onRowExpand(data)"></Badge>
            </template>
        </Column>
        <Column header="Acciones" style="width: 5%; min-width: 8rem;">
            <template #body="{ data }">
                <div class="text-center">
                    <template v-if="hasPermission('view income tax withholdings') && hasPermission('create income tax withholdings')">
                        <ConfirmPopup></ConfirmPopup>
                        <button v-tooltip="'Agregar retención'"><i class="pi pi-plus-circle text-green-500 text-2xl"
                                @click="addNewIncomeTaxWithholding(data)"></i></button>
                    </template>
                </div>
            </template>
        </Column>
        <template #expansion="{ data }">
            <Divider v-if="data.incomeTax.some(tax => tax.maxAmount)" align="center" type="solid" class="text-lg text-primary-700 !m-0 !mb-2">
                <b>Sobre escala</b>
            </Divider>
            <DataTable v-model:editingRows="editingRows" :value="data.incomeTax" editMode="row" class="data-table-expanded"
                @row-edit-init="onRowEditInitIncomeTaxWithholding($event)" @row-edit-save="onRowEditSaveIncomeTaxWithholding"
                @row-edit-cancel="onRowEditCancelIncomeTaxWithholding($event)">
                {{ data.maxAmount }}
                <Column field="rate" header="Tasa">
                    <template #body="{ data }">
                        {{ percentNumber(data.rate) }}
                    </template>
                    <template #editor="{ data, field }">
                        <FloatLabel>
                            <!-- <span class="relative">
                                <i class="pi pi-percentage text-xs absolute top-2/4 -mt-2 left-3 text-surface-400 dark:text-surface-600" />
                                <InputText :class="'uppercase'" v-model="data[field]" name="name" autocomplete="off" class="pl-7"
                                    :invalid="!data[field] || data[field].trim() === ''" placeholder="Nombre" style="width: 100%;" max="100" /> 
                            </span> -->
                            <InputNumber v-model="data[field]" id="rate" class="w-full" autocomplete="off" inputId="percent" prefix="%" :max="100"
                                :invalid="!data[field] || data[field] === 0" />
                            <label for="rate">Tasa</label>
                        </FloatLabel>
                        <InputError :message="!data[field] || data[field] === 0 ? rules : ''" />
                    </template>
                </Column>
                <Column field="minAmount" header="Monto mínimo">
                    <template #body="{ data }">
                        {{ currencyNumber(data.minAmount) }}
                    </template>
                </Column>
                <Column v-if="data.incomeTax.some(tax => tax.maxAmount)" field="maxAmount" header="Monto máximo">
                    <template #body="{ data }">
                        {{ currencyNumber(data.maxAmount) }}
                    </template>
                </Column>
                <Column field="fixedAmount" header="Monto fijo">
                    <template #body="{ data }">
                        {{ currencyNumber(data.fixedAmount) }}
                    </template>
                </Column>
                <Column field="startAt" header="F. inicio">
                    <template #body="{ data }">
                        {{ dateFormat(data.startAt) }}
                    </template>
                </Column>
                <Column field="endAt" header="F. fin">
                    <template #body="{ data }">
                        {{ dateFormat(data.endAt) }}
                    </template>
                </Column>
                <Column header="Acciones" :rowEditor="true" style="width: 5%; min-width: 8rem;">
                    <template #body="{ editorInitCallback, data }">
                        <div class="space-x-4 flex pl-6">
                            <template v-if="hasPermission('edit income tax withholdings')">
                                <button v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                        @click="disabledEditButtons(editorInitCallback, $event)"></i></button>
                            </template>
                            <template v-if="hasPermission('view users')">
                                <button v-tooltip="'+Info'"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                        @click="info('bankAccounts', data, data.idBankAccount)"></i></button>
                            </template>
                        </div>
                    </template>
                    <template #editor="{ data, editorSaveCallback, editorCancelCallback }">
                        <div class="space-x-4 flex pl-7">
                            <ConfirmPopup></ConfirmPopup>
                            <button><i class="pi pi-check text-primary-500 text-lg font-extrabold"
                                    @click="validateBankAccount($event, editorSaveCallback, data)"></i></button>
                            <button><i class="pi pi-times text-red-500 text-lg font-extrabold"
                                    @click="enabledEditButtons(editorCancelCallback, $event, data)"></i></button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </template>
    </DataTable>
</template>
