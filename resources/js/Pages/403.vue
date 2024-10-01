<script setup>
import { Link } from '@inertiajs/vue3';
</script>
<style>
.border-gradient {
    border-radius: 1.5rem;
    border: 1px solid transparent;
    background: linear-gradient(180deg, #ef4444, transparent 60%) border-box;
}

.card-container {
    border-radius: 1.5rem;
    padding: 1px;
    background-color: transparent;
}
</style>
<template>
    <div class="flex items-center justify-center min-h-[90vh]">
        <div class="card-container border-gradient">
            <Card class="overflow-hidden rounded-3xl">
                <template #title>
                    <div class="flex items-center justify-center">
                        <i class="pi pi-times-circle text-4xl text-red-500"></i>
                        <span class="text-4xl font-bold ml-2">403</span>
                    </div>
                </template>
                <template #content>
                    <div class="w-full bg-surface-0 dark:bg-surface-900 py-5 px-8 sm:px-20 flex flex-col items-center">
                        <h1 class="text-surface-900 dark:text-surface-0 font-bold text-3xl lg:text-4xl mb-2">Sin autorización</h1>
                        <div class="text-surface-600 dark:text-surface-200 mb-8">No posee permiso para acceder a esta página.</div>
                        <Link v-ripple href="/" method="get" as="button"
                            class="px-4 py-3 flex items-center space-x-4 rounded-md text-gray-100 bg-emerald-600 group hover:text-white-400">
                        Volver al inicio
                        </Link>
                    </div>
                </template>
            </Card>
        </div>
    </div>
</template>