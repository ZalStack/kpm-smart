<script setup>
import { inject, ref, computed } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';

const route = inject('route');
const page = usePage();

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

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
    form.put(route('admin.profile.change-password.update'));
}
</script>

<template>
    <AdminLayout>
        <Head title="Ubah Password - Admin PR KK" />

        <template #header-title>Ubah Password</template>
        <template #header-sub>Perbarui password untuk menjaga keamanan akun Administrator Anda</template>

        <div class="w-full max-w-xl space-y-5">
            <!-- Back Link -->
            <Link :href="route('admin.profile.edit')"
                  class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground hover:text-primary transition-colors anim-fade-in-up">
                <Icon icon="mdi:arrow-left" class="w-4 h-4" /> Kembali ke Profil
            </Link>

            <!-- Security Info Banner -->
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3 anim-fade-in-up anim-delay-1">
                <Icon icon="mdi:shield-check-outline" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                <div>
                    <p class="text-xs font-bold text-amber-800">Tips Keamanan Password</p>
                    <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                        Gunakan kombinasi huruf besar, huruf kecil, angka, dan karakter khusus untuk password yang lebih kuat. Minimal 8 karakter.
                    </p>
                </div>
            </div>

            <!-- Password Form Card -->
            <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden anim-fade-in-up anim-delay-2">
                <div class="p-5 border-b border-border/60 bg-gradient-to-r from-amber-500/5 to-transparent flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                        <Icon icon="mdi:lock-reset" class="w-5 h-5" />
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-foreground">Form Ubah Password</h2>
                        <p class="text-xs text-muted-foreground">Isi semua field yang tersedia di bawah</p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="p-5 sm:p-6 space-y-5">
                    <!-- Current Password -->
                    <div class="space-y-1.5">
                        <Label for="current_password" class="text-xs font-bold">
                            Password Saat Ini <span class="text-destructive">*</span>
                        </Label>
                        <div class="relative">
                            <Input id="current_password" v-model="form.current_password"
                                   :type="showCurrent ? 'text' : 'password'"
                                   required placeholder="Masukkan password yang aktif sekarang"
                                   class="pr-11 rounded-xl" />
                            <button type="button" @click="showCurrent = !showCurrent"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition">
                                <Icon :icon="showCurrent ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.errors?.current_password" class="text-[11px] text-destructive flex items-center gap-1">
                            <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />{{ form.errors.current_password }}
                        </p>
                    </div>

                    <!-- Divider -->
                    <div class="flex items-center gap-2">
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
                            <Input id="password" v-model="form.password"
                                   :type="showNew ? 'text' : 'password'"
                                   required placeholder="Min. 8 karakter, kombinasi kuat"
                                   class="pr-11 rounded-xl" />
                            <button type="button" @click="showNew = !showNew"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition">
                                <Icon :icon="showNew ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Strength Indicator -->
                        <div v-if="form.password" class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 rounded-full bg-border overflow-hidden">
                                    <div :class="['h-full rounded-full transition-all duration-500', strengthColor]"
                                         :style="{ width: (strength / 5 * 100) + '%' }"></div>
                                </div>
                                <span class="text-[11px] font-bold" :class="strength >= 4 ? 'text-emerald-600' : strength >= 3 ? 'text-amber-600' : 'text-rose-600'">
                                    {{ strengthLabel }}
                                </span>
                            </div>
                        </div>

                        <p v-if="form.errors?.password" class="text-[11px] text-destructive flex items-center gap-1">
                            <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />{{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-1.5">
                        <Label for="password_confirmation" class="text-xs font-bold">
                            Konfirmasi Password Baru <span class="text-destructive">*</span>
                        </Label>
                        <div class="relative">
                            <Input id="password_confirmation" v-model="form.password_confirmation"
                                   :type="showConfirm ? 'text' : 'password'"
                                   required placeholder="Ulangi password baru Anda"
                                   class="pr-11 rounded-xl"
                                   :class="form.password_confirmation && !passwordsMatch ? 'border-destructive focus:ring-destructive/20' : ''" />
                            <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition">
                                <Icon :icon="showConfirm ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-4 h-4" />
                            </button>
                        </div>
                        <p v-if="form.password_confirmation && !passwordsMatch" class="text-[11px] text-destructive flex items-center gap-1">
                            <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" /> Password tidak cocok
                        </p>
                        <p v-if="form.password_confirmation && passwordsMatch" class="text-[11px] text-emerald-600 flex items-center gap-1">
                            <Icon icon="mdi:check-circle-outline" class="w-3.5 h-3.5" /> Password cocok
                        </p>
                    </div>

                    <!-- Password Checklist -->
                    <div class="bg-muted/30 rounded-xl p-4 border border-border/50 space-y-2.5">
                        <p class="text-[11px] font-bold text-foreground flex items-center gap-1.5">
                            <Icon icon="mdi:clipboard-check-outline" class="w-4 h-4 text-primary" />
                            Persyaratan Password
                        </p>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2 text-[11px]">
                                <Icon :icon="form.password.length >= 8 ? 'mdi:check-circle' : 'mdi:circle-outline'"
                                      :class="form.password.length >= 8 ? 'text-emerald-500' : 'text-muted-foreground/50'"
                                      class="w-3.5 h-3.5 flex-shrink-0" />
                                <span :class="form.password.length >= 8 ? 'text-foreground' : 'text-muted-foreground'">Minimal 8 karakter</span>
                            </div>
                            <div class="flex items-center gap-2 text-[11px]">
                                <Icon :icon="passwordsMatch ? 'mdi:check-circle' : 'mdi:circle-outline'"
                                      :class="passwordsMatch ? 'text-emerald-500' : 'text-muted-foreground/50'"
                                      class="w-3.5 h-3.5 flex-shrink-0" />
                                <span :class="passwordsMatch ? 'text-foreground' : 'text-muted-foreground'">Password konfirmasi cocok</span>
                            </div>
                        </div>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="form.errors && Object.keys(form.errors).filter(k => !['current_password','password'].includes(k)).length > 0"
                         class="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-xl text-xs flex items-start gap-2">
                        <Icon icon="mdi:alert-outline" class="w-4 h-4 flex-shrink-0 mt-0.5" />
                        <ul class="space-y-0.5">
                            <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                        </ul>
                    </div>

                    <!-- Success Alert -->
                    <div v-if="page.props.flash?.success"
                         class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 px-4 py-3 rounded-xl text-xs flex items-center gap-2">
                        <Icon icon="mdi:check-circle-outline" class="w-4 h-4 flex-shrink-0" />
                        <span>{{ page.props.flash.success }}</span>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
                        <Link :href="route('admin.profile.edit')"
                              class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-border text-sm font-semibold hover:bg-muted transition">
                            Batal
                        </Link>
                        <Button type="submit" :disabled="form.processing || !passwordsMatch || form.password.length < 8"
                                class="gap-2 px-6 rounded-xl font-bold shadow-sm">
                            <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                            <Icon v-else icon="mdi:shield-key-outline" class="w-4 h-4" />
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Password Baru' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
