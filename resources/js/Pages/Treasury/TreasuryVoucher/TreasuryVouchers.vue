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
const loading = ref(true);
const activeIndex = ref(0);
const voucherStatusesSelect = ref([]);
const status = ref(0);
const incomeTreasuryVouchersRef = ref(null);
const expenseTreasuryVouchersRef = ref(null);
const expenseTreasuryVouchersCount = ref(0);
const incomeTreasuryVouchersCount = ref(0);

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

const handleTabChange = async (e) => {
    activeIndex.value = e.index;

    switch (e.index) {
        case 0:
            if (expenseTreasuryVouchersRef.value) {
                await expenseTreasuryVouchersRef.value.fetchExpenseTreasuryVouchers(e.index + 2, status.value);
                expenseTreasuryVouchersCount.value = expenseTreasuryVouchersRef.value.expenseTreasuryVouchersCount;
            }

            break;
        case 1:
            if (incomeTreasuryVouchersRef.value) {
                await incomeTreasuryVouchersRef.value.fetchIncomeTreasuryVouchers(e.index, status.value);
                incomeTreasuryVouchersCount.value = incomeTreasuryVouchersRef.value.incomeTreasuryVouchersCount;
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

const getTreasuryVoucherCountData = async () => {
    loading.value = true;

    for (let i = 2; i >= 0; i--) {
        handleTabChange({ index: i });
    }

    setTimeout(() => {
        loading.value = false;
    }, 500);
}

watch(status, async () => {
    await getTreasuryVoucherCountData();
});

onMounted(async () => {
    await getTreasuryVoucherStatusData();
    status.value = voucherStatusesSelect.value[0]?.value;
    await getTreasuryVoucherCountData();

    Echo.channel('treasuryVouchers')
        .listen('Treasury\\TreasuryVoucher\\TreasuryVoucherEvent', (e) => {
            setTimeout(() => {
                expenseTreasuryVouchersCount.value = expenseTreasuryVouchersRef.value.expenseTreasuryVouchersCount;
                incomeTreasuryVouchersCount.value = incomeTreasuryVouchersRef.value.incomeTreasuryVouchersCount;
            }, 500);
        });
});
</script>
<style>
div[data-pc-name="tabpanel"] {
    padding: 1.25rem 0;
}

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
                    <TabPanel>
                        <template #header>
                            <div class="flex align-items-center gap-2">
                                <span class="font-bold white-space-nowrap">Egresos</span>
                                <template v-if="status === 1">
                                    <template v-if="loading">
                                        <Skeleton shape="circle" size="1.5rem"></Skeleton>
                                    </template>
                                    <template v-else>
                                        <Badge :value="expenseTreasuryVouchersCount"
                                            :severity="expenseTreasuryVouchersCount > 0 ? 'info' : 'success'" />
                                    </template>
                                </template>
                            </div>
                        </template>
                        <ExpenseTreasuryVouchersIndex ref="expenseTreasuryVouchersRef" />
                    </TabPanel>
                    <TabPanel>
                        <template #header>
                            <div class="flex align-items-center gap-2">
                                <span class="font-bold white-space-nowrap">Ingresos</span>
                                <template v-if="status === 1">
                                    <template v-if="loading">
                                        <Skeleton shape="circle" size="1.5rem"></Skeleton>
                                    </template>
                                    <template v-else>
                                        <Badge :value="incomeTreasuryVouchersCount"
                                            :severity="incomeTreasuryVouchersCount > 0 ? 'info' : 'success'" />
                                    </template>
                                </template>
                            </div>
                        </template>
                        <IncomeTreasuryVouchersIndex ref="incomeTreasuryVouchersRef" />
                    </TabPanel>
                </TabView>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>