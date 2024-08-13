<script setup>
import { inject, onMounted, ref } from "vue";
import { usePermissions } from '@/composables/permissions';
import { currencyNumber, dateFormat, invoiceNumberFormat } from "@/utils/formatterFunctions";
import { compareDates } from "@/utils/validateFunctions";

const { hasPermission } = usePermissions();
const treasuryVouchersArray = ref([]);
const checked = ref(false);

const dialogRef = inject("dialogRef");

onMounted(async () => {
    try {
        const response = await fetch(`/vouchers/${dialogRef.value.data.voucher.id}`);

        if (!response.ok) {
            throw new Error('Error al obtener los comprobantes del proveedor');
        }

        const data = await response.json();
        data.vouchers.map(voucher => {
            voucher.checked = false;
        })
        
        treasuryVouchersArray.value = data.vouchers;
    } catch (error) {
        console.error(error);
    }
});
</script>
<template>
    <DataTable :value="treasuryVouchersArray" scrollable scrollHeight="70vh" dataKey="id" filterDisplay="menu" :pt="{
        table: { style: 'min-width: 50rem' },
        paginator: {
            root: { class: 'p-paginator-custom' },
            current: { class: 'p-paginator-current' },
        }
    }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table uppercase">
        <Column header="Comprobante">
            <template #body="{ data }">
                {{ data.invoiceType.name }}
                <span class="font-bold">{{ data.invoiceTypeCode.name }}</span>
            </template>
        </Column>
        <Column header="Número">
            <template #body="{ data }">
                {{ invoiceNumberFormat(data.pointOfNumber, 5) }} - {{ invoiceNumberFormat(data.invoiceNumber, 8) }}
            </template>
        </Column>
        <Column header="F. vencimiento">
            <template #body="{ data }">
                <span :class="{ 'text-red-500': compareDates(data.invoicePaymentDate, '', 'before') }">
                    {{ dateFormat(data.invoicePaymentDate) }}
                </span>
            </template>
        </Column>
        <Column header="Monto pendiente">
            <template #body="{ data }">
                {{ currencyNumber(data.totalAmount) }}
            </template>
        </Column>
        <Column field="checked" header="Acciones" style="width: 5%; min-width: 1rem;">
            <template #body="{ data }">
                <Checkbox v-model="data.checked" binary />
            </template>
        </Column>
    </DataTable>
</template>