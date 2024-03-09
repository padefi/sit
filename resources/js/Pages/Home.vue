<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3'

</script>

<template>
    <AuthenticatedLayout>
        <div class="max w-7xl mx-auto py-4 px-4">
            <div class="flex justify-between">
                <h1>You're logged in!</h1>
            </div>

            <div class="mt-6">
                <Link :href="route('logout')" method="post" as="button"
                    class="px-4 py-3 flex items-center space-x-4 rounded-md text-gray-600 group hover:text-red-400">
                <span class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 border border-blue-700 rounded">Logout</span>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
