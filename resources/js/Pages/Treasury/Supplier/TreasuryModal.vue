<script setup>
import { inject, onMounted, ref } from "vue";
import { usePermissions } from '@/composables/permissions';
import { useDialog } from 'primevue/usedialog';
import treasuryVoucherModal from './TreasuryVoucherModal.vue';

const { hasPermission } = usePermissions();
const treasuryArray = ref([]);

const dialog = useDialog();
const dialogRef = inject("dialogRef");

const addNewTreasuryVoucher = () => {
    dialog.open(treasuryVoucherModal, {
        props: {
            header: 'Comprobantes',
            style: {
                width: '70vw',
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
</script>
<template>
    <Card class="mt-5 mx-4 uppercase">
        <template #title>
            <div class="flex justify-between items-center mx-4">
                <div class="align-left">
                    <h3 class="uppercase">Comprobantes tesorería</h3>
                </div>
                <template v-if="hasPermission('create vouchers')">
                    <div class="align-right space-x-2">
                        <Button severity="info" outlined icon="pi pi-wallet" size="large" @click="addNewTreasuryVoucher($event)" />
                    </div>
                </template>
            </div>
        </template>
        <template #content>
            <DataTable :value="treasuryArray" scrollable scrollHeight="70vh" dataKey="id" filterDisplay="menu" :pt="{
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
                <Column field="description" header="Descripción" class="rounded-tl-lg min-w-56 max-w-56">
                    <template #body="{ data }">
                        <!-- {{ data.description }} -->
                    </template>
                </Column>
            </DataTable>
        </template>
    </Card>
</template>