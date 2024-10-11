<script setup>
import { ref, onMounted, inject, computed } from "vue";
import { currencyNumber } from "@/utils/formatterFunctions";
import { dropdownClasses } from '@/utils/cssUtils';
import { useForm } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
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
const paymentDateGlobal = ref();
const confirm = useConfirm();
const toast = useToast();
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

const calculateWithholdingTax = async (voucher, voucherBySupplier) => {
    const voucherIds = voucherBySupplier.map(v => v.voucherId).flat();

    try {
        const response = await axios.post(`/treasury-voucher/${voucher.id}/calculate-withholding-tax`, {
            voucherIds,
        });

        if (response.status !== 200) {
            throw new Error('Error al obtener el importe a retener');
        }

        const data = response.data;
        voucher.withholdings.incomeTax = 0;
        const incomeTaxWithholdingAmount = await treasuryVouchersArray.value
            .filter(voucher => voucherIds.includes(voucher.id))
            .reduce((total, voucher) => total + (voucher.withholdings.incomeTax || 0), 0);

        const incomeTaxAmount = data.incomeTaxWithholdingAmount - incomeTaxWithholdingAmount;
        voucher.withholdings.incomeTax = incomeTaxAmount > 0 ? incomeTaxAmount : 0;
        voucher.withholdings.socialTax = data.socialTaxAmount;
        voucher.withholdings.vatTax = data.vatTaxAmount;
        const totalAmount = voucher.amount - (voucher.withholdings.incomeTax + voucher.withholdings.socialTax + voucher.withholdings.vatTax);

        if (voucher.withholdings.incomeTax > totalAmount) {
            toast.add({
                severity: 'error',
                detail: 'El importe de ganancias a retener es mayor al importe a pagar',
                life: 5000,
            });
        }

        voucher.totalAmount = totalAmount;
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

        handleTransactionNumber(index);
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

const handleTransactionNumber = (index) => {
    treasuryVouchersArray.value[index].transactionNumber = undefined;
    treasuryVouchersArray.value[index].transactionNumberStatus = treasuryVouchersArray.value[index].paymentMethod === 4 ? 0 : 1;
}

const validateTransactionNumber = async (data, index) => {
    if (data.length > 15) return;

    const bankAccountId = treasuryVouchersArray.value[index].bankAccountId;
    const paymentMethod = treasuryVouchersArray.value[index].paymentMethod;
    const transactionNumber = data;

    try {
        const response = await fetch(`/treasury-vouchers/validate-transaction-number?bankAccountId=${bankAccountId}&paymentMethod=${paymentMethod}&transactionNumber=${transactionNumber}`);

        if (!response.ok) {
            throw new Error('Error al validar el número de operación');
        }

        const data = await response.json();
        if (data.transaction) {
            if (treasuryVouchersArray.value[index].paymentMethod === 2) {
                treasuryVouchersArray.value[index].transactionNumber = undefined;
            }

            toast.add({
                severity: 'error',
                detail: `El N° de operación ${transactionNumber} ya fue utilizado`,
                life: 3000,
            });
        }
    } catch (error) {
        console.error(error);
    }
}

const isFormInvalid = computed(() => {
    return treasuryVouchersArray.value.some(voucher => {
        if (voucher.totalAmount <= 0) return true;
        if (!voucher.paymentMethod) return true;
        if (!voucher.bankId && voucher.paymentMethod !== 4) return true;
        if (!voucher.bankAccountId && voucher.paymentMethod !== 4) return true;
        if (!voucher.transactionNumber && voucher.paymentMethod !== 4) return true;
        if (!voucher.paymentDate || voucher.paymentDate > new Date()) return true;

        return false;
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
        handleTransactionNumber(index);
    });
}

const handleBanks = (event) => {
    treasuryVouchersArray.value.forEach((voucher, index) => {
        voucher.bankId = event.value;
        getBankAccounts(event.value, index);
        handleTransactionNumber(index);
    });
}

const handleBankAccounts = (event) => {
    treasuryVouchersArray.value.forEach((voucher, index) => {
        voucher.bankAccountId = event.value;
        handleTransactionNumber(index);
    });
}

const handlePaymentDate = (newDate) => {
    treasuryVouchersArray.value.forEach((voucher) => {
        voucher.paymentDate = newDate;
    });
}

const confirmVouchers = (event) => {
    if (totalPaymentAmount.value === 0) {
        toast.add({
            severity: 'error',
            detail: 'Debe haber almenos un importe a pagar.',
            life: 3000,
        });

        return;
    }

    if (isFormInvalid.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });

        return;
    }

    confirm.require({
        target: event.currentTarget,
        message: '¿Está seguro de confirmar el pago de los comprobantes?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            const form = useForm({
                vouchers: treasuryVouchersArray.value,
                totalPaymentAmount: totalPaymentAmount.value,
            });

            form.put(route("treasury-vouchers.confirm"), {
                onSuccess: () => {
                    dialogRef.value.close('confirm');
                },
            });

        },
    });
};

