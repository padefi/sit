<script setup>
import { ref, onMounted, inject } from "vue";

const permissions = ref([]);
const dialogRef = inject("dialogRef");

onMounted(() => {
    permissions.value = dialogRef.value.data.permissions;
});
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
                                <template v-for="(permission, index, key) in category" :key="permission.id">
                                    <td class="text-base items-center text-center text-gray-900 font-light px-6 py-4 whitespace-nowrap"
                                        v-if="permission.show === 1">
                                        <input :name="'permission_' + permission.id" type="checkbox"
                                            :checked="permission.show" :value="permission.name"
                                            class="w-6 h-6 cursor-pointer text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
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