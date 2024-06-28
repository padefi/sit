<script setup>
import { ref, onMounted } from 'vue';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import { usePermissions } from '@/composables/permissions';
import { useConfirm } from "primevue/useconfirm";
import { toastService } from '@/composables/toastService'
import { validateEmail } from '@/utils/validateFunctions';

toastService();

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
const { hasPermission } = usePermissions();
const usersArray = ref([]);
const originalUsersArray = ref([]);
const toast = useToast();
const rolesSelect = ref([]);
const newRow = ref([]);
const editingRows = ref([]);
const rules = 'Debe completar el campo'
const editing = ref(false);
const confirm = useConfirm();

usersArray.value = props.users;

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

const filters = ref({
    surname: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    email: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    username: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    role: { operator: FilterOperator.OR, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
    is_active: { operator: FilterOperator.OR, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
});

const addNewUser = () => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de agregar un usuario.',
            life: 3000,
        });

        return;
    }

    originalUsersArray.value = [...usersArray.value];

    const newUser = {
        id: crypto.randomUUID(),
        surname: newRow.value?.surname,
        name: newRow.value?.name,
        email: newRow.value?.email,
        username: '',
        role: newRow.value?.role,
        is_active: newRow.value?.is_active,
        condition: 'newUser',
    };

    usersArray.value.unshift(newUser);
    editing.value = true;
    editingRows.value = [newUser];
};

const onRowEditInit = (event) => {
    originalUsersArray.value = [...usersArray.value];
    editingRows.value = [event.data];
}

const disabledEditButtons = (callback, event) => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de modificar un usuario.',
            life: 3000,
        });

        return;
    }

    editing.value = true;
    callback(event);
}
const enabledEditButtons = (callback, event) => {
    editing.value = false;
    editingRows.value = [];
    callback(event);
}

const validate = (event, saveCallback, data) => {
    if (!data.surname.trim() || !data.name.trim() || !validateEmail(data.email) || !data.role || !data.is_active) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });

        return;
    }

    if (data.condition === 'newUser') {
        confirm.require({
            target: event.currentTarget,
            message: '¿Está seguro de agrear el usuario?',
            rejectClass: 'bg-red-500 text-white hover:bg-red-600',
            accept: () => {
                newRow.value = data;
                saveCallback(event);
            },
        });

        return;
    }

    confirm.require({
        target: event.currentTarget,
        message: '¿Está seguro de modificar el usuario?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            saveCallback(event);
        },
    });
}

const onRowEditSave = (event) => {
    let { newData, index } = event;

    const form = useForm({
        surname: newData.surname,
        name: newData.name,
        email: newData.email,
        role: newData.role,
        is_active: newData.is_active === 'ACTIVO' ? 1 : 0,
    })

    if (newData.condition === 'newUser') {
        form.post(route("users.store", newData.id), {
            onSuccess: (result) => {
                editing.value = false;
                newData.condition = 'editUser';
                newData.id = result.props.flash.info.user.id;
                newData.username = result.props.flash.info.user.username;
                newRow.value = [];
            },
            onError: () => {
                usersArray.value = [...originalUsersArray.value];
                editing.value = false;
                addNewUser();
            }
        });

        return;
    }

    form.put(route("users.update", newData.id), {
        onSuccess: () => {
            editing.value = false;
            usersArray.value[index] = newData;
        },
        onError: () => {
            editing.value = true;
            editingRows.value = [newData];
        }
    });
};

const onRowEditCancel = () => {
    usersArray.value = [...originalUsersArray.value];
    editing.value = false;
    newRow.value = [];
    editingRows.value = [];
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

    Echo.channel('users')
        .listen('Users\\UserEvent', (e) => {
            e.user.role = props.roles.find(role => role.id === e.user.roles[0].id).name;
            e.user.is_active = e.user.is_active === 1 ? 'ACTIVO' : 'INACTIVO';

            if (e.type === 'create') {
                if (!usersArray.value.some(user => user.id === e.userId)) {
                    usersArray.value.unshift(e.user);
                }
            } else if (e.type === 'update') {
                const index = usersArray.value.findIndex(user => user.id === e.user.id);

                if (index !== -1) {
                    usersArray.value[index] = e.user;
                }
            }
        });
});

