<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, onMounted, inject, watch } from "vue";

const userId = ref([]);
const permissions = ref([]);
const dialogRef = inject("dialogRef");
const isProcessing = ref(false);

onMounted(() => {
    permissions.value = dialogRef.value.data.permissions;
    userId.value = dialogRef.value.data.userId;
});

watch(() => dialogRef.value.data, (newData) => {
    permissions.value = newData.permissions;
    userId.value = newData.userId;
});

const isViewSelected = (category) => {
    return category.some(permission => permission.name.startsWith('view') && permission.hasPermission);
}

const updateUserPermission = async (category, permission) => {
    permission.hasPermission = !permission.hasPermission;

    await updatePermissionOnServer(permission);

    if (!isViewSelected(category) && permission.name.startsWith('view')) {
        isProcessing.value = true;
        for (const perm of category) {
            if (!perm.name.startsWith('view') && perm.hasPermission) {
                await removePermission(perm);
            }
        }

        isProcessing.value = false;
    }
};

const removePermission = async (permission) => {
    permission.hasPermission = false;
    await updatePermissionOnServer(permission);
};

const updatePermissionOnServer = (permission) => {
    return new Promise((resolve, reject) => {
        const form = useForm({
            permission: permission.id,
        });

        form.put(route("users.updatePermission", userId.value), {
            onSuccess: () => {
                resolve();
            },
            onError: () => {
                reject();
            }
        });
    });
};
</script>
<template>
    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="py-2 inline-block min-w-full">
                <div class="overflow-hidden">
                    <table class="min-w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="text-lg font-medium text-gray-900 px-6 py-4 text-left">Menú</th>
                                <th class="text-lg text-center font-medium text-gray-900 px-6 py-4 text-left">Ver</th>
                                <th class="text-lg text-center font-medium text-gray-900 px-6 py-4 text-left">Crear</th>
                                <th class="text-lg text-center font-medium text-gray-900 px-6 py-4 text-left">Editar
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(category, index, key) in permissions" class="border-b" :key="key">
                                <td class="px-6 py-4 whitespace-nowrap text-base font-medium text-gray-900">
                                    {{ index }}
                                </td>
                                <template v-for="(permission) in category" :key="permission.id">
                                    <td class="text-base items-center text-center text-gray-900 font-light px-6 py-4 whitespace-nowrap"
                                        v-if="permission.show === 1">
                                        <input :name="'permission_' + permission.id" type="checkbox"
                                            :checked="permission.hasPermission" :value="permission.name"
                                            :disabled="isProcessing || !isViewSelected(category) && !permission.name.startsWith('view')"
                                            @click="updateUserPermission(category, permission)" :class="[
                                'w-6 h-6 cursor-pointer text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600',
                                (isProcessing || (!isViewSelected(category) && !permission.name.startsWith('view'))) ? 'cursor-not-allowed' : 'cursor-pointer'
                            ]" />
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>