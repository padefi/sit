<script setup>
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { toastService } from '@/composables/toastService'
import { useDialog } from 'primevue/usedialog';

toastService();
const dialog = useDialog();
const dailyTransactions = ref([]);
const loading = ref(true);

const getFormattedDate = () => {
    const options = { timeZone: 'America/Argentina/Buenos_Aires', year: 'numeric', month: '2-digit', day: '2-digit' };
    const [day, month, year] = new Date().toLocaleDateString('es-AR', options).split('/');
    return `${year}-${month}-${day}`;
};

const getDailyTransactions = async (date) => {    
    try {
        const encodedDate = encodeURIComponent(date);
        const response = await fetch(`/daily-transactions/${date}`);

        if (!response.ok) {
            throw new Error('Error al obtener los datos de los tipos de pago');
        }

        const data = await response.json();
        dailyTransactions.value = data.dailyTransactions;        
    } catch (error) {
        console.error(error);
    }
}

onMounted(async () => {
    const date = getFormattedDate();
    await getDailyTransactions(date);
    loading.value = false;
});
</script>
<template>
    <AuthenticatedLayout>
    </AuthenticatedLayout>
</template>