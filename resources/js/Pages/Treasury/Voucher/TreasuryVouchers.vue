<script setup>
import { onMounted, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import IncomeTreasuryVouchersIndex from './IncomeTreasuryVouchersIndex.vue';
import ExpenseTreasuryVouchersIndex from './ExpenseTreasuryVouchersIndex.vue';
import { toastService } from '@/composables/toastService'

toastService();

const activeIndex = ref(0);
const voucherStatusesSelect = ref([]);
const status = ref();
const incomeTreasuryVouchersRef = ref(null);
const expenseTreasuryVouchersRef = ref(null);

function handleTabChange(e) {
    activeIndex.value = e.index;

    switch (e.index) {
        case 0:
            if (incomeTreasuryVouchersRef.value) {
                incomeTreasuryVouchersRef.value.fetchIncomeTreasuryVouchers(e.index + 1, status.value);
            }
            break;
        case 1:
            if (expenseTreasuryVouchersRef.value) {
                expenseTreasuryVouchersRef.value.fetchExpenseTreasuryVouchers(e.index + 1, status.value);
            }

            break;
    }
}

const getTreasuryVoucherStatusData = async () => {
    try {
        const response = await fetch('/treasury-vouchers/status');

        if (!response.ok) {
            throw new Error('Error al obtener los datos de los tipos de comprobantes');
        }

        const data = await response.json();
        voucherStatusesSelect.value = data.treasuryVoucherStatus.map((treasuryVoucherStatus) => {
            return { label: treasuryVoucherStatus.name, value: treasuryVoucherStatus.id };
        });
    } catch (error) {
        console.error(error);
    }
}

onMounted(async () => {
    await getTreasuryVoucherStatusData();
    status.value = voucherStatusesSelect.value[0]?.value;
});

/* watch([activeIndex, status], () => {
    console.log(activeIndex.value, status.value);
    
    handleTabChange(activeIndex.value);
}); */
</script>
<template>
    <AuthenticatedLayout>
        <Card class="mt-5 mx-4 uppercase">
            <template #title>
                <div class="flex justify-between items-center mx-4">
                    <div class="align-left">
                        <h3 class="uppercase">Comprobantes</h3>
                    </div>
                </div>
            </template>
            <template #content>
                <div class="flex justify-center">
                    <Dropdown v-model="status" :options="voucherStatusesSelect"
                        placeholder="Estado" name="voucherStatusName" class="p-column-filter" optionLabel="label" optionValue="value"
                        style="min-width: 12rem" />
                </div>
                <TabView @tab-change="handleTabChange" v-model:activeIndex="activeIndex">
                    <TabPanel header="Egresos">
                        <ExpenseTreasuryVouchersIndex ref="expenseTreasuryVouchersRef" />
                    </TabPanel>
                    <TabPanel header="Ingresos">
                        <IncomeTreasuryVouchersIndex ref="incomeTreasuryVouchersRef" />
                    </TabPanel>
                </TabView>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>