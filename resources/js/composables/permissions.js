import { usePage } from "@inertiajs/vue3";

export function usePermissions() {
    const user = () => usePage().props.auth.user.name + ' ' + usePage().props.auth.user.surname;
    const username = () => usePage().props.auth.user ? usePage().props.auth.user.username : '';
    const hasRole = (name) => usePage().props.auth.user.roles.includes(name);
    const hasPermission = (name) => usePage().props.auth.user.permissions.includes(name);
    const hasPermissionColumn = (dataArray) => dataArray.some(data => hasPermission(data));

    return { user, username, hasRole, hasPermission, hasPermissionColumn };
}