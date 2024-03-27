<script setup>
import { ref, onMounted, inject } from "vue";

const permissions = ref([]);
const dialogRef = inject("dialogRef");

onMounted(() => {
    permissions.value = dialogRef.value.data.permissions;
    /* dialogRef.value.data.permissions.map((data) => {
        const category = data.description;

        permissions.value[category] ??= [];
        permissions.value[category].push({
            id: data.id,
            name: data.name,
            show: data.show,
        });
    }); */
    /* const flattenedPermissions = [];
    dialogRef.value.data.permissions.forEach((data) => {
        // Extraer los datos necesarios para cada permiso
        const permission = {
            id: data.id,
            name: data.name,
            show: data.show,
            category: data.description, // Agregar la categoría como una propiedad del objeto
        };
        flattenedPermissions.push(permission);
    });
    permissions.value = flattenedPermissions; */
});
</script>
<template>
    <div>
        <DataTable :value="permissions">
            <Column field="category" header="Menú"></Column>
            <Column field="name" header="Menú"></Column>
        </DataTable>
    </div>
</template>