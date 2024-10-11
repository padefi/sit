<script setup>
import { ref, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { toastService } from '@/composables/toastService'
import DailyTransactionsCash from './DailyTransactionsCash.vue';
import DailyTransactionsBank from './DailyTransactionsBank.vue';
import DailyTransactionsCheque from './DailyTransactionsCheque.vue';


toastService();
const dailyTransactionsCashRef = ref(null);
const dailyTransactionsBankRef = ref(null);
const dailyTransactionsChequeRef = ref(null);
const transactionDate = ref(new Date());

const getFormattedDate = (date) => {
    const options = { timeZone: 'America/Argentina/Buenos_Aires', year: 'numeric', month: '2-digit', day: '2-digit' };
    const [day, month, year] = new Date(date).toLocaleDateString('es-AR', options).split('/');
    return `${year}-${month}-${day}`;
};

const getDailyTransactions = async () => {
    try {
        const date = getFormattedDate(transactionDate.value);
        const encodedDate = encodeURIComponent(date);
        const response = await fetch(`/daily-transactions/${date}`);

        if (!response.ok) {
            throw new Error('Error al obtener los datos de los tipos de pago');
        }

        const data = await response.json();
        dailyTransactionsCashRef.value.totalCashIn = 0;
        dailyTransactionsCashRef.value.totalCashOut = 0;
        dailyTransactionsCashRef.value.previousCash = 0;
        dailyTransactionsCashRef.value.totalCash = 0;
        dailyTransactionsBankRef.value.totalBankIn = 0;
        dailyTransactionsBankRef.value.totalBankOut = 0;
        dailyTransactionsChequeRef.value.totalChequeIn = 0;
        dailyTransactionsChequeRef.value.totalChequeOut = 0;

        dailyTransactionsCashRef.value.loading = true;
        dailyTransactionsBankRef.value.loading = true;
        dailyTransactionsChequeRef.value.loading = true;

        setTimeout(() => {
            dailyTransactionsCashRef.value.dailyCashData(
                data.dailyTransactions[1].previousCash, data.dailyTransactions[2].previousCash,
                data.dailyTransactions[1].cashTransactions, data.dailyTransactions[2].cashTransactions
            );
            dailyTransactionsBankRef.value.dailyBankData(data.dailyTransactions[1].bankTransactions, data.dailyTransactions[2].bankTransactions);
            dailyTransactionsChequeRef.value.dailyChequeData(data.dailyTransactions[1].checkTransactions, data.dailyTransactions[2].checkTransactions);
        }, 500);
    } catch (error) {
        console.error(error);
    }
}

const generatePdf = (event) => {
    const date = getFormattedDate(transactionDate.value);
    window.open(route('daily-transactions.pdf', date), '_blank');
}

watch(transactionDate, async () => {
    await getDailyTransactions();
});

onMounted(async () => {
    await getDailyTransactions();

    Echo.channel('treasuryVouchers')
        .listen('Treasury\\TreasuryVoucher\\TreasuryVoucherEvent', async () => {
            await getDailyTransactions();
        });
});
</script>
<style>
.transaction-table {
    th:last-child div {
        display: contents !important;
        text-align: right !important;
    }

    th {
        color: #404040 !important;
    }

    td {
        padding: 0.5rem !important;
        height: 2rem !important;
    }

    /* tr:last-child td {
        border: 0
    } */
}
</style>
<template>
    <AuthenticatedLayout>
        <Card class="my-5 mx-4 uppercase">
            <template #title>
                <div class="grid w-full justify-center">
                    <span class="text-3xl font-bold text-teal-700">Movimientos diarios</span>
                    <div class="flex justify-center space-x-2">
                        <FloatLabel class="w-2/5 justify-self-center mt-2">
                            <Calendar v-model="transactionDate" placeholder="DD/MM/AAAA" showButtonBar id="transactiontransactionDate"
                                inputClass="w-full text-lg" class="w-full"
                                :class="transactionDate !== null && transactionDate !== undefined ? 'filled' : ''" :invalid="transactionDate === null"
                                :maxDate="new Date()" />
                            <label for="transactionDate" class="text-base">F. Movimientos</label>
                        </FloatLabel>
                        <Button icon="pi pi-file-pdf" iconClass="text-4xl text-red-500" class="p-0 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-transparent focus:ring-0 focus:outline-0" text @click="generatePdf($event)" />
                    </div>
                </div>
            </template>
            <template #content>
                <div class="grid w-full justify-center font-bold mb-5 py-2 border-y-2 border-slate-300">
                    <span class="text-2xl font-bold">Caja</span>
                </div>

                <DailyTransactionsCash ref="dailyTransactionsCashRef" />

                <div class="grid w-full justify-center font-bold my-5 py-2 border-y-2 border-slate-300">
                    <span class="text-2xl font-bold">Banco</span>
                </div>

                <DailyTransactionsBank ref="dailyTransactionsBankRef" />

                <div class="grid w-full justify-center font-bold my-5 py-2 border-y-2 border-slate-300">
                    <span class="text-2xl font-bold">Cheque</span>
                </div>

                <DailyTransactionsCheque ref="dailyTransactionsChequeRef" />
            </template>
        </Card>
    </AuthenticatedLayout>
</template>
