<script setup>
import { useForm } from "@inertiajs/vue3";
import { inject, onMounted, ref } from "vue";

const form = useForm({
    cuit: '',
    name: '',
    bussinessName: '',
    taxCondition: '',
});
const taxCondition = ref([]);

const dialogRef = inject("dialogRef");

onMounted(() => {
    taxCondition.value = dialogRef.value.data;
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
                        <div class="flex flex-column gap-3 border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 
                            flex-auto flex justify-center items-center font-medium">
                            <FloatLabel>
                                <InputMask id="cuit" v-model="form.cuit" mask="99-99999999-9" autocomplete="off"
                                    class="w-3/4 text-lg" />
                                <label for="cuit">CUIT</label>
                            </FloatLabel>

                            <FloatLabel>
                                <InputText id="name" v-model="form.name" autocomplete="off"
                                    class="w-full text-lg uppercase" />
                                <label for="name">Razón Social</label>
                            </FloatLabel>

                            <FloatLabel>
                                <InputText id="bussinessName" v-model="form.bussinessName" autocomplete="off"
                                    class="w-full text-lg uppercase" />
                                <label for="bussinessName">Nombre de fantasía</label>
                            </FloatLabel>

                            <FloatLabel>
                                <Dropdown inputId="taxCondition" v-model="form.taxCondition" :options="taxCondition" filter
                                    class="w-full text-lg" />
                                <label for="taxCondition">Condición tributaria</label>
                            </FloatLabel>
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
