<script setup>
import { inject, ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';

const route = inject('route');

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showCurrent = ref(false);
const showNew = ref(false);
const showConfirm = ref(false);

function submit() {
    form.put(route('user.profile.change-password.update'));
}
</script>

<template>
    <UserLayout>
        <Head title="Ubah Kata Sandi - KPM SMART" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <Icon icon="mdi:lock-reset" class="w-5 h-5" />
                </span>
                <span>Ubah Kata Sandi</span>
            </div>
        </template>
        <template #header-sub>
            Amankan akun belajarmu dengan kata sandi baru yang kuat dan unik
        </template>

        <div class="max-w-2xl mx-auto space-y-5">
            <!-- Back navigation -->
            <Link :href="route('user.profile.edit')"
                  class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                Kembali ke Pengaturan Profil
            </Link>

            <!-- Password Card -->
            <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Current Password -->
                    <div class="space-y-2">
                        <Label for="current_password">Kata Sandi Saat Ini <span class="text-destructive">*</span></Label>
                        <div class="relative">
                            <Input id="current_password"
                                   v-model="form.current_password"
                                   :type="showCurrent ? 'text' : 'password'"
                                   required
                                   placeholder="Masukkan kata sandi saat ini"
                                   class="pr-11 rounded-xl" />
                            <button type="button"
                                    @click="showCurrent = !showCurrent"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition p-1">
                                <Icon :icon="showCurrent ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-5 h-5" />
                            </button>
                        </div>
                        <p v-if="form.errors?.current_password" class="text-xs text-destructive flex items-center gap-1">
                            <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />
                            {{ form.errors.current_password }}
                        </p>
                    </div>

                    <!-- New Password -->
                    <div class="space-y-2">
                        <Label for="password">Kata Sandi Baru <span class="text-destructive">*</span></Label>
                        <div class="relative">
                            <Input id="password"
                                   v-model="form.password"
                                   :type="showNew ? 'text' : 'password'"
                                   required
                                   placeholder="Minimal 8 karakter"
                                   class="pr-11 rounded-xl" />
                            <button type="button"
                                    @click="showNew = !showNew"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition p-1">
                                <Icon :icon="showNew ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-5 h-5" />
                            </button>
                        </div>
                        <p v-if="form.errors?.password" class="text-xs text-destructive flex items-center gap-1">
                            <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <Label for="password_confirmation">Konfirmasi Kata Sandi Baru <span class="text-destructive">*</span></Label>
                        <div class="relative">
                            <Input id="password_confirmation"
                                   v-model="form.password_confirmation"
                                   :type="showConfirm ? 'text' : 'password'"
                                   required
                                   placeholder="Ketik ulang kata sandi baru"
                                   class="pr-11 rounded-xl" />
                            <button type="button"
                                    @click="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition p-1">
                                <Icon :icon="showConfirm ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Requirements Box -->
                    <div class="p-4 rounded-2xl bg-muted/30 border border-border/50 space-y-2 text-xs">
                        <p class="font-bold text-foreground flex items-center gap-1.5">
                            <Icon icon="mdi:shield-check-outline" class="w-4 h-4 text-primary" />
                            Ketentuan Kata Sandi:
                        </p>
                        <ul class="space-y-1 text-muted-foreground ml-5 list-disc">
                            <li :class="form.password.length >= 8 ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : ''">
                                Terdiri dari minimal 8 karakter
                            </li>
                            <li :class="form.password && form.password === form.password_confirmation ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : ''">
                                Konfirmasi kata sandi harus sama persis
                            </li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-3">
                        <Link :href="route('user.profile.edit')" class="w-full sm:w-auto">
                            <Button variant="ghost" type="button" class="w-full sm:w-auto">
                                Batal
                            </Button>
                        </Link>
                        <Button type="submit" :loading="form.processing" class="w-full sm:w-auto gap-2 font-semibold shadow-sm min-h-11">
                            <Icon v-if="!form.processing" icon="mdi:check" class="w-4 h-4" />
                            {{ form.processing ? 'Menyimpan Kata Sandi...' : 'Simpan Kata Sandi Baru' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </UserLayout>
</template>
