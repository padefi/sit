<script setup>
import { ref, onMounted, inject, watch } from "vue";
import { format } from "@formkit/tempo"

const data = ref({
    userCreated: null,
    created_at: null,
    userUpdated: null,
    updated_at: null,
    userConfirmed: null,
    confirmed_at: null,
    userVoided: null,
    voided_at: null
});
const dialogRef = inject("dialogRef");

onMounted(() => {
    if (dialogRef?.value?.data) {
        const { userCreated, created_at, userUpdated, updated_at, userConfirmed, confirmed_at, userVoided, voided_at } = dialogRef.value.data;

        data.value.userCreated = userCreated?.name + ' ' + userCreated?.surname;
        data.value.created_at = format(created_at, "DD/MM/YYYY HH:mm:ss", "es");
        data.value.userUpdated = userUpdated ? userUpdated?.name + ' ' + userUpdated?.surname : '----';
        data.value.updated_at = userUpdated ? format(updated_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00';
        data.value.userConfirmed = userConfirmed ? userConfirmed?.name + ' ' + userConfirmed?.surname : null;
        data.value.confirmed_at = userConfirmed ? format(confirmed_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00';
        data.value.userVoided = userVoided ? userVoided?.name + ' ' + userVoided?.surname : null;
        data.value.voided_at = userVoided ? format(voided_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00';
    }
});

/* watch(() => dialogRef.value.data, () => {
    console.log("watcher", dialogRef.value.data);
    if (dialogRef?.value?.data) {
        const { userCreated, created_at, userUpdated, updated_at } = dialogRef.value.data;

        data.value.userCreated = userCreated?.name + ' ' + userCreated?.surname;
        data.value.created_at = format(created_at, "DD/MM/YYYY HH:mm:ss", "es");
        data.value.userUpdated = userUpdated ? userUpdated?.name + ' ' + userUpdated?.surname : '----';
        data.value.updated_at = userUpdated ? format(updated_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00';
    }
}); */
</script>
<template>
    <DataTable :value="[data]" tableStyle="min-width: 50rem" class="data-table uppercase">
        <Column field="userCreated" header="Usuario carga"></Column>
        <Column field="created_at" header="Fecha carga"></Column>
        <Column :field="data.userConfirmed ?  'userConfirmed' : data.userVoided ? 'userVoided' : 'userUpdated'"
            :header="data.userConfirmed ? 'Usuario confirmación' : data.userVoided ? 'Usuario anulación' : 'Usuario modificación'">
        </Column>
        <Column :field="data.userConfirmed ? 'confirmed_at' : data.userVoided ? 'voided_at' : 'updated_at'"
            :header="data.userConfirmed ? 'Fecha confirmación' : data.userVoided ? 'Fecha anulación' : 'Fecha modificación'">
        </Column>
    </DataTable>
</template>