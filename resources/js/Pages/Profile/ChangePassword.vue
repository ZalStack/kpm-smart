<script setup>
import { ref, computed, inject } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';

defineOptions({ layout: (h, page) => h(UserLayout, null, () => page) });

const route = inject('route');

const props = defineProps({
    status: { type: String, default: null },
});

const show = ref({
    current: false,
    password: false,
    password_confirmation: false,
});

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.transform((data) => ({ ...data })).put(route('user.profile.password.update'), {
        preserveScroll: true,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const passwordChecklist = computed(() => [
    { label: 'Minimal 8 karakter', valid: form.password.length >= 8 },
    { label: 'Mengandung huruf besar', valid: /[A-Z]/.test(form.password) },
    { label: 'Mengandung angka', valid: /\d/.test(form.password) },
]);
</script>

<template>
    <Head title="Ganti Password" />

    <div>
        <div class="lg:grid lg:grid-cols-12 lg:items-start lg:gap-8">
            <div class="lg:col-span-7">
                <div class="ui-card">
                    <div class="card-head">
                        <div class="icon-box"><Icon icon="mdi:lock-reset" width="22" /></div>
                        <div>
                            <h1 class="card-title">Ganti Password</h1>
                            <p class="card-sub">Perbarui password akun kamu secara berkala</p>
                        </div>
                    </div>

                    <div
                        v-if="status === 'password-updated'"
                        class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700"
                    >
                        <Icon icon="mdi:check-circle-outline" width="20" class="mt-0.5 shrink-0" />
                        <p>Password berhasil diperbarui.</p>
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div>
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <div class="relative">
                                <input
                                    id="current_password"
                                    v-model="form.current_password"
                                    :type="show.current ? 'text' : 'password'"
                                    name="current_password"
                                    class="form-input pr-12"
                                    placeholder="Masukkan password saat ini"
                                    required
                                    autocomplete="current-password"
                                />
                                <button
                                    type="button"
                                    class="absolute right-1 top-1/2 -translate-y-1/2 rounded-lg p-2.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                    :aria-label="show.current ? 'Sembunyikan password' : 'Tampilkan password'"
                                    @click="show.current = !show.current"
                                >
                                    <Icon :icon="show.current ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" width="20" />
                                </button>
                            </div>
                            <p v-if="form.errors.current_password" class="form-error">{{ form.errors.current_password }}</p>
                        </div>

                        <div>
                            <label for="password" class="form-label">Password Baru</label>
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="show.password ? 'text' : 'password'"
                                    name="password"
                                    class="form-input pr-12"
                                    placeholder="Minimal 8 karakter"
                                    required
                                    autocomplete="new-password"
                                />
                                <button
                                    type="button"
                                    class="absolute right-1 top-1/2 -translate-y-1/2 rounded-lg p-2.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                    :aria-label="show.password ? 'Sembunyikan password' : 'Tampilkan password'"
                                    @click="show.password = !show.password"
                                >
                                    <Icon :icon="show.password ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" width="20" />
                                </button>
                            </div>
                            <p v-if="form.errors.password" class="form-error">{{ form.errors.password }}</p>

                            <ul v-if="form.password" class="mt-2.5 grid gap-1.5">
                                <li
                                    v-for="item in passwordChecklist"
                                    :key="item.label"
                                    class="flex items-center gap-2 text-xs font-medium"
                                    :class="item.valid ? 'text-emerald-600' : 'text-slate-400'"
                                >
                                    <Icon :icon="item.valid ? 'mdi:check-circle' : 'mdi:circle-outline'" width="15" />
                                    {{ item.label }}
                                </li>
                            </ul>
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="show.password_confirmation ? 'text' : 'password'"
                                    name="password_confirmation"
                                    class="form-input pr-12"
                                    placeholder="Ulangi password baru"
                                    required
                                    autocomplete="new-password"
                                />
                                <button
                                    type="button"
                                    class="absolute right-1 top-1/2 -translate-y-1/2 rounded-lg p-2.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                                    :aria-label="show.password_confirmation ? 'Sembunyikan password' : 'Tampilkan password'"
                                    @click="show.password_confirmation = !show.password_confirmation"
                                >
                                    <Icon :icon="show.password_confirmation ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" width="20" />
                                </button>
                            </div>
                            <p v-if="form.errors.password_confirmation" class="form-error">{{ form.errors.password_confirmation }}</p>
                        </div>

                        <div class="border-t border-slate-100 pt-5">
                            <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="form.processing">
                                <Icon v-if="form.processing" icon="mdi:loading" width="18" class="animate-spin" />
                                <Icon v-else icon="mdi:content-save-outline" width="18" />
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Password Baru' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-6 lg:col-span-5 lg:mt-0">
                <div class="ui-card">
                    <div class="mb-3 flex items-center gap-2">
                        <div class="icon-box icon-box-sm"><Icon icon="mdi:shield-lock-outline" width="18" /></div>
                        <h2 class="text-sm font-bold text-slate-900">Tips Keamanan</h2>
                    </div>
                    <ul class="space-y-3">
                        <li v-for="tip in [
                            'Gunakan minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.',
                            'Jangan gunakan password yang sama dengan akun lain.',
                            'Jangan bagikan password kamu kepada siapa pun, termasuk pengajar.',
                            'Segera ganti password jika mencurigai akun kamu diakses orang lain.',
                        ]" :key="tip" class="flex items-start gap-2.5 text-sm text-slate-600">
                            <Icon icon="mdi:check-circle-outline" width="17" class="mt-0.5 shrink-0 text-indigo-500" />
                            <span>{{ tip }}</span>
                        </li>
                    </ul>
                </div>

                <div class="ui-card mt-4">
                    <div class="mb-3 flex items-center gap-2">
                        <div class="icon-box icon-box-sm"><Icon icon="mdi:history" width="18" /></div>
                        <h2 class="text-sm font-bold text-slate-900">Informasi</h2>
                    </div>
                    <p class="text-sm leading-relaxed text-slate-600">
                        Setelah password diganti, kamu akan tetap login di perangkat ini. Namun, jika ada perangkat lain yang
                        mencurigakan, segera hubungi admin sekolah untuk melakukan pengecekan keamanan akun.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
