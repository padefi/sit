import { useToast } from "primevue/usetoast";
import { usePage } from "@inertiajs/vue3";
import { watch } from "vue";

export function toastService() {
    const toast = useToast();

    watch(
        () => usePage().props,
        (next) => {
            if(next.errors.message) {
                toast.add({
                    severity: 'error',
                    detail: next.errors.message,
                    life: 3000,
                })

                return;
            }

            if(Object.keys(next.errors).length > 0) {
                Object.keys(next.errors).map((key) => {
                    toast.add({
                        severity: 'error',
                        detail: next.errors[key],
                        life: 3000,
                    })
                });

                return;
            }

            toast.add({
                severity: next.flash.info?.type ?? 'info',
                detail: next.flash.info?.message ?? '',
                life: 3000,
            });
        }
    );

    return { toastService };
}
