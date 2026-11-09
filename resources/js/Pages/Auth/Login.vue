<script setup>
import { ref, computed, inject, onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Alert from '@/Components/ui/alert/Alert.vue';
import AlertTitle from '@/Components/ui/alert/AlertTitle.vue';

const route = inject('route');

const props = defineProps({
    status: { type: String, default: '' },
    students: { type: Array, default: () => [] },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const search = ref('');
const isOpen = ref(false);
const selectedStudent = ref(null);

const studentList = computed(() => props.students || []);

const filteredStudents = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return [];
    return studentList.value
        .filter((s) => s.name.toLowerCase().includes(q) || (s.username && s.username.toLowerCase().includes(q)))
        .slice(0, 10);
});

const onSearchInput = () => {
    const q = search.value.trim().toLowerCase();
    if (q.length > 0) {
        isOpen.value = true;
    } else {
        isOpen.value = false;
        selectedStudent.value = null;
    }

    const exact = studentList.value.find(
        (s) => s.name.toLowerCase() === q || (s.email && s.email.toLowerCase() === q) || (s.username && s.username.toLowerCase().includes(q))
    );
    if (exact) {
        selectedStudent.value = exact;
        form.email = exact.email || exact.username || exact.name;
    } else {
        form.email = search.value;
    }
};

const onFocus = () => {
    if (search.value.trim().length > 0) {
        isOpen.value = true;
    }
};

const selectStudent = (student) => {
    selectedStudent.value = student;
    search.value = student.name;
    form.email = student.email || student.username || student.name;
    isOpen.value = false;
};

const clearSelection = () => {
    selectedStudent.value = null;
    search.value = '';
    form.email = '';
    isOpen.value = false;
};

function submit() {
    if (!form.email && search.value) {
        form.email = search.value;
    }
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
}

onMounted(() => {
    if (form.email) {
        search.value = form.email;
        const found = studentList.value.find(
            (s) => s.name.toLowerCase() === search.value.toLowerCase() || s.email === search.value
        );
        if (found) {
            selectedStudent.value = found;
        }
    }
});

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
        <Head title="Masuk - PR KK" />

        <div class="auth-card rounded-3xl sm:rounded-4xl p-7 sm:p-9 anim-fade-in-up">
            <div class="mb-7 text-center sm:text-left">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 mb-4 shadow-xs">
                    <Icon icon="mdi:login" class="h-6 w-6" />
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900">Selamat datang kembali</h1>
                <p class="mt-1.5 text-sm text-slate-500">Pilih nama akun dan masukkan kata sandi untuk melanjutkan.</p>
            </div>

            <div v-if="status" class="mb-6">
                <Alert variant="success">
                    <AlertTitle>{{ status }}</AlertTitle>
                </Alert>
            </div>

            <div v-if="generalErrors.length > 0" class="mb-6">
                <Alert variant="destructive">
                    <span class="flex items-start gap-2.5">
                        <Icon icon="mdi:alert-outline" class="w-4 h-4 mt-0.5 shrink-0" aria-hidden="true" />
                        <div>
                            <AlertTitle class="block mb-1">Terjadi Kesalahan</AlertTitle>
                            <ul class="text-sm space-y-1">
                                <li v-for="(error, i) in generalErrors" :key="i">{{ error }}</li>
                            </ul>
                        </div>
                    </span>
                </Alert>
            </div>

            <form @submit.prevent="submit" class="space-y-5" autocomplete="off" novalidate :aria-busy="form.processing">
                <!-- 1. Input Nama / Username (Autocomplete) -->
                <div class="space-y-2 relative">
                    <Label for="search-input">Nama Siswa / Username</Label>
                    <div class="relative">
                        <Input
                            id="search-input"
                            v-model="search"
                            type="text"
                            placeholder="Ketik nama siswa atau admin..."
                            required
                            autofocus
                            @input="onSearchInput"
                            @focus="onFocus"
                            class="pr-12"
                        />
                        <button
                            v-if="search"
                            type="button"
                            @click="clearSelection"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors"
                        >
                            &times;
                        </button>
                    </div>

                    <div
                        v-if="isOpen && filteredStudents.length > 0"
                        class="absolute left-0 right-0 top-full mt-1.5 bg-white rounded-2xl shadow-2xl border border-slate-200 max-h-64 overflow-y-auto z-40 divide-y divide-slate-100"
                    >
                        <button
                            v-for="s in filteredStudents"
                            :key="s.id"
                            type="button"
                            @click="selectStudent(s)"
                            class="w-full text-left px-3.5 py-3 hover:bg-indigo-50/70 transition flex items-center justify-between gap-3 cursor-pointer"
                        >
                            <div class="min-w-0">
                                <div class="font-extrabold text-sm text-slate-900 truncate flex items-center gap-2">
                                    <span>{{ s.name }}</span>
                                    <span
                                        v-if="!s.is_admin && s.school"
                                        class="text-[9px] px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200/80 font-extrabold shrink-0"
                                    >
                                        🎓 {{ s.school }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 font-medium">
                                    <template v-if="s.is_admin">
                                        Administrator Sistem • {{ s.role }}
                                    </template>
                                    <template v-else-if="s.is_guru">
                                        Pengajar / Guru • {{ s.bidang || 'Pembina' }}
                                    </template>
                                    <template v-else>
                                        {{ s.bidang || 'Matematika & IPA' }} • Level {{ s.level || '1' }} • {{ s.kelas || '-' }}
                                    </template>
                                </div>
                            </div>
                            <span class="text-xs shrink-0">{{ s.avatar || (s.is_admin ? '🛡️' : (s.is_guru ? '👨‍🏫' : '👤')) }}</span>
                        </button>
                    </div>

                    <p
                        v-if="form.errors.email"
                        id="email-error"
                        class="text-xs font-extrabold text-rose-600 flex items-center gap-1.5 mt-1"
                    >
                        <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Auto-Filled Detail Pill -->
                <div
                    v-if="selectedStudent && !selectedStudent.is_admin && !selectedStudent.is_guru"
                    class="rounded-2xl border border-indigo-100 bg-indigo-50/50 p-4 space-y-2.5 animate-fade-in"
                >
                    <div class="flex items-center justify-between text-xs gap-3">
                        <span class="text-indigo-700 font-extrabold flex items-center gap-1.5 text-[11px] shrink-0">
                            <span>🎓</span> Sekolah
                        </span>
                        <span class="font-extrabold text-slate-800 bg-white border border-indigo-200/60 px-3 py-1 rounded-xl text-xs text-right break-words min-w-0 shadow-xs">
                            {{ selectedStudent.school || '-' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs gap-3">
                        <span class="text-indigo-700 font-extrabold flex items-center gap-1.5 text-[11px]">
                            <span>📐</span> Bidang
                        </span>
                        <span class="font-extrabold text-slate-800 bg-white border border-indigo-200/60 px-3 py-1 rounded-xl text-xs shadow-xs">
                            {{ selectedStudent.bidang || 'Matematika & IPA' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs gap-3">
                        <span class="text-indigo-700 font-extrabold flex items-center gap-1.5 text-[11px]">
                            <span>🎯</span> Level
                        </span>
                        <span class="font-extrabold text-slate-800 bg-white border border-indigo-200/60 px-3 py-1 rounded-xl text-xs shadow-xs">
                            {{ selectedStudent.level || '1' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs gap-3">
                        <span class="text-indigo-700 font-extrabold flex items-center gap-1.5 text-[11px]">
                            <span>🏫</span> Kelas
                        </span>
                        <span class="font-extrabold text-slate-800 bg-white border border-indigo-200/60 px-3 py-1 rounded-xl text-xs shadow-xs">
                            {{ selectedStudent.kelas || '-' }}
                        </span>
                    </div>
                </div>

                <!-- 3. Password Input -->
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
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 p-2 rounded-xl text-muted-foreground hover:text-primary transition-colors cursor-pointer"
                        >
                            <Icon
                                :icon="showPassword ? 'mdi:eye-off-outline' : 'mdi:eye-outline'"
                                class="w-4 h-4"
                                aria-hidden="true"
                            />
                        </button>
                    </div>
                    <p
                        v-if="form.errors.password"
                        id="password-error"
                        class="text-xs font-extrabold text-rose-600 flex items-center gap-1.5 mt-1"
                    >
                        <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="toggle-switch items-center gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" v-model="form.remember" class="sr-only peer" />
                        <span class="toggle-track" aria-hidden="true">
                            <span class="toggle-thumb"></span>
                        </span>
                        <span class="text-sm text-muted-foreground peer-focus-visible:text-foreground transition-colors">Ingat saya</span>
                    </label>
                    <Link :href="route('password.request')" class="text-sm text-brand-blue hover:text-brand-blue/80 font-extrabold transition">Lupa kata sandi?</Link>
                </div>

                <!-- Submit Button -->
                <Button
                    type="submit"
                    :loading="form.processing"
                    variant="brand"
                    class="w-full min-h-12 text-[15px] font-extrabold mt-1"
                >
                    <span v-if="!form.processing" class="flex items-center justify-center gap-2">
                        <Icon icon="mdi:login" class="w-4 h-4" />
                        <span>Masuk Sekarang</span>
                    </span>
                    <span v-else>Memproses…</span>
                </Button>

                <p v-if="hasErrors" class="sr-only" role="status">
                    Formulir gagal dikirim. Periksa kembali isian yang ditandai merah.
                </p>
            </form>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}
</style>