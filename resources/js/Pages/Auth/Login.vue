<script setup>
import { ref, computed, inject } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';
import Alert from '@/Components/ui/alert/Alert.vue';
import AlertTitle from '@/Components/ui/alert/AlertTitle.vue';

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

// Error yang punya field sendiri ditampilkan tepat di bawah field tersebut.
// Sisanya (mis. "kredensial salah") tetap lewat blok ringkasan agar informasinya
// tidak hilang — sebelumnya keduanya ditampilkan sehingga pesan muncul dua kali.
const FIELD_ERRORS = ['email', 'password'];

const generalErrors = computed(() =>
    Object.entries(form.errors || {})
        .filter(([key]) => !FIELD_ERRORS.includes(key))
        .map(([, message]) => message)
);

const hasErrors = computed(() => Object.keys(form.errors || {}).length > 0);
</script>

<template>
    <GuestLayout>
        <Head title="Masuk - KPM SMART" />

        <div class="auth-card rounded-[1.75rem] p-5 sm:p-9 anim-fade-in-up">
            <div class="mb-7">
                <h1 class="text-xl sm:text-[1.8rem] font-bold text-foreground">Selamat datang kembali</h1>
                <p class="text-muted-foreground text-sm mt-1.5">Masuk untuk melanjutkan proses belajarmu.</p>
            </div>

            <div v-if="status" class="mb-4">
                <Alert variant="success">
                    <AlertTitle>{{ status }}</AlertTitle>
                </Alert>
            </div>

            <div
                v-if="generalErrors.length > 0"
                class="bg-destructive/10 border border-destructive/20 border-l-4 border-l-destructive text-destructive px-4 py-3 rounded-xl mb-6 text-sm"
                role="alert"
            >
                <div class="flex items-start gap-2.5">
                    <Icon icon="mdi:alert-outline" class="w-[18px] h-[18px] mt-0.5 flex-shrink-0" aria-hidden="true" />
                    <ul class="space-y-1">
                        <li v-for="(error, i) in generalErrors" :key="i">{{ error }}</li>
                    </ul>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-5" novalidate :aria-busy="form.processing">
                <div class="space-y-2">
                    <Label for="email">Alamat Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        inputmode="email"
                        placeholder="nama@sekolah.id"
                        required
                        autofocus
                        autocomplete="email"
                        :error="form.errors.email"
                        :aria-describedby="form.errors.email ? 'email-error' : undefined"
                    />
                    <p
                        v-if="form.errors.email"
                        id="email-error"
                        class="text-xs font-medium text-destructive flex items-center gap-1.5"
                    >
                        <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                        {{ form.errors.email }}
                    </p>
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
                            :error="form.errors.password"
                            :aria-describedby="form.errors.password ? 'password-error' : undefined"
                            class="pr-12"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            :aria-pressed="showPassword"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-md text-muted-foreground hover:text-foreground transition-colors"
                        >
                            <Icon
                                :icon="showPassword ? 'mdi:eye-off-outline' : 'mdi:eye-outline'"
                                class="w-[18px] h-[18px]"
                                aria-hidden="true"
                            />
                        </button>
                    </div>
                    <p
                        v-if="form.errors.password"
                        id="password-error"
                        class="text-xs font-medium text-destructive flex items-center gap-1.5"
                    >
                        <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="toggle-switch items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" v-model="form.remember" class="sr-only peer" />
                        <span class="toggle-track" aria-hidden="true">
                            <span class="toggle-thumb"></span>
                        </span>
                        <span class="text-sm text-muted-foreground peer-focus-visible:text-foreground transition-colors">Ingat saya</span>
                    </label>
                    <Link :href="route('password.request')" class="text-sm text-primary hover:text-primary/80 font-semibold transition">Lupa kata sandi?</Link>
                </div>

                <Button
                    type="submit"
                    :loading="form.processing"
                    class="btn-auth w-full min-h-12 text-[15px] font-semibold"
                >
                    <span v-if="!form.processing">Masuk</span>
                    <span v-else>Memproses…</span>
                </Button>

                <p v-if="hasErrors" class="sr-only" role="status">
                    Formulir gagal dikirim. Periksa kembali isian yang ditandai merah.
                </p>
            </form>
        </div>
    </GuestLayout>
</template>
