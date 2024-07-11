<script setup>
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, ref, computed } from "vue";
import { dropdownClasses, dropdownLabelClasses } from '@/utils/cssUtils';
import { nominatim, nominatimOsmId } from '@/utils/apis';
import { validatePhoneNumber, validateEmail, cuitValidator, validateCBU } from '@/utils/validateFunctions';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    cuit: null,
    cuitDisplay: null,
    name: null,
    businessName: null,
    address: {},
    phone: null,
    email: null,
    cbu: null,
    idVC: null,
    idCat: null,
    incomeTax: false,
    socialTax: false,
    vatTax: false,
    notes: null,
});

const rules = 'Debe completar el campo'
const addressArray = ref([]);
const selectedAddress = ref(null);
const invalidAddress = ref(false);
const autoCompleteAddress = ref(null);
const vatConditions = ref([]);
const categories = ref([]);

const search = async (event) => {
    addressArray.value = await nominatim(event.query);
}

const selectData = async (e) => {
    selectedAddress.value = e.value.display_name;

    form.address = {
        display_name: e.value.display_name,
        street: e.value.address?.road || '',
        streetNumber: parseInt(e.value.address?.house_number) || '',
        floor: e.value.address?.floor || '',
        apartment: e.value.address?.apartment || '',
        city: e.value.address?.city || e.value.address?.town || '',
        state: e.value.address?.state || '',
        country: e.value.address?.country || '',
        postalCode: e.value.address?.postcode || '',
        latitude: e.value.lat,
        longitude: e.value.lon,
        osm_id: e.value.osm_id,
    }
}

const cleanIfEmpty = () => {
    setTimeout(() => {
        if (selectedAddress.value === '' || !selectedAddress.value || !form.address.display_name || form.address.display_name === '') {
            document.querySelector('#address').value = '';
            form.address = {};
            invalidAddress.value = true;
        } else {
            invalidAddress.value = false;
        }
    }, 200)
}

const isFirstInvalid = computed(() => {
    if (!form.cuitDisplay || !cuitValidator(form.cuitDisplay)) return true;
    if (!form.name || form.name.trim() === '') return true;
    if (!form.businessName || form.businessName.trim() === '') return true;
    if (!form.address.display_name && !selectedAddress.value) return true;
    if (form.email && !validateEmail(form.email)) return true;
    if (form.phone && !validatePhoneNumber(form.phone)) return true;
    if (form.cbu && !validateCBU(form.cbu)) return true;

    form.cuit = parseInt(form.cuitDisplay.replace(/-/g, ''));

    return false;
});

const handleFirst = (nextCallback) => {
    if (!isFirstInvalid.value) {
        nextCallback();
    }
};

const isSecondInvalid = computed(() => {
    if (!form.idVC) return true;
    if (!form.idCat) return true;

    return false;
});

const handleSecond = (nextCallback) => {
    if (!isSecondInvalid.value) {
        nextCallback();
    }
};

const saveSupplier = () => {
    form.post(route("suppliers.store"), {
        // preserveScroll: true,
        onSuccess: () => {
            dialogRef.value.close();
        },
    });
};

const dialogRef = inject("dialogRef");

