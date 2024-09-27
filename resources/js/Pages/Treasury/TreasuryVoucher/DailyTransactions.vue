<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { toastService } from '@/composables/toastService'
import { useDialog } from 'primevue/usedialog';

toastService();
const dialog = useDialog();
const dailyCashInTransactions = ref([]);
const dailyCashOutTransactions = ref([]);
const dailyBankInTransactions = ref([]);
const dailyBankOutTransactions = ref([]);
const dailyChequeInTransactions = ref([]);
const dailyChequeOutTransactions = ref([]);
const transactionDate = ref(new Date());
const loading = ref(true);

const getFormattedDate = (date) => {
    const options = { timeZone: 'America/Argentina/Buenos_Aires', year: 'numeric', month: '2-digit', day: '2-digit' };
    const [day, month, year] = new Date(date).toLocaleDateString('es-AR', options).split('/');
    return `${year}-${month}-${day}`;
};

const getDailyTransactions = async () => {
    try {
        loading.value = true;
        const date = getFormattedDate(transactionDate.value);
        const encodedDate = encodeURIComponent(date);
        const response = await fetch(`/daily-transactions/${date}`);

        if (!response.ok) {
            throw new Error('Error al obtener los datos de los tipos de pago');
        }

        const data = await response.json();
        dailyCashInTransactions.value = data.dailyTransactions[1].cashTransactions;
        dailyCashOutTransactions.value = data.dailyTransactions[2].cashTransactions;
        dailyBankInTransactions.value = data.dailyTransactions[1].bankTransactions;
        dailyBankOutTransactions.value = data.dailyTransactions[2].bankTransactions;
        dailyChequeInTransactions.value = data.dailyTransactions[1].checkTransactions;
        dailyChequeOutTransactions.value = data.dailyTransactions[2].checkTransactions;
        loading.value = false;
    } catch (error) {
        console.error(error);
    }
}

onMounted(async () => {
    await getDailyTransactions();
});
</script>
<style>
.transaction-table {
    td {
        padding: 0.5rem !important;
        height: 2rem !important;
        min-width: 50rem;
        font-size: 12px !important;
    }

    tr:last-child td {
        border: 0
    }
}
</style>
<template>
    <AuthenticatedLayout>
        <Card class="mt-5 mx-4 uppercase">
            <template #title>
                <div class="grid w-full justify-center">
                    <span class="text-3xl font-bold text-teal-700">Movimientos diarios</span>
                    <FloatLabel class="w-1/3 justify-self-center mt-2">
                        <Calendar v-model="transactionDate" placeholder="DD/MM/AAAA" showButtonBar id="transactiontransactionDate"
                            inputClass="w-full text-lg" class="w-full"
                            :class="transactionDate !== null && transactionDate !== undefined ? 'filled' : ''" :invalid="transactionDate === null"
                            :maxDate="new Date()" />
                        <label for="transactionDate" class="text-base">F. Movimientos</label>
                    </FloatLabel>
                </div>
            </template>
            <template #content>
                <div class="grid w-full justify-center font-bold py-2 border-y-2 border-slate-300">
                    <span class="text-2xl font-bold">Caja</span>
                </div>

                <div class="grid w-full justify-center font-bold my-2">
                    <span class="text-lg font-bold">Ingresos</span>
                </div>

                <DataTable :value="dailyCashInTransactions" class="transaction-table" size="small" :loading="loading">
                    <template #empty>
                        <template v-if="!loading">
                            <div class="flex justify-center items-center">
                                Sin movimientos
                            </div>
                        </template>
                    </template>
                    <template #loading>
                        <ProgressSpinner class="!w-7 !h-7 top-5" />
                    </template>
                    <Column field="cuit" header="Cuit" sortable>
                        <template #body="{ data }">
                            {{ data }}
                        </template>
                    </Column>
                </DataTable>

                <div class="grid w-full justify-center font-bold my-2">
                    <span class="text-lg font-bold">Engresos</span>
                </div>

                <Divider class="my-5" />

            </template>
        </Card>
    </AuthenticatedLayout>
</template>
