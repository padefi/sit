<script setup>
import { ref, onMounted, inject, watch, computed } from "vue";
import { currencyNumber } from "@/utils/formatterFunctions";
import { dropdownClasses } from '@/utils/cssUtils';
import InputError from '@/Components/InputError.vue';

const treasuryVouchersArray = ref([]);
const totalPaymentAmount = ref(0);
const isProcessing = ref(false);
const paymentMethodsSelect = ref([]);
const banksSelect = ref([]);
const bankAccountsSelect = ref([]);
const dialogRef = inject("dialogRef");
const rules = 'Debe completar el campo';

const setTotalPaymentAmount = (event, index, withholdingType) => {
    totalPaymentAmount.value = treasuryVouchersArray.value.reduce((total, voucher, i) => {
        const wittholding = voucher.withholdings;
        const incomeAmount = wittholding.incomeTaxStatus === 1 ? withholdingType === 'incomeTax' && index === i ? event.value || 0 : wittholding.incomeTax || 0 : 0;
        const socialAmount = wittholding.socialTaxStatus === 1 ? withholdingType === 'socialTax' && index === i ? event.value || 0 : wittholding.socialTax || 0 : 0;
        const vatAmount = wittholding.vatTaxStatus === 1 ? withholdingType === 'vatTax' && index === i ? event.value || 0 : wittholding.vatTax || 0 : 0;

        return total + incomeAmount + socialAmount + vatAmount;
    }, 0);
}

const getPaymentMethods = async () => {
    try {
        const response = await fetch(`/payment-methods/${dialogRef.value.data.status}`);

        if (!response.ok) {
            throw new Error('Error al obtener los datos de los tipos de pago');
        }

        const data = await response.json();
        const paymentMethods = data.paymentMethods;
        paymentMethodsSelect.value = treasuryVouchersArray.value.map(() =>
            paymentMethods.map(paymentMethod => ({
                label: paymentMethod.name,
                value: paymentMethod.id
            }))
        );
    } catch (error) {
        console.error(error);
    }
}


const getBanks = async (paymentMethod, index) => {
    banksSelect.value[index] = [];
    bankAccountsSelect.value[index] = [];
    treasuryVouchersArray.value[index].bankId = undefined;
    treasuryVouchersArray.value[index].bankAccountId = undefined;
    treasuryVouchersArray.value[index].transactionNumber = undefined;

    if (paymentMethod === 4) {
        banksSelect.value[index] = [{ value: 0, label: 'SIN DATOS' }];
        treasuryVouchersArray.value[index].bankId = 0;

        bankAccountsSelect.value[index] = [{ value: 0, label: 'SIN DATOS' }];
        treasuryVouchersArray.value[index].bankAccountId = 0;
        return;
    }

    try {
        const response = await fetch('/banks/show-banks');

        if (!response.ok) {
            throw new Error('Error al obtener los datos de los bancos');
        }

        const data = await response.json();
        banksSelect.value[index] = data.banks.map((bank) => {
            return {
                label: bank.name,
                value: bank.id
            };
        });
    } catch (error) {
        console.error(error);
    }
}

const getBankAccounts = async (bankId, index) => {
    bankAccountsSelect.value[index] = [];
    treasuryVouchersArray.value[index].bankAccountId = undefined;
    treasuryVouchersArray.value[index].transactionNumber = undefined;

    try {
        const response = await fetch(`/bankAccounts/${bankId}`);

        if (!response.ok) {
            throw new Error('Error al obtener los datos de las ctas. bancarias');
        }

        const data = await response.json();
        bankAccountsSelect.value[index] = data.bankAccounts.map((bankAccount) => {
            return {
                label: bankAccount.accountType.name + ' ' + bankAccount.accountNumber,
                value: bankAccount.id
            };
        });
    } catch (error) {
        console.error(error);
    }
}

const handleTtransactionNumber = (index) => {
    treasuryVouchersArray.value[index].transactionNumber = null;
}

const isFormInvalid = computed(() => {
    return treasuryVouchersArray.value.some(voucher => {
        return !voucher.paymentMethod ||
            !voucher.bankId && voucher.bankId !== 0 ||
            !voucher.bankAccountId && voucher.bankId !== 0 ||
            (!voucher.transactionNumber && voucher.paymentMethod !== 4) ||
            !voucher.paymentDate;
    });
});

