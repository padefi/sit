<script setup>
import { ref, onMounted, inject, watch } from "vue";
import { currencyNumber } from "@/utils/formatterFunctions";
import InputError from '@/Components/InputError.vue';

const treasuryVouchersArray = ref([]);
const dialogRef = inject("dialogRef");

onMounted(() => {
    treasuryVouchersArray.value = dialogRef.value.data.vouchers;
    console.log(treasuryVouchersArray.value);
});
</script>
<template>
    <DataTable :value="treasuryVouchersArray" scrollable scrollHeight="30vh" dataKey="id" filterDisplay="menu" :pt="{
        table: { style: 'min-width: 50rem' }, tbody: { class: 'thin-td' }, wrapper: { class: 'datatable-scrollbar' },
        paginator: {
            root: { class: 'p-paginator-custom' },
            current: { class: 'p-paginator-current' },
        }
    }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table uppercase">
        <template #empty>
            Sin comprobantes cargados
        </template>
        <Column field="businessName" header="Proveedor">
            <template #body="{ data }">
                {{ data.businessName }}
            </template>
        </Column>
        <Column field="amount" header="Importe">
            <template #body="{ data }">
                {{ currencyNumber(data.amount) }}
            </template>
        </Column>
        <Column field="paymentDate" header="F. pago">
            <template #body="{ data, field }">
                <FloatLabel>
                    <Calendar v-model="data[field]" placeholder="DD/MM/AAAA" showButtonBar id="startAt" class="w-full"
                        :class="data[field] !== null ? 'filled' : ''" :invalid="data[field] === null" :maxDate="data.endAt" />
                    <label for="startAt">F. inicio</label>
                </FloatLabel>
                <InputError :message="data[field] === null ? rules : ''" />
            </template>
        </Column>
    </DataTable>
</template>