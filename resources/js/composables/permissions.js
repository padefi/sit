import { usePage } from "@inertiajs/vue3";

export function usePermissions() {
    const hasRole = (name) => usePage().props.auth.user.roles.includes(name);
    const hasPermission = (name) => usePage().props.auth.user.permissions.includes(name);

    return { hasRole, hasPermission };
}