onMounted(async () => {
    treasuryVouchersArray.value = dialogRef.value.data.form.vouchers;
    totalPaymentAmount.value = dialogRef.value.data.form.totalPaymentAmount;
    await getPaymentMethods();
});
</script>
<style>
.thin-td tr td {
    padding: 0.5rem 0.25rem !important;
}
</style>
<template>
    <DataTable :value="treasuryVouchersArray" scrollable scrollHeight="30vh" dataKey="id" filterDisplay="menu" :pt="{
        table: { style: 'min-width: 50rem' }, tbody: { class: 'thin-td' }, wrapper: { class: 'datatable-scrollbar' },
    }" class="data-table uppercase">
        <template #empty>
            Sin comprobantes cargados
        </template>
        <Column field="businessName" header="Proveedor" class="w-2/12">
            <template #body="{ data }">
                {{ data.businessName }}
            </template>
        </Column>
        <Column field="amount" header="Importe" class="w-36">
            <template #body="{ data }">
                {{ currencyNumber(data.amount) }}
            </template>
        </Column>
        <Column field="withholdings" header="Retenciones" class="w-2/12">
            <template #body="{ data, index }">
                <div class="flex space-x-2">
                    <div>
                        <FloatLabel>
                            <InputNumber v-model="data.withholdings.incomeTax" placeholder="$ 0,00" inputId="incomeTax" mode="currency" currency="ARS"
                                locale="es-AR" id="incomeTax" inputClass="w-36" class="w-full"
                                :class="data.withholdings.incomeTax !== null && data.withholdings.incomeTax !== undefined ? 'filled' : ''" :min="0"
                                :max="99999999" :minFractionDigits="2" :invalid="data.withholdings.incomeTax === null"
                                :disabled="data.withholdings.incomeTaxStatus === 0" @input="setTotalPaymentAmount($event, index, 'incomeTax')" />
                            <label for="incomeTax">Ganancias</label>
                        </FloatLabel>
                        <InputError :message="data.withholdings.incomeTax === null ? rules : ''" />
                    </div>
                    <div>
                        <FloatLabel>
                            <InputNumber v-model="data.withholdings.socialTax" placeholder="$ 0,00" inputId="socialTax" mode="currency" currency="ARS"
                                locale="es-AR" id="socialTax" inputClass="w-36" class="w-full"
                                :class="data.withholdings.socialTax !== null && data.withholdings.socialTax !== undefined ? 'filled' : ''" :min="0"
                                :max="99999999" :minFractionDigits="2" :invalid="data.withholdings.socialTax === null"
                                :disabled="data.withholdings.socialTaxStatus === 0" />
                            <label for="socialTax">Suss</label>
                        </FloatLabel>
                        <InputError :message="data.withholdings.socialTax === null ? rules : ''" />
                    </div>
                    <div>
                        <FloatLabel>
                            <InputNumber v-model="data.withholdings.vatTax" placeholder="$ 0,00" inputId="vatTax" mode="currency" currency="ARS"
                                locale="es-AR" id="vatTax" inputClass="w-36" class="w-full"
                                :class="data.withholdings.vatTax !== null && data.withholdings.vatTax !== undefined ? 'filled' : ''" :min="0"
                                :max="99999999" :minFractionDigits="2" :invalid="data.withholdings.vatTax === null"
                                :disabled="data.withholdings.vatTaxStatus === 0" />
                            <label for="vatTax">I.V.A.</label>
                        </FloatLabel>
                        <InputError :message="data.withholdings.vatTax === null ? rules : ''" />
                    </div>
                </div>
            </template>
        </Column>
        <Column field="totalAmount" header="Importe" class="w-36">
            <template #body="{ data }">
                {{ currencyNumber(data.totalAmount) }}
            </template>
        </Column>
        <Column field="paymentMethod" header="Forma Pago" class="w-2/12">
            <template #body="{ data, field, index }">
                <FloatLabel>
                    <Dropdown inputId="paymentMethod" v-model="data[field]" :options="paymentMethodsSelect[index]" filter
                        class="!focus:border-primary-500 w-full" :class="dropdownClasses(data[field], index)" optionLabel="label" optionValue="value"
                        @change="getBanks(data[field], index)" />
                    <label for="bank">Forma Pago</label>
                </FloatLabel>
            </template>
        </Column>
        <Column field="bankId" header="Banco" class="w-2/12">
            <template #body="{ data, field, index }">
                <FloatLabel>
                    <Dropdown inputId="bank" v-model="data[field]" :options="banksSelect[index]" filter class="!focus:border-primary-500 w-full"
                        :class="dropdownClasses(data[field])" optionLabel="label" optionValue="value" @change="getBankAccounts(data[field], index)" />
                    <label for="bank">Banco</label>
                </FloatLabel>
            </template>
        </Column>
        <Column field="bankAccountId" header="Cta. Bancaria" class="w-2/12">
            <template #body="{ data, field, index }">
                <FloatLabel>
                    <Dropdown inputId="bankAccount" v-model="data[field]" :options="bankAccountsSelect[index]" filter
                        class="!focus:border-primary-500 w-full" :class="dropdownClasses(data[field], index)" optionLabel="label" optionValue="value"
                        @change="handleTtransactionNumber(index)" />
                    <label for="bank">Cta. bancaria</label>
                </FloatLabel>
            </template>
        </Column>
        <Column field="transactionNumber" header="N° Operación" class="w-1/12">
            <template #body="{ data, field, index }">
                <FloatLabel>
                    <InputNumber v-model="data[field]" placeholder="12345678" :useGrouping="false" inputId="transactionNumber" id="transactionNumber"
                        class="w-full" :class="data[field] !== null && data[field] !== undefined ? 'filled' : ''" :min="1" :max="9999999999999"
                        :disabled="data[field] === undefined" :invalid="data[field] === null" />
                    <label for="rate">N° Operación</label>
                </FloatLabel>
                <InputError :message="data[field] === null ? rules : ''" />
            </template>
        </Column>
        <Column field="paymentDate" header="F. pago" class="w-1/12">
            <template #body="{ data, field }">
                <FloatLabel>
                    <Calendar v-model="data[field]" placeholder="DD/MM/AAAA" showButtonBar id="paymentDate" class="w-full"
                        :class="data[field] !== null && data[field] !== undefined ? 'filled' : ''" :invalid="data[field] === null"
                        :maxDate="new Date()" />
                    <label for="paymentDate">F. Pago</label>
                </FloatLabel>
                <InputError :message="data[field] === null ? rules : ''" />
            </template>
        </Column>
    </DataTable>

    <div class="flex flex-col mx-3 my-4 ">
        <div class="flex justify-between">
            <div class="flex md:w-2/5 items-center">
                <div class="w-full text-left text-surface-900/60 font-bold">Total a Pagar: </div>
                <div class="w-full text-left font-bold" :class="totalPaymentAmount < 0 ? 'text-red-500' : ''">
                    {{ currencyNumber(totalPaymentAmount) }}
                </div>
            </div>
            <Button label="Confirmar" icon="pi pi-save" iconPos="right" :disabled="totalPaymentAmount === 0 || isProcessing || isFormInvalid" />
        </div>
    </div>
</template>