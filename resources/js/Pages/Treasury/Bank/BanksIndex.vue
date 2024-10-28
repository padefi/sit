<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { dropdownClasses } from '@/utils/cssUtils';
import { nominatim, nominatimOsmId } from '@/utils/apis';
import { useForm } from '@inertiajs/vue3';
import { useToast } from "primevue/usetoast";
import { usePermissions } from '@/composables/permissions';
import { useConfirm } from "primevue/useconfirm";
import { toastService } from '@/composables/toastService';
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { validatePhoneNumber, validateEmail, validateAccountNumber, validateCBU, validateAlias } from '@/utils/validateFunctions';
import { v4 as uuidv4 } from 'uuid';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import L from 'leaflet';

toastService();

const props = defineProps({
    banks: {
        type: Object,
        default: () => ({}),
    },
    bankAccounts: {
        type: Object,
        default: () => ({}),
    },
    bankAccountTypes: {
        type: Object,
        default: () => ({}),
    },
});

const { hasPermission, hasPermissionColumn } = usePermissions();
const toast = useToast();
const bankAccountTypesSelect = ref([]);
const newRow = ref([]);
const editingRows = ref([]);
const expandedRows = ref({});
const rules = 'Debe completar el campo'
const addressArray = ref([]);
const editing = ref(false);
const confirm = useConfirm();
const OverlayPanelMap = ref();

const banksArray = ref([]);
const originalBanksArray = ref([]);
bankAccountTypesSelect.value = props.bankAccountTypes.map((type) => {
    return {
        label: type.name,
        value: type.id
    }
})

const statuses = ref([
    { label: 'ACTIVA', value: 'ACTIVA' },
    { label: 'INACTIVA', value: 'INACTIVA' }
]);

const banksFilters = ref({
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    phone: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
    email: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

const setBankAccount = (bankData, accountData) => {
    bankData.map(async (bank, index) => {
        const bankAccount = accountData
            .filter(account => account.bank.id === bank.id)
            .map(account =>
                accountDataStructure(index, account),
            );

        const data = {
            id: bank.id,
            bankIndex: index,
            name: bank.name,
            street: bank.street,
            streetNumber: bank.streetNumber,
            city: bank.city,
            state: bank.state,
            country: bank.country,
            postalCode: bank.postalCode,
            latitude: bank.latitude,
            longitude: bank.longitude,
            osm_id: bank.osm_id,
            selectedAddress: null,
            invalidAddress: false,
            phone: bank.phone,
            email: bank.email,
            accounts: bankAccount,
        };

        banksArray.value.push(data);
    });
}

const accountDataStructure = (index, accountData) => {
    return {
        id: accountData.bank.id + '-' + accountData.id,
        idBankAccount: accountData.id,
        idBank: accountData.bank.id,
        idAT: accountData.accountType.id,
        bankIndex: index,
        accountNumber: accountData.accountNumber,
        accountType: accountData.accountType.name,
        cbu: accountData.cbu,
        alias: accountData.alias,
        status: accountData.status === 1 ? 'ACTIVA' : 'INACTIVA',
    };
}

const getStatusLabel = (status) => {
    switch (status) {
        case 'ACTIVA':
            return 'success';

        case 'INACTIVA':
            return 'danger';

        default:
            return null;
    }
};

const search = async (event) => {
    addressArray.value = await nominatim(event.query);
}

const selectData = (e, bankData) => {
    const data = e.value ?? e;
    bankData.selectedAddress = data.display_name;

    bankData.address = {
        display_name: data.display_name,
        street: data.address?.road || '',
        streetNumber: parseInt(data.address?.house_number) || '',
        city: data.address?.city || data.address?.town || '',
        state: data.address?.state || '',
        country: data.address?.country || '',
        postalCode: data.address?.postcode || '',
        latitude: data.lat,
        longitude: data.lon,
        osm_id: data.osm_id,
    }
}

const cleanIfEmpty = (data, index) => {
    setTimeout(() => {
        if (data.selectedAddress === '' || !data.selectedAddress || !data.address.display_name || data.address.display_name === '') {
            document.querySelector('#address_' + index).value = '';
            data.address = {};
            data.invalidAddress = true;
        } else {
            data.invalidAddress = false;
        }
    }, 200);
}

const onRowExpand = (event) => {
    const originalExpandedRows = { ...expandedRows.value };
    const newExpandedRows = banksArray.value.reduce((acc) => (acc[event.data.id] = true) && acc, {});
    expandedRows.value = { ...originalExpandedRows, ...newExpandedRows };
}

const onRowCollapse = (event) => {
    delete expandedRows.value[event.data.id]
}

const disabledEditButtons = async (callback, event, data, dataType) => {
    if (editing.value) {
        const message = (dataType === 'banks') ? 'un banco' : 'una cuenta';

        toast.add({
            severity: 'error',
            detail: `Debe guardar los cambios antes de modificar ${message}.`,
            life: 3000,
        });

        return;
    }

    if (dataType === 'banks') {
        const address = await nominatimOsmId(data.osm_id);
        selectData(address[0], data);
    }

    editing.value = true;
    callback(event);
}

const enabledEditButtons = async (callback, event, data, dataType) => {
    if (data.condition === 'editBank') {
        const address = await nominatimOsmId(data.osm_id);
        selectData(address[0], data);
    }

    editing.value = false;
    editingRows.value = [];
    callback(event);
}

/* Bank validations */
const onRowEditInitBank = async (event) => {
    originalBanksArray.value = [...banksArray.value];
    editingRows.value = [event.data];
}

const addNewBank = () => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de agregar un banco.',
            life: 3000,
        });

        return;
    }

    originalBanksArray.value = [...banksArray.value];

    const newBank = {
        id: uuidv4(),
        name: null,
        address: {},
        selectedAddress: null,
        invalidAddress: false,
        phone: null,
        email: null,
        condition: 'newBank',
        accounts: [],
    };

    banksArray.value.unshift(newBank);
    editing.value = true;
    editingRows.value = [newBank];
};

