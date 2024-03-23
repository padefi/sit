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

            toast.add({
                severity: next.flash.info.type,
                detail: next.flash.info.message,
                life: 3000,
            });
        }
    );

    return { toastService };
}
