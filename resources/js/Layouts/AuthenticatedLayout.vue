<script setup>
import { onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import { usePermissions } from '@/composables/permissions';
import { router } from '@inertiajs/vue3'
import Navbar from './Navbar.vue';

const { username } = usePermissions();

onMounted(async () => {
    Echo.channel('users')
        .listen('Users\\UserEvent', (e) => {
            if (e.type === 'update') {
                if (e.user.username === username() && (e.user.is_active === 0 || e.user.reset_password === 1)) {
                    router.post('/logout');
                }
            }
        });
});
</script>
<template>

    <Head title="Dashboard" />
    <Navbar />
    <div>
        <slot />
    </div>
    <Toast />
</template>