onMounted(async () => {
    treasuryVouchersArray.value = dialogRef.value.data.form.vouchers;
    totalPaymentAmount.value = dialogRef.value.data.form.totalPaymentAmount;
    await getPaymentMethods();
    const voucherBySupplier = {};

    for (const voucher of treasuryVouchersArray.value) {
        if (!voucherBySupplier[voucher.supplierId]) {
            voucherBySupplier[voucher.supplierId] = {
                ...voucher,
                voucherId: [voucher.id],
                vouchersSupplierIds: voucher.vouchers.map(v => v.id),
            };

            const data = [voucherBySupplier[voucher.supplierId]];
            await calculateWithholdingTax(voucher, data); // Calculate withholding tax for each voucher as it's added
        } else {
            const vouchersSupplierIds = voucher.vouchers.map(v => v.id);
            const flatVouchersSupplierIds = voucherBySupplier[voucher.supplierId].vouchersSupplierIds.flat();

            if (!flatVouchersSupplierIds.some(v => vouchersSupplierIds.includes(v))) {
                voucherBySupplier[voucher.supplierId] = {
                    ...voucherBySupplier[voucher.supplierId],
                    voucherId: [...voucherBySupplier[voucher.supplierId].voucherId, voucher.id],
                    vouchersSupplierIds: [...voucherBySupplier[voucher.supplierId].vouchersSupplierIds, vouchersSupplierIds],
                };

                const data = [voucherBySupplier[voucher.supplierId]];
                await calculateWithholdingTax(voucher, data); // Calculate withholding tax for each voucher as it's added
            } else {
                voucher.withholdings.incomeTax = 0;
                voucher.withholdings.socialTax = 0;
                voucher.withholdings.vatTax = 0;
            }
        }
    }

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
    <DataTable :value="treasuryVouchersArray" scrollable scrollHeight="60vh" dataKey="id" filterDisplay="menu" :pt="{
        table: { style: 'min-width: 50rem' }, tbody: { class: 'thin-td' }, wrapper: { class: 'datatable-scrollbar' },
    }" class="data-table uppercase">
        <template #empty>
            Sin comprobantes seleccionados
        </template>
        <Column field="businessName" header="Proveedor" class="w-2/12">
            <template #body="{ data }">
                <template v-if="loading">
                    <Skeleton></Skeleton>
                </template>
                <template v-if="!loading">
                    {{ data.businessName }}
                </template>
            </template>
        </Column>
        <Column field="amount" header="Importe" class="w-36">
            <template #body="{ data }">
                <template v-if="loading">
                    <Skeleton></Skeleton>
                </template>
                <template v-if="!loading">
                    {{ currencyNumber(data.amount) }}
                </template>
            </template>
        </Column>
        <Column field="withholdings" header="Retenciones" class="w-2/12">
            <template #body="{ data, index }">
                <div class="flex space-x-2 relative !z-0">
                    <template v-if="loading">
                        <Skeleton></Skeleton>
                    </template>
                    <template v-if="!loading">
                        <div>
                            <FloatLabel>
                                <InputNumber v-model="data.withholdings.incomeTax" placeholder="$ 0,00" inputId="incomeTax" mode="currency"
                                    currency="ARS" locale="es-AR" id="incomeTax" inputClass="w-32" class="w-full"
                                    :class="data.withholdings.incomeTax !== null && data.withholdings.incomeTax !== undefined ? 'filled' : ''"
                                    :min="0" :max="99999999" :minFractionDigits="2" :invalid="data.withholdings.incomeTax === null"
                                    :disabled="data.withholdings.incomeTaxStatus === 0" @input="setTotalPaymentAmount($event, index, 'incomeTax')"
                                    :pt="{ input: { root: { autocomplete: 'off' } } }" />
                                <label for="incomeTax">Ganancias</label>
                            </FloatLabel>
                            <InputError :message="data.withholdings.incomeTax === null ? rules : ''" />
                        </div>
                    </template>

                    <template v-if="loading">
                        <Skeleton></Skeleton>
                    </template>
                    <template v-if="!loading">
                        <div>
                            <FloatLabel>
                                <InputNumber v-model="data.withholdings.socialTax" placeholder="$ 0,00" inputId="socialTax" mode="currency"
                                    currency="ARS" locale="es-AR" id="socialTax" inputClass="w-32" class="w-full"
                                    :class="data.withholdings.socialTax !== null && data.withholdings.socialTax !== undefined ? 'filled' : ''"
                                    :min="0" :max="99999999" :minFractionDigits="2" :invalid="data.withholdings.socialTax === null"
                                    :disabled="data.withholdings.socialTaxStatus === 0" :pt="{ input: { root: { autocomplete: 'off' } } }" />
                                <label for="socialTax">Suss</label>
                            </FloatLabel>
                            <InputError :message="data.withholdings.socialTax === null ? rules : ''" />
                        </div>
                    </template>

                    <template v-if="loading">
                        <Skeleton></Skeleton>
                    </template>
                    <template v-if="!loading">
                        <div>
                            <FloatLabel>
                                <InputNumber v-model="data.withholdings.vatTax" placeholder="$ 0,00" inputId="vatTax" mode="currency" currency="ARS"
                                    locale="es-AR" id="vatTax" inputClass="w-32" class="w-full"
                                    :class="data.withholdings.vatTax !== null && data.withholdings.vatTax !== undefined ? 'filled' : ''" :min="0"
                                    :max="99999999" :minFractionDigits="2" :invalid="data.withholdings.vatTax === null"
                                    :disabled="data.withholdings.vatTaxStatus === 0" :pt="{ input: { root: { autocomplete: 'off' } } }" />
                                <label for="vatTax">I.V.A.</label>
                            </FloatLabel>
                            <InputError :message="data.withholdings.vatTax === null ? rules : ''" />
                        </div>
                    </template>
                </div>
            </template>
        </Column>
        <Column field="totalAmount" header="A Pagar" class="min-w-24">
            <template #body="{ data }">
                <template v-if="loading">
                    <Skeleton></Skeleton>
                </template>
                <template v-if="!loading">
                    <span class="w-fit text-left font-bold" :class="data.totalAmount < 0 ? 'text-red-500' : ''">
                        {{ currencyNumber(data.totalAmount) }}
                    </span>
                </template>
            </template>
        </Column>
        <Column field="paymentMethod" header="Forma Pago" class="min-w-48">
            <template #body="{ data, field, index }">
                <template v-if="loading">
                    <Skeleton></Skeleton>
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
                    <Skeleton></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <Dropdown inputId="bank" v-model="data[field]" :options="banksSelect[index]" filter class="!focus:border-primary-500 w-full"
                            :class="dropdownClasses(data[field])" optionLabel="label" optionValue="value"
                            @change="getBankAccounts(data[field], index)" :disabled="data[field] === 0" />
                        <label for="bank">Banco</label>
                    </FloatLabel>
                </template>
            </template>
        </Column>
        <Column field="bankAccountId" header="Cta. Bancaria" class="min-w-44">
            <template #body="{ data, field, index }">
                <template v-if="loading">
                    <Skeleton></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <Dropdown inputId="bankAccount" v-model="data[field]" :options="bankAccountsSelect[index]" filter
                            class="!focus:border-primary-500 w-full" :class="dropdownClasses(data[field])" optionLabel="label" optionValue="value"
                            @change="handleTransactionNumber(index)" :disabled="data[field] === 0" />
                        <label for="bank">Cta. bancaria</label>
                    </FloatLabel>
                </template>
            </template>
        </Column>
        <Column field="transactionNumber" header="N° Operación" class="w-1/12">
            <template #body="{ data, field, index }">
                <template v-if="loading">
                    <Skeleton></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <InputText v-model="data[field]" :useGrouping="false" inputId="transactionNumber" id="transactionNumber"
                            class="w-full uppercase" :minlength="1" :maxlength="15" :disabled="data.transactionNumberStatus === 0"
                            :invalid="data[field] === null" :pt="{ input: { root: { autocomplete: 'off' } } }"
                            @blur="validateTransactionNumber(data[field], index)" />
                        <label for="rate">N° Operación</label>
                    </FloatLabel>
                    <InputError :message="data[field] === null && data.transactionNumberStatus === 1 ? rules : ''" />
                </template>
            </template>
        </Column>
        <Column field="paymentDate" header="F. pago" class="min-w-36">
            <template #body="{ data, field }">
                <template v-if="loading">
                    <Skeleton></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <Calendar v-model="data[field]" placeholder="DD/MM/AAAA" showButtonBar id="paymentDate" inputClass="w-full" class="w-full"
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
                    <Skeleton class="mt-1"></Skeleton>
                </div>
            </template>
            <template v-if="!loading">
                <span class="w-fit text-left font-bold" :class="totalPaymentAmount < 0 ? 'text-red-500' : ''">
                    {{ currencyNumber(totalPaymentAmount) }}
                </span>
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
                    :class="dropdownClasses(bankGlobal)" inputClass="text-clip" optionLabel="label" optionValue="value" @change="handleBanks($event)"
                    :disabled="bankGlobal === 0" />
                <label for="bank">Banco</label>
            </FloatLabel>
            <FloatLabel class="min-w-36 flex-1">
                <Dropdown inputId="bankAccountGlobal" v-model="bankAccountGlobal" :options="bankAccountsSelect[0]" filter
                    class="!focus:border-primary-500 w-full" :class="dropdownClasses(bankAccountGlobal)" inputClass="text-clip" optionLabel="label"
                    optionValue="value" @change="handleBankAccounts($event)" :disabled="bankGlobal === 0" />
                <label for="bank">Cta. bancaria</label>
            </FloatLabel>
            <FloatLabel class="min-w-36 flex-1 bottom-0.5">
                <Calendar v-model="paymentDateGlobal" placeholder="DD/MM/AAAA" showButtonBar id="paymentDateGlobal" inputClass="w-full" class="w-full"
                    :class="paymentDateGlobal !== null && paymentDateGlobal !== undefined ? 'filled' : ''" :maxDate="new Date()"
                    @update:model-value="handlePaymentDate" />
                <label for="paymentDateGlobal">F. Pago</label>
            </FloatLabel>
        </div>

        <ConfirmPopup></ConfirmPopup>
        <Button label="Confirmar" icon="pi pi-save" iconPos="right" :disabled="totalPaymentAmount === 0 || isProcessing || isFormInvalid"
            @click="confirmVouchers($event)" />
    </div>
</template>