const validateBank = (event, saveCallback, data) => {
    if ((data.name && !data.name.trim()) || (Object.keys(data.address).length === 0) || !validatePhoneNumber(data.phone) || !validateEmail(data.email)) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });

        return;
    }

    if (data.condition === 'newBank') {
        confirm.require({
            target: event.currentTarget,
            message: '¿Está seguro de agregar el banco?',
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
        message: '¿Está seguro de modificar el banco?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            saveCallback(event);
        },
    });
}

const onRowEditSaveBank = (event) => {
    let { newData, index } = event;

    const form = useForm({
        name: newData.name,
        address: newData.address,
        phone: newData.phone,
        email: newData.email,
        notes: newData.notes,
    })

    if (newData.condition === 'newBank') {
        form.post(route("banks.store", newData.id), {
            onSuccess: async (result) => {
                editing.value = false;
                newData.condition = 'editBank';
                newData.id = result.props.flash.info.bank.id;
                newData.bankIndex = 0;
                newData.name = result.props.flash.info.bank.name;
                newData.street = result.props.flash.info.bank.street;
                newData.streetNumber = result.props.flash.info.bank.streetNumber;
                newData.city = result.props.flash.info.bank.city;
                newData.state = result.props.flash.info.bank.state;
                newData.country = result.props.flash.info.bank.country;
                newData.postalCode = result.props.flash.info.bank.postalCode;
                newData.latitude = result.props.flash.info.bank.latitude;
                newData.longitude = result.props.flash.info.bank.longitude;
                newData.osm_id = result.props.flash.info.bank.osm_id;
                newData.phone = result.props.flash.info.bank.phone;
                newData.email = result.props.flash.info.bank.email;
                newData.notes = result.props.flash.info.bank.notes;
                newRow.value = [];
            },
            onError: () => {
                banksArray.value = [...originalBanksArray.value];
                editing.value = false;
                addNewBank();
            }
        });

        return;
    }

    form.put(route("banks.update", newData.id), {
        onSuccess: (result) => {
            editing.value = false;
            // Address update
            newData.street = result.props.flash.info.bank.street;
            newData.streetNumber = result.props.flash.info.bank.streetNumber;
            newData.city = result.props.flash.info.bank.city;
            newData.state = result.props.flash.info.bank.state;
            newData.country = result.props.flash.info.bank.country;
            newData.postalCode = result.props.flash.info.bank.postalCode;
            newData.latitude = result.props.flash.info.bank.latitude;
            newData.longitude = result.props.flash.info.bank.longitude;
            newData.osm_id = result.props.flash.info.bank.osm_id;
            // Address update

            banksArray.value[index] = newData;
        },
        onError: () => {
            editing.value = true;
            editingRows.value = [newData];
        }
    });
};

