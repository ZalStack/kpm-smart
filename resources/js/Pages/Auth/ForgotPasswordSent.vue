<script setup>
import { inject } from 'vue';
const route = inject('route');

import { Head, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    email: { type: String, default: '' },
});
</script>

<template>
    <GuestLayout>
        <Head title="Tautan Terkirim - KPM Belajar Online" />

        <div class="auth-card rounded-[1.75rem] p-5 sm:p-9 text-center">
            <div class="w-16 h-16 rounded-full bg-fern/10 flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-fern" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
            </div>

            <h1 class="text-xl font-bold text-foreground mb-2">Tautan terkirim!</h1>
            <p class="text-muted-foreground text-sm mb-6">
                Kami telah mengirimkan tautan reset password ke <strong>{{ email }}</strong>. Silakan cek inbox atau folder spam Anda.
            </p>

            <Link :href="route('login')" class="inline-flex items-center justify-center btn-auth px-6 py-3 rounded-md text-sm font-semibold text-primary-foreground">
                Kembali ke halaman masuk
            </Link>
        </div>
    </GuestLayout>
</template>
