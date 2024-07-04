<script setup>
import { useForm } from "@inertiajs/vue3";
import { computed, inject, onMounted, ref } from "vue";
import { dropdownClasses, dropdownLabelClasses } from '@/utils/cssUtils';
import { states } from '@/utils/apis';
import { watch } from "vue";

const form = useForm({
    cuit: '',
    name: '',
    bussinessName: '',
    state: '',
    street: '',
    streetNumber: '',
    email: '',
    taxCondition: '',
});

const statesArray = ref([]);
const taxCondition = ref([]);

const dialogRef = inject("dialogRef");

onMounted(async () => {
    taxCondition.value = dialogRef.value.data;
    try {
        statesArray.value = await states();
    } catch (error) {
        console.error(error);
    }
});
</script>
<template>
    <!-- <div class="flex flex-column gap-3">
        <div class="flex align-items-center gap-3 mb-2 w-1/8">
            <FloatLabel class="w-full">
                <InputMask id="username" mask="99-99999999-9" />
                <label for="username">CUIT</label>
            </FloatLabel>
        </div>
        <div class="flex align-items-center gap-3 mb-2 w-1/8">
            <FloatLabel class="w-full">
                <InputText id="username" />
                <label for="username">Razón social</label>
            </FloatLabel>
        </div>
    </div> -->
    <div class="card flex justify-center">
        <Stepper linear>
            <StepperPanel header="Header I">
                <template #content="{ nextCallback }">
                    <div class="flex flex-col h-[12rem]">
                        <div class="flex flex-col border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 
                            justify-center font-medium">
                            <div class="flex w-5/5 gap-3 m-3">
                                <FloatLabel class="w-full md:w-1/5">
                                    <InputMask id="cuit" v-model="form.cuit" mask="99-99999999-9" autocomplete="off"
                                        class="w-full" />
                                    <label for="cuit">CUIT</label>
                                </FloatLabel>

                                <FloatLabel class="w-full md:w-2/5">
                                    <InputText id="name" v-model="form.name" autocomplete="off"
                                        class="w-full uppercase" />
                                    <label for="name">Razón Social</label>
                                </FloatLabel>

                                <FloatLabel class="w-full md:w-2/5">
                                    <InputText id="bussinessName" v-model="form.bussinessName" autocomplete="off"
                                        class="w-full uppercase" />
                                    <label for="bussinessName">Nombre de fantasía</label>
                                </FloatLabel>
                            </div>

                            <div class="flex w-5/5 gap-3 m-3">
                                <FloatLabel class="w-full md:w-2/6">
                                    <Dropdown inputId="state" v-model="form.state" :options="statesArray" filter
                                        class="w-full uppercase" :class="dropdownClasses(form.state)" optionLabel="nombre"
                                        optionValue="id" />
                                    <label for="state" :class="dropdownLabelClasses(form.state)">
                                        Provincia</label>
                                </FloatLabel>
                            </div>
                            <!-- <FloatLabel class="w-2/6">
                                    <InputMask id="phone" v-model="form.phone" mask="(999) 9999-9999" autocomplete="off"
                                        class="w-full" />
                                    <label for="phone">Telefono</label>
                                </FloatLabel> -->

                            <!-- <FloatLabel class="w-2/6">
                                    <InputText id="email" v-model="form.email" autocomplete="off"
                                        class="w-full uppercase" />
                                    <label for="email">Email</label>
                                </FloatLabel> -->

                            <!-- <FloatLabel class="w-2/6 !top-[1px]">
                                    <Dropdown inputId="taxCondition" v-model="form.taxCondition" :options="taxCondition"
                                        filter class="w-full" :class="dropdownClasses(form.taxCondition)"
                                        optionLabel="name" optionValue="id" />
                                    <template #option="slotProps">
                                        <Tag :value="slotProps.option.name" class="bg-transparent uppercase" />
                                    </template>
<label for="taxCondition" :class="dropdownLabelClasses(form.taxCondition)">Condición
    tributaria</label>
</FloatLabel> -->
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
