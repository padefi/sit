<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import IncomeTaxWithholdingsIndex from './IncomeTaxWithholdingsIndex.vue';
import { usePermissions } from '@/composables/permissions';

const { hasPermission } = usePermissions();
const activeIndex = ref(0);
const incomeTaxComponentRef = ref(null);

function handleTabChange(e) {
    activeIndex.value = e.index;

    if (e.index === 0 && incomeTaxComponentRef.value) {
        incomeTaxComponentRef.value.fetchIncomeTaxWithholdings();
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Card class="mt-5 mx-4 uppercase">
            <template #title>
                <div class="flex justify-between items-center mx-4">
                    <div class="align-left">
                        <h3 class="uppercase">Impuestos</h3>
                    </div>
                </div>
            </template>
            <template #content>
                <TabView @tab-change="handleTabChange" v-model:activeIndex="activeIndex">
                    <template v-if="hasPermission('view income tax withholdings')">
                        <TabPanel header="Ganancias">
                            <IncomeTaxWithholdingsIndex ref="incomeTaxComponentRef" />
                        </TabPanel>
                    </template>
                    <template v-if="hasPermission('view social security withholdings')">
                        <TabPanel header="Suss">
                        </TabPanel>
                    </template>
                    <TabPanel header="I.V.A.">
                    </TabPanel>
                </TabView>
            </template>
        </Card>
    </AuthenticatedLayout>
</template>