/*  */
import PermissionModal from './PermissionModal.vue';
import { useDialog } from 'primevue/usedialog';
// const permissions = ref({});
const dialog = useDialog();
const userRolePermission = (userRole, userId) => {
    const userPermissions = props.users.find(user => user.id === userId).permissions;
    const rolePermissions = props.roles.find(role => role.name === userRole).permissions;

    const permissions = {};

    rolePermissions.map((data) => {
        if (data.show === 0) return;

        const category = data.description;
        permissions[category] ??= [];
        permissions[category].push({
            id: data.id,
            name: data.name,
            show: data.show,
            hasPermission: userPermissions.includes(data.name),
        });
    });

    return permissions;
}

const modalPermissions = (name, surname, userId, userRole) => {
    const permissions = userRolePermission(userRole, userId);

    dialog.open(PermissionModal, {
        props: {
            header: `Permisos del usuario ${name} ${surname}`,
            style: {
                width: '50vw',
            },
            breakpoints: {
                '960px': '75vw',
                '640px': '90vw'
            },
            modal: true
        },
        data: {
            userId: userId,
            permissions: permissions,
        }
    });
}
/*  */
</script>

<template>
    <AuthenticatedLayout>
        <Card class="mt-5 mx-4 uppercase">
            <template #title>
                <div class="flex justify-between items-center mx-4">
                    <div class="align-left">
                        <h3 class="uppercase">Panel de usuarios</h3>
                    </div>
                    <template v-if="hasPermission('create users')">
                        <div class="align-right">
                            <Button label="Agregar usuario" severity="info" outlined icon="pi pi-user-plus" size="large"
                                @click="addNewUser($event)" />
                        </div>
                    </template>
                </div>
            </template>
            <template #content>
                <DataTable v-model:editingRows="editingRows" v-model:filters="filters" :value="usersArray" scrollable
                    scrollHeight="70vh" editMode="row" dataKey="id" filterDisplay="menu"
                    :globalFilterFields="['name', 'surname', 'email', 'username', 'role', 'is_active']"
                    @row-edit-init="onRowEditInit($event)" @row-edit-save="onRowEditSave"
                    @row-edit-cancel="onRowEditCancel" :pt="{
                        table: { style: 'min-width: 50rem' },
                        paginator: {
                            root: { class: 'p-paginator-custom' },
                            current: { class: 'p-paginator-current' },
                        }
                    }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table">
                    <template #empty>
                        <div class="text-center text-lg text-red-500">
                            Sin usuarios cargados
                        </div>
                    </template>
                    <Column field="surname" header="Apellido" style="width: 10%;" class="rounded-tl-lg" sortable>
                        <template #body="{ data }">
                            {{ data.surname }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="surname"
                                autocomplete="off" class="p-column-filter" placeholder="Buscar por apellido" />
                        </template>
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" name="surname" autocomplete="off"
                                :invalid="!data[field] || data[field].trim() === ''" placeholder="Apellido" />
                            <InputError :message="!data[field] || data[field].trim() === '' ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="name" header="Nombre" style="width: 10%;" sortable>
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="name"
                                autocomplete="off" class="p-column-filter" placeholder="Buscar por nombre" />
                        </template>
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" name="name" autocomplete="off"
                                :invalid="!data[field] || data[field].trim() === ''" placeholder="Nombre" />
                            <InputError :message="!data[field] || data[field].trim() === '' ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="email" header="Email" style="width: 15%;" sortable>
                        <template #body="{ data }">
                            {{ data.email }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="email"
                                autocomplete="off" class="p-column-filter" placeholder="Buscar por email" />
                        </template>
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" name="email" autocomplete="off"
                                :invalid="!data[field] || data[field].trim() === '' || !validateEmail(data[field])"
                                placeholder="Email" />
                            <InputError
                                :message="!data[field] || data[field].trim() === '' ? rules : validateEmail(data[field]) ? '' : 'Dirección de mail invalida'" />
                        </template>
                    </Column>
                    <Column field="username" header="Usuario" style="width: 10%;" sortable>
                        <template #body="{ data }">
                            {{ data.username }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="username"
                                autocomplete="off" class="p-column-filter" placeholder="Buscar por usuario" />
                        </template>
                    </Column>
                    <Column field="role" header="Rol" style="width: 10%;">
                        <template #body="{ data }">
                            <Tag :value="data.role"
                                class="bg-transparent !text-surface-700 !text-base !font-normal !p-0 uppercase" />
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="rolesSelect"
                                placeholder="Rol" class="p-column-filter" optionLabel="label" optionValue="value"
                                style="min-width: 12rem" :showClear="true">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value" name="role"
                                        class="bg-transparent !text-surface-700 !text-base !font-normal !p-0 uppercase" />
                                </template>
                            </Dropdown>
                        </template>
                        <template #editor="{ data, field }">
                            <Dropdown v-model="data[field]" :invalid="!data[field]" :options="rolesSelect" filter
                                optionLabel="label" optionValue="value" placeholder="Seleccione un rol">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value"
                                        class="bg-transparent !text-surface-700 !text-base !font-normal !p-0 uppercase" />
                                </template>
                            </Dropdown>
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="is_active" header="Estado" style="width: 10%;">
                        <template #body="{ data }">
                            <Tag :value="data.is_active" class="!text-sm uppercase"
                                :severity="getStatusLabel(data.is_active)" />
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <Dropdown v-model="filterModel.value" @change="filterCallback()" :options="statuses"
                                placeholder="Estado" class="p-column-filter" optionLabel="label" optionValue="value"
                                style="min-width: 12rem" :showClear="true">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value" name="is_active"
                                        :severity="getStatusLabel(slotProps.option.value)" class="!text-sm uppercase" />
                                </template>
                            </Dropdown>
                        </template>
                        <template #editor="{ data, field }">
                            <Dropdown v-model="data[field]" :invalid="!data[field]" :options="statuses"
                                optionLabel="label" optionValue="value" placeholder="Seleccione un estado">
                                <template #option="slotProps">
                                    <Tag :value="slotProps.option.value"
                                        :severity="getStatusLabel(slotProps.option.value)" class="!text-sm uppercase" />
                                </template>
                            </Dropdown>
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column header="Acciones" style="width: 5%; min-width: 8rem;" :rowEditor="true">
                        <template #body="{ editorInitCallback, data }">
                            <div class="space-x-4 flex pl-6" v-if="data.username != username() && data.role != 'admin'">
                                <template v-if="hasPermission('edit users')">
                                    <button v-tooltip="'Editar'"><i
                                            class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                            @click="disabledEditButtons(editorInitCallback, $event)"></i></button>
                                </template>
                                <template v-if="hasPermission('permission users')">
                                    <button v-tooltip="'Ver permisos'"><i
                                            class="pi pi-eye text-cyan-500 text-lg font-extrabold"
                                            @click="modalPermissions(data.name, data.surname, data.id, data.role)"></i></button>
                                </template>
                            </div>
                        </template>
                        <template #editor="{ data, editorSaveCallback, editorCancelCallback }">
                            <div class="space-x-4 flex pl-7">
                                <ConfirmPopup></ConfirmPopup>
                                <button><i class="pi pi-check text-primary-500 text-lg font-extrabold"
                                        @click="validate($event, editorSaveCallback, data)"></i></button>
                                <button><i class="pi pi-times text-red-500 text-lg font-extrabold"
                                        @click="enabledEditButtons(editorCancelCallback, $event, data)"></i></button>
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>