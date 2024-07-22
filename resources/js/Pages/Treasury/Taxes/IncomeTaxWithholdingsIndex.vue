<script setup>
import { onMounted, ref, defineExpose } from 'vue';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { usePermissions } from '@/composables/permissions';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { useForm } from '@inertiajs/vue3';
import { percentNumber, currencyNumber, dateFormat } from "@/utils/formatterFunctions";
import InputError from '@/Components/InputError.vue';
import { toastService } from '@/composables/toastService';

toastService();

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
        const response = await fetch('/incomeTaxWithholdings');

        if (!response.ok) {
            throw new Error('Error al obtener datos de impuestos a las ganancias');
        }

        const data = await response.json();
        categoryIncomeTaxWithholdings(data.categories, data.incomeTaxWithholdings, data.incomeTaxWithholdingScales);
    } catch (error) {
        console.error(error);
    }
}

const categoryIncomeTaxWithholdings = (categories, incomeTaxWithholdings, incomeTaxWithholdingScales) => {
    categoriesArray.value = [];

    categories.map((category, index) => {
        const tax = incomeTaxWithholdings
            .filter(tax => tax.idCat === category.id)
            .map(t => {
                const startAt = new Date(t.startAt);
                startAt.setDate(startAt.getDate() + 1);

                const endAt = new Date(t.endAt);
                endAt.setDate(endAt.getDate() + 1);

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

        const taxScale = incomeTaxWithholdingScales
            .filter(tax => tax.idCat === category.id)
            .map(t => {
                const startAt = new Date(t.startAt);
                startAt.setDate(startAt.getDate() + 1);

                const endAt = new Date(t.endAt);
                endAt.setDate(endAt.getDate() + 1);

                return {
                    ...t,
                    rate: parseFloat(t.rate),
                    minAmount: parseFloat(t.minAmount),
                    maxAmount: parseFloat(t.maxAmount),
                    fixedAmount: parseFloat(t.fixedAmount),
                    startAt,
                    endAt,
                    categoryIndex: index
                }
            });

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

/* Add new income tax withholdings */
const addNewIncomeTaxWithholding = async (data) => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de modificar una retención.',
            life: 3000,
        });

        return;
    }

    originalCategoriesArray.value = [...categoriesArray.value[data.categoryIndex].incomeTax];

    if (data.scale === 0 && data.incomeTax.length > 0) {
        try {
            await new Promise((resolve, reject) => {
                confirm.require({
                    message: 'Ya posee una retención ¿Desea aplicar escala?',
                    rejectClass: 'bg-red-500 text-white hover:bg-red-600',
                    accept: () => {
                        categoriesArray.value[data.categoryIndex].incomeTax.map(incomeTax => {
                            incomeTax.maxAmount = 0.001;
                        });

                        resolve();
                    },
                    reject: () => {
                        originalCategoriesArray.value = [];
                        reject(new Error());
                    },
                });
            });
        } catch (error) {
            return;
        };
    }

    const newIncomeTax = {
        id: crypto.randomUUID(),
        idCat: data.id,
        categoryIndex: data.categoryIndex,
        rate: newRow.value?.rate,
        minAmount: newRow.value?.minAmount,
        maxAmount: newRow.value?.maxAmount,
        fixedAmount: newRow.value?.fixedAmount,
        startAt: newRow.value?.startAt,
        endAt: newRow.value?.endAt,
        condition: 'newIncomeTaxWithholding',
    };

    categoriesArray.value[data.categoryIndex].incomeTax.unshift(newIncomeTax);
    editing.value = true;
    editingRows.value = [newIncomeTax];
    onRowExpand(data);

}
/* End add new income tax withholdings */

/* Editing income tax withholdings  */
const onRowEditInitIncomeTaxWithholding = (event) => {
    originalCategoriesArray.value = [...categoriesArray.value[event.data.categoryIndex].incomeTax];
    editingRows.value = [event.data];
}

const onRowEditCancelIncomeTaxWithholding = (event) => {
    if (categoriesArray.value[event.data.categoryIndex].scale === 0 && categoriesArray.value[event.data.categoryIndex].incomeTax.length > 1) {
        originalCategoriesArray.value.map(incomeTax => {
            delete incomeTax.maxAmount;
        });

        onRowCollapse(categoriesArray.value[event.data.categoryIndex]);

        setTimeout(() => {
            onRowExpand(categoriesArray.value[event.data.categoryIndex]);
        }, 100);
    }

    categoriesArray.value[event.data.categoryIndex].incomeTax = [...originalCategoriesArray.value];
    editing.value = false;
    newRow.value = [];
    editingRows.value = [];
};

const validateIncomeTaxWithholding = (event, saveCallback, data) => {
    if (!data.rate || data.minAmount === null || (!data.maxAmount && data.maxAmount === null) || data.fixedAmount === null || !data.startAt || !data.endAt) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });

        return;
    }

    if (data.minAmount > data.maxAmount) {
        toast.add({
            severity: 'error',
            detail: 'El monto mínimo no puede ser mayor al monto máximo.',
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

    if (data.condition === 'newIncomeTaxWithholding') {
        confirm.require({
            target: event.currentTarget,
            message: '¿Está seguro de agrear la retención?',
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

const onRowEditSaveIncomeTaxWithholding = (event) => {
    let { newData, index } = event;

    const form = useForm({
        idCat: newData.idCat,
        rate: newData.rate,
        minAmount: newData.minAmount,
        maxAmount: newData.maxAmount,
        fixedAmount: newData.fixedAmount,
        startAt: newData.startAt,
        endAt: newData.endAt,
    });

    if (newData.condition === 'newIncomeTaxWithholding') {
        const routeUrl = (categoriesArray.value[event.data.categoryIndex].scale === 0 && categoriesArray.value[event.data.categoryIndex].incomeTax.length > 1) ? "incomeTaxWithholdingScales.store" : "incomeTaxWithholdings.store";

        form.post(route(routeUrl, newData.id), {
            onSuccess: (result) => {
                const data = result.props.flash.info.incomeTaxWithholding;
                editing.value = false;
                newData.condition = 'editIncomeTaxWithholding';
                newData.id = data.id;
                newData.idCat = data.idCat;
                newData.rate = data.rate;
                newData.minAmount = data.minAmount;
                newData.maxAmount = data.maxAmount;
                newData.fixedAmount = data.fixedAmount;
                newData.startAt = data.startAt;
                newData.endAt = data.endAt;
                newRow.value = [];
            },
            onError: (error) => {
                Object.keys(error).forEach((key) => {
                    toast.add({
                        severity: 'error',
                        detail: error[key],
                        life: 3000,
                    });
                });

                editing.value = true;
                editingRows.value = [newData];
            }
        });

        return;
    }

    const routeUrl = categoriesArray.value[newData.categoryIndex].scale === 0 ? "incomeTaxWithholdings.update" : "incomeTaxWithholdingScales.update";
    editing.value = false;

    form.put(route(routeUrl, newData.id), {
        onSuccess: () => {
            editing.value = false;
            categoriesArray.value[newData.categoryIndex].incomeTax[index] = newData;
        },
        onError: (error) => {
            Object.keys(error).forEach((key) => {
                toast.add({
                    severity: 'error',
                    detail: error[key],
                    life: 3000,
                });
            });

            editing.value = true;
            editingRows.value = [newData];
        }
    });
}
/* End editing income tax withholdings */
onMounted(() => {
    fetchIncomeTaxWithholdings();

    Echo.channel('incomeTaxWithholdings')
        .listen('Treasury\\Taxes\\IncomeTaxWithholdingEvent', (e) => {
            const indexCategory = categoriesArray.value.findIndex(category => category.id === e.incomeTaxWithholding.category.id);

            const incomeTaxEventDataStructure = (indexCategory, incomeTaxWithholding) => {
                const startAt = new Date(incomeTaxWithholding.startAt);
                startAt.setDate(startAt.getDate() + 1);

                const endAt = new Date(incomeTaxWithholding.endAt);
                endAt.setDate(endAt.getDate() + 1);

                return {
                    ...incomeTaxWithholding,
                    rate: parseFloat(incomeTaxWithholding.rate),
                    minAmount: parseFloat(incomeTaxWithholding.minAmount),
                    fixedAmount: parseFloat(incomeTaxWithholding.fixedAmount),
                    startAt,
                    endAt,
                    categoryIndex: indexCategory,
                }
            }

            if (indexCategory !== -1) {
                if (e.type === 'create') {
                    setTimeout(() => {
                        /* if (!banksArray.value[indexBank].accounts.some(account => account.idBankAccount === e.bankAccount.id)) {
                            banksArray.value[indexBank].accounts.unshift(accountEventDataStructure(indexBank, e.bankAccount));
                        } */
                    }, 500);
                } else if (e.type === 'update') {
                    const indexIncomeTax = categoriesArray.value[indexCategory].incomeTax.findIndex(tax => tax.id === e.incomeTaxWithholding.id);

                    if (indexIncomeTax !== -1) {
                        categoriesArray.value[indexCategory].incomeTax[indexIncomeTax] = incomeTaxEventDataStructure(indexCategory, e.incomeTaxWithholding);
                    }
                }
            }

        });

    Echo.channel('incomeTaxWithholdingScales')
        .listen('Treasury\\Taxes\\IncomeTaxWithholdingScaleEvent', (e) => {
            const indexCategory = categoriesArray.value.findIndex(category => category.id === e.incomeTaxWithholdingScale.category.id);

            const incomeTaxEventDataStructure = (indexCategory, incomeTaxWithholdingScale) => {
                const startAt = new Date(incomeTaxWithholdingScale.startAt);
                startAt.setDate(startAt.getDate() + 1);

                const endAt = new Date(incomeTaxWithholdingScale.endAt);
                endAt.setDate(endAt.getDate() + 1);

                return {
                    ...incomeTaxWithholdingScale,
                    rate: parseFloat(incomeTaxWithholdingScale.rate),
                    minAmount: parseFloat(incomeTaxWithholdingScale.minAmount),
                    maxAmount: parseFloat(incomeTaxWithholdingScale.maxAmount),
                    fixedAmount: parseFloat(incomeTaxWithholdingScale.fixedAmount),
                    startAt,
                    endAt,
                    categoryIndex: indexCategory,
                }
            }

            if (indexCategory !== -1) {
                if (e.type === 'create') {
                    setTimeout(() => {
                        /* if (!banksArray.value[indexBank].accounts.some(account => account.idBankAccount === e.bankAccount.id)) {
                            banksArray.value[indexBank].accounts.unshift(accountEventDataStructure(indexBank, e.bankAccount));
                        } */
                    }, 500);
                } else if (e.type === 'update') {
                    const indexIncomeTax = categoriesArray.value[indexCategory].incomeTax.findIndex(tax => tax.id === e.incomeTaxWithholdingScale.id);

                    if (indexIncomeTax !== -1) {
                        categoriesArray.value[indexCategory].incomeTax[indexIncomeTax] = incomeTaxEventDataStructure(indexCategory, e.incomeTaxWithholdingScale);
                    }
                }
            }

        });
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
                    v-tooltip="data.scale === 1 && data.incomeTax.length > 0 ? 'Sobre escala' : 'Sin escala'" @click="onRowExpand(data)"></Badge>
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
                                :max="100" :minFractionDigits="2" :invalid="data[field] === null || data[field] === 0" />
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
                                :minFractionDigits="2" :invalid="data[field] === null" />
                            <label for="minAmount">Monto mínino</label>
                        </FloatLabel>
                        <InputError :message="data[field] === null ? rules : ''" />
                    </template>
                </Column>
                <Column v-if="data.incomeTax.some(tax => tax.maxAmount)" field="maxAmount" header="Monto máximo">
                    <template #body="{ data }">
                        {{ currencyNumber(data.maxAmount) }}
                    </template>
                    <template #editor="{ data, field }">
                        <FloatLabel>
                            <InputNumber v-model="data[field]" placeholder="$ 0,00" inputId="maxAmount" mode="currency" currency="ARS" locale="es-AR"
                                id="maxAmount" class="w-full" :class="data[field] !== null ? 'filled' : ''" :min="data.minAmount" :max="99999999"
                                :minFractionDigits="2" :invalid="data[field] === null" />
                            <label for="maxAmount">Monto máximo</label>
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
                                :minFractionDigits="2" :invalid="data[field] === null" />
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
                                    @click="validateIncomeTaxWithholding($event, editorSaveCallback, data)"></i></button>
                            <button><i class="pi pi-times text-red-500 text-lg font-extrabold"
                                    @click="enabledEditButtons(editorCancelCallback, $event, data)"></i></button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </template>
    </DataTable>
</template>
