<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import IncomeTaxWithholdingsIndex from './IncomeTaxWithholdingsIndex.vue';
import SocialSecurityTaxWithholdingsIndex from './SocialSecurityTaxWithholdingsIndex.vue';
import VatTaxWithholdingsIndex from './VatTaxWithholdingsIndex.vue';
import { usePermissions } from '@/composables/permissions';
import { toastService } from '@/composables/toastService';

toastService();

const { hasPermission } = usePermissions();
const activeIndex = ref(0);
const incomeTaxComponentRef = ref(null);
const socialSecurityTaxomponentRef = ref(null);
const vatTaxomponentRef = ref(null);

function handleTabChange(e) {
    activeIndex.value = e.index;

    switch (e.index) {
        case 0:
            if (incomeTaxComponentRef.value) {
                incomeTaxComponentRef.value.fetchIncomeTaxWithholdings();
            }
            break;
        case 1:
            if (socialSecurityTaxomponentRef.value) {
                socialSecurityTaxomponentRef.value.fetchSocialSecurityWithholdings();
            }
            break;
        case 2:
            if (vatTaxomponentRef.value) {
                vatTaxomponentRef.value.fetchVatWithholdings();
            }
            break;
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
                    <template v-if="hasPermission('view social security tax withholdings')">
                        <TabPanel header="Suss">
                            <SocialSecurityTaxWithholdingsIndex ref="socialSecurityTaxomponentRef" />
                        </TabPanel>
                    </template>
                    <TabPanel header="I.V.A." v-if="hasPermission('view vat tax withholdings')">
                        <VatTaxWithholdingsIndex ref="vatTaxomponentRef" />
                    </TabPanel>
                </TabView>
            </template>
        </Card>

        <DynamicDialog />
    </AuthenticatedLayout>
</template>
