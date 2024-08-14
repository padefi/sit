<script setup>
import { inject, onMounted, ref } from "vue";
import { usePermissions } from '@/composables/permissions';
import { currencyNumber, dateFormat, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { compareDates } from "@/utils/validateFunctions";
import InputError from '@/Components/InputError.vue';

const { hasPermission } = usePermissions();
const treasuryVouchersArray = ref([]);
const originalTreasuryVouchersArray = ref([]);
const editingRows = ref([]);
const rules = 'Debe completar el campo'

const dialogRef = inject("dialogRef");

const disabledEditButtons = (callback, event, data) => {
    // editing.value = true;
    callback(event);
};

const validateAmount = (event, saveCallback, data) => {
    saveCallback(event, data);
}

const enabledEditButtons = (callback, event) => {
    /* editing.value = false;
    editingRows.value = []; */
    callback(event);
}

const onRowEditInit = (event) => {
    originalTreasuryVouchersArray.value = [...treasuryVouchersArray.value];
    editingRows.value = [event.data];
}

const onRowEditCancel = (event) => {
    treasuryVouchersArray.value = [...originalTreasuryVouchersArray.value];
    /* editing.value = false;
    newRow.value = []; */
    editingRows.value = [];
}

onMounted(async () => {
    try {
        const response = await fetch(`/vouchers/${dialogRef.value.data.voucher.id}`);

        if (!response.ok) {
            throw new Error('Error al obtener los comprobantes del proveedor');
        }

        const data = await response.json();
        data.vouchers.map(voucher => {
            voucher.checked = false;
        })

        treasuryVouchersArray.value = data.vouchers;
    } catch (error) {
        console.error(error);
    }
});
</script>
<template>
    <DataTable :value="treasuryVouchersArray" v-model:editingRows="editingRows" editMode="row" scrollable scrollHeight="70vh" dataKey="id"
        filterDisplay="menu" :pt="{
        table: { style: 'min-width: 50rem' },
        paginator: {
            root: { class: 'p-paginator-custom' },
            current: { class: 'p-paginator-current' },
        }
    }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table uppercase" @row-edit-init="onRowEditInit($event)"
        @row-edit-cancel="onRowEditCancel($event)">
        <template #empty>
            <div class="text-center text-lg text-red-500">
                Sin comprobantes cargados
            </div>
        </template>
        <Column header="Comprobante">
            <template #body="{ data }">
                {{ data.invoiceType.name }}
                <span class="font-bold">{{ data.invoiceTypeCode.name }}</span>
            </template>
        </Column>
        <Column header="Número">
            <template #body="{ data }">
                {{ invoiceNumberFormat(data.pointOfNumber, 5) }} - {{ invoiceNumberFormat(data.invoiceNumber, 8) }}
            </template>
        </Column>
        <Column header="F. vencimiento">
            <template #body="{ data }">
                <span :class="{ 'text-red-500': compareDates(data.invoicePaymentDate, '', 'before') }">
                    {{ dateFormat(data.invoicePaymentDate) }}
                </span>
            </template>
        </Column>
        <Column header="Cond. Pago">
            <template #body="{ data }">
                {{ data.payCondition.name }}
            </template>
        </Column>
        <Column field="totalAmount" header="Importe">
            <template #body="{ data }">
                {{ currencyNumber(data.totalAmount) }}
            </template>
            <template #editor="{ data, field }">
                <FloatLabel>
                    <InputNumber v-model="data[field]" placeholder="$ 0,00" inputId="totalAmount" mode="currency" currency="ARS" locale="es-AR"
                        id="totalAmount" class="w-full" :class="data[field] !== null ? 'filled' : ''" :min="0" :max="99999999" :minFractionDigits="2"
                        :invalid="data[field] === null" />
                    <label for="totalAmount">Importe</label>
                </FloatLabel>
                <InputError :message="data[field] === null ? rules : ''" />
            </template>
        </Column>
        <Column header="Saldo">
            <template #body="{ data }">
                {{ currencyNumber(data.totalAmount) }}
            </template>
        </Column>
        <Column field="checked" header="Acciones" style="width: 5%; min-width: 1rem;" bodyStyle="text-align:center">
            <template #body="{ editorInitCallback, data }">
                <div class="space-x-4 flex pl-7">
                    <Checkbox v-model="data.checked" binary />
                    <template v-if="data.checked">
                        <button class="bottom-[0.2rem] relative" v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                @click="disabledEditButtons(editorInitCallback, $event)"></i></button>
                    </template>
                </div>
            </template>
            <template #editor="{ data, editorSaveCallback, editorCancelCallback }">
                <div class="space-x-4 flex pl-7">
                    <ConfirmPopup></ConfirmPopup>
                    <button><i class="pi pi-check text-primary-500 text-lg font-extrabold"
                            @click="validateAmount($event, editorSaveCallback, data)"></i></button>
                    <button><i class="pi pi-times text-red-500 text-lg font-extrabold"
                            @click="enabledEditButtons(editorCancelCallback, $event, data)"></i></button>
                </div>
            </template>
        </Column>
    </DataTable>
</template>