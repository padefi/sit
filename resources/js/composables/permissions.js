import { usePage } from "@inertiajs/vue3";

export function usePermissions() {
    const user = () => usePage().props.auth.user.name + ' ' + usePage().props.auth.user.surname;
    const username = () => usePage().props.auth.user.username;
    const hasRole = (name) => usePage().props.auth.user.roles.includes(name);
    const hasPermission = (name) => usePage().props.auth.user.permissions.includes(name);

    return { user, username, hasRole, hasPermission };
}