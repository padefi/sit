<script setup>
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, ref } from "vue";
import { dropdownClasses, dropdownLabelClasses } from '@/utils/cssUtils';
import { nominatim } from '@/utils/apis';
import { validatePhoneNumber, validateEmail, cuitValidator } from '@/utils/validateFunctions';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    cuit: '',
    name: '',
    bussinessName: '',
    address: {},
    phone: '',
    email: '',
    taxCondition: '',
});

const rules = 'Debe completar el campo'
const addressArray = ref([]);
const selectedAddress = ref('');
const autoCompleteAddress = ref(null);
const taxCondition = ref([]);

const search = async (event) => {
    addressArray.value = await nominatim(event.query);
}

const selectData = (e) => {
    selectedAddress.value = e.value.display_name;

    form.address = {
        display_name: e.value.display_name,
        street: e.value.address?.road || '',
        number: e.value.address?.house_number || '',
        city: e.value.address?.city || e.value.address?.town || '',
        state: e.value.address?.state || '',
        postal_code: e.value.address?.postcode || '',
        latitude: e.value.lat,
        longitude: e.value.lon,
        osm_ids: e.value.osm_id,
    }
}

const cleanIfEmpty = () => {
    setTimeout(() => {
        if (selectedAddress.value === '') {
            document.querySelector('#address').value = '';
        }
    }, 200)
}

const dialogRef = inject("dialogRef");

onMounted(async () => {
    taxCondition.value = dialogRef.value.data;
});
</script>
<template>
    <div class="card flex justify-center">
        <Stepper linear>
            <StepperPanel header="Header I">
                <template #content="{ nextCallback }">
                    <div class="flex flex-col h-[12rem]">
                        <div class="flex flex-col border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 
                            justify-center font-medium">
                            <div class="flex w-5/5 gap-3 m-3">
                                <div class="w-full md:w-1/5">
                                    <FloatLabel>
                                        <InputMask id="cuit" v-model="form.cuit" mask="99-99999999-9" autocomplete="off"
                                            class="w-full"
                                            :invalid="!form.cuit || form.cuit.trim() === '' || !cuitValidator(form.cuit)" />
                                        <label for="cuit">CUIT</label>
                                    </FloatLabel>
                                    <InputError :message="!form.cuit || form.cuit.trim() === '' ? rules : cuitValidator(form.cuit) ? '' : 'Cuit invalida'" />
                                </div>

                                <div class="w-full md:w-2/5">
                                    <FloatLabel>
                                        <InputText id="name" v-model="form.name" autocomplete="off"
                                            class="w-full uppercase" :invalid="!form.name || form.name.trim() === ''" />
                                        <label for="name">Razón Social</label>
                                    </FloatLabel>
                                    <InputError :message="!form.name || form.name.trim() === '' ? rules : ''" />
                                </div>

                                <div class="w-full md:w-2/5">
                                    <FloatLabel>
                                        <InputText id="bussinessName" v-model="form.bussinessName" autocomplete="off"
                                            class="w-full uppercase" :invalid="!form.bussinessName || form.bussinessName.trim() === ''" />
                                        <label for="bussinessName">Nombre de fantasía</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="!form.bussinessName || form.bussinessName.trim() === '' ? rules : ''" />
                                </div>
                            </div>

                            <div class="flex w-5/5 gap-3 m-3">
                                <div class="w-full md:w-4/6">
                                    <FloatLabel>
                                        <AutoComplete v-model="form.address.display_name" inputId="address"
                                            ref="autoCompleteAddress" :suggestions="addressArray" @complete="search"
                                            @item-select="selectData" @blur="cleanIfEmpty()" class="w-full uppercase">
                                            <template #option="slotProps">
                                                <div class="flex items-center country-item">
                                                    <div>{{ slotProps.option.display_name }}</div>
                                                </div>
                                            </template>
                                        </AutoComplete>
                                        <label for="address"
                                            :class="dropdownLabelClasses(selectedAddress)">Domicilio</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="!selectedAddress || selectedAddress.trim() === '' ? rules : ''" />
                                </div>

                                <div class="w-full md:w-1/6">
                                    <FloatLabel>
                                        <InputText :class="'uppercase'" v-model="form.email" id="email"
                                            autocomplete="off" maxlength="100" class="w-full"
                                            :invalid="!form.email || form.email.trim() === '' || !validateEmail(form.email)" />
                                        <label for="email">Email</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="!form.email || form.email.trim() === '' ? rules : validateEmail(form.email) ? '' : 'Dirección de mail invalida'" />
                                </div>

                                <div class="w-full md:w-1/6">
                                    <FloatLabel>
                                        <InputText :class="'uppercase'" v-model="form.phone" id="phone"
                                            autocomplete="off" maxlength="15" class="w-full"
                                            :invalid="!form.phone || form.phone.trim() === '' || !validatePhoneNumber(form.phone)"
                                            onkeypress='return event.keyCode >= 47 && event.keyCode <= 57 || event.keyCode === 45' />
                                        <label for="phone">Teléfono</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="!form.phone || form.phone.trim() === '' ? rules : validatePhoneNumber(form.phone) ? '' : rules" />
                                </div>
                                <!-- <FloatLabel class="w-2/6 !top-[1px]">
                                    <Dropdown inputId="taxCondition" v-model="form.taxCondition" :options="taxCondition"
                                        filter class="!focus:border-primary-500 w-full"
                                        :class="dropdownClasses(form.taxCondition)" optionLabel="name"
                                        optionValue="id" />
                                    <template #option="slotProps">
                                        <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                                    </template>
                                    <label for="taxCondition" :class="dropdownLabelClasses(form.taxCondition)">Condición
                                        tributaria</label>
                                </FloatLabel> -->
                            </div>
                        </div>
                    </div>
                    <div class="flex pt-4 justify-end">
                        <Button label="Next" icon="pi pi-arrow-right" iconPos="right" @click="nextCallback" />
                    </div>
                </template>
            </StepperPanel>
            <StepperPanel header="Header II">
                <template #content="{ prevCallback, nextCallback }">
                    <div class="flex flex-col h-[12rem]">
                        <div
                            class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 flex-auto flex justify-center items-center font-medium">
                            Content II</div>
                    </div>
                    <div class="flex pt-4 justify-between">
                        <Button label="Back" severity="secondary" icon="pi pi-arrow-left" @click="prevCallback" />
                        <Button label="Next" icon="pi pi-arrow-right" iconPos="right" @click="nextCallback" />
                    </div>
                </template>
            </StepperPanel>
            <StepperPanel header="Header III">
                <template #content="{ prevCallback }">
                    <div class="flex flex-col h-[12rem]">
                        <div
                            class="border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 flex-auto flex justify-center items-center font-medium">
                            Content III</div>
                    </div>
                    <div class="flex pt-4 justify-start">
                        <Button label="Back" severity="secondary" icon="pi pi-arrow-left" @click="prevCallback" />
                    </div>
                </template>
            </StepperPanel>
        </Stepper>
    </div>
</template>
<style>
div[data-pc-section="header"] {
    padding-bottom: 0px;
}
</style>
