<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { currencyNumber } from "@/utils/formatterFunctions";
import { Link } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/permissions'
import { onMounted, ref } from 'vue';

const { user, hasPermission } = usePermissions();

const loading = ref(true);
const treasuryVouchers = ref(0);
const dailyTransactions = ref(0);
const invoiceSuppliers = ref(0);

const getData = async () => {
    const response = await fetch('/dashboard');

    if (!response.ok) {
        throw new Error('Error al obtener los datos del home');
    }

    const data = await response.json();
    treasuryVouchers.value = data.treasuryVouchers;
    dailyTransactions.value = data.totalTransactions;
    invoiceSuppliers.value = data.totalInvoiceSuppliers;
    loading.value = false;    
}
onMounted(async () => {
    loading.value = true;
    await getData();

    Echo.channel('treasuryVouchers')
        .listen('Treasury\\TreasuryVoucher\\TreasuryVoucherEvent', async () => {
            loading.value = true;
            await getData();
        });

    Echo.channel('vouchers')
        .listen('Treasury\\Voucher\\VoucherEvent', async () => {
            loading.value = true;
            await getData();
        });
});
</script>

<style>
.dashboard-card:hover {
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
</style>
<template>
    <AuthenticatedLayout>
        <div class="p-5">
            <h1 class="text-3xl mb-4 capitalize">Bienvenido {{ user() }}</h1>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card v-if="hasPermission('view treasury vouchers')" class="dashboard-card">
                    <template #title>
                        <div class="flex justify-between items-center">
                            <div class="align-left">
                                <span>Tesorería</span>
                            </div>
                            <div class="align-right">
                                <Link v-ripple href="/treasury-vouchers" as="button"
                                    class="flex items-center justify-center bg-emerald-100 dark:bg-emerald-400/10 rounded-md"
                                    style="width: 2.5rem; height: 2.5rem">
                                <i class="pi pi-wallet text-emerald-500 !text-xl"></i>
                                </Link>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <template v-if="loading">
                            <Skeleton></Skeleton>
                        </template>
                        <template v-else>
                            <div class="text-primary text-lg">
                                <template v-if="treasuryVouchers.totalTreasuryVouchers > 0">
                                    <span class="text-orange-500 font-bold">{{ treasuryVouchers.totalTreasuryVouchers }}</span>
                                    <span> comprobantes pendientes.</span>
                                    <div class="flex flex-wrap items-center mt-2">
                                        <div class="flex items-center space-x-2 mr-6">
                                            <div class="w-6 h-6 flex items-center justify-center bg-green-100 rounded-full shrink-0">
                                                <i class="pi pi-arrow-up !text-sm text-green-500"></i>
                                            </div>
                                            <span class="font-bold">Ingresos: </span>
                                            <span class="text-green-500 font-bold">
                                                {{ currencyNumber(treasuryVouchers.amountIncomeTreasuryVouchers) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 flex items-center justify-center bg-red-100 rounded-full shrink-0">
                                                <i class="pi pi-arrow-down !text-sm text-red-500"></i>
                                            </div>
                                            <span class="font-bold">Egresos: </span>
                                            <span class="text-red-500 font-bold">
                                                {{ currencyNumber(treasuryVouchers.amountExpenseTreasuryVouchers) }}
                                            </span>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <span>No hay comprobantes pendientes.</span>
                                </template>
                            </div>
                        </template>
                    </template>
                </Card>
                <Card v-if="hasPermission('view daily transactions')" class="dashboard-card">
                    <template #title>
                        <div class="flex justify-between items-center">
                            <div class="align-left">
                                <span>Movimientos diarios</span>
                            </div>
                            <div class="align-right">
                                <Link v-ripple href="/daily-transactions" as="button"
                                    class="flex items-center justify-center bg-blue-100 dark:bg-blue-400/10 rounded-md"
                                    style="width: 2.5rem; height: 2.5rem">
                                <i class="pi pi-calendar-clock text-blue-500 !text-xl"></i>
                                </Link>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <template v-if="loading">
                            <Skeleton></Skeleton>
                        </template>
                        <template v-else>
                            <div class="text-primary text-lg">
                                <template v-if="dailyTransactions.totalTransactions > 0">
                                    <span class="text-orange-500 font-bold">{{ dailyTransactions.totalTransactions }}</span>
                                    <span> nuevos movimientos.</span>
                                    <div class="flex flex-wrap items-center mt-2">
                                        <div class="flex items-center space-x-2 mr-6">
                                            <div class="w-6 h-6 flex items-center justify-center bg-green-100 rounded-full shrink-0">
                                                <i class="pi pi-arrow-up !text-sm text-green-500"></i>
                                            </div>
                                            <span class="font-bold">Ingresos: </span>
                                            <span class="text-green-500 font-bold">
                                                {{ currencyNumber(dailyTransactions.amountIncomeTransactions) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 flex items-center justify-center bg-red-100 rounded-full shrink-0">
                                                <i class="pi pi-arrow-down !text-sm text-red-500"></i>
                                            </div>
                                            <span class="font-bold">Egresos: </span>
                                            <span class="text-red-500 font-bold">
                                                {{ currencyNumber(dailyTransactions.amountExpenseTransactions) }}
                                            </span>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <span>No hay nuevos movimientos.</span>
                                </template>
                            </div>
                        </template>
                    </template>
                </Card>
                <Card v-if="hasPermission('view vouchers')" class="dashboard-card">
                    <template #title>
                        <div class="flex justify-between items-center">
                            <div class="align-left">
                                <span>Proveedores</span>
                            </div>
                            <div class="align-right">
                                <Link v-ripple href="/suppliers" as="button"
                                    class="flex items-center justify-center bg-orange-100 dark:bg-orange-400/10 rounded-md"
                                    style="width: 2.5rem; height: 2.5rem">
                                <i class="pi pi-truck text-orange-500 !text-xl"></i>
                                </Link>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <template v-if="loading">
                            <Skeleton></Skeleton>
                        </template>
                        <template v-else>
                            <div class="text-primary text-lg">
                                <template v-if="invoiceSuppliers.totalInvoiceSuppliers > 0">
                                    <span class="text-orange-500 font-bold">{{ invoiceSuppliers.totalInvoiceSuppliers }}</span>
                                    <span> facturas pendientes.</span>
                                    <div class="flex flex-wrap items-center mt-2">
                                        <div class="flex items-center space-x-2 mr-6">
                                            <div class="w-6 h-6 flex items-center justify-center bg-green-100 rounded-full shrink-0">
                                                <i class="pi pi-arrow-up !text-sm text-green-500"></i>
                                            </div>
                                            <span class="font-bold">Ingresos: </span>
                                            <span class="text-green-500 font-bold">
                                                {{ currencyNumber(invoiceSuppliers.amountIncomeInvoiceSuppliers) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <div class="w-6 h-6 flex items-center justify-center bg-red-100 rounded-full shrink-0">
                                                <i class="pi pi-arrow-down !text-sm text-red-500"></i>
                                            </div>
                                            <span class="font-bold">Egresos: </span>
                                            <span class="text-red-500 font-bold">
                                                {{ currencyNumber(invoiceSuppliers.amountExpenseInvoiceSuppliers) }}
                                            </span>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <span>No hay facturas pendientes.</span>
                                </template>
                            </div>
                        </template>
                    </template>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
