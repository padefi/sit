<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePermissions } from '@/composables/permissions';
import { toastService } from '@/composables/toastService'

toastService();

const props = defineProps({
    subtypes: {
        type: Object,
        default: () => ({}),
    },
});

const { hasPermission } = usePermissions();

const addNewSubtype = () => {

};

</script>

<template>
    <AuthenticatedLayout>
        <Card class="mt-5 mx-4 uppercase">
            <template #title>
                <div class="flex justify-between items-center mx-4">
                    <div class="align-left">
                        <h3 class="uppercase">Subtipos</h3>
                    </div>
                    <template v-if="hasPermission('create voucher subtypes')">
                        <div class="align-right">
                            <Button label="Agregar subtipo" severity="info" outlined icon="pi pi-list" size="large"
                                @click="addNewSubtype($event)" />
                        </div>
                    </template>
                </div>
            </template>
            <template #content>
            </template>
        </Card>
    </AuthenticatedLayout>
</template>