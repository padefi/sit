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
const newPasswordPattern = {
    uppercase: 'pi pi-times text-red-600',
    lowercase: 'pi pi-times text-red-600',
    number: 'pi pi-times text-red-600',
    special: 'pi pi-times text-red-600',
    length: 'pi pi-times text-red-600',
}

const newPasswordConfirmPattern = {
    uppercase: 'pi pi-times text-red-600',
    lowercase: 'pi pi-times text-red-600',
    number: 'pi pi-times text-red-600',
    special: 'pi pi-times text-red-600',
    length: 'pi pi-times text-red-600',
    match: 'pi pi-times text-red-600',
}

const passwordPattern = {
    uppercase: /[A-Z]/,
    lowercase: /[a-z]/,
    number: /[0-9]/,
    special: /[!@#$%^&*()_+\-=[\]{};':"\\|,.<>/?]/,
}
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


const passwordPatternValidation = (pattern, form) => {    
    pattern.uppercase = passwordPattern.uppercase.test(form) ? 'pi pi-check text-emerald-600' : 'pi pi-times text-red-600';
    pattern.lowercase = passwordPattern.lowercase.test(form) ? 'pi pi-check text-emerald-600' : 'pi pi-times text-red-600';
    pattern.number = passwordPattern.number.test(form) ? 'pi pi-check text-emerald-600' : 'pi pi-times text-red-600';
    pattern.special = passwordPattern.special.test(form) ? 'pi pi-check text-emerald-600' : 'pi pi-times text-red-600';
    pattern.length = form && form.length >= 8 ? 'pi pi-check text-emerald-600' : 'pi pi-times text-red-600';

    if (pattern.match) {
        pattern.match = form === formChangePassword.newPassword ? 'pi pi-check text-emerald-600' : 'pi pi-times text-red-600';
    }
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

.panelPassword {
    h6 {
        margin-top: 0;
        font-size: 0.80rem;
        color: var(--surface-900);
        font-family: inherit;
        font-weight: 600;
        line-height: 1.2;
        margin-bottom: 1rem;
    }

    div[data-pc-section="meter"] {
        margin-bottom: 0.75rem;
        background: #e2e8f0;
        border-radius: 6px;
    }

    div[data-pc-section="meterlabel"] {
        border-radius: 6px;
    }

    div[data-pc-name="divider"] {
        margin: 1rem 0;
    }
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
                                        <i class="pi pi-user absolute text-lg right-3 mt-2 h-6 text-gray-600"></i>
                                        <label for="username">Usuario</label>
                                    </FloatLabel>
                                    <InputError :message="form.username && form.username.trim() === '' || form.username === '' ? rules : ''" />
                                </div>

                                <FloatLabel>
                                    <Password name="password" v-model="form.password" toggleMask :feedback="false" autocomplete="off" class="w-full"
                                        inputClass="w-full focus:!z-0" panelClass="panelPassword" minlength="8"
                                        :class="form.password !== '' && form.password !== undefined ? 'filled' : ''"
                                        :invalid="form.password && (form.password.trim() === '' || form.password === '')">
                                    </Password>
                                    <label for="password">Contraseña</label>
                                </FloatLabel>
                                <InputError :message="form.password && form.password.trim() === '' || form.password === '' ? rules : ''" />

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
                                    <i class="pi pi-user absolute text-lg right-3 mt-2 h-6 text-gray-600"></i>
                                    <label for="username">Usuario</label>
                                </FloatLabel>
                                <InputError
                                    :message="formChangePassword.username && formChangePassword.username.trim() === '' || formChangePassword.username === '' ? rules : ''" />

                                <FloatLabel>
                                    <Password name="currentPassword" v-model="formChangePassword.currentPassword" toggleMask :feedback="false"
                                        autocomplete="off" class="w-full" inputClass="w-full focus:!z-0" panelClass="panelPassword" minlength="8"
                                        :class="formChangePassword.currentPassword !== '' && formChangePassword.currentPassword !== undefined ? 'filled' : ''"
                                        :invalid="formChangePassword.currentPassword && (formChangePassword.currentPassword.trim() === '' || formChangePassword.currentPassword === '')">
                                    </Password>
                                    <label for="currentPassword">Contraseña actual</label>
                                </FloatLabel>
                                <InputError
                                    :message="formChangePassword.currentPassword && formChangePassword.currentPassword.trim() === '' || formChangePassword.currentPassword === '' ? rules : ''" />

                                <div class="relative z-0 group">
                                    <FloatLabel>
                                        <Password name="newPassword" promptLabel="Ingrese contraseña" weakLabel="Debil" mediumLabel="Media"
                                            strongLabel="Fuerte" v-model="formChangePassword.newPassword" toggleMask autocomplete="off" class="w-full"
                                            inputClass="w-full focus:!z-0" panelClass="panelPassword" minlength="8"
                                            @input="passwordPatternValidation(newPasswordPattern, formChangePassword.newPassword)"
                                            :class="formChangePassword.newPassword !== '' && formChangePassword.newPassword !== undefined ? 'filled' : ''"
                                            :invalid="formChangePassword.newPassword && (formChangePassword.newPassword.trim() === '' || formChangePassword.newPassword === '')">
                                            <template #header>
                                                <h6>Establezca su nueva contraseña</h6>
                                            </template>
                                            <template #footer>
                                                <Divider />
                                                <p class="my-2">Requisitos</p>
                                                <ul class="pl-2 ml-2 mt-0 list-disc" style="line-height: 1.5">
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Al menos una letra mayúscula</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordPattern.uppercase"></i>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Al menos una letra minúscula</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordPattern.lowercase"></i>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Al menos un número</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordPattern.number"></i>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Al menos un carácter especial</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordPattern.special"></i>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Al menos 8 caracteres</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordPattern.length"></i>
                                                    </div>
                                                </ul>
                                            </template>
                                        </Password>
                                        <label for="newPassword">Contraseña nueva</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="formChangePassword.newPassword && formChangePassword.newPassword.trim() === '' || formChangePassword.newPassword === '' ? rules : ''" />
                                </div>

                                <div class="relative z-0 group">
                                    <FloatLabel>
                                        <Password name="newPassword_confirmation" promptLabel="Repetir contraseña" weakLabel="Debil"
                                            mediumLabel="Media" strongLabel="Fuerte" v-model="formChangePassword.newPassword_confirmation" toggleMask
                                            autocomplete="off" class="w-full" inputClass="w-full focus:!z-0" panelClass="panelPassword" minlength="8"
                                            @input="passwordPatternValidation(newPasswordConfirmPattern, formChangePassword.newPassword_confirmation)"
                                            @focus="passwordPatternValidation(newPasswordConfirmPattern, formChangePassword.newPassword_confirmation)"
                                            :class="formChangePassword.newPassword_confirmation !== '' && formChangePassword.newPassword_confirmation !== undefined ? 'filled' : ''"
                                            :invalid="formChangePassword.newPassword_confirmation && (formChangePassword.newPassword_confirmation.trim() === '' || formChangePassword.newPassword_confirmation === '')">
                                            <template #header>
                                                <h6>Repita su nueva contraseña</h6>
                                            </template>
                                            <template #footer>
                                                <Divider />
                                                <p class="my-2">Requisitos</p>
                                                <ul class="pl-2 ml-2 mt-0 list-disc" style="line-height: 1.5">
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Al menos una letra mayúscula</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordConfirmPattern.uppercase"></i>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Al menos una letra minúscula</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordConfirmPattern.lowercase"></i>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Al menos un número</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordConfirmPattern.number"></i>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Al menos un carácter especial</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordConfirmPattern.special"></i>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Al menos 8 caracteres</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordConfirmPattern.length"></i>
                                                    </div>
                                                    <div class="flex space-x-2">
                                                        <li class="w-2/3">Las contraseñas deben coincidir</li>
                                                        <i class="w-1/3 mt-1" :class="newPasswordConfirmPattern.match"></i>
                                                    </div>
                                                </ul>
                                            </template>
                                        </Password>
                                        <label for="password">Repetir contraseña</label>
                                    </FloatLabel>
                                    <InputError
                                        :message="formChangePassword.newPassword_confirmation && formChangePassword.newPassword_confirmation.trim() === '' || formChangePassword.newPassword_confirmation === '' ? rules : ''" />
                                </div>

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
