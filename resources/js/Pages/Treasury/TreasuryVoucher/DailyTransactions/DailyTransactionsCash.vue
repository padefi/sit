<script setup>
import { ref, onMounted } from 'vue';
import { currencyNumber, dateFormat, invoiceNumberFormat } from "@/utils/formatterFunctions";

const dailyCashInTransactions = ref([]);
const dailyCashOutTransactions = ref([]);
const totalCashIn = ref(0);
const totalCashOut = ref(0);
const previousCash = ref(0);
const totalCash = ref(0);
const loading = ref(true);

const dailyCashData = (previousCashIn, previousCashOut, cashIn, cashOut) => {
    dailyCashInTransactions.value = cashIn;
    dailyCashOutTransactions.value = cashOut;
    totalCashIn.value = cashIn.reduce((acc, curr) => acc + curr.treasuryVoucher.totalAmount, 0);
    totalCashOut.value = cashOut.reduce((acc, curr) => acc + curr.treasuryVoucher.totalAmount, 0);
    previousCash.value = previousCashIn - previousCashOut;
    totalCash.value = previousCash.value + totalCashIn.value - totalCashOut.value;
    loading.value = false;
}

defineExpose({ loading, totalCashIn, totalCashOut, previousCash, totalCash, dailyCashData });
</script>
<style>
.transaction-table {
    th:nth-child(2) div {
        display: contents;
        text-align: center !important;
    }
}
</style>
<template>
    <div class="grid w-full justify-center font-bold text-xl mt-2">
        <span :class="{ 'text-neutral-950': previousCash >= 0, 'text-red-500': previousCash < 0 }">Saldo Anterior: {{ currencyNumber(previousCash) }}</span>
    </div>

    <Divider />

    <div class="grid w-full justify-center font-bold my-2">
        <span class="text-lg font-bold text-neutral-950">Ingresos</span>
    </div>

    <DataTable :value="dailyCashInTransactions" class="transaction-table" size="small" :loading="loading">
        <template #empty>
            <template v-if="!loading">
                <div class="flex justify-center items-center text-red-500">
                    Sin movimientos
                </div>
            </template>
        </template>
        <template #loading>
            <ProgressSpinner class="!w-7 !h-7 top-5" />
        </template>
        <Column field="cuit" header="Cuit" class="w-1/3">
            <template #body="{ data }">
                {{ data.treasuryVoucher.supplier.cuit }}
            </template>
        </Column>
        <Column field="businessName" header="Proveedor" class="w-1/3 text-center">
            <template #body="{ data }">
                {{ data.treasuryVoucher.supplier.businessName }}
            </template>
        </Column>
        <Column field="totalAmount" header="Importe" class="w-1/3 text-right">
            <template #body="{ data }">
                {{ currencyNumber(data.treasuryVoucher.totalAmount) }}
            </template>
        </Column>
    </DataTable>

    <div class="grid w-full justify-center font-bold relative top-2.5">
        <span>Total Ingresos: {{ currencyNumber(totalCashIn) }}</span>
    </div>

    <Divider class="my-5" />

    <div class="grid w-full justify-center font-bold my-2">
        <span class="text-lg font-bold text-neutral-950">Egresos</span>
    </div>

    <DataTable :value="dailyCashOutTransactions" class="transaction-table" size="small" :loading="loading">
        <template #empty>
            <template v-if="!loading">
                <div class="flex justify-center items-center text-red-500">
                    Sin movimientos
                </div>
            </template>
        </template>
        <template #loading>
            <ProgressSpinner class="!w-7 !h-7 top-5" />
        </template>
        <Column field="cuit" header="Cuit" class="w-1/3">
            <template #body="{ data }">
                {{ data.treasuryVoucher.supplier.cuit }}
            </template>
        </Column>
        <Column field="businessName" header="Proveedor" class="w-1/3 text-center ">
            <template #body="{ data }">
                {{ data.treasuryVoucher.supplier.businessName }}
            </template>
        </Column>
        <Column field="totalAmount" header="Importe" class="w-1/3 text-right">
            <template #body="{ data }">
                {{ currencyNumber(data.treasuryVoucher.totalAmount) }}
            </template>
        </Column>
    </DataTable>

    <div class="grid w-full justify-center font-bold relative top-2.5">
        <span>Total Egresos: {{ currencyNumber(totalCashOut) }}</span>
    </div>

    <Divider />

    <div class="grid w-full justify-center font-bold text-xl mt-2">
        <span :class="{ 'text-neutral-950': totalCash >= 0, 'text-red-500': totalCash < 0 }">Saldo Actual: {{ currencyNumber(totalCash) }}</span>
    </div>
</template>