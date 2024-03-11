<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import FormInput from '@/Components/FormInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { EyeIcon, EyeSlashIcon, LockClosedIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const rules = 'Debe completar el campo'

const blurInput = e => {
    if (e.key !== 'Tab') {
        if (!e.target.value) {
            form.errors[e.target.id] = rules
        } else {
            form.errors[e.target.id] = ''
        }
    }
}

const visible = ref(false)
const togglePassword = () => {
    visible.value = !visible.value
}

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />
        <div v-if="status" class="mb-4 font-medium text-base text-green-600">
            {{ status }}
        </div>

        <div class="space-y-5">
            <div class="relative top-3 text-center">
                <h1
                    class="font-[inherit] font-bold text-3xl bg-gradient-to-r from-cyan-600 to-cyan-600 inline-block text-transparent bg-clip-text">
                    Iniciar Sesión
                </h1>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="relative z-0 group">
                    <FormInput value="Usuario" name="username" type="text" v-model="form.username" :uppercase="false"
                        :blurFunction="blurInput" :colorInput="form.errors.username ? 'alert-input' : 'normal-input'"
                        :colorLabel="form.errors.username ? 'alert-label' : 'normal-label'" />
                    <InputError class="mt-2" :message="form.errors.username" />
                </div>
                <div class="relative z-0 group pb-4">
                    <div class="flex items-center">
                        <FormInput value="Contraseña" name="password" :type="visible ? 'text' : 'password'"
                            v-model="form.password" :blurFunction="blurInput"
                            :colorInput="form.errors.password ? 'alert-input' : 'normal-input'"
                            :colorLabel="form.errors.password ? 'alert-label' : 'normal-label'" />
                        <EyeIcon class="absolute right-0.5 mt-2 h-6 cursor-pointer text-gray-400"
                            @click="togglePassword" v-if="!visible" />
                        <EyeSlashIcon class="absolute right-0.5 mt-2 h-6 cursor-pointer text-gray-400"
                            @click="togglePassword" v-if="visible" />
                    </div>
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-end my-4">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Log in
                    </PrimaryButton>
                </div>
            </form>

            <hr class="relative !my-5 w-[28rem] left-1/2 -translate-x-1/2 border-gray-300">

            <button
                class="transition duration-200 !m-0 px-2 py-2 cursor-pointer font-normal text-base rounded-lg text-gray-500 hover:border-2 hover:border-dashed hover:border-blue-300">
                <LockClosedIcon class="h-6 inline-block" />
                <span class="inline-block ml-1">¿Ha olvidado su contraseña?</span>
            </button>
        </div>
    </GuestLayout>
</template>
