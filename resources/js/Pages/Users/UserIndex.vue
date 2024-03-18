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
    roles: {
        type: Object,
        default: () => ({}),
    },
});

const rolesSelect = ref([]); 

onMounted(() => {
    props.users.map((user) => {
        user.is_active = user.is_active === 1 ? 'ACTIVO' : 'INACTIVO';
    });

    props.users.map((user) => {
        user.roles = user.roles[0]
    })

    rolesSelect.value = props.roles.map((role) => {
        return {
            label: role.name,
            value: role.name
        }
    })
});

const products = ref();
const editingRows = ref([]);

const statuses = ref([
    { label: 'ACTIVO', value: 'ACTIVO' },
    { label: 'INACTIVO', value: 'INACTIVO' }
]);

const onRowEditSave = (event) => {
    let { newData, index } = event;

    products.value[index] = newData;
};
const getStatusLabel = (status) => {
    switch (status) {
        case 'ACTIVO':
            return 'success';

        case 'INACTIVO':
            return 'danger';

        default:
            return null;
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Card class="mt-5 uppercase">
            <template #title>Panel de usuarios</template>
            <template #content>
                <DataTable v-model:editingRows="editingRows" :value="users" editMode="row" dataKey="id"
                    @row-edit-save="onRowEditSave" :pt="{
                    table: { style: 'min-width: 50rem' }
                }">
                    <Column field="surname" header="Apellido" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText v-model="data[field]" />
                        </template>
                    </Column>
                    <Column field="name" header="Nombre" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText v-model="data[field]" />
                        </template>
                    </Column>
                    <Column field="email" header="Email" style="width: 15%;">
                        <template #editor="{ data, field }">
                            <InputText v-model="data[field]" />
                        </template>
                    </Column>
                    <Column field="username" header="Usuario" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText v-model="data[field]" />
                        </template>
                    </Column>
                    <Column field="roles" header="Rol" style="width: 10%;">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.roles"
                                class="bg-transparent !text-surface-700 !text-base !font-normal !p-0 uppercase" />
                        </template>
                        <template #editor="{ data, field }">
                            <Dropdown v-model="data[field]" :options="rolesSelect" optionLabel="label"
                                optionValue="value" placeholder="Seleccione un rol">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value"
                                        class="bg-transparent !text-surface-700 !text-base !font-normal !p-0 uppercase" />
                                </template>
                            </Dropdown>
                        </template>
                    </Column>
                    <Column field="is_active" header="Estado" style="width: 10%;">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.is_active" class="!text-sm uppercase"
                                :severity="getStatusLabel(slotProps.data.is_active)" />
                        </template>
                        <template #editor="{ data, field }">
                            <Dropdown v-model="data[field]" :options="statuses" optionLabel="label" optionValue="value"
                                placeholder="Seleccione un estado">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value"
                                        :severity="getStatusLabel(slotProps.option.value)" class="!text-sm uppercase" />
                                </template>
                            </Dropdown>
                        </template>
                    </Column>
                    <Column header="Acciones" :rowEditor="true" style="width: 5%; min-width: 8rem"
                        bodyStyle="text-align:center">
                    </Column>
                </DataTable>
            </template>
        </Card>
    </AuthenticatedLayout>
</template>