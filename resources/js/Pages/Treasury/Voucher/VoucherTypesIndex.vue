<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePermissions } from '@/composables/permissions';
import { toastService } from '@/composables/toastService'
import { useForm } from '@inertiajs/vue3';
import { format } from "@formkit/tempo"
import { FilterMatchMode, FilterOperator } from 'primevue/api';
import { useConfirm } from 'primevue/useconfirm';

toastService();

const props = defineProps({
    voucherTypes: {
        type: Object,
        default: () => ({}),
    },
    voucherSubtypes: {
        type: Object,
        default: () => ({}),
    },
});

const { hasPermission } = usePermissions();
const voucherTypesArray = ref([]);
const voucherSubtypesArray = ref([]);
const dataVoucherSubtypesArray = ref([]);
const subtypesPanel = ref();
const confirm = useConfirm();
const voucherTypeRelated = ref();

voucherTypesArray.value = props.voucherTypes;
dataVoucherSubtypesArray.value = props.voucherSubtypes;

const related = (data, event) => {
    voucherTypeRelated.value = data;
    voucherSubtypesArray.value = [];

    dataVoucherSubtypesArray.value.map((voucherSubtype) => {
        const matchingSubtype = data.subtypes.find(subtype => subtype.id === voucherSubtype.id);

        if (matchingSubtype) {
            voucherSubtype.related = true;
            voucherSubtype.relatedData = {
                related_at: matchingSubtype.related_at ? format(matchingSubtype.related_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00',
                userRelated: {
                    name: matchingSubtype.userRelated.name,
                    surname: matchingSubtype.userRelated.surname
                }
            }
        } else {
            voucherSubtype.related = false;
        }

        voucherSubtypesArray.value.push(voucherSubtype);
    });

    subtypesPanel.value.toggle(event);
};

const subtypesFilters = ref({
    name: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
});

const allowHide = ref(true);

const handleHide = (event) => {
    if (!allowHide.value) {
        allowHide.value = true;
    }
};

const onContentMouseDown = () => {
    allowHide.value = false;
};

const relateButton = (event, data) => {
    confirm.require({
        target: event.currentTarget,
        message: '¿Está seguro de relacionar el subtipo al tipo?',
        rejectClass: 'bg-red-500 text-white hover:bg-red-600',
        accept: () => {
            const form = useForm({
                voucherSubtype: data.id
            })

            form.post(route("voucher-types.relate", voucherTypeRelated.value.id), {
                onSuccess: () => {
                },
                onError: () => {
                }
            });
        },
    });
}

onMounted(() => {
    Echo.channel('types')
        .listen('Treasury\\Voucher\\VoucherTypeEvent', (e) => {
            if (e.type === 'relate') {
                const index = voucherTypesArray.value.findIndex(voucherType => voucherType.id === e.voucherType.id);

                if (index !== -1) {
                    voucherTypesArray.value[index] = e.voucherType;
                }

                if (voucherTypeRelated.value.id != e.voucherType.id) return;

                voucherSubtypesArray.value.map((voucherSubtype) => {
                    const data = e.voucherType.subtypes.find(subtypeRelated => subtypeRelated.id === voucherSubtype.id);

                    if (data) {
                        voucherSubtype.related = true;
                        voucherSubtype.relatedData = {
                            related_at: data.related_at ? format(data.related_at, "DD/MM/YYYY HH:mm:ss", "es") : '00/00/0000 00:00:00',
                            userRelated: {
                                name: data.userRelated.name,
                                surname: data.userRelated.surname
                            }
                        }
                    } else {
                        voucherSubtype.related = false;
                    }
                });
            }
        });

    Echo.channel('subtypes')
        .listen('Treasury\\Voucher\\VoucherSubtypeEvent', (e) => {
            e.voucherSubtype.status = e.voucherSubtype.status === 1 ? 'ACTIVO' : 'INACTIVO';

            if (e.type === 'create') {
                if (!dataVoucherSubtypesArray.value.some(voucherSubtype => voucherSubtype.id === e.voucherSubtypeId)) {
                    dataVoucherSubtypesArray.value.unshift(e.voucherSubtype);
                }

                if (!voucherSubtypesArray.value.some(voucherSubtype => voucherSubtype.id === e.voucherSubtypeId)) {
                    voucherSubtypesArray.value.unshift(e.voucherSubtype);
                }
            } else if (e.type === 'update') {
                const dataIndex = dataVoucherSubtypesArray.value.findIndex(voucherSubtype => voucherSubtype.id === e.voucherSubtype.id);
                const index = voucherSubtypesArray.value.findIndex(voucherSubtype => voucherSubtype.id === e.voucherSubtype.id);

                if (dataIndex !== -1) {
                    dataVoucherSubtypesArray.value[dataIndex].name = e.voucherSubtype.name;
                    dataVoucherSubtypesArray.value[dataIndex].status = e.voucherSubtype.status;
                    /* voucherSubtypesArray.value[index].name = e.voucherSubtype.name;
                    voucherSubtypesArray.value[index].status = e.voucherSubtype.status; */
                }
            }
        });
});
</script>

<template>
    <AuthenticatedLayout>
        <Card class="mt-5 mx-4 uppercase">
            <template #title>
                <div class="flex justify-between items-center mx-4">
                    <div class="align-left">
                        <h3 class="uppercase">Tipos</h3>
                    </div>
                </div>
            </template>
            <template #content>
                <DataTable :value="voucherTypesArray" scrollable scrollHeight="70vh" dataKey="id" class="data-table">
                    <template #empty>
                        <div class="text-center text-lg text-red-500">
                            Sin tipos cargados
                        </div>
                    </template>
                    <Column field="name" header="Descripción">
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                    </Column>
                    <Column header="Subtipos relacionados">
                        <template #body="{ data }">
                            <Button severity="info" raised rounded outlined @click="related(data, $event)">{{
                    data.subtypes.length }}</Button>
                        </template>
                    </Column>
                </DataTable>

                <OverlayPanel ref="subtypesPanel" appendTo="body" :dismissable="false" @hide="handleHide">
                    <div @mousedown.stop="onContentMouseDown">
                        <DataTable v-model:filters="subtypesFilters" :value="voucherSubtypesArray" paginator :rows="5" dataKey="id"
                            filterDisplay="menu" :globalFilterFields="['name']">
                            <template #empty>
                                <div class="text-center text-lg text-red-500">
                                    Sin subtipos cargados
                                </div>
                            </template>
                            <Column field="name" header="Subtipo" style="min-width: 12rem" class="uppercase" sortable>
                                <template #body="{ data }">
                                    <span :class="{ 'text-red-500': data.status === 'INACTIVO' || data.status === 0 }">
                                        {{ data.name }}
                                    </span>
                                </template>
                                <template #filter="{ filterModel, filterCallback }">
                                    <InputText v-model="filterModel.value" type="text" @input="filterCallback()" name="subtypeName" autocomplete="off"
                                        class="p-column-filter" placeholder="Buscar por subtipo" />
                                </template>
                            </Column>
                            <Column field="related" header="Relacionado" dataType="boolean" class="action-column text-center"
                                headerClass="min-w-28 w-28">
                                <template #body="{ data }">
                                    <div class="space-x-4">
                                        <template v-if="!hasPermission('relationship voucher types')">
                                            <i class="pi top-1 relative"
                                                :class="{ 'pi-check-circle text-green-500': data.related, 'pi-times-circle text-red-400': !data.related }"
                                                v-tooltip="data.related ? 'Relacionado' : 'No relacionado'">
                                            </i>
                                        </template>
                                        <template v-if="hasPermission('relationship voucher types')">
                                            <ConfirmPopup></ConfirmPopup>
                                            <button v-tooltip="data.related ? 'Quitar relación' : 'Relacionar'"><i class="pi text-blue-300"
                                                    :class="{ 'pi-plus-circle text-green-500': !data.related, 'pi-minus-circle text-red-400': data.related }"
                                                    @click="relateButton($event, data)"></i></button>
                                        </template>
                                        <template v-if="hasPermission('view users') && data.related">
                                            <i class="pi pi-id-card text-cyan-500 text-2xl top-1 relative"
                                                v-tooltip="`Usuario: ${data.relatedData.userRelated.surname} ${data.relatedData.userRelated.name} \n Fecha: ${data.relatedData.related_at}`"></i>
                                        </template>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </OverlayPanel>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>