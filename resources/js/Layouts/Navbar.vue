<script setup>
import { computed, ref } from "vue";
import { Link } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/permissions'

const { user, hasPermission } = usePermissions();

const allItems = ref([
    {
        label: 'Home',
        icon: 'pi pi-home',
        method: 'get',
        route: 'home'
    },
    {
        label: 'Configuración',
        icon: 'pi pi-cog',
        items: [
            {
                label: 'Bancos',
                icon: 'pi pi-building-columns',
                method: 'get',
                route: 'banks.index',
                name: 'banks',
            },
            {
                label: 'Relaciones',
                icon: 'pi pi-database',
                items: [
                    {
                        label: 'Tipos',
                        icon: 'pi pi-list',
                        method: 'get',
                        route: 'voucher-types.index',
                        name: 'voucher types',
                    },
                    {
                        label: 'Subtipos',
                        icon: 'pi pi-share-alt',
                        method: 'get',
                        route: 'voucher-subtypes.index',
                        name: 'voucher subtypes',
                    },
                    {
                        label: 'Gastos',
                        icon: 'pi pi-sitemap',
                        method: 'get',
                        route: 'voucher-expenses.index',
                        name: 'voucher expenses',
                    },
                ]
            },
            {
                label: 'Retenciones',
                icon: 'pi pi-book',
                method: 'get',
                route: 'taxes.index',
                name: [
                    'income tax withholdings',
                    'social security tax withholdings',
                    'vat tax withholdings',
                ]
            },
        ]
    },
    {
        label: 'Finanzas',
        icon: 'pi pi-money-bill',
        items: [
            {
                label: 'Comprobantes',
                icon: 'pi pi-wallet',
                method: 'get',
                route: 'treasury-vouchers.index',
                name: 'treasury vouchers',
            },
            {
                label: 'Proveedores',
                icon: 'pi pi-truck',
                method: 'get',
                route: 'suppliers.index',
                name: 'suppliers',
            },
        ]
    },
    {
        label: 'Usuarios',
        icon: 'pi pi-users',
        method: 'get',
        route: 'users.index',
        name: 'users',
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

const filterItems = (items) => {
    return items.reduce((acc, item) => {
        if (item.items) {
            // If the item has sub-items, we need to filter them recursively
            const filteredSubItems = filterItems(item.items);
            if (filteredSubItems.length > 0 || (item.name && hasPermissionForName(item.name))) {
                acc.push({ ...item, items: filteredSubItems });
            }
        } else if (item.name) {
            // If the item has no sub-items, we need to check if it has permission
            if (hasPermissionForName(item.name)) {
                acc.push(item);
            }
        } else {
            // If the item has no sub-items and no name, we just include it
            acc.push(item);
        }
        return acc;
    }, []);
};

const hasPermissionForName = (name) => {
    if (Array.isArray(name)) {
        return name.some(n => hasPermission('view ' + n));
    } else {
        return hasPermission('view ' + name);
    }
};

const items = computed(() => {
    return filterItems(allItems.value);
});
</script>
<template>
    <Menubar :model="items" class="menubar">
        <template #item="{ item, props, hasSubmenu, root }">
            <Link v-if="item.route" v-ripple :href="route(item.route)" :method="item.method" as="button"
                class="px-4 py-2 flex items-center space-x-4 rounded-md text-gray-600 group hover:text-white-400 hover:rounded-md">
            <span :class="item.icon"></span>
            <span class="ml-2">{{ item.label }}</span>
            </Link>
            <a v-else v-ripple class="!px-4 !py-3 flex items-center" v-bind="props.action">
                <span :class="item.icon" />
                <span class="ml-4">{{ item.label }}</span>
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
