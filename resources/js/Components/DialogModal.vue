<script setup>
import { ref } from "vue";

const visible = ref(true);
</script>
<template>
    <div class="flex flex-column gap-3">
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
    </div>
</template>
<style>
div[data-pc-section="header"] {
    padding-bottom: 0px;
}
</style>
