<script setup>
import { ref, onMounted } from 'vue';
import { currencyNumber, dateFormat, invoiceNumberFormat } from "@/utils/formatterFunctions";

const dailyBankInTransactions = ref([]);
const dailyBankOutTransactions = ref([]);
const totalBankIn = ref(0);
const totalBankOut = ref(0);
const loading = ref(true);

const dailyBankData = (BankIn, BankOut) => {
    loading.value = true;
    dailyBankInTransactions.value = BankIn;
    dailyBankOutTransactions.value = BankOut;
    totalBankIn.value = BankIn.reduce((acc, curr) => acc + curr.treasuryVoucher.totalAmount, 0);
    totalBankOut.value = BankOut.reduce((acc, curr) => acc + curr.treasuryVoucher.totalAmount, 0);
    loading.value = false;
}

defineExpose({ loading, totalBankIn, totalBankOut, dailyBankData });
</script>
<template>
    <div class="grid w-full justify-center font-bold my-2">
        <span class="text-lg font-bold text-neutral-950">Ingresos</span>
    </div>

    <DataTable :value="dailyBankInTransactions" class="transaction-table" size="small" :loading="loading">
        <template #empty>
            <div class="flex justify-center items-center text-red-500">
                <template v-if="!loading">
                    Sin movimientos
                </template>
            </div>
        </template>
        <template #loading>
            <ProgressSpinner class="!w-7 !h-7 top-5" />
        </template>
        <Column field="cuit" header="Cuit" class="w-1/12">
            <template #body="{ data }">
                {{ data.treasuryVoucher.supplier.cuit }}
            </template>
        </Column>
        <Column field="businessName" header="Proveedor" class="w-2/6">
            <template #body="{ data }">
                {{ data.treasuryVoucher.supplier.businessName }}
            </template>
        </Column>
        <Column field="bank" header="Banco" class="w-2/6">
            <template #body="{ data }">
                {{ data.treasuryVoucher.bankAccount.bank.name }}
                -
                {{ data.treasuryVoucher.bankAccount.accountNumber }}
            </template>
        </Column>
        <Column field="number" header="N° Operación" class="w-1/6">
            <template #body="{ data }">
                {{ data.number }}
            </template>
        </Column>
        <Column field="totalAmount" header="Importe" class="w-1/12 text-right">
            <template #body="{ data }">
                {{ currencyNumber(data.treasuryVoucher.totalAmount) }}
            </template>
        </Column>
    </DataTable>

    <div class="grid w-full justify-center font-bold relative top-2.5">
        <span>Total Ingresos: {{ currencyNumber(totalBankIn) }}</span>
    </div>

    <Divider class="my-5" />

    <div class="grid w-full justify-center font-bold my-2">
        <span class="text-lg font-bold text-neutral-950">Egresos</span>
    </div>

    <DataTable :value="dailyBankOutTransactions" class="transaction-table" size="small" :loading="loading">
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
        <Column field="cuit" header="Cuit" class="w-1/12">
            <template #body="{ data }">
                {{ data.treasuryVoucher.supplier.cuit }}
            </template>
        </Column>
        <Column field="businessName" header="Proveedor" class="w-2/6">
            <template #body="{ data }">
                {{ data.treasuryVoucher.supplier.businessName }}
            </template>
        </Column>
        <Column field="bank" header="Banco" class="w-2/6">
            <template #body="{ data }">
                {{ data.treasuryVoucher.bankAccount.bank.name }}
                -
                {{ data.treasuryVoucher.bankAccount.accountNumber }}
            </template>
        </Column>
        <Column field="number" header="N° Operación" class="w-1/6">
            <template #body="{ data }">
                {{ data.number }}
            </template>
        </Column>
        <Column field="totalAmount" header="Importe" class="w-1/12 text-right">
            <template #body="{ data }">
                {{ currencyNumber(data.treasuryVoucher.totalAmount) }}
            </template>
        </Column>
    </DataTable>

    <div class="grid w-full justify-center font-bold relative top-2.5">
        <span>Total Egresos: {{ currencyNumber(totalBankOut) }}</span>
    </div>
</template>