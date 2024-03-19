<script setup>
import { Head } from '@inertiajs/vue3';
import Navbar from './Navbar.vue';
</script>
<template>

    <Head title="Dashboard" />
    <Navbar />
    <div>
        <slot />
    </div>
    <Toast />
</template>