onMounted(async () => {
    vatConditions.value = dialogRef.value.data.vatConditions;
    categories.value = dialogRef.value.data.categories;
});
</script>
<template>
    <div class="card flex justify-center">
        <Stepper linear>
            <StepperPanel header="Datos del proveedor">
                <template #content="{ nextCallback }">
                    <div class="flex flex-col">
                        <div class="flex flex-col border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 
                            justify-center font-medium">
                            <div class="flex w-5/5 gap-3 m-3">
                                <div class="w-full md:w-1/5">
                                    <FloatLabel>
                                        <InputMask id="cuit" v-model="form.cuitDisplay" mask="99-99999999-9"
                                            autocomplete="off" class="w-full"
                                            :invalid="form.cuitDisplay && !cuitValidator(form.cuitDisplay)" />
                                        <label for="cuit">CUIT</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="form.cuitDisplay === '' ? rules : form.cuitDisplay && !cuitValidator(form.cuitDisplay) ? 'Cuit invalido' : ''" />
                                </div>

                                <div class="w-full md:w-2/5">
                                    <FloatLabel>
                                        <InputText id="name" v-model="form.name" autocomplete="off"
                                            class="w-full uppercase"
                                            :invalid="form.name && (form.name.trim() === '' || form.name === '')" />
                                        <label for="name">Razón Social</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="form.name && form.name.trim() === '' || form.name === '' ? rules : ''" />
                                </div>

                                <div class="w-full md:w-2/5">
                                    <FloatLabel>
                                        <InputText id="businessName" v-model="form.businessName" autocomplete="off"
                                            class="w-full uppercase"
                                            :invalid="form.businessName && (form.businessName.trim() === '' || form.businessName === '')" />
                                        <label for="businessName">Nombre de fantasía</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="form.businessName && form.businessName.trim() === '' || form.businessName === '' ? rules : ''" />
                                </div>
                            </div>

                            <div class="flex w-5/5 gap-3 m-3">
                                <div class="w-full md:w-[88%]">
                                    <FloatLabel>
                                        <AutoComplete v-model="form.address.display_name" inputId="address"
                                            ref="autoCompleteAddress" :suggestions="addressArray" @complete="search"
                                            @item-select="selectData" @blur="cleanIfEmpty()" class="w-full uppercase"
                                            :invalid="invalidAddress"
                                            :class="dropdownClasses(form.address.display_name)">
                                            <template #option="slotProps">
                                                <div class="flex items-center">
                                                    <div>{{ slotProps.option.display_name }}</div>
                                                </div>
                                            </template>
                                        </AutoComplete>
                                        <label for="address">Domicilio</label>
                                    </FloatLabel>
                                    <InputError :message="invalidAddress ? rules : ''" />
                                </div>

                                <div class="w-full md:w-[6%]">
                                    <FloatLabel>
                                        <InputText id="floor" v-model="form.address.floor" autocomplete="off"
                                            class="w-full uppercase" maxlength="2" />
                                        <label for="floor">Piso</label>
                                    </FloatLabel>
                                </div>

                                <div class="w-full md:w-[6%]">
                                    <FloatLabel>
                                        <InputText id="apartment" v-model="form.address.apartment" autocomplete="off"
                                            class="w-full uppercase" maxlength="2" />
                                        <label for="apartment">Dto</label>
                                    </FloatLabel>
                                </div>
                            </div>

                            <div class="flex w-5/5 gap-3 m-3">
                                <div class="w-full md:w-2/5">
                                    <FloatLabel>
                                        <InputText :class="'uppercase'" v-model="form.email" id="email"
                                            autocomplete="off" maxlength="100" class="w-full"
                                            :invalid="form.email && (form.email.trim() === '' || form.email === '') || form.email && !validateEmail(form.email)" />
                                        <label for="email">Email</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="form.email && form.email.trim() === '' || form.email === '' ? rules : form.email && !validateEmail(form.email) ? 'Dirección de mail invalida' : ''" />
                                </div>

                                <div class="w-full md:w-1/5">
                                    <FloatLabel>
                                        <InputText :class="'uppercase'" v-model="form.phone" id="phone"
                                            autocomplete="off" maxlength="13" class="w-full"
                                            :invalid="form.phone && (form.phone.trim() === '' || form.phone === '') || form.phone && !validatePhoneNumber(form.phone)"
                                            onkeypress='return event.keyCode >= 47 && event.keyCode <= 57 || event.keyCode === 45' />
                                        <label for="phone">Teléfono</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="form.phone && form.phone.trim() === '' || form.phone === '' ? rules : form.phone && !validatePhoneNumber(form.phone) ? rules : ''" />
                                </div>

                                <div class="w-full md:w-2/5">
                                    <FloatLabel>
                                        <InputText :class="'uppercase'" v-model="form.cbu" id="cbu" autocomplete="off"
                                            :minlength="22" :maxlength="22" class="w-full"
                                            :invalid="form.cbu && (form.cbu.trim() === '' || form.cbu === '') || form.cbu && !validateCBU(form.cbu)"
                                            onkeypress='return event.keyCode >= 47 && event.keyCode <= 57 || event.keyCode === 45' />
                                        <label for="cbu">CBU</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="form.cbu && form.cbu.trim() === '' || form.cbu === '' ? rules : form.cbu && !validateCBU(form.cbu) ? 'CBU invalido' : ''" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex pt-4 justify-end">
                        <Button label="Siguiente" icon="pi pi-arrow-right" iconPos="right" :disabled="isFirstInvalid"
                            @click="() => handleFirst(nextCallback)" />
                    </div>
                </template>
            </StepperPanel>
            <StepperPanel header="Datos fiscales">
                <template #content="{ prevCallback, nextCallback }">
                    <div class="flex flex-col">
                        <div class="flex flex-col border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 
                            justify-center font-medium">
                            <div class="flex w-5/5 gap-3 m-3">
                                <FloatLabel class="w-3/6 !top-[1px]">
                                    <Dropdown inputId="vatCondition" v-model="form.idVC" :options="vatConditions" filter
                                        class="!focus:border-primary-500 w-full" :class="dropdownClasses(form.idVC)"
                                        optionLabel="name" optionValue="id" />
                                    <template #option="slotProps">
                                        <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                                    </template>
                                    <label for="vatCondition">Condición de I.V.A.</label>
                                </FloatLabel>

                                <FloatLabel class="w-3/6 !top-[1px]">
                                    <Dropdown inputId="category" v-model="form.idCat" :options="categories" filter
                                        class="!focus:border-primary-500 w-full" :class="dropdownClasses(form.idCat)"
                                        optionLabel="name" optionValue="id" />
                                    <template #option="slotProps">
                                    </template>
                                    <label for="category">Rubro</label>
                                </FloatLabel>
                            </div>

                            <div class="flex w-5/5 gap-3 m-3">
                                <div class="flex flex-col align-items-center w-3/6">
                                    <div class="w-full text-center font-bold bottom-2 relative">Retenciones</div>
                                    <div class="flex w-full">
                                        <div class="flex align-items-center w-1/3">
                                            <Checkbox v-model="form.incomeTax" inputId="incomeTax" name="tax"
                                                :binary="true" />
                                            <label for="incomeTax" class="ml-2">Gcias.</label>
                                        </div>

                                        <div class="flex align-items-center w-1/3">
                                            <Checkbox v-model="form.socialSecurityTax" inputId="socialSecurityTax"
                                                name="tax" :binary="true" />
                                            <label for="socialSecurityTax" class="ml-2">Suss</label>
                                        </div>

                                        <div class="flex align-items-center w-1/3">
                                            <Checkbox v-model="form.vatTax" inputId="vatTax" name="tax"
                                                :binary="true" />
                                            <label for="vatTax" class="ml-2">I.V.A.</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex pt-4 justify-between">
                        <Button label="Back" severity="secondary" icon="pi pi-arrow-left" @click="prevCallback" />
                        <Button label="Siguiente" icon="pi pi-arrow-right" iconPos="right" :disabled="isSecondInvalid"
                            @click="() => handleSecond(nextCallback)" />
                    </div>
                </template>
            </StepperPanel>
            <StepperPanel header="Resumen">
                <template #content="{ prevCallback }">
                    <div class="flex flex-col">
                        <div class="flex flex-col border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 
                            justify-center font-medium">
                            <Divider align="center" type="solid" class="text-lg text-primary-700 !mt-2 mb-0">
                                <b>Datos del proveedor</b>
                            </Divider>

                            <div class="flex w-5/5 gap-3 mx-3 my-0">
                                <div class="w-full md:w-1/5">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">Cuit</div>
                                    <div>{{ form.cuitDisplay }}</div>
                                </div>

                                <div class="w-full md:w-2/5">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">Razón Social
                                    </div>
                                    <div class="uppercase">{{ form.name }}</div>
                                </div>

                                <div class="w-full md:w-2/5">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">Nombre de
                                        fantasía</div>
                                    <div class="uppercase">{{ form.businessName }}</div>
                                </div>
                            </div>

                            <Divider class="mt-3 mb-0" type="dashed" />

                            <div class="flex w-5/5 gap-3 m-3 mb-0">
                                <div class="w-full md:w-1/5">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">País</div>
                                    <div class="uppercase">{{ form.address.country }}</div>
                                </div>

                                <div class="w-full md:w-1/5">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">Provincia</div>
                                    <div class="uppercase">{{ form.address.state }}</div>
                                </div>

                                <div class="w-full md:w-1/5">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">Ciudad</div>
                                    <div class="uppercase">{{ form.address.city }}</div>
                                </div>

                                <div class="w-full md:w-2/5">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">Calle</div>
                                    <div class="uppercase">
                                        {{ form.address.street }}
                                        {{ form.address.streetNumber }} -
                                        {{ form.address.postalCode }}
                                    </div>
                                </div>
                            </div>

                            <Divider class="mt-3 mb-0" type="dashed" />

                            <div class="flex w-5/5 gap-3 m-3 mb-0">
                                <div class="w-full md:w-2/5">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">Email</div>
                                    <div class="uppercase" :class="{ 'text-red-500': !form.email }">
                                        {{ (form.email) ? form.email : 'Sin email' }}
                                    </div>
                                </div>

                                <div class="w-full md:w-1/5">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">Teléfono</div>
                                    <div class="uppercase" :class="{ 'text-red-500': !form.phone }">
                                        {{ (form.phone) ? form.phone : 'Sin teléfono' }}
                                    </div>
                                </div>

                                <div class="w-full md:w-2/5">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">CBU</div>
                                    <div class="uppercase" :class="{ 'text-red-500': !form.cbu }">
                                        {{ (form.cbu) ? form.cbu : 'Sin CBU' }}
                                    </div>
                                </div>
                            </div>

                            <Divider align="center" type="solid" class="text-lg text-primary-700 !mt-2 mb-0">
                                <b>Datos fiscales</b>
                            </Divider>

                            <div class="flex w-5/5 gap-3 m-3 mb-0">
                                <div class="w-full md:w-3/6">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">
                                        Condición de I.V.A.
                                    </div>
                                    <div class="uppercase">
                                        {{ (form.idVC) && vatConditions.filter(v => v.id === form.idVC)[0].name }}
                                    </div>
                                </div>

                                <div class="w-full md:w-3/6">
                                    <div class="w-full text-sm text-left text-surface-900/60 font-bold">Rubro</div>
                                    <div class="uppercase">
                                        {{ (form.idCat) && categories.filter(c => c.id === form.idCat)[0].name }}
                                    </div>
                                </div>
                            </div>

                            <Divider class="mt-3 mb-0" type="dashed" />

                            <div class="flex w-2/2 gap-3 m-3">
                                <div class="flex w-full flex-col align-items-center">
                                    <div class="w-full text-center font-bold bottom-2 relative">Retenciones</div>
                                    <div class="flex w-full">
                                        <div class="flex align-items-center w-1/3">
                                            <div class="w-full">
                                                <div class="w-full text-sm text-surface-900/60 font-bold">
                                                    Gcias.
                                                </div>
                                                <div class="uppercase" :class="{ 'text-red-500': !form.incomeTax }">
                                                    {{ (form.incomeTax) ? 'SI' : 'NO' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex align-items-center w-1/3">
                                            <div class="w-full">
                                                <div class="w-full text-sm text-surface-900/60 font-bold">
                                                    Suss
                                                </div>
                                                <div class="uppercase"
                                                    :class="{ 'text-red-500': !form.socialSecurityTax }">
                                                    {{ (form.socialSecurityTax) ? 'SI' : 'NO' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex align-items-center w-1/3">
                                            <div class="w-full">
                                                <div class="w-full text-sm text-surface-900/60 font-bold">
                                                    I.V.A.
                                                </div>
                                                <div class="uppercase" :class="{ 'text-red-500': !form.vatTax }">
                                                    {{ (form.vatTax) ? 'SI' : 'NO' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex pt-4 justify-between">
                        <Button label="Back" severity="secondary" icon="pi pi-arrow-left" @click="prevCallback" />
                        <Button label="Finalizar" icon="pi pi-save" iconPos="right"
                            :disabled="isFirstInvalid || isSecondInvalid" @click="() => saveSupplier()" />
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