const onRowEditCancelBank = () => {
    banksArray.value = [...originalBanksArray.value];
    editing.value = false;
    newRow.value = [];
    editingRows.value = [];
};
/* Bank validations */

/* Bank Account validations */
const onRowEditInitBankAccount = (event) => {
    originalBanksArray.value = [...banksArray.value[event.data.bankIndex].accounts];
    editingRows.value = [event.data];
}

const addNewBankAccount = (data) => {
    if (editing.value) {
        toast.add({
            severity: 'error',
            detail: 'Debe guardar los cambios antes de agregar una cuenta bancaria.',
            life: 3000,
        });

        return;
    }

    const originalExpandedRows = { ...expandedRows.value };
    const newExpandedRows = banksArray.value.reduce((acc) => (acc[data.id] = true) && acc, {});
    expandedRows.value = { ...originalExpandedRows, ...newExpandedRows };
    originalBanksArray.value = [...banksArray.value[data.bankIndex].accounts];

    const newBankAccount = {
        id: uuidv4(),
        idBankAccount: uuidv4(),
        idBank: data.id,
        idAT: newRow.value?.idAT,
        bankIndex: data.bankIndex,
        accountNumber: newRow.value?.accountNumber,
        cbu: newRow.value?.cbu,
        alias: newRow.value?.alias,
        status: newRow.value?.status,
        condition: 'newBankAccount',
    };

    banksArray.value[data.bankIndex].accounts.unshift(newBankAccount);
    editing.value = true;
    editingRows.value = [newBankAccount];
    rowClassEditing(data);
};

const validateBankAccount = (event, saveCallback, data) => {
    if (!data.idAT || !validateAccountNumber(data.accountNumber) || !validateCBU(data.cbu) || !validateAlias(data.alias) || !data.status) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos.',
            life: 3000,
        });

        return;
    }

    if (data.condition === 'newBankAccount') {
        confirm.require({
            target: event.currentTarget,
            message: '¿Está seguro de agregar la cuenta bancaria?',
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
        message: '¿Está seguro de modificar la cuenta bancaria?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            saveCallback(event);
        },
    });
}

const onRowEditSaveBankAccount = (event) => {
    let { newData, index } = event;

    const form = useForm({
        idBank: newData.idBank,
        idAT: newData.idAT,
        accountNumber: newData.accountNumber,
        accountType: newData.accountType,
        cbu: newData.cbu,
        alias: newData.alias,
        status: newData.status === 'ACTIVA' ? 1 : 0,
    });

    if (newData.condition === 'newBankAccount') {
        form.post(route("bankAccounts.store", newData.id), {
            onSuccess: (result) => {
                const data = result.props.flash.info.bankAccount;
                editing.value = false;
                newData.condition = 'editBankAccount';
                newData.id = data.idBank + '-' + data.id;
                newData.idBankAccount = data.id;
                newData.idBank = data.idBank;
                newData.idAT = data.idAT;
                newData.accountNumber = data.accountNumber;
                newData.accountType = bankAccountTypesSelect.value.filter(a => a.value === data.idAT)[0].label;
                newData.cbu = data.cbu;
                newData.alias = data.alias;
                newData.status = data.status === 1 ? 'ACTIVA' : 'INACTIVA';
                newRow.value = [];
            },
            onError: () => {
                banksArray.value[newData.bankIndex].accounts = [...originalBanksArray.value];
                editing.value = false;
                addNewBankAccount(banksArray.value[event.data.bankIndex]);
            }
        });

        return;
    }

    form.put(route("bankAccounts.update", newData.idBankAccount), {
        onSuccess: () => {
            editing.value = false;
            newData.accountType = bankAccountTypesSelect.value.filter(a => a.value === newData.idAT)[0].label;
            banksArray.value[newData.bankIndex].accounts[index] = newData;
        },
        onError: () => {
            editing.value = true;
            editingRows.value = [newData];
        }
    });
};

const onRowEditCancelBankAccount = (event) => {
    banksArray.value[event.data.bankIndex].accounts = [...originalBanksArray.value];
    editing.value = false;
    newRow.value = [];
    editingRows.value = [];
    rowClassEditing(banksArray.value[event.data.bankIndex]);
};
/* Bank Account validations */

const rowClassEditing = (rowData) => {
    if (editingRows.value.some(row => row.idBank === rowData.id)) {
        return '!bg-amber-100';
    }

    return '';
}

const viewOnMap = async (data, event) => {
    OverlayPanelMap.value.toggle(event);

    await nextTick();

    const map = L.map('map').setView([data.latitude, data.longitude], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    L.marker([data.latitude, data.longitude]).addTo(map).openPopup();
}

onMounted(async () => {
    setBankAccount(props.banks, props.bankAccounts);

    Echo.channel('banks')
        .listen('Treasury\\Bank\\BankEvent', (e) => {
            if (e.type === 'create') {
                if (!banksArray.value.some(bank => bank.id === e.bankId)) {
                    e.bank.bankIndex = 0;
                    e.bank.accounts = [];
                    banksArray.value.unshift(e.bank);
                }
            } else if (e.type === 'update') {
                const index = banksArray.value.findIndex(bank => bank.id === e.bank.id);

                if (index !== -1) {
                    banksArray.value[index] = e.bank;
                }
            }
        });

    Echo.channel('bankAccounts')
        .listen('Treasury\\Bank\\BankAccountEvent', (e) => {
            const indexBank = banksArray.value.findIndex(bank => bank.id === e.bankAccount.idBank);

            const accountEventDataStructure = (index, accountData) => {
                return {
                    id: accountData.bank.id + '-' + accountData.id,
                    idBankAccount: accountData.id,
                    idBank: accountData.bank.id,
                    idAT: accountData.account_type.id,
                    bankIndex: index,
                    accountNumber: accountData.accountNumber,
                    accountType: accountData.account_type.name,
                    cbu: accountData.cbu,
                    alias: accountData.alias,
                    status: accountData.status === 1 ? 'ACTIVA' : 'INACTIVA',
                }
            }

            if (indexBank !== -1) {
                if (e.type === 'create') {
                    setTimeout(() => {
                        if (!banksArray.value[indexBank].accounts.some(account => account.idBankAccount === e.bankAccount.id)) {
                            banksArray.value[indexBank].accounts.unshift(accountEventDataStructure(indexBank, e.bankAccount));
                        }
                    }, 500);
                } else if (e.type === 'update') {
                    const indexBank = banksArray.value.findIndex(bank => bank.id === e.bankAccount.idBank);
                    const indexAccount = banksArray.value[indexBank].accounts.findIndex(account => account.idBankAccount === e.bankAccount.id);

                    if (indexAccount !== -1) {
                        banksArray.value[indexBank].accounts[indexAccount] = accountEventDataStructure(indexBank, e.bankAccount);
                    }
                }
            }
        });
});

/*  */
import infoModal from '@/Components/InfoModal.vue';
import { useDialog } from 'primevue/usedialog';

const dialog = useDialog();

const info = (route, data, id) => {
    axios.get(`/${route}/${id}/info`)
        .then((response) => {
            const header = (route === 'banks') ? `Información del banco ${data.name.toUpperCase()}` : `Información de la cta. ${data.accountNumber.toUpperCase()}`;

            dialog.open(infoModal, {
                props: {
                    header: header,
                    style: {
                        width: '75vw',
                    },
                    breakpoints: {
                        '960px': '75vw',
                        '640px': '90vw'
                    },
                    modal: true
                },
                data: response.data
            });
        })
        .catch((error) => {
            toast.add({
                severity: 'error',
                detail: error.response.data.message,
                life: 3000,
            });
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
                        <h3 class="uppercase">Bancos</h3>
                    </div>
                    <template v-if="hasPermission('create banks')">
                        <div class="align-right">
                            <Button label="Agregar banco" severity="info" outlined icon="pi pi-building-columns" size="large"
                                @click="addNewBank($event)" />
                        </div>
                    </template>
                </div>
            </template>
            <template #content>
                <DataTable v-model:editingRows="editingRows" v-model:filters="banksFilters" :expandedRows="expandedRows" :value="banksArray"
                    scrollable scrollHeight="70vh" editMode="row" dataKey="id" filterDisplay="menu" :globalFilterFields="['name', 'phone', 'email']"
                    @row-edit-init="onRowEditInitBank($event)" @row-edit-save="onRowEditSaveBank" @row-edit-cancel="onRowEditCancelBank"
                    @row-expand="onRowExpand($event)" @row-toggle="onRowToggle($event)" @row-collapse="onRowCollapse($event)" :pt="{
                        table: { style: 'min-width: 50rem' },
                        paginator: {
                            root: { class: 'p-paginator-custom' },
                            current: { class: 'p-paginator-current' },
                        }
                    }" :paginator="true" :rows="5" :rowsPerPageOptions="[5, 10, 25]"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    currentPageReportTemplate="{first} - {last} de {totalRecords}" class="data-table" :row-class="rowClassEditing">
                    <template #empty>
                        <div class="text-center text-lg text-red-500">
                            Sin bancos cargados
                        </div>
                    </template>
                    <Column expander class="min-w-2 w-2 !px-0" v-if="hasPermission('view bank accounts')" />
                    <Column field="name" header="Nombre" class="w-3/12" sortable>
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="name" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por nombre" />
                        </template>
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" name="name" autocomplete="off"
                                :invalid="data[field] && data[field].trim() === ''" placeholder="Nombre" style="width: 100%;" maxlength="100" />
                            <InputError :message="data[field] !== null && data[field].trim() === '' ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="address" header="Domicilio" class="w-5/12" sortable>
                        <template #body="{ data, field }">
                            {{ data['street'] }} {{ data['streetNumber'] }} {{ data['floor'] }} {{ data['apartment'] }}
                            - {{ data['postalCode'] }},
                            {{ data['city'] }}, {{ data['state'] }} - {{ data['country'] }}
                            <Button icon="pi pi-map-marker"
                                class="!p-0 !text-cyan-500 text-lg hover:!bg-transparent focus:!bg-transparent focus:!ring-transparent" text rounded
                                v-tooltip="'Ver en mapa'" @click="viewOnMap(data[field], $event)" />
                        </template>
                        <template #editor="{ data, field, index }">
                            <FloatLabel>
                                <AutoComplete v-model="data[field].display_name" :inputId="'address_' + index" ref="autoCompleteAddress"
                                    :suggestions="addressArray" @complete="search" @item-select="selectData($event, data)"
                                    @blur="cleanIfEmpty(data, index)" class="w-full uppercase" :invalid="data['invalidAddress']"
                                    :class="dropdownClasses(data[field].display_name)">
                                    <template #option="slotProps">
                                        <div class="flex items-center">
                                            <div>{{ slotProps.option.display_name }}</div>
                                        </div>
                                    </template>
                                </AutoComplete>
                                <label for="address">Domicilio</label>
                            </FloatLabel>
                            <InputError :message="data['invalidAddress'] ? rules : ''" />
                        </template>
                    </Column>
                    <Column field="phone" header="Teléfono" class="w-1/12" sortable>
                        <template #body="{ data }">
                            {{ data.phone }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="phone" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por teléfono" />
                        </template>
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" name="phone" autocomplete="off"
                                :invalid="data[field] && (data[field].trim() === '' || !validatePhoneNumber(data[field]))" placeholder="Teléfono"
                                style="width: 100%;" maxlength="15"
                                onkeypress='return event.keyCode >= 47 && event.keyCode <= 57 || event.keyCode === 45' />
                            <InputError
                                :message="data[field] !== null && data[field].trim() === '' ? rules : data[field] && !validatePhoneNumber(data[field]) ? 'N° de telefono invalido' : ''" />
                        </template>
                    </Column>
                    <Column field="email" header="Email" class="w-2/12" sortable>
                        <template #body="{ data }">
                            {{ data.email }}
                        </template>
                        <template #filter="{ filterModel, filterCallback }">
                            <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="email" autocomplete="off"
                                class="p-column-filter" placeholder="Buscar por email" />
                        </template>
                        <template #editor="{ data, field }">
                            <InputText :class="'uppercase'" v-model="data[field]" name="email" autocomplete="off"
                                :invalid="data[field] && (data[field].trim() === '' || !validateEmail(data[field]))" placeholder="Email"
                                style="width: 100%;" maxlength="100" />
                            <InputError
                                :message="data[field] !== null && data[field].trim() === '' ? rules : data[field] && !validateEmail(data[field]) ? 'Domicilio de mail invalida' : ''" />
                        </template>
                    </Column>
                    <Column header="Acciones" class="action-column text-center" headerClass="min-w-32 w-32"
                        v-if="hasPermissionColumn(['edit banks', 'create bank accounts', 'view users'])">
                        <template #body="{ editorInitCallback, data }">
                            <div class="space-x-2">
                                <template v-if="hasPermission('edit banks')">
                                    <button v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                            @click="disabledEditButtons(editorInitCallback, $event, data, 'banks')"></i></button>
                                </template>
                                <template v-if="hasPermission('view users')">
                                    <button v-tooltip="'+Info'" class="btn-info"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                            @click="info('banks', data, data.id)"></i></button>
                                </template>
                                <template v-if="hasPermission('view bank accounts') && hasPermission('create bank accounts')">
                                    <button v-tooltip="'Agregar cuenta'" class="btn-plus"><i class="pi pi-plus-circle text-green-500 text-xl"
                                            @click="addNewBankAccount(data)"></i></button>
                                </template>
                            </div>
                        </template>
                        <template #editor="{ data, editorSaveCallback, editorCancelCallback }">
                            <div class="space-x-4 flex pl-7">
                                <ConfirmPopup></ConfirmPopup>
                                <button><i class="pi pi-check text-primary-500 text-lg font-extrabold"
                                        @click="validateBank($event, editorSaveCallback, data)"></i></button>
                                <button><i class="pi pi-times text-red-500 text-lg font-extrabold"
                                        @click="enabledEditButtons(editorCancelCallback, $event, data)"></i></button>
                            </div>
                        </template>
                    </Column>
                    <template #expansion="{ data }">
                        <template v-if="data.accounts">
                            <DataTable v-model:editingRows="editingRows" :value="data.accounts" editMode="row" dataKey="id"
                                class="data-table-expanded" @row-edit-init="onRowEditInitBankAccount($event)"
                                @row-edit-save="onRowEditSaveBankAccount" @row-edit-cancel="onRowEditCancelBankAccount($event)">
                                <template #empty>
                                    <div class="text-center text-lg text-red-500">
                                        Sin cuentas asociadas
                                    </div>
                                </template>
                                <Column field="accountNumber" header="Nº Cta.">
                                    <template #editor="{ data, field }">
                                        <InputText :class="'uppercase'" v-model="data[field]" name="accountNumber" autocomplete="off"
                                            :invalid="!data[field] || !validateAccountNumber(data[field])" placeholder="Nº Cta." style="width: 100%;"
                                            maxlength="10" onkeypress='return event.keyCode >= 47 && event.keyCode <= 57' />
                                        <InputError :message="!data[field] ? rules : validateAccountNumber(data[field]) ? '' : 'Nº Cta. invalido'" />
                                    </template>
                                </Column>
                                <Column field="idAT" header="Tipo Cta.">
                                    <template #body="slotProps">
                                        <Tag :value="slotProps.data.accountType"
                                            class="bg-transparent !text-surface-700 !text-base !font-normal !p-0 uppercase" />
                                    </template>
                                    <template #editor="{ data, field }">
                                        <Dropdown v-model="data[field]" :invalid="!data[field]" :options="bankAccountTypesSelect" optionLabel="label"
                                            optionValue="value" placeholder="Seleccione un tipo de cta">
                                            <template #option="slotProps">
                                                <Tag :value="slotProps.option.label"
                                                    class="bg-transparent !text-surface-700 !text-base !font-normal !p-0 uppercase" />
                                            </template>
                                        </Dropdown>
                                        <InputError :message="!data[field] ? rules : ''" />
                                    </template>
                                </Column>
                                <Column field="cbu" header="CBU">
                                    <template #editor="{ data, field }">
                                        <InputText v-model="data[field]" name="cbu" autocomplete="off"
                                            :invalid="!data[field] || !validateCBU(data[field])" placeholder="CBU" style="width: 100%;"
                                            :minlength="22" :maxlength="22" onkeypress='return event.keyCode >= 48 && event.keyCode <= 57' />
                                        <InputError :message="!data[field] ? rules : validateCBU(data[field]) ? '' : 'CBU invalido'" />
                                    </template>
                                </Column>
                                <Column field="alias" header="ALIAS">
                                    <template #editor="{ data, field }">
                                        <InputText :class="'uppercase'" v-model="data[field]" name="alias" autocomplete="off"
                                            :invalid="!data[field] || !validateAlias(data[field])" placeholder="Alias" style="width: 100%;"
                                            minlength="6" maxlength="20" />
                                        <InputError :message="!data[field] ? rules : validateAlias(data[field]) ? '' : 'Alias invalido'" />
                                    </template>
                                </Column>
                                <Column field="status" header="Estado" style="width: 10%;">
                                    <template #body="slotProps">
                                        <Tag :value="slotProps.data.status" class="!text-sm uppercase"
                                            :severity="getStatusLabel(slotProps.data.status)" />
                                    </template>
                                    <template #editor="{ data, field }">
                                        <Dropdown v-model="data[field]" :invalid="!data[field]" :options="statuses" optionLabel="label"
                                            optionValue="value" placeholder="Seleccione un estado">
                                            <template #option="slotProps">
                                                <Tag :value="slotProps.option.value" :severity="getStatusLabel(slotProps.option.value)"
                                                    class="!text-sm uppercase" />
                                            </template>
                                        </Dropdown>
                                        <InputError :message="!data[field] ? rules : ''" />
                                    </template>
                                </Column>
                                <Column header="Acciones" class="action-column text-center" headerClass="min-w-28 w-28"
                                    v-if="hasPermissionColumn(['edit banks', 'view users'])">
                                    <template #body="{ editorInitCallback, data }">
                                        <div class="space-x-2">
                                            <template v-if="hasPermission('edit banks')">
                                                <button v-tooltip="'Editar'"><i class="pi pi-pencil text-orange-500 text-lg font-extrabold"
                                                        @click="disabledEditButtons(editorInitCallback, $event, data, 'bankAccounts')"></i></button>
                                            </template>
                                            <template v-if="hasPermission('view users')">
                                                <button v-tooltip="'+Info'" class="btn-info"><i class="pi pi-id-card text-cyan-500 text-2xl"
                                                        @click="info('bankAccounts', data, data.idBankAccount)"></i></button>
                                            </template>
                                        </div>
                                    </template>
                                    <template #editor="{ data, editorSaveCallback, editorCancelCallback }">
                                        <div class="space-x-4 flex pl-7">
                                            <ConfirmPopup></ConfirmPopup>
                                            <button><i class="pi pi-check text-primary-500 text-lg font-extrabold"
                                                    @click="validateBankAccount($event, editorSaveCallback, data)"></i></button>
                                            <button><i class="pi pi-times text-red-500 text-lg font-extrabold"
                                                    @click="enabledEditButtons(editorCancelCallback, $event, data)"></i></button>
                                        </div>
                                    </template>
                                </Column>
                            </DataTable>
                        </template>
                    </template>
                </DataTable>

                <OverlayPanel ref="OverlayPanelMap">
                    <div class="flex flex-col gap-3 w-[25rem]">
                        <div id="map" style="height: 400px;"></div>
                    </div>
                </OverlayPanel>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>