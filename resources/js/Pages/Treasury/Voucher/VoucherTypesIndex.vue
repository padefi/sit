<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useToast } from "primevue/usetoast";
import { usePermissions } from '@/composables/permissions';
import { toastService } from '@/composables/toastService'
import { format } from "@formkit/tempo"
import { FilterMatchMode } from 'primevue/api';

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
const toast = useToast();

voucherTypesArray.value = props.voucherTypes;
dataVoucherSubtypesArray.value = props.voucherSubtypes;

const related = (data, event) => {
    voucherSubtypesArray.value = [];

    dataVoucherSubtypesArray.value.map((voucherSubtype) => {
        const matchingSubtype = data.subtypes.find(subtype => subtype.id === voucherSubtype.id);
        if (matchingSubtype) {
            voucherSubtype.related = {
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
    name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    related: { value: null, matchMode: FilterMatchMode.EQUALS },
});

onMounted(() => {
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
                    dataVoucherSubtypesArray.value[dataIndex] = e.voucherSubtype;
                    voucherSubtypesArray.value[index] = e.voucherSubtype;
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
                    <Column field="name" header="Descripción" style="width: 10%;">
                        <template #body="{ data }">
                            {{ data.name }}
                        </template>
                    </Column>
                    <Column header="Subtipos relacionados" style="width: 10%;">
                        <template #body="{ data }">
                            <Button severity="info" raised rounded outlined @click="related(data, $event)">{{
                    data.subtypes.length }}</Button>
                        </template>
                    </Column>
                </DataTable>

                <OverlayPanel ref="subtypesPanel" appendTo="body">
                    <DataTable v-model:filters="subtypesFilters" :value="voucherSubtypesArray" paginator :rows="5"
                        dataKey="id" filterDisplay="row" :globalFilterFields="['name']">
                        <template #empty>
                            <div class="text-center text-lg text-red-500">
                                Sin subtipos cargados
                            </div>
                        </template>
                        <Column field="name" header="Subtipo" style="min-width: 12rem" class="uppercase" sortable>
                            <template #body="{ data }">
                                {{ data.name }}
                            </template>
                            <template #filter="{ filterModel, filterCallback }">
                                <InputText v-model="filterModel.value" type="text" @input="filterCallback()"
                                    name="subtypeName" autocomplete="off" class="p-column-filter"
                                    placeholder="Buscar por subtipo" />
                            </template>
                        </Column>
                        <Column field="related" header="Relacionado" style="width: 5%; min-width: 8rem;">
                            <template #body="{ data }">
                                <i class="pi"
                                    :class="{ 'pi-check-circle text-green-500': data.related, 'pi-times-circle text-red-400': !data.related }"
                                    v-tooltip="data.related ? `Usuario: ${data.related.userRelated.surname} ${data.related.userRelated.name} \n Fecha: ${data.related.related_at}` : 'No relacionado'">
                                </i>
                            </template>
                        </Column>
                    </DataTable>
                </OverlayPanel>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>