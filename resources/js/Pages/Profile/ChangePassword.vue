<script setup>
import { ref, computed, inject } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';

defineOptions({ layout: (h, page) => h(UserLayout, null, () => page) });

const route = inject('route');

const props = defineProps({
    status: { type: String, default: null },
    canChangePassword: { type: Boolean, default: false },
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

const strength = computed(() => {
    const p = form.password;
    if (!p) return 0;
    let score = 0;
    if (p.length >= 8) score++;
    if (p.length >= 12) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/[0-9]/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;
    return score;
});

const strengthLabel = computed(() => {
    const labels = ['', 'Sangat Lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
    return labels[strength.value] || '';
});

const strengthColor = computed(() => {
    const colors = ['', 'bg-rose-500', 'bg-orange-500', 'bg-amber-500', 'bg-emerald-500', 'bg-emerald-600'];
    return colors[strength.value] || '';
});

const passwordsMatch = computed(() => form.password && form.password === form.password_confirmation);

function submit() {
    form.put(route('user.profile.change-password.update'), {
        preserveScroll: true,
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Ganti Password - PR KK" />

    <div class="w-full max-w-4xl mx-auto">
        <!-- Back Link -->
        <Link
            :href="route('user.profile.edit')"
            class="inline-flex items-center gap-2 text-sm font-extrabold text-muted-foreground hover:text-primary transition-colors mb-6 sm:mb-8 anim-fade-in-up"
        >
            <Icon icon="mdi:arrow-left" class="w-4 h-4" /> Kembali ke Profil
        </Link>

        <!-- Not allowed (siswa / non-admin) -->
        <div v-if="!canChangePassword" class="ui-card anim-fade-in-up anim-delay-1">
            <div class="p-5 sm:p-6 text-center sm:text-left">
                <div class="inline-flex items-center justify-center gap-2 rounded-full bg-rose-50 text-rose-600 mb-4 sm:mb-5 px-4 py-2 sm:px-5 sm:py-2.5 text-xs font-extrabold">
                    <Icon icon="mdi:shield-lock-outline" class="w-4 h-4" />
                    <span>Kelola Secara Terpusat</span>
                </div>
                <h2 class="text-base sm:text-lg font-extrabold text-foreground mb-2">Kata sandi akun siswa dikelola dari Web Induk</h2>
                <p class="text-sm text-muted-foreground leading-relaxed mb-5 sm:mb-6 max-w-sm sm:max-w-none mx-auto sm:mx-0">
                    Perubahan kata sandi untuk akun siswa tidak dapat dilakukan di sini. Kata sandi akun kamu dikelola terpusat melalui <strong>Web Induk KPM Academy</strong>. Jika lupa kata sandi atau ingin mengubahnya, silakan gunakan fitur <em>Lupa Kata Sandi</em> pada halaman masuk atau hubungi admin sekolah.
                </p>
                <div class="bg-amber-50/60 border border-amber-200/60 rounded-2xl p-4 sm:p-5">
                    <p class="text-xs sm:text-sm font-bold text-amber-800 mb-1">Perlu bantuan?</p>
                    <p class="text-xs text-amber-700 leading-relaxed">Segera hubungi admin sekolah untuk melakukan pengecekan dan penanganan keamanan akun kamu.</p>
                </div>
            </div>
        </div>

        <!-- Change password form (admin only) -->
        <div v-else class="lg:grid lg:grid-cols-12 lg:gap-8 anim-fade-in-up anim-delay-1">
            <!-- Form card -->
            <div class="lg:col-span-7">
                <div class="ui-card overflow-hidden">
                    <div class="p-5 sm:p-6 border-b border-border/60 bg-gradient-to-r from-primary/5 to-transparent flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="text-sm sm:text-base font-extrabold text-foreground">Form Ubah Password</h2>
                            <p class="text-xs text-muted-foreground mt-0.5">Isi semua field yang tersedia di bawah</p>
                        </div>
                        <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                            <Icon icon="mdi:lock-reset" class="w-5 h-5" />
                        </div>
                    </div>

                    <div class="p-5 sm:p-6">
                        <form @submit.prevent="submit" class="space-y-5">
                            <!-- Current Password -->
                            <div class="space-y-1.5">
                                <Label for="current_password" class="text-xs font-bold">
                                    Password Saat Ini <span class="text-destructive">*</span>
                                </Label>
                                <div class="relative">
                                    <Input
                                        id="current_password"
                                        v-model="form.current_password"
                                        :type="show.current ? 'text' : 'password'"
                                        required
                                        placeholder="Masukkan password saat ini"
                                        class="pr-12 rounded-xl"
                                    />
                                    <button
                                        type="button"
                                        @click="show.current = !show.current"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-xl text-muted-foreground hover:text-foreground hover:bg-muted transition"
                                    >
                                        <Icon :icon="show.current ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-4 h-4" />
                                    </button>
                                </div>
                                <p v-if="form.errors?.current_password" class="text-[11px] text-destructive flex items-center gap-1">
                                    <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" /> {{ form.errors.current_password }}
                                </p>
                            </div>

                            <!-- Divider -->
                            <div class="flex items-center gap-2 my-1">
                                <div class="flex-1 h-px bg-border/60"></div>
                                <span class="text-[11px] text-muted-foreground font-medium px-2">Password Baru</span>
                                <div class="flex-1 h-px bg-border/60"></div>
                            </div>

                            <!-- New Password -->
                            <div class="space-y-1.5">
                                <Label for="password" class="text-xs font-bold">
                                    Password Baru <span class="text-destructive">*</span>
                                </Label>
                                <div class="relative">
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        :type="show.password ? 'text' : 'password'"
                                        required
                                        placeholder="Min. 8 karakter, kombinasi kuat"
                                        class="pr-12 rounded-xl"
                                    />
                                    <button
                                        type="button"
                                        @click="show.password = !show.password"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-xl text-muted-foreground hover:text-foreground hover:bg-muted transition"
                                    >
                                        <Icon :icon="show.password ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-4 h-4" />
                                    </button>
                                </div>

                                <!-- Strength indicator -->
                                <div v-if="form.password" class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 rounded-full bg-border overflow-hidden">
                                            <div
                                                :class="['h-full rounded-full transition-all duration-500', strengthColor]"
                                                :style="{ width: (strength / 5 * 100) + '%' }"
                                            ></div>
                                        </div>
                                        <span
                                            class="text-[11px] font-bold"
                                            :class="strength >= 4 ? 'text-emerald-600' : strength >= 3 ? 'text-amber-600' : 'text-rose-600'"
                                        >
                                            {{ strengthLabel }}
                                        </span>
                                    </div>
                                    <p v-if="form.errors?.password" class="text-[11px] text-destructive flex items-center gap-1">
                                        <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" /> {{ form.errors.password }}
                                    </p>
                                </div>
                                <p v-if="!form.password && form.errors?.password" class="text-[11px] text-destructive flex items-center gap-1">
                                    <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" /> {{ form.errors.password }}
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div class="space-y-1.5">
                                <Label for="password_confirmation" class="text-xs font-bold">
                                    Konfirmasi Password Baru <span class="text-destructive">*</span>
                                </Label>
                                <div class="relative">
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        :type="show.password_confirmation ? 'text' : 'password'"
                                        required
                                        placeholder="Ulangi password baru Anda"
                                        class="pr-12 rounded-xl"
                                        :class="form.password_confirmation && !passwordsMatch ? 'border-destructive focus:ring-destructive/20' : ''"
                                    />
                                    <button
                                        type="button"
                                        @click="show.password_confirmation = !show.password_confirmation"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center rounded-xl text-muted-foreground hover:text-foreground hover:bg-muted transition"
                                    >
                                        <Icon :icon="show.password_confirmation ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-4 h-4" />
                                    </button>
                                </div>
                                <p v-if="form.password_confirmation && !passwordsMatch" class="text-[11px] text-destructive flex items-center gap-1">
                                    <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" /> Password tidak cocok
                                </p>
                                <p v-if="form.password_confirmation && passwordsMatch" class="text-[11px] text-emerald-600 flex items-center gap-1">
                                    <Icon icon="mdi:check-circle-outline" class="w-3.5 h-3.5" /> Password cocok
                                </p>
                            </div>

                            <!-- Password checklist -->
                            <div class="bg-muted/30 rounded-2xl p-4 border border-border/50 space-y-2">
                                <p class="text-[11px] font-bold text-foreground flex items-center gap-1.5">
                                    <Icon icon="mdi:clipboard-check-outline" class="w-4 h-4 text-primary" />
                                    Persyaratan Password
                                </p>
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2 text-[11px]">
                                        <Icon
                                            :icon="form.password.length >= 8 ? 'mdi:check-circle' : 'mdi:circle-outline'"
                                            :class="form.password.length >= 8 ? 'text-emerald-500' : 'text-muted-foreground/50'"
                                            class="w-3.5 h-3.5 flex-shrink-0"
                                        />
                                        <span :class="form.password.length >= 8 ? 'text-foreground' : 'text-muted-foreground'">Minimal 8 karakter</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px]">
                                        <Icon
                                            :icon="passwordsMatch ? 'mdi:check-circle' : 'mdi:circle-outline'"
                                            :class="passwordsMatch ? 'text-emerald-500' : 'text-muted-foreground/50'"
                                            class="w-3.5 h-3.5 flex-shrink-0"
                                        />
                                        <span :class="passwordsMatch ? 'text-foreground' : 'text-muted-foreground'">Password konfirmasi cocok</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4">
                                <Link
                                    :href="route('user.profile.edit')"
                                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-border text-sm font-extrabold hover:bg-muted transition"
                                >
                                    Batal
                                </Link>
                                <Button
                                    type="submit"
                                    :disabled="form.processing || !passwordsMatch || form.password.length < 8"
                                    class="gap-2 px-6 rounded-xl font-bold shadow-sm"
                                >
                                    <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                                    <Icon v-else icon="mdi:shield-key-outline" class="w-4 h-4" />
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Password Baru' }}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar cards: tips + info -->
            <div class="mt-6 lg:col-span-5 lg:mt-0 lg:col-start-1">
                <!-- Tips Keamanan -->
                <div class="ui-card mb-4">
                    <div class="p-5 sm:p-6">
                        <div class="mb-3 flex items-center gap-2">
                            <div class="icon-box-sm bg-amber-50 text-amber-600">
                                <Icon icon="mdi:shield-lock-outline" class="w-5 h-5" />
                            </div>
                            <h3 class="text-sm font-bold text-foreground">Tips Keamanan</h3>
                        </div>
                        <ul class="space-y-3">
                            <li
                                v-for="tip in [
                                    'Gunakan minimal 8 karakter dengan kombinasi huruf besar, huruf kecil, dan angka.',
                                    'Jangan gunakan password yang sama dengan akun lain.',
                                    'Jangan bagikan password kamu kepada siapa pun, termasuk pengajar.',
                                    'Segera ganti password jika mencurigai akun kamu diakses orang lain.',
                                ]"
                                :key="tip"
                                class="flex items-start gap-2.5 text-sm text-slate-600"
                            >
                                <Icon icon="mdi:check-circle-outline" class="w-4 h-4 mt-0.5 shrink-0 text-emerald-500" />
                                <span>{{ tip }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Informasi -->
                <div class="ui-card">
                    <div class="p-5 sm:p-6">
                        <div class="mb-3 flex items-center gap-2">
                            <div class="icon-box-sm bg-blue-50 text-blue-600">
                                <Icon icon="mdi:history" class="w-5 h-5" />
                            </div>
                            <h3 class="text-sm font-bold text-foreground">Informasi</h3>
                        </div>
                        <p class="text-sm leading-relaxed text-slate-600">
                            Setelah password diganti, kamu akan tetap login di perangkat ini. Namun, jika ada perangkat lain yang mencurigakan,
                            segera hubungi admin sekolah untuk melakukan pengecekan keamanan akun.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* No custom classes needed — uses the shared design system (Input, Label, Button, ui-card). */
</style>
