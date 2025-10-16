<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const { props } = usePage()
console.log(props)

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Inicio de sesión" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">

            <div class="mt-2">

                <TextInput id="text" v-model="form.email" type="text" class="mt-1 block w-full" required
                    placeholder="Username" autofocus autocomplete="username" />
            </div>

            <div class="mt-2">

                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required
                    placeholder="Password" autocomplete="current-password" />
            </div>

            <InputError class="mt-2" :message="form.errors.password" />
            <InputError class="mt-2" :message="form.errors.email" />
            <!-- <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Recuérdame</span>
                </label>
            </div> -->

            <div class="flex items-center justify-end mt-4">
                <PrimaryButton class="w-full h-10" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Login
                </PrimaryButton>
            </div>
            <div class="mt-2">
                <img src="/images/logo-transparente.png" alt="logo" class="w-1/2 mx-auto">
            </div>
            <div class="mt-2 text-white text-center font-bold">
                <Link :href="route('password.request')" class="text-sm">
                Forgot your password? <span class="">Reset your password</span>
                </Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
