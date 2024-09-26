<script setup>
import { ref, onMounted, inject, computed } from "vue";
import { currencyNumber } from "@/utils/formatterFunctions";
import { dropdownClasses } from '@/utils/cssUtils';
import { useForm } from "@inertiajs/vue3";
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import InputError from '@/Components/InputError.vue';

const treasuryVouchersArray = ref([]);
const totalIncomeAmount = ref(0);
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
    if (totalIncomeAmount.value === 0) {
        toast.add({
            severity: 'error',
            detail: 'Debe haber almenos un importe a ingresaro.',
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
        message: '¿Está seguro de confirmar el ingresos de los comprobantes?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            const form = useForm({
                vouchers: treasuryVouchersArray.value,
                totalIncomeAmount: totalIncomeAmount.value,
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
    totalIncomeAmount.value = dialogRef.value.data.form.totalIncomeAmount;
    await getPaymentMethods();
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
        <Column field="paymentMethod" header="Forma Ingreso" class="min-w-48">
            <template #body="{ data, field, index }">
                <template v-if="loading">
                    <Skeleton></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <Dropdown inputId="paymentMethod" v-model="data[field]" :options="paymentMethodsSelect[index]" filter
                            class="!focus:border-primary-500 w-full" :class="dropdownClasses(data[field])" optionLabel="label" optionValue="value"
                            @change="getBanks(data[field], index)" />
                        <label for="bank">Forma Ingreso</label>
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
                        <InputNumber v-model="data[field]" placeholder="12345678" :useGrouping="false" inputId="transactionNumber"
                            id="transactionNumber" class="w-full" :class="data[field] !== null && data[field] !== undefined ? 'filled' : ''" :min="1"
                            :max="9999999999999" :disabled="data.transactionNumberStatus === 0" :invalid="data[field] === null"
                            :pt="{ input: { root: { autocomplete: 'off' } } }" />
                        <label for="rate">N° Operación</label>
                    </FloatLabel>
                    <InputError :message="data[field] === null && data.transactionNumberStatus === 1 ? rules : ''" />
                </template>
            </template>
        </Column>
        <Column field="paymentDate" header="F. Ingreso" class="min-w-36">
            <template #body="{ data, field }">
                <template v-if="loading">
                    <Skeleton></Skeleton>
                </template>
                <template v-if="!loading">
                    <FloatLabel>
                        <Calendar v-model="data[field]" placeholder="DD/MM/AAAA" showButtonBar id="paymentDate" inputClass="w-full" class="w-full"
                            :class="data[field] !== null && data[field] !== undefined ? 'filled' : ''" :invalid="data[field] === null"
                            :maxDate="new Date()" />
                        <label for="paymentDate">F. Ingreso</label>
                    </FloatLabel>
                    <InputError :message="data[field] === null ? rules : ''" />
                </template>
            </template>
        </Column>
    </DataTable>

    <div class="flex mt-3 pb-0 items-center justify-between">
        <div class="flex w-fit space-x-4">
            <div class="w-fit text-left text-surface-900/60 font-bold">Total a Ingresar: </div>
            <template v-if="loading">
                <div class="w-24">
                    <Skeleton class="mt-1"></Skeleton>
                </div>
            </template>
            <template v-if="!loading">
                <span class="w-fit text-left font-bold" :class="totalIncomeAmount < 0 ? 'text-red-500' : ''">
                    {{ currencyNumber(totalIncomeAmount) }}
                </span>
            </template>
        </div>

        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            <FloatLabel class="min-w-48 flex-1">
                <Dropdown inputId="paymentMethodGlobal" v-model="paymentMethodGlobal" :options="paymentMethodsSelect[0]" filter
                    class="!focus:border-primary-500 w-full" :class="dropdownClasses(paymentMethodGlobal)" inputClass="text-clip" optionLabel="label"
                    optionValue="value" @change="handlePaymentMethod($event)" />
                <label for="bank">Forma Ingreso</label>
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
        <Button label="Confirmar" icon="pi pi-save" iconPos="right" :disabled="totalIncomeAmount === 0 || isProcessing || isFormInvalid"
            @click="confirmVouchers($event)" />
    </div>
</template>