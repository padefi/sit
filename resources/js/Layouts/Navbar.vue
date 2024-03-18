<script setup>
import { ref } from "vue";
import { Link, usePage } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/permissions'

const { user, username, hasRole, hasPermission } = usePermissions();

const items = ref([
    {
        label: 'Home',
        icon: 'pi pi-home',
        method: 'get',
        route: 'home'
    },
    {
        label: 'Usuarios',
        icon: 'pi pi-users',
        method: 'get',
        route: 'users.index'
    },
    {
        label: user(),
        icon: 'pi pi-user',
        items: [
            {
                label: 'Perfil',
                icon: 'pi pi-user-edit',
                /* method: 'post',
                route: 'logout', */
            },
        ]

    },
]);

const userItems = ref([
    {
        label: 'Logout',
        icon: 'pi pi-sign-out',
        method: 'post',
        route: 'logout',
    },
]);
</script>
<template>
    <Menubar :model="items">
        <template #item="{ item, props, hasSubmenu, root }">
            <Link v-if="item.route" v-ripple :href="route(item.route)" :method="item.method" as="button"
                class="px-4 py-3 flex items-center space-x-4 rounded-md text-gray-600 group hover:text-white-400">
            <span :class="item.icon"></span>
            <span class="ml-2">{{ item.label }}</span>
            </Link>
            <a v-else v-ripple class="flex items-center" v-bind="props.action">
                <span :class="item.icon" />
                <span class="ml-2">{{ item.label }}</span>
                <Badge v-if="item.badge" :class="{ 'ml-auto': !root, 'ml-2': root }" :value="item.badge" />
                <span v-if="item.shortcut"
                    class="ml-auto border border-surface-200 dark:border-surface-500 rounded-md bg-surface-100 dark:bg-surface-800 text-xs p-1">
                    {{ item.shortcut }}
                </span>
                <i v-if="hasSubmenu"
                    :class="['pi pi-angle-down text-primary-500 dark:text-primary-400-500 dark:text-primary-400', { 'pi-angle-down ml-2': root, 'pi-angle-right ml-auto': !root }]"></i>
            </a>
        </template>
        <template #end>
            <Link v-for="userItem in userItems" v-ripple :href="route(userItem.route)" :method="userItem.method" as="button"
                class="px-4 py-3 flex items-center space-x-4 rounded-md text-gray-600 group hover:text-white-400">
            <span :class="userItem.icon"></span>
            <span class="ml-2">{{ userItem.label }}</span>
            </Link>
        </template>
    </Menubar>
</template>
