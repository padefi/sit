<script setup>
import { inject, onMounted, ref } from "vue";
import { usePermissions } from '@/composables/permissions';

const data = ref({});
const vatConditions = ref([]);
const categories = ref([]);
const dialogRef = inject("dialogRef");

onMounted(async () => {
    vatConditions.value = dialogRef.value.data.vatConditions;
    categories.value = dialogRef.value.data.categories;
    data.value = dialogRef.value.data.supplierData;
    console.log(data.value);
});
</script>
<template>
    <div class="flex flex-col border-2 border-dashed border-surface-200 dark:border-surface-700 rounded-md bg-surface-0 dark:bg-surface-950 justify-center font-medium">
        <div class="flex w-5/5 gap-3 mx-3 my-0">
            <div class="w-full md:w-1/5">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">Cuit</div>
                <div>{{ data.cuit }}</div>
            </div>

            <div class="w-full md:w-2/5">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">Razón Social
                </div>
                <div class="uppercase">{{ data.name }}</div>
            </div>

            <div class="w-full md:w-2/5">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">Nombre de
                    fantasía</div>
                <div class="uppercase">{{ data.businessName }}</div>
            </div>
        </div>

        <Divider class="mt-3 mb-0" type="dashed" />

        <div class="flex w-5/5 gap-3 m-3 mb-0">
            <div class="w-full md:w-1/5">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">País</div>
                <div class="uppercase">{{ data.country }}</div>
            </div>

            <div class="w-full md:w-1/5">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">Provincia</div>
                <div class="uppercase">{{ data.state }}</div>
            </div>

            <div class="w-full md:w-1/5">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">Ciudad</div>
                <div class="uppercase">{{ data.city }}</div>
            </div>

            <div class="w-full md:w-2/5">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">Calle</div>
                <div class="uppercase">
                    {{ data.street }}
                    {{ data.streetNumber }} -
                    {{ data.postalCode }}
                </div>
            </div>
        </div>

        <Divider class="mt-3 mb-0" type="dashed" />

        <div class="flex w-5/5 gap-3 m-3 mb-0">
            <div class="w-full md:w-2/5">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">Email</div>
                <div class="uppercase" :class="{ 'text-red-500': !data.email }">
                    {{ (data.email) ? data.email : 'Sin email' }}
                </div>
            </div>

            <div class="w-full md:w-1/5">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">Teléfono</div>
                <div class="uppercase" :class="{ 'text-red-500': !data.phone }">
                    {{ (data.phone) ? data.phone : 'Sin teléfono' }}
                </div>
            </div>

            <div class="w-full md:w-2/5">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">CBU</div>
                <div class="uppercase" :class="{ 'text-red-500': !data.cbu }">
                    {{ (data.cbu) ? data.cbu : 'Sin CBU' }}
                </div>
            </div>
        </div>

        <Divider align="center" type="solid" class="text-lg text-primary-700 !mt-2 mb-0">
            <b>Datos fiscales</b>
        </Divider>

        <div class="flex w-5/5 gap-3 m-3 mb-0">
            <div class="w-full md:w-3/6">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">
                    Condición de I.V.A.
                </div>
                <div class="uppercase">
                    {{ (data.idVC) && vatConditions.filter(v => v.id === data.idVC)[0].name }}
                </div>
            </div>

            <div class="w-full md:w-3/6">
                <div class="w-full text-sm text-left text-surface-900/60 font-bold">Rubro</div>
                <div class="uppercase">
                    {{ (data.idCat) && categories.filter(c => c.id === data.idCat)[0].name }}
                </div>
            </div>
        </div>

        <Divider class="mt-3 mb-0" type="dashed" />

        <div class="flex w-2/2 gap-3 m-3">
            <div class="flex w-full flex-col align-items-center">
                <div class="w-full text-center font-bold bottom-2 relative">Retenciones</div>
                <div class="flex w-full">
                    <div class="flex align-items-center w-1/3">
                        <div class="w-full">
                            <div class="w-full text-sm text-surface-900/60 font-bold">
                                Gcias.
                            </div>
                            <div class="uppercase" :class="{ 'text-red-500': !data.incomeTax }">
                                {{ (data.incomeTax) ? 'SI' : 'NO' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex align-items-center w-1/3">
                        <div class="w-full">
                            <div class="w-full text-sm text-surface-900/60 font-bold">
                                Suss
                            </div>
                            <div class="uppercase" :class="{ 'text-red-500': !data.socialSecurityTax }">
                                {{ (data.socialSecurityTax) ? 'SI' : 'NO' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex align-items-center w-1/3">
                        <div class="w-full">
                            <div class="w-full text-sm text-surface-900/60 font-bold">
                                I.V.A.
                            </div>
                            <div class="uppercase" :class="{ 'text-red-500': !data.vatTax }">
                                {{ (data.vatTax) ? 'SI' : 'NO' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>