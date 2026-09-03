<script setup>
import { inject } from 'vue';
const route = inject('route');

import { Head, useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';

const form = useForm({
    email: '',
});

function submit() {
    form.post(route('password.email'));
}
</script>

<template>
    <GuestLayout>
        <Head title="Lupa Kata Sandi - KPM SMART" />

        <div class="auth-card rounded-[1.75rem] p-5 sm:p-9">
            <div class="mb-7">
                <h1 class="text-xl sm:text-[1.8rem] font-bold text-foreground">Lupa kata sandi?</h1>
                <p class="text-muted-foreground text-sm mt-1.5">Masukkan email Anda dan kami akan mengirimkan tautan reset password.</p>
            </div>

            <div v-if="form.errors && Object.keys(form.errors).length > 0" class="bg-destructive/10 border border-destructive/20 border-l-4 border-l-destructive text-destructive px-4 py-3 rounded-md mb-6 text-sm" role="alert">
                <ul class="space-y-1">
                    <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="space-y-2">
                    <Label for="email">Alamat Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="Masukkan email terdaftar"
                        required
                        autofocus
                    />
                </div>

                <Button type="submit" class="btn-auth w-full py-3.5 text-[15px] font-semibold" :disabled="form.processing">
                    <span v-if="!form.processing">Kirim Tautan Reset</span>
                    <span v-else class="flex items-center gap-2">Mengirim… <span class="spinner" /></span>
                </Button>

                <div class="text-center">
                    <Link :href="route('login')" class="text-sm text-muted-foreground hover:text-foreground transition">← Kembali ke halaman masuk</Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
