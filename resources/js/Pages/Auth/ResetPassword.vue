<script setup>
import { inject,  ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';
const route = inject('route');

const props = defineProps({
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);

function submit() {
    form.post(route('password.reset.submit'));
}
</script>

<template>
    <GuestLayout>
        <Head title="Reset Kata Sandi - KPM Belajar Online" />

        <div class="auth-card rounded-[1.75rem] p-5 sm:p-9">
            <div class="mb-7">
                <h1 class="text-xl sm:text-[1.8rem] font-bold text-foreground">Buat kata sandi baru</h1>
                <p class="text-muted-foreground text-sm mt-1.5">Masukkan kata sandi baru Anda di bawah ini.</p>
            </div>

            <div v-if="form.errors && Object.keys(form.errors).length > 0" class="bg-destructive/10 border border-destructive/20 border-l-4 border-l-destructive text-destructive px-4 py-3 rounded-md mb-6 text-sm" role="alert">
                <ul class="space-y-1">
                    <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                </ul>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="space-y-2">
                    <Label for="password">Kata Sandi Baru</Label>
                    <div class="relative">
                        <Input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Minimal 8 karakter"
                            required
                        />
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition p-1">
                            <svg v-if="!showPassword" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg v-else viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A10.6 10.6 0 0 1 12 4c7 0 11 8 11 8a21.5 21.5 0 0 1-2.61 3.94M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="password_confirmation">Konfirmasi Kata Sandi</Label>
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Ulangi kata sandi"
                        required
                    />
                </div>

                <Button type="submit" class="btn-auth w-full py-3.5 text-[15px] font-semibold" :disabled="form.processing">
                    <span v-if="!form.processing">Reset Kata Sandi</span>
                    <span v-else class="flex items-center gap-2">Memproses… <span class="spinner" /></span>
                </Button>

                <div class="text-center">
                    <Link :href="route('login')" class="text-sm text-muted-foreground hover:text-foreground transition">← Kembali ke halaman masuk</Link>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
