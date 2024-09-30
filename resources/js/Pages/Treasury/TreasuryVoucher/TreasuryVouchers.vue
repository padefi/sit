<script setup>
import { onMounted, ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import IncomeTreasuryVouchersIndex from './IncomeTreasuryVouchersIndex.vue';
import ExpenseTreasuryVouchersIndex from './ExpenseTreasuryVouchersIndex.vue';
import { usePermissions } from '@/composables/permissions';
import { toastService } from '@/composables/toastService'
import { useDialog } from 'primevue/usedialog';
import treasuryVoucherModal from './TreasuryVoucherModal.vue';

toastService();
const dialog = useDialog();

const { hasPermission } = usePermissions();
const activeIndex = ref(0);
const voucherStatusesSelect = ref([]);
const status = ref(0);
const incomeTreasuryVouchersRef = ref(null);
const expenseTreasuryVouchersRef = ref(null);

const addNewVoucher = () => {
    dialog.open(treasuryVoucherModal, {
        props: {
            header: 'Nuevo comprobante',
            style: {
                width: '55vw',
            },
            breakpoints: {
                '960px': '75vw',
            },
            modal: true,
            contentStyle: {
                padding: '1.25rem',
            },
        },
    });
}

const handleTabChange = (e) => {
    activeIndex.value = e.index;

    switch (e.index) {
        case 0:
            if (expenseTreasuryVouchersRef.value) {
                expenseTreasuryVouchersRef.value.fetchExpenseTreasuryVouchers(e.index + 2, status.value);
            }

            break;
        case 1:
            if (incomeTreasuryVouchersRef.value) {
                incomeTreasuryVouchersRef.value.fetchIncomeTreasuryVouchers(e.index, status.value);
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
    handleTabChange({ index: 0 });
});
</script>
<style>
.card-treasury-vouchers>div[data-pc-section="body"]>div[data-pc-section="content"] {
    padding-top: 0;
    padding-bottom: 0;
    margin-top: -2rem;
}
</style>
<template>
    <AuthenticatedLayout>
        <Card class="card-treasury-vouchers mt-5 mx-4 uppercase">
            <template #title>
                <div class="flex justify-between items-center mx-4">
                    <div class="align-left">
                        <h3 class="uppercase">Comprobantes</h3>
                    </div>
                    <template v-if="hasPermission('create treasury vouchers')">
                        <div class="align-right">
                            <Button label="Cargar comprobante" severity="info" outlined icon="pi pi-shopping-cart" size="large"
                                @click="addNewVoucher($event)" />
                        </div>
                    </template>
                </div>
            </template>
            <template #content>
                <div class="w-fit left-[85vh] justify-center top-12 relative z-50">
                    <Dropdown v-model="status" :options="voucherStatusesSelect" name="voucherStatusName"
                        class="!border-solid !border-teal-600 p-column-filter font-bold" optionLabel="label" optionValue="value"
                        style="min-width: 12rem" @change="handleTabChange({ index: activeIndex })" />
                </div>
                <TabView @tab-change="handleTabChange($event)" v-model:activeIndex="activeIndex" class="!bg-transparent">
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