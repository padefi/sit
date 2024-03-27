<script setup>
import { ref, onMounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import { usePermissions } from '@/composables/permissions';
import { useConfirm } from "primevue/useconfirm";
import { toastService } from '@/composables/toastService'

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
    permissions: {
        type: Object,
        default: () => ({}),
    },
});

const { username } = usePermissions();
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
        id: createId(),
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

const createId = () => {
    let id = '';
    var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    for (var i = 0; i < 5; i++) {
        id += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    return id;
}

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
    if (!data.surname || !data.name || !validateEmail(data.email) || !data.role || !data.is_active) {
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
});

/*  */
import PermissionModal from './PermissionModal.vue';
import { useDialog } from 'primevue/usedialog';
const permissions = ref([]);
const dialog = useDialog();

props.permissions.map((data) => {
    const category = data.description;

    permissions.value[category] ??= [];
    permissions.value[category].push({
        id: data.id,
        name: data.name,
        show: data.show,
    });
});

const modalPermissions = () => {
    const dialogRef = dialog.open(PermissionModal, {
        props: {
            header: 'Product List',
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
            permissions: props.permissions,
        }
    });
}
/*  */
</script>

<template>
    <AuthenticatedLayout>
        <Card class="mt-5 uppercase">
            <template #title>
                <div class="flex justify-between items-center mx-4">
                    <div class="align-left">
                        <h3 class="uppercase">Panel de usuarios</h3>
                    </div>
                    <div class="align-right">
                        <Button label="Agregar usuario" severity="info" outlined icon="pi pi-user-plus" size="large"
                            @click="addNewUser($event)" />
                    </div>
                </div>
            </template>
            <template #content>
                <DataTable v-model:editingRows="editingRows" :value="usersArray" editMode="row" dataKey="id"
                    @row-edit-init="onRowEditInit($event)" @row-edit-save="onRowEditSave"
                    @row-edit-cancel="onRowEditCancel" :pt="{
                                table: { style: 'min-width: 50rem' }
                            }" :paginator="true" :rows="10" :rowsPerPageOptions="[5, 10, 25]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="Mostrando del {first} al {last} de {totalRecords} usuarios">
                    <Column field="surname" header="Apellido" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" :invalid="!data[field]"
                                placeholder="Apellido" />
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="name" header="Nombre" style="width: 10%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" :invalid="!data[field]"
                                placeholder="Nombre" />
                            <InputError :message="!data[field] ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="email" header="Email" style="width: 15%;">
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]"
                                :invalid="!data[field] || !validateEmail(data[field])" placeholder="Email" />
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
                    <Column header="Acciones" style="width: 5%; min-width: 8rem;" :rowEditor="true">
                        <template #body="{ editorInitCallback, data }">
                            <div class="space-x-4 flex pl-6" v-if="data.username != username()">
                                <button v-tooltip="'Editar'"><i
                                        class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                        @click="disabledEditButtons(editorInitCallback, $event)"></i></button>
                                <button v-tooltip="'Ver permisos'"><i
                                        class="pi pi-eye text-cyan-500 text-lg font-extrabold"
                                        @click="modalPermissions()"></i></button>
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
                    <!-- <Column header="Rol" style="width: 10%;">
                        <template #body="slotProps">
                            {{ slotProps.data }}
                        </template>
                    </Column> -->
                </DataTable>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>