<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3'
import { LockClosedIcon } from '@heroicons/vue/24/outline'
import { useToast } from "primevue/usetoast";
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue'

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    username: undefined,
    password: undefined,
    remember: false,
});

const formChangePassword = useForm({
    username: undefined,
    currentPassword: undefined,
    newPassword: undefined,
    newPassword_confirmation: undefined,
});

const toast = useToast();
const back = ref(false);
const flip = ref(false);
const visiblePassword = ref(false);
const visiblecurrentPassword = ref(false);
const visibleNewPassword = ref(false);
const visibleConfirmPassword = ref(false);
const rules = 'Debe completar el campo'

const openMailClient = () => {
    const email = 'soporte@sit.com';
    const subject = 'Recuperación de contraseña';
    const mailtoLink = `mailto:${email}?subject=${encodeURIComponent(subject)}`;
    window.location.href = mailtoLink;
}

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onError: (errors) => {
            if (errors.changePassword) {
                back.value = true
                formChangePassword.username = form.username
                formChangePassword.reset('currentPassword', 'newPassword', 'confirmPassword')
                flip.value = true;
            }
        }
    });
}

const submitChangePassword = () => {
    if (!formChangePassword.username || !formChangePassword.currentPassword || !formChangePassword.newPassword || !formChangePassword.newPassword_confirmation) {
        toast.add({
            severity: 'error',
            detail: 'Debe completar todos los campos',
            life: 3000,
        });
        return;
    }

    if (formChangePassword.username === formChangePassword.newPassword) {
        toast.add({
            severity: 'error',
            detail: 'La contraseña no puede ser igual al nombre de usuario',
            life: 3000,
        });

        return;
    }

    if (formChangePassword.newPassword !== formChangePassword.newPassword_confirmation) {
        toast.add({
            severity: 'error',
            detail: 'Las contraseñas no coinciden',
            life: 3000,
        });

        return;
    }

    if (formChangePassword.newPassword.length < 8 || formChangePassword.newPassword_confirmation.length < 8) {
        toast.add({
            severity: 'error',
            detail: 'La contraseña debe tener al menos 8 caracteres',
            life: 3000,
        });
        return;
    }

    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

    if (!passwordPattern.test(formChangePassword.newPassword)) {
        toast.add({
            severity: 'error',
            detail: 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
            life: 3000,
        });
        return;
    }

    formChangePassword.put(route('change-password'), {
        onSuccess: () => {
            flip.value = false;
            form.username = formChangePassword.username;
            form.reset('password');
            formChangePassword.reset('username', 'currentPassword', 'newPassword', 'newPassword_confirmation');

            setTimeout(() => {
                back.value = false;
            }, 500);
        }
    });
}
</script>
<style scope>
.flipper {
    width: 30rem;
    height: 30rem;
    position: relative;
    transform-style: preserve-3d;
}

.front,
.back {
    position: absolute;
    padding: 0.5rem;
    top: 0;
    left: 0;
    right: 0;
    backface-visibility: hidden;
}

.back {
    transform: rotateY(180deg);
}
</style>
<template>
    <GuestLayout>

        <Head title="Log in" />
        <div v-if="status" class="mb-4 font-medium text-base text-green-600">
            {{ status }}
        </div>
        <div class="flip-container relative bottom-10">
            <div class="flipper transition-transform duration-1000 relative"
                :style="flip ? 'transform: rotateY(180deg)' : 'transform: rotateY(0deg)'">
                <Card class="front">
                    <template #content>
                        <div class="space-y-5">
                            <div class="text-center">
                                <h1 class="font-[inherit] font-bold text-3xl bg-gradient-to-r text-cyan-600 inline-block bg-clip-text">
                                    Iniciar Sesión
                                </h1>
                            </div>

                            <form @submit.prevent="submit" class="space-y-3">
                                <div class="relative z-0 group">
                                    <FloatLabel>
                                        <InputText name="username" v-model="form.username" autocomplete="off" class="w-full"
                                            :invalid="form.username && (form.username.trim() === '' || form.username === '')" />
                                        <i class="pi pi-user absolute text-xl right-1 mt-2 h-6 text-gray-400"></i>
                                        <label for="username">Usuario</label>
                                    </FloatLabel>
                                    <InputError :message="form.username && form.username.trim() === '' || form.username === '' ? rules : ''" />
                                </div>
                                <div class="relative z-0 group pb-4">
                                    <FloatLabel>
                                        <InputText name="password" :type="visiblePassword ? 'text' : 'password'" v-model="form.password"
                                            autocomplete="off" class="w-full"
                                            :invalid="form.password && (form.password.trim() === '' || form.password === '')" />
                                        <i :class="visiblePassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                                            class="absolute text-xl right-1 mt-2 h-6 cursor-pointer text-gray-400"
                                            @click="visiblePassword = !visiblePassword"></i>
                                        <label for="password">Contraseña</label>
                                    </FloatLabel>
                                    <InputError :message="form.password && form.password.trim() === '' || form.password === '' ? rules : ''" />
                                </div>

                                <div class="flex items-center justify-end my-4">
                                    <PrimaryButton :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing || !form.username || !form.password">
                                        Log in
                                    </PrimaryButton>
                                </div>
                            </form>

                            <hr class="relative !my-5 w-[28rem] left-1/2 -translate-x-1/2 border-gray-300">

                            <button
                                class="transition duration-200 !m-0 px-2 py-2 cursor-pointer font-normal text-base rounded-lg text-gray-500 hover:border-2 hover:border-dashed hover:border-blue-300"
                                @click="openMailClient">
                                <LockClosedIcon class="h-6 inline-block" />
                                <span class="inline-block ml-1">¿Ha olvidado su contraseña?</span>
                            </button>
                        </div>
                    </template>
                </Card>

                <Card class="back" v-if="back">
                    <template #content>
                        <div class="space-y-5">
                            <div class="relative top-3 text-center">
                                <h1 class="font-[inherit] font-bold text-3xl bg-gradient-to-r text-emerald-600 inline-block bg-clip-text">
                                    Cambiar contraseña
                                </h1>
                            </div>

                            <form @submit.prevent="submitChangePassword" class="space-y-3 !mt-7">
                                <FloatLabel>
                                    <InputText name="usernameChangePassword" v-model="formChangePassword.username" autocomplete="off" class="w-full"
                                        :invalid="formChangePassword.username && (formChangePassword.username.trim() === '' || formChangePassword.username === '')" />
                                    <i class="pi pi-user absolute text-xl right-1 mt-2 h-6 text-gray-400"></i>
                                    <label for="username">Usuario</label>
                                </FloatLabel>
                                <InputError
                                    :message="formChangePassword.username && formChangePassword.username.trim() === '' || formChangePassword.username === '' ? rules : ''" />

                                <FloatLabel>
                                    <InputText name="currentPassword" :type="visiblecurrentPassword ? 'text' : 'password'"
                                        v-model="formChangePassword.currentPassword" autocomplete="off" class="w-full"
                                        :invalid="formChangePassword.currentPassword && (formChangePassword.currentPassword.trim() === '' || formChangePassword.currentPassword === '')" />
                                    <i :class="visiblecurrentPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                                        class="absolute text-xl right-1 mt-2 h-6 cursor-pointer text-gray-400"
                                        @click="visiblecurrentPassword = !visiblecurrentPassword"></i>
                                    <label for="currentPassword">Contraseña anterior</label>
                                </FloatLabel>
                                <InputError
                                    :message="formChangePassword.currentPassword && formChangePassword.currentPassword.trim() === '' || formChangePassword.currentPassword === '' ? rules : ''" />

                                <FloatLabel>
                                    <InputText name="newPassword" :type="visibleNewPassword ? 'text' : 'password'"
                                        v-model="formChangePassword.newPassword" autocomplete="off" class="w-full" minlength="8"
                                        :invalid="formChangePassword.newPassword && (formChangePassword.newPassword.trim() === '' || formChangePassword.newPassword === '')" />
                                    <i :class="visibleNewPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                                        class="absolute text-xl right-1 mt-2 h-6 cursor-pointer text-gray-400"
                                        @click="visibleNewPassword = !visibleNewPassword"></i>
                                    <label for="newPassword">Contraseña nueva</label>
                                </FloatLabel>
                                <InputError
                                    :message="formChangePassword.newPassword && formChangePassword.newPassword.trim() === '' || formChangePassword.newPassword === '' ? rules : ''" />

                                <FloatLabel>
                                    <InputText name="newPassword_confirmation" :type="visibleConfirmPassword ? 'text' : 'password'"
                                        v-model="formChangePassword.newPassword_confirmation" autocomplete="off" class="w-full" minlength="8"
                                        :invalid="formChangePassword.newPassword_confirmation && (formChangePassword.newPassword_confirmation.trim() === '' || formChangePassword.newPassword_confirmation === '')" />
                                    <i :class="visibleConfirmPassword ? 'pi pi-eye-slash' : 'pi pi-eye'"
                                        class="absolute text-xl right-1 mt-2 h-6 cursor-pointer text-gray-400"
                                        @click="visibleConfirmPassword = !visibleConfirmPassword"></i>
                                    <label for="newPassword_confirmation">Repetir contraseña</label>
                                </FloatLabel>
                                <InputError
                                    :message="formChangePassword.newPassword_confirmation && formChangePassword.newPassword_confirmation.trim() === '' || formChangePassword.newPassword_confirmation === '' ? rules : ''" />

                                <div class="flex items-center justify-end !mt-5">
                                    <PrimaryButton :class="{ 'opacity-25': formChangePassword.processing }"
                                        :disabled="formChangePassword.processing || !formChangePassword.username || !formChangePassword.currentPassword || !formChangePassword.newPassword || !formChangePassword.newPassword_confirmation">
                                        Modificar contraseña
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </GuestLayout>
</template>
