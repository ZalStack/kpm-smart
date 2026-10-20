<script setup>
import { ref, computed, inject, onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';
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

// Filter autocomplete sugesti: HANYA muncul saat search diketik minimal 1 karakter
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

    // Pencocokan eksak
    const exact = studentList.value.find(
        (s) => s.name.toLowerCase() === q || (s.email && s.email.toLowerCase() === q) || (s.username && s.username.toLowerCase() === q)
    );
    if (exact) {
        selectedStudent.value = exact;
        form.email = exact.email || exact.username || exact.name;
    } else {
        form.email = search.value;
        if (q === 'admin') {
            const adminMatch = studentList.value.find((s) => s.is_admin || s.name.toLowerCase() === 'admin');
            if (adminMatch) {
                selectedStudent.value = adminMatch;
            }
        }
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
        <Head title="Masuk - KPM SMART" />

        <div class="auth-card rounded-[1.75rem] p-5 sm:p-9 anim-fade-in-up">
            <div class="mb-7 text-center sm:text-left">
                <h1 class="text-xl sm:text-[1.8rem] font-bold text-foreground">Selamat datang kembali</h1>
                <p class="text-muted-foreground text-sm mt-1.5">Pilih nama akun dan masukkan kata sandi untuk melanjutkan.</p>
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

            <form @submit.prevent="submit" class="space-y-4" autocomplete="off" novalidate :aria-busy="form.processing">
                <!-- 1. Input Nama / Username (Autocomplete) -->
                <div class="space-y-1.5 relative">
                    <Label for="search-input">Nama Siswa / Username</Label>
                    <div class="relative">
                        <input
                            id="search-input"
                            v-model="search"
                            type="text"
                            placeholder="Ketik nama siswa atau admin..."
                            required
                            autofocus
                            @input="onSearchInput"
                            @focus="onFocus"
                            class="flex h-11 w-full rounded-xl border border-input bg-background px-3.5 pr-8 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <button
                            v-if="search"
                            type="button"
                            @click="clearSelection"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground text-base p-1 cursor-pointer"
                        >
                            &times;
                        </button>
                    </div>

                    <!-- Autocomplete Dropdown -->
                    <div
                        v-if="isOpen && filteredStudents.length > 0"
                        class="absolute left-0 right-0 top-full mt-1 bg-popover text-popover-foreground rounded-xl shadow-xl border border-border max-h-52 overflow-y-auto z-40 divide-y divide-border/50"
                    >
                        <button
                            v-for="s in filteredStudents"
                            :key="s.id"
                            type="button"
                            @click="selectStudent(s)"
                            class="w-full text-left px-3.5 py-2.5 hover:bg-muted/80 transition flex items-center justify-between gap-2 cursor-pointer"
                        >
                            <div class="min-w-0">
                                <div class="font-bold text-xs text-foreground truncate flex items-center gap-2">
                                    <span>{{ s.name }}</span>
                                    <span
                                        v-if="!s.is_admin && s.school"
                                        class="text-[9px] px-1.5 py-0.5 rounded bg-primary/10 text-primary border border-primary/20 font-medium shrink-0"
                                    >
                                        🎓 {{ s.school }}
                                    </span>
                                </div>
                                <div class="text-[10px] text-muted-foreground mt-0.5">
                                    <template v-if="s.is_admin">
                                        Administrator Sistem &bull; {{ s.role }}
                                    </template>
                                    <template v-else>
                                        {{ s.bidang || 'Matematika & IPA' }} &bull; Level {{ s.level || '1' }} &bull; {{ s.kelas || '-' }}
                                    </template>
                                </div>
                            </div>
                            <span class="text-xs shrink-0">{{ s.avatar || (s.is_admin ? '🛡️' : '👤') }}</span>
                        </button>
                    </div>

                    <p
                        v-if="form.errors.email"
                        id="email-error"
                        class="text-xs font-medium text-destructive flex items-center gap-1.5 mt-1"
                    >
                        <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5 shrink-0" aria-hidden="true" />
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- 2. Auto-Filled Detail Pill (Muncul saat siswa dipilih) -->
                <div
                    v-if="selectedStudent && !selectedStudent.is_admin"
                    class="rounded-xl border border-border/80 bg-muted/40 p-3 space-y-2 animate-fadeIn"
                >
                    <!-- 1. SEKOLAH -->
                    <div class="flex items-center justify-between text-xs gap-3">
                        <span class="text-primary font-semibold flex items-center gap-1.5 text-[11px] shrink-0">
                            <span>🎓</span> Sekolah
                        </span>
                        <span class="font-bold text-foreground bg-background border border-border px-2 py-0.5 rounded-md text-[11px] text-right break-words min-w-0">
                            {{ selectedStudent.school || '-' }}
                        </span>
                    </div>

                    <!-- 2. BIDANG -->
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-primary font-semibold flex items-center gap-1.5 text-[11px]">
                            <span>📐</span> Bidang
                        </span>
                        <span class="font-bold text-foreground bg-background border border-border px-2 py-0.5 rounded-md text-[11px]">
                            {{ selectedStudent.bidang || 'Matematika & IPA' }}
                        </span>
                    </div>

                    <!-- 3. LEVEL -->
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-primary font-semibold flex items-center gap-1.5 text-[11px]">
                            <span>🎯</span> Level
                        </span>
                        <span class="font-bold text-foreground bg-background border border-border px-2 py-0.5 rounded-md text-[11px]">
                            {{ selectedStudent.level || '1' }}
                        </span>
                    </div>

                    <!-- 4. KELAS -->
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-primary font-semibold flex items-center gap-1.5 text-[11px]">
                            <span>🏫</span> Kelas
                        </span>
                        <span class="font-bold text-foreground bg-background border border-border px-2 py-0.5 rounded-md text-[11px]">
                            {{ selectedStudent.kelas || '-' }}
                        </span>
                    </div>
                </div>

                <!-- 3. Password Input -->
                <div class="space-y-1.5">
                    <Label for="password">Kata Sandi</Label>
                    <div class="relative">
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Masukkan kata sandi"
                            required
                            autocomplete="current-password"
                            class="flex h-11 w-full rounded-xl border border-input bg-background px-3.5 pr-12 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            class="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-md text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
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
                        class="text-xs font-medium text-destructive flex items-center gap-1.5 mt-1"
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
                    <Link :href="route('password.request')" class="text-sm text-primary hover:text-primary/80 font-semibold transition">Lupa kata sandi?</Link>
                </div>

                <!-- Submit Button -->
                <Button
                    type="submit"
                    :loading="form.processing"
                    class="btn-auth w-full min-h-12 text-[15px] font-semibold mt-2 cursor-pointer"
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
.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}
</style>
