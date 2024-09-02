<script setup>
import { ref, onMounted, inject, computed } from "vue";
import { currencyNumber } from "@/utils/formatterFunctions";
import { dropdownClasses } from '@/utils/cssUtils';
import InputError from '@/Components/InputError.vue';

const treasuryVouchersArray = ref([]);
const totalPaymentAmount = ref(0);
const isProcessing = ref(false);
const paymentMethodsSelect = ref([]);
const banksSelect = ref([]);
const bankAccountsSelect = ref([]);
const paymentMethodGlobal = ref();
const bankGlobal = ref();
const bankAccountGlobal = ref();
const dialogRef = inject("dialogRef");
const rules = 'Debe completar el campo';
const loading = ref(true);

const setTotalPaymentAmount = (event, index, withholdingType) => {
    totalPaymentAmount.value = treasuryVouchersArray.value.reduce((total, voucher, i) => {
        const withholding = voucher.withholdings;
        const incomeAmount = withholding.incomeTaxStatus === 1 ? withholdingType === 'incomeTax' && index === i ? event.value || 0 : withholding.incomeTax || 0 : 0;
        const socialAmount = withholding.socialTaxStatus === 1 ? withholdingType === 'socialTax' && index === i ? event.value || 0 : withholding.socialTax || 0 : 0;
        const vatAmount = withholding.vatTaxStatus === 1 ? withholdingType === 'vatTax' && index === i ? event.value || 0 : withholding.vatTax || 0 : 0;
        const totalAmountVoucher = voucher.amount - (incomeAmount + socialAmount + vatAmount);
        voucher.totalAmount = totalAmountVoucher;

        return total + (totalAmountVoucher || 0);
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

const calculateWithholdingTax = async (voucher, index) => {
    try {
        const response = await fetch(`/treasury-voucher/${voucher.id}/calculate-withholding-tax`);

        if (!response.ok) {
            throw new Error('Error al obtener el importe a retener');
        }

        const data = await response.json();
        voucher.withholdings.incomeTax = data.incomeTaxWithholdingAmount;
        voucher.withholdings.socialTax = data.socialTaxAmount;
        voucher.withholdings.vatTax = data.vatTaxAmount;
        voucher.totalAmount = voucher.amount - (data.incomeTaxWithholdingAmount + data.socialTaxAmount + data.vatTaxAmount);
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
    treasuryVouchersArray.value[index].transactionNumberStatus = 1;
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

const handlePaymentMethod = (event) => {
    banksSelect.value[0] = [];
    bankAccountsSelect.value[0] = [];
    bankGlobal.value = undefined;
    bankAccountGlobal.value = undefined;

    if (event.value === 4) {
        banksSelect.value[0] = [{ value: 0, label: 'SIN DATOS' }];
        bankGlobal.value = 0;

        bankAccountsSelect.value[0] = [{ value: 0, label: 'SIN DATOS' }];
        bankAccountGlobal.value = 0;
    }

    treasuryVouchersArray.value.forEach((voucher, index) => {
        voucher.paymentMethod = event.value;
        getBanks(event.value, index);
    });
}

const handleBanks = (event) => {
    treasuryVouchersArray.value.forEach((voucher, index) => {
        voucher.bankId = event.value;
        getBankAccounts(event.value, index);
    });
}

const handleBankAccounts = (event) => {
    treasuryVouchersArray.value.forEach((voucher, index) => {
        voucher.bankAccountId = event.value;
        handleTtransactionNumber(index);
    });
}

onMounted(async () => {
    treasuryVouchersArray.value = dialogRef.value.data.form.vouchers;
    totalPaymentAmount.value = dialogRef.value.data.form.totalPaymentAmount;
    await getPaymentMethods();

    const promises = treasuryVouchersArray.value.map((voucher, index) => calculateWithholdingTax(voucher, index));
    await Promise.all(promises);

    totalPaymentAmount.value = treasuryVouchersArray.value.reduce((total, voucher) => total + voucher.totalAmount, 0);

    loading.value = false;
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
                <template v-if="loading">
                    <Skeleton class="mb-2"></Skeleton>
                </template>
                <template v-if="!loading">
                    {{ data.businessName }}
                </template>
            </template>
        </Column>
        <Column field="amount" header="Importe" class="w-36">
            <template #body="{ data }">
                <template v-if="loading">
                    <Skeleton class="mb-2"></Skeleton>
                </template>
                <template v-if="!loading">
                    {{ currencyNumber(data.amount) }}
                </template>
            </template>
        </Column>
        <Column field="withholdings" header="Retenciones" class="w-2/12">
            <template #body="{ data, index }">
                <div class="flex space-x-2">
                    <div>
                        <template v-if="loading">
                            <Skeleton class="mb-2"></Skeleton>
                        </template>
                        <template v-if="!loading">
                            <FloatLabel>
                                <InputNumber v-model="data.withholdings.incomeTax" placeholder="$ 0,00" inputId="incomeTax" mode="currency"
                                    currency="ARS" locale="es-AR" id="incomeTax" inputClass="w-36" class="w-full"
                                    :class="data.withholdings.incomeTax !== null && data.withholdings.incomeTax !== undefined ? 'filled' : ''"
                                    :min="0" :max="99999999" :minFractionDigits="2" :invalid="data.withholdings.incomeTax === null"
                                    :disabled="data.withholdings.incomeTaxStatus === 0" @input="setTotalPaymentAmount($event, index, 'incomeTax')" />
                                <label for="incomeTax">Ganancias</label>
                            </FloatLabel>
                            <InputError :message="data.withholdings.incomeTax === null ? rules : ''" />
                        </template>
                    </div>
                    <div>
                        <template v-if="loading">
                            <Skeleton class="mb-2"></Skeleton>
                        </template>
                        <template v-if="!loading">
                            <FloatLabel>
                                <InputNumber v-model="data.withholdings.socialTax" placeholder="$ 0,00" inputId="socialTax" mode="currency"
                                    currency="ARS" locale="es-AR" id="socialTax" inputClass="w-36" class="w-full"
                                    :class="data.withholdings.socialTax !== null && data.withholdings.socialTax !== undefined ? 'filled' : ''"
                                    :min="0" :max="99999999" :minFractionDigits="2" :invalid="data.withholdings.socialTax === null"
                                    :disabled="data.withholdings.socialTaxStatus === 0" />
                                <label for="socialTax">Suss</label>
                            </FloatLabel>
                            <InputError :message="data.withholdings.socialTax === null ? rules : ''" />
                        </template>
                    </div>
                    <div>
                        <template v-if="loading">
                            <Skeleton class="mb-2"></Skeleton>
                        </template>
                        <template v-if="!loading">
                            <FloatLabel>
                                <InputNumber v-model="data.withholdings.vatTax" placeholder="$ 0,00" inputId="vatTax" mode="currency" currency="ARS"
                                    locale="es-AR" id="vatTax" inputClass="w-36" class="w-full"
                                    :class="data.withholdings.vatTax !== null && data.withholdings.vatTax !== undefined ? 'filled' : ''" :min="0"
                                    :max="99999999" :minFractionDigits="2" :invalid="data.withholdings.vatTax === null"
                                    :disabled="data.withholdings.vatTaxStatus === 0" />
                                <label for="vatTax">I.V.A.</label>
                            </FloatLabel>
                            <InputError :message="data.withholdings.vatTax === null ? rules : ''" />
                        </template>
                    </div>
                </div>
            </template>
        </Column>
        <Column field="totalAmount" header="Importe" class="w-36">
            <template #body="{ data }">
                <template v-if="loading">
                    <Skeleton class="mb-2"></Skeleton>
                </template>
                <template v-if="!loading">
                    {{ currencyNumber(data.totalAmount) }}
                </template>
            </template>
        </Column>
        <Column field="paymentMethod" header="Forma Pago" class="min-w-48">
            <template #body="{ data, field, index }">
                <template v-if="loading">
                    <Skeleton class="mb-2"></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <Dropdown inputId="paymentMethod" v-model="data[field]" :options="paymentMethodsSelect[index]" filter
                            class="!focus:border-primary-500 w-full" :class="dropdownClasses(data[field])" optionLabel="label" optionValue="value"
                            @change="getBanks(data[field], index)" />
                        <label for="bank">Forma Pago</label>
                    </FloatLabel>
                </template>
            </template>
        </Column>
        <Column field="bankId" header="Banco" class="min-w-72">
            <template #body="{ data, field, index }">
                <template v-if="loading">
                    <Skeleton class="mb-2"></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <Dropdown inputId="bank" v-model="data[field]" :options="banksSelect[index]" filter class="!focus:border-primary-500 w-full"
                            :class="dropdownClasses(data[field])" optionLabel="label" optionValue="value"
                            @change="getBankAccounts(data[field], index)" />
                        <label for="bank">Banco</label>
                    </FloatLabel>
                </template>
            </template>
        </Column>
        <Column field="bankAccountId" header="Cta. Bancaria" class="min-w-44">
            <template #body="{ data, field, index }">
                <template v-if="loading">
                    <Skeleton class="mb-2"></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <Dropdown inputId="bankAccount" v-model="data[field]" :options="bankAccountsSelect[index]" filter
                            class="!focus:border-primary-500 w-full" :class="dropdownClasses(data[field])" optionLabel="label" optionValue="value"
                            @change="handleTtransactionNumber(index)" />
                        <label for="bank">Cta. bancaria</label>
                    </FloatLabel>
                </template>
            </template>
        </Column>
        <Column field="transactionNumber" header="N° Operación" class="w-1/12">
            <template #body="{ data, field, index }">
                <template v-if="loading">
                    <Skeleton class="mb-2"></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <InputNumber v-model="data[field]" placeholder="12345678" :useGrouping="false" inputId="transactionNumber"
                            id="transactionNumber" class="w-full" :class="data[field] !== null && data[field] !== undefined ? 'filled' : ''" :min="1"
                            :max="9999999999999" :disabled="data.transactionNumberStatus === 0" :invalid="data[field] === null" />
                        <label for="rate">N° Operación</label>
                    </FloatLabel>
                    <InputError :message="data[field] === null ? rules : ''" />
                </template>
            </template>
        </Column>
        <Column field="paymentDate" header="F. pago" class="w-1/12">
            <template #body="{ data, field }">
                <template v-if="loading">
                    <Skeleton class="mb-2"></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <Calendar v-model="data[field]" placeholder="DD/MM/AAAA" showButtonBar id="paymentDate" class="w-full"
                            :class="data[field] !== null && data[field] !== undefined ? 'filled' : ''" :invalid="data[field] === null"
                            :maxDate="new Date()" />
                        <label for="paymentDate">F. Pago</label>
                    </FloatLabel>
                    <InputError :message="data[field] === null ? rules : ''" />
                </template>
            </template>
        </Column>
    </DataTable>

    <div class="flex mt-3 pb-0 items-center justify-between">
        <div class="flex w-fit space-x-4">
            <div class="w-fit text-left text-surface-900/60 font-bold">Total a Pagar: </div>
            <template v-if="loading">
                <div class="w-24">
                    <Skeleton class="mb-2"></Skeleton>
                </div>
            </template>
            <template v-if="!loading">
                <div class="w-fit text-left font-bold" :class="totalPaymentAmount < 0 ? 'text-red-500' : ''">
                    {{ currencyNumber(totalPaymentAmount) }}
                </div>
            </template>
        </div>

        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            <FloatLabel class="min-w-48 flex-1">
                <Dropdown inputId="paymentMethodGlobal" v-model="paymentMethodGlobal" :options="paymentMethodsSelect[0]" filter
                    class="!focus:border-primary-500 w-full" :class="dropdownClasses(paymentMethodGlobal)" inputClass="text-clip" optionLabel="label"
                    optionValue="value" @change="handlePaymentMethod($event)" />
                <label for="bank">Forma Pago</label>
            </FloatLabel>
            <FloatLabel class="min-w-72 flex-1">
                <Dropdown inputId="bankGlobal" v-model="bankGlobal" :options="banksSelect[0]" filter class="!focus:border-primary-500 w-full"
                    :class="dropdownClasses(bankGlobal)" inputClass="text-clip" optionLabel="label" optionValue="value"
                    @change="handleBanks($event)" />
                <label for="bank">Banco</label>
            </FloatLabel>
            <FloatLabel class="min-w-36 flex-1">
                <Dropdown inputId="bankAccountGlobal" v-model="bankAccountGlobal" :options="bankAccountsSelect[0]" filter
                    class="!focus:border-primary-500 w-full" :class="dropdownClasses(bankAccountGlobal)" inputClass="text-clip" optionLabel="label"
                    optionValue="value" @change="handleBankAccounts($event)" />
                <label for="bank">Cta. bancaria</label>
            </FloatLabel>
        </div>

        <Button label="Confirmar" icon="pi pi-save" iconPos="right" :disabled="totalPaymentAmount === 0 || isProcessing || isFormInvalid" />
    </div>
</template>