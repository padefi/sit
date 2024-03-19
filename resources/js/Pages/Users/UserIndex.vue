<script setup>
import { ref, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import { usePermissions } from '@/composables/permissions'

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

const { username } = usePermissions();
const page = usePage();
const toast = useToast();
const rolesSelect = ref([]);
const editingRows = ref([]);
const rules = 'Debe completar el campo'

const validateEmail = value => {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(value)) {
        return true
    } else {
        return false
    }
}

const statuses = ref([
    { label: 'ACTIVO', value: 'ACTIVO' },
    { label: 'INACTIVO', value: 'INACTIVO' }
]);

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

const onRowEditSave = (event) => {
    let { newData } = event;

    if (!newData.surname || !newData.name || !validateEmail(newData.email)) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });
        return;
    }

    const form = useForm({
        surname: newData.surname,
        name: newData.name,
        email: newData.email,
        role: newData.role,
        is_active: newData.is_active === 'ACTIVO' ? 1 : 0,
    })

    form.put(route("users.update", newData.id));
};

onMounted(() => {
    props.users.map((user) => {
        user.role = user.role[0]
        user.is_active = user.is_active === 1 ? 'ACTIVO' : 'INACTIVO';
    });

    rolesSelect.value = props.roles.map((role) => {
        return {
            label: role.name,
            value: role.name
        }
    })
});

watch(() => page.props.flash, (next) => {
    toast.add({
        severity: next.info.type,
        detail: next.info.message,
        life: 3000,
    });

    props.users.map((user) => {
        user.role = user.role[0]
        user.is_active = user.is_active === 1 ? 'ACTIVO' : 'INACTIVO';
    });

    rolesSelect.value = props.roles.map((role) => {
        return {
            label: role.name,
            value: role.name
        }
    })
});

const funcion = (data) => {
    console.log(data);

}
</script>

<template>
    <AuthenticatedLayout>
        <Card class="mt-5 uppercase">
            <template #title>Panel de usuarios</template>
            <template #content>
                <DataTable v-model:editingRows="editingRows" :value="users" editMode="row" dataKey="id"
                    @row-edit-save="onRowEditSave" :pt="{
                    table: { style: 'min-width: 50rem' }
                }" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 20, 50]" ref="dataTable">
                    <Column field="surname" header="Apellido" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" :invalid="!data[field]" />
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="name" header="Nombre" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" :invalid="!data[field]" />
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="email" header="Email" style="width: 15%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]"
                                :invalid="!data[field] || !validateEmail(data[field])" />
                            <InputError
                                :message="!data[field] ? rules : validateEmail(data[field]) ? '' : 'Dirección de mail invalida'" />
                        </template>
                    </Column>
                    <Column field="username" header="Usuario" style="width: 10%;">
                    </Column>
                    <Column field="role" header="Rol" style="width: 10%;">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.role"
                                class="bg-transparent !text-surface-700 !text-base !font-normal !p-0 uppercase" />
                        </template>
                        <template #editor="{ data, field }">
                            <Dropdown v-model="data[field]" :options="rolesSelect" filter optionLabel="label"
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
                    <Column header="Acciones" style="width: 5%; min-width: 8rem" bodyStyle="text-align:center"
                        :rowEditor="true">
                    </Column>
                </DataTable>
            </template>
        </Card>
    </AuthenticatedLayout>
</template>