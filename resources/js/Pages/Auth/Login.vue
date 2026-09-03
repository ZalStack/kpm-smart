<script setup>
import { ref, inject } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';
import Alert from '@/Components/ui/alert/Alert.vue';
import AlertTitle from '@/Components/ui/alert/AlertTitle.vue';
import AlertDescription from '@/Components/ui/alert/AlertDescription.vue';

const route = inject('route');

const props = defineProps({
    status: { type: String, default: '' },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

function submit() {
    form.post(route('login'));
}
</script>

<template>
    <GuestLayout>
        <Head title="Masuk - KPM Belajar Online" />

        <div class="auth-card rounded-[1.75rem] p-5 sm:p-9">
            <div class="mb-7">
                <h1 class="text-xl sm:text-[1.8rem] font-bold text-foreground">Selamat datang kembali</h1>
                <p class="text-muted-foreground text-sm mt-1.5">Masuk untuk melanjutkan proses belajarmu.</p>
            </div>

            <div v-if="status" class="mb-4">
                <Alert variant="success">
                    <AlertTitle>{{ status }}</AlertTitle>
                </Alert>
            </div>

            <div v-if="form.errors && Object.keys(form.errors).length > 0" class="bg-destructive/10 border border-destructive/20 border-l-4 border-l-destructive text-destructive px-4 py-3 rounded-md mb-6 text-sm" role="alert">
                <div class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                    <ul class="space-y-1">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5" novalidate>
                <div class="space-y-2">
                    <Label for="email">Alamat Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="Masukkan email"
                        required
                        autofocus
                        autocomplete="email"
                    />
                    <p v-if="form.errors.email" class="text-xs text-destructive mt-1">{{ form.errors.email }}</p>
                </div>

                <div class="space-y-2">
                    <Label for="password">Kata Sandi</Label>
                    <div class="relative">
                        <Input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Masukkan kata sandi"
                            required
                            autocomplete="current-password"
                            class="pr-12"
                        />
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition p-1">
                            <svg v-if="!showPassword" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg v-else viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A10.6 10.6 0 0 1 12 4c7 0 11 8 11 8a21.5 21.5 0 0 1-2.61 3.94M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                        </button>
                    </div>
                    <p v-if="form.errors.password" class="text-xs text-destructive mt-1">{{ form.errors.password }}</p>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" v-model="form.remember" class="sr-only peer">
                        <span class="relative inline-flex h-6 w-11 items-center rounded-full bg-input transition-colors peer-checked:bg-primary">
                            <span :class="['inline-block h-4 w-4 transform rounded-full bg-background shadow-lg transition-transform', form.remember ? 'translate-x-6' : 'translate-x-1']" />
                        </span>
                        <span class="text-sm text-muted-foreground">Ingat saya</span>
                    </label>
                    <Link :href="route('password.request')" class="text-sm text-primary hover:text-primary/80 font-semibold transition">Lupa kata sandi?</Link>
                </div>

                <Button type="submit" class="btn-auth w-full py-3.5 text-[15px] font-semibold" :disabled="form.processing">
                    <span v-if="!form.processing">Masuk</span>
                    <span v-else class="flex items-center gap-2">Memproses… <span class="spinner" /></span>
                </Button>
            </form>
        </div>
    </GuestLayout>
</template>
