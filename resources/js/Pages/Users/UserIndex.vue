<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// import { ProductService } from '@/service/ProductService';

// defineProps(['users'])

const props = defineProps({
    users: {
        type: Object,
        default: () => ({}),
    },
});

const getRoleName = (user) => {
  return user.roles && user.roles.length > 0 ? user.roles[0] : 'SIN ROL';
};

const getStatusName = (user) => {
  return user.is_active ? 'Activo' : 'Inactivo';
};

console.log(props.users);

const products = ref();
const editingRows = ref([]);
const statuses = ref([
    { label: 'In Stock', value: 'INSTOCK' },
    { label: 'Low Stock', value: 'LOWSTOCK' },
    { label: 'Out of Stock', value: 'OUTOFSTOCK' }
]);

onMounted(() => {
    // ProductService.getProductsMini().then((data) => (products.value = data));
});

const onRowEditSave = (event) => {
    let { newData, index } = event;

    products.value[index] = newData;
};
const getStatusLabel = (status) => {
    switch (status) {
        case 'INSTOCK':
            return 'success';

        case 'LOWSTOCK':
            return 'warning';

        case 'OUTOFSTOCK':
            return 'danger';

        default:
            return null;
    }
};
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
}
</script>

<template>
    <AuthenticatedLayout>
        <Card class="mt-5">
            <template #title>Panel de usuarios</template>
            <template #content>
                <DataTable v-model:editingRows="editingRows" :value="users" editMode="row" dataKey="id"
                    @row-edit-save="onRowEditSave" :pt="{
                    table: { style: 'min-width: 50rem' }
                }">
                    <Column field="surname" header="Apellido">
                        <template #editor="{ data, field }">
                            <InputText v-model="data[field]" />
                        </template>
                    </Column>
                    <Column field="name" header="Nombre">
                        <template #editor="{ data, field }">
                            <InputText v-model="data[field]" />
                        </template>
                    </Column>
                    <Column field="email" header="Email">
                        <template #editor="{ data, field }">
                            <InputText v-model="data[field]" />
                        </template>
                    </Column>
                    <Column field="username" header="Usuario">
                        <template #editor="{ data, field }">
                            <InputText v-model="data[field]" />
                        </template>
                    </Column>
                    <Column field="roles" header="Rol">                        
                        <template #body="slotProps">
                            {{ getRoleName(slotProps.data) }}
                        </template>
                        <template #editor="{ data, field }">
                            <InputText v-model="data[field]" />
                        </template>
                    </Column>
                    <Column field="is_active" header="Estado">
                        <template #body="slotProps">
                            {{ getStatusName(slotProps.data) }}
                        </template>
                        <template #editor="{ data, field }">
                            <InputText v-model="data[field]" />
                        </template>
                    </Column><!-- 
                    <Column field="role" header="Rol">
                        <template #editor="{ data, field }">
                            <Dropdown v-model="data[field]" :options="statuses" optionLabel="label" optionValue="value"
                                placeholder="Select a Status">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value"
                                        :severity="getStatusLabel(slotProps.option.value)" />
                                </template>
                            </Dropdown>
                        </template>
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.inventoryStatus"
                                :severity="getStatusLabel(slotProps.data.inventoryStatus)" />
                        </template>
                    </Column>
                    <Column field="is_active" header="Status">
                        <template #editor="{ data, field }">
                            <Dropdown v-model="data[field]" :options="statuses" optionLabel="label" optionValue="value"
                                placeholder="Seleccione un estado">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value"
                                        :severity="getStatusLabel(slotProps.option.value)" />
                                </template>
                            </Dropdown>
                        </template>
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.inventoryStatus"
                                :severity="getStatusLabel(slotProps.data.inventoryStatus)" />
                        </template>
                    </Column> -->
                    <Column header="Acciones" :rowEditor="true" style="width: 10%; min-width: 8rem"
                        bodyStyle="text-align:center">
                    </Column>
                </DataTable>
            </template>
        </Card>
    </AuthenticatedLayout>
</template>