<script setup>
import { ref, onMounted, inject, watch } from "vue";
import { format, parse } from "@formkit/tempo"

const data = ref({
    userCreated: '',
    created_at: '',
    userUpdated: '',
    updated_at: '',
});
const dialogRef = inject("dialogRef");

onMounted(() => {
    if (dialogRef?.value?.data) {
        const { userCreated, created_at, userUpdated, updated_at } = dialogRef.value.data;

        data.value.userCreated = userCreated?.name + ' ' + userCreated?.surname;
        data.value.created_at = format(created_at, "DD/MM/YYYY HH:mm:ss", "es");
        data.value.userUpdated = userUpdated ? userUpdated?.name + ' ' + userUpdated?.surname : '----';
        data.value.updated_at = userUpdated ? format(updated_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00';
    }
});
</script>
<template>
    <DataTable :value="[data]" tableStyle="min-width: 50rem" class="data-table">
        <Column field="userCreated" header="Usuario carga"></Column>
        <Column field="created_at" header="Fecha carga"></Column>
        <Column field="userUpdated" header="Usuario modificación"></Column>
        <Column field="updated_at" header="Fecha modificación"></Column>
    </DataTable>
</template>