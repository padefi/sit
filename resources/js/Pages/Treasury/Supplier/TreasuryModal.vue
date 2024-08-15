<script setup>
import { inject, onMounted, ref } from "vue";
import { currencyNumber, dateFormat, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { usePermissions } from '@/composables/permissions';
import { useDialog } from 'primevue/usedialog';
import treasuryVoucherModal from './TreasuryVoucherModal.vue';

const { hasPermission } = usePermissions();
const treasuryVouchersArray = ref([]);
const dialog = useDialog();
const dialogRef = inject("dialogRef");

const addNewTreasuryVoucher = () => {
    dialog.open(treasuryVoucherModal, {
        props: {
            header: 'Comprobantes',
            style: {
                width: '75vw',
                height: '45vh',
            },
            breakpoints: {
                '960px': '75vw',
                '640px': '90vw'
            },
            modal: true,
            contentStyle: {
                padding: '1.25rem',
                height: '85vh',
            },
        },
        data: {
            voucher: dialogRef.value.data.voucher,
        }
    });
}

onMounted(async () => {
    // treasuryVouchersArray.value = props.suppliers;
    try {
        const response = await fetch(`/treasury-vouchers/${dialogRef.value.data.voucher.id}`);

        if (!response.ok) {
            throw new Error('Error al obtener los comprobantes de tesorería del proveedor');
        }

        const data = await response.json();        
        treasuryVouchersArray.value = data.treasuryVouchers;
    } catch (error) {
        console.error(error);
    }

    Echo.channel('voucherToTreasury')
        .listen('Treasury\\Voucher\\VoucherToTreasuryEvent', (e) => {
            console.log(e);
            if (e.type === 'create') {
                if (!treasuryVouchersArray.value.some(treasuryVoucher => treasuryVoucher.id === e.treasuryVoucherId)) {
                    treasuryVouchersArray.value.unshift(e.treasuryVoucher);
                }
            }
        });
});
</script>
<template>
    <Card class="mt-5 mx-4 uppercase">
        <template #title>
            <div class="flex justify-between items-center mx-4">
                <div class="align-left">
                    <h3 class="uppercase">Comprobantes tesorería</h3>
                </div>
                <template v-if="hasPermission('create treasury vouchers')">
                    <div class="align-right space-x-2">
                        <Button severity="info" outlined icon="pi pi-wallet" size="large" @click="addNewTreasuryVoucher($event)" />
                    </div>
                </template>
            </div>
        </template>
        <template #content>
            <DataTable :value="treasuryVouchersArray" scrollable scrollHeight="70vh" dataKey="id" filterDisplay="menu" :pt="{
                    table: { style: 'min-width: 50rem' },
                    paginator: {
                        root: { class: 'p-paginator-custom' },
                        current: { class: 'p-paginator-current' },
                    }
                }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table">
                <template #empty>
                    <div class="text-center text-lg text-red-500">
                        Sin comprobantes cargados
                    </div>
                </template>
                <Column field="voucherType" header="Tipo" class="rounded-tl-lg min-w-56 max-w-56">
                    <template #body="{ data }">
                        {{ data.voucherType.name }}
                    </template>
                </Column>
                <Column field="voucherStatus" header="Estado" class="rounded-tl-lg min-w-56 max-w-56">
                    <template #body="{ data }">
                        {{ data.voucherStatus.name }}
                    </template>
                </Column>
                <Column field="totalAmount" header="Importe" class="rounded-tl-lg min-w-56 max-w-56">
                    <template #body="{ data }">
                        {{ currencyNumber(data.totalAmount) }}
                    </template>
                </Column>
            </DataTable>
        </template>
    </Card>
</template>