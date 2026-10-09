<script setup>
import { inject, ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import MathRenderer from '@/Components/shared/MathRenderer.vue';

const route = inject('route');

const props = defineProps({
    package: { type: Object, required: true },
    questions: { type: Array, default: () => [] },
    session: { type: Object, required: true },
    inProgress: { type: Object, default: null },
    timeLimitMinutes: { type: Number, default: 0 },
    savedAnswers: { type: Object, default: () => ({}) },
    savedCurrentIndex: { type: Number, default: null },
    savedDurationSeconds: { type: Number, default: 0 },
});

const currentIndex = ref(0);
const answers = ref({});
const durationSeconds = ref(0);
const showConfirm = ref(false);
const integrityChecked = ref(false);
const autoSaveInterval = ref(null);
const lastSavedAt = ref(null);
const saveStatus = ref('');
const tabViolationCount = ref(0);
const maxTabViolations = 5;
const showTabWarning = ref(false);
const testActive = ref(false);
const tabWarningMessage = ref('');
const showMobileNav = ref(false);

let visibilityHandler = null;
let blurHandler = null;
let contextMenuHandler = null;
let keydownHandler = null;
let timerInterval = null;
const timeDisplay = ref('00:00');
const isWarning = ref(false);

function formatTime(totalSeconds) {
    const m = Math.floor(Math.abs(totalSeconds) / 60);
    const s = Math.abs(totalSeconds) % 60;
    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
}

function startTimer() {
    if (props.timeLimitMinutes > 0) {
        const maxSeconds = props.timeLimitMinutes * 60;
        durationSeconds.value = maxSeconds;
        timerInterval = setInterval(() => {
            durationSeconds.value--;
            timeDisplay.value = formatTime(durationSeconds.value);
            isWarning.value = durationSeconds.value <= 60 && durationSeconds.value > 0;
            if (durationSeconds.value <= 0) {
                clearInterval(timerInterval);
                submitForm();
            }
        }, 1000);
    } else {
        durationSeconds.value = 0;
        timerInterval = setInterval(() => {
            durationSeconds.value++;
            timeDisplay.value = formatTime(durationSeconds.value);
        }, 1000);
    }
}

function startTest() {
    if (!integrityChecked.value) return;
    testActive.value = true;
    startTimer();
    startAutoSave();
    enableSecureMode();
}

function startAutoSave() {
    autoSaveInterval.value = setInterval(() => {
        saveAnswers(true);
    }, 30000);
}

async function saveAnswers(silent = false) {
    if (!testActive.value) return;
    if (!silent) saveStatus.value = 'Menyimpan...';
    try {
        const resp = await fetch(route('user.practice.save-answers', props.session.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || ''),
            },
            body: JSON.stringify({
                answers: answers.value,
                duration_seconds: durationSeconds.value,
            }),
        });
        if (resp.ok) {
            const data = await resp.json();
            lastSavedAt.value = data.saved_at;
            if (!silent) saveStatus.value = 'Tersimpan!';
            setTimeout(() => { saveStatus.value = ''; }, 2000);
        }
    } catch {
        if (!silent) saveStatus.value = 'Gagal menyimpan';
    }
}

function enableSecureMode() {
    visibilityHandler = () => {
        if (document.hidden && testActive.value) {
            tabViolationCount.value++;
            const remaining = maxTabViolations - tabViolationCount.value;
            if (remaining <= 0) {
                tabWarningMessage.value = 'Anda telah berpindah tab terlalu sering. Tes akan diselesaikan otomatis.';
                showTabWarning.value = true;
                setTimeout(() => submitForm(), 3000);
            } else {
                tabWarningMessage.value = `Peringatan! Anda berpindah halaman. Sisa batas peringatan: ${remaining}`;
                showTabWarning.value = true;
                setTimeout(() => { showTabWarning.value = false; }, 4000);
            }
        }
    };
    blurHandler = () => {
        if (testActive.value) {
            tabViolationCount.value++;
            const remaining = maxTabViolations - tabViolationCount.value;
            if (remaining <= 0) {
                tabWarningMessage.value = 'Anda telah berpindah tab terlalu sering. Tes akan diselesaikan otomatis.';
                showTabWarning.value = true;
                setTimeout(() => submitForm(), 3000);
            } else {
                tabWarningMessage.value = `Peringatan! Jangan tinggalkan halaman tes. Sisa toleransi: ${remaining}`;
                showTabWarning.value = true;
                setTimeout(() => { showTabWarning.value = false; }, 4000);
            }
        }
    };
    contextMenuHandler = (e) => { if (testActive.value) e.preventDefault(); };
    keydownHandler = (e) => {
        if (!testActive.value) return;
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J'))) e.preventDefault();
        if (e.ctrlKey && (e.key === 'p' || e.key === 's' || e.key === 'u')) e.preventDefault();
    };
    document.addEventListener('visibilitychange', visibilityHandler);
    window.addEventListener('blur', blurHandler);
    document.addEventListener('contextmenu', contextMenuHandler);
    document.addEventListener('keydown', keydownHandler);
}

function disableSecureMode() {
    if (visibilityHandler) document.removeEventListener('visibilitychange', visibilityHandler);
    if (blurHandler) window.removeEventListener('blur', blurHandler);
    if (contextMenuHandler) document.removeEventListener('contextmenu', contextMenuHandler);
    if (keydownHandler) document.removeEventListener('keydown', keydownHandler);
}

function goToQuestion(index) {
    if (index >= 0 && index < props.questions.length) {
        currentIndex.value = index;
        showMobileNav.value = false;
    }
}
function nextQuestion() { if (currentIndex.value < props.questions.length - 1) currentIndex.value++; }
function prevQuestion() { if (currentIndex.value > 0) currentIndex.value--; }

function handleKeydown(e) {
    if (!testActive.value) return;
    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { e.preventDefault(); prevQuestion(); }
    else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); nextQuestion(); }
}

const answeredCount = computed(() => Object.keys(answers.value).filter(k => answers.value[k] !== null && answers.value[k] !== undefined && answers.value[k] !== '').length);
const progressPercent = computed(() => props.questions.length > 0 ? Math.round((answeredCount.value / props.questions.length) * 100) : 0);

function submitForm() {
    testActive.value = false;
    if (timerInterval) clearInterval(timerInterval);
    if (autoSaveInterval.value) clearInterval(autoSaveInterval.value);
    disableSecureMode();
    router.post(route('user.practice.submit', props.session.id), {
        answers: answers.value,
        duration_seconds: durationSeconds.value,
    });
}

function confirmSubmit() {
    if (answeredCount.value < props.questions.length) {
        showConfirm.value = true;
    } else {
        submitForm();
    }
}

onMounted(() => {
    if (props.savedAnswers && Object.keys(props.savedAnswers).length > 0) {
        answers.value = { ...props.savedAnswers };
    }
    if (props.savedCurrentIndex !== null && props.savedCurrentIndex !== undefined) {
        currentIndex.value = props.savedCurrentIndex;
    }
    if (props.savedDurationSeconds > 0) {
        durationSeconds.value = props.savedDurationSeconds;
    }
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (autoSaveInterval.value) clearInterval(autoSaveInterval.value);
    disableSecureMode();
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <UserLayout>
        <Head :title="'Mengerjakan - ' + package.title" />

        <div :class="{ 'select-none': testActive }">
            <!-- Integrity Agreement Modal (Pre-Test) -->
            <div v-if="!testActive" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md p-4">
                <div class="bg-card rounded-3xl shadow-2xl p-6 sm:p-8 max-w-md w-full border border-border/80 space-y-5 anim-fade-in-up">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center mx-auto shadow-xs">
                        <Icon icon="mdi:shield-check-outline" class="w-8 h-8" />
                    </div>
                    <div class="text-center">
                        <h2 class="text-lg sm:text-xl font-extrabold text-foreground">{{ package.title }}</h2>
                        <p class="text-xs text-muted-foreground mt-1 font-semibold">{{ questions.length }} Butir Soal Latihan</p>
                    </div>

                    <div class="bg-muted/60 rounded-2xl p-4 border border-border/60">
                        <p class="text-xs font-bold text-foreground mb-2 flex items-center gap-1.5">
                            <Icon icon="mdi:alert-decagram" class="w-4 h-4 text-amber-500" />
                            Pernyataan Kejujuran & Integritas
                        </p>
                        <p class="text-xs text-muted-foreground leading-relaxed">
                            Saya berjanji akan mengerjakan latihan tugas ini secara mandiri, jujur tanpa bantuan pihak lain, tidak membuka tab/aplikasi lain, dan tidak menyalin isi soal.
                        </p>
                    </div>

                    <label class="flex items-start gap-3 cursor-pointer bg-card rounded-2xl p-3.5 border border-border/80 hover:border-primary/40 transition">
                        <input
                            type="checkbox"
                            v-model="integrityChecked"
                            class="mt-0.5 w-4 h-4 rounded text-primary focus:ring-primary/20 cursor-pointer"
                        />
                        <span class="text-xs font-medium text-foreground leading-snug">
                            Saya menyetujui pernyataan di atas dan siap mulai mengerjakan dengan sungguh-sungguh.
                        </span>
                    </label>

                    <Button
                        @click="startTest"
                        :disabled="!integrityChecked"
                        class="w-full h-12 rounded-xl text-sm font-bold shadow-md active:scale-98"
                    >
                        <Icon icon="mdi:play" class="w-4 h-4" />
                        Mulai Mengerjakan Tugas
                    </Button>
                </div>
            </div>

            <!-- Tab Switching Warning Modal -->
            <Transition name="fade">
                <div v-if="showTabWarning" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-md p-4">
                    <div class="bg-rose-600 text-white rounded-3xl shadow-2xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 anim-fade-in-up">
                        <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center mx-auto shadow-inner">
                            <Icon icon="mdi:alert-octagon" class="w-8 h-8" />
                        </div>
                        <h3 class="text-base font-bold">Peringatan Integritas!</h3>
                        <p class="text-xs sm:text-sm text-white/90 leading-relaxed">{{ tabWarningMessage }}</p>
                    </div>
                </div>
            </Transition>

            <!-- Test Interface -->
            <div v-if="testActive" class="min-h-[calc(100vh-140px)] flex flex-col lg:flex-row gap-6">
                <!-- LEFT: Question Area -->
                <div class="flex-1 flex flex-col min-w-0">
                    <!-- Top Status Bar in Quiz -->
                    <div class="bg-card rounded-2xl border border-border/80 p-4 mb-5 shadow-2xs">
                        <!-- Desktop Bar -->
                        <div class="hidden sm:flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <h1 class="text-sm font-bold text-foreground truncate">{{ package.title }}</h1>
                                <span class="text-xs text-muted-foreground font-medium">Soal ke-{{ currentIndex + 1 }} dari {{ questions.length }}</span>
                            </div>

                            <div class="flex items-center gap-6">
                                <div v-if="saveStatus" class="text-xs text-primary font-medium animate-pulse flex items-center gap-1">
                                    <Icon icon="mdi:loading" class="w-3.5 h-3.5 animate-spin" />
                                    {{ saveStatus }}
                                </div>
                                <div v-else-if="lastSavedAt" class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                                    <Icon icon="mdi:check-circle" class="w-3.5 h-3.5" />
                                    Tersimpan
                                </div>

                                <div class="text-right">
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-bold">Waktu</p>
                                    <p :class="['text-base font-black font-mono tabular-nums', isWarning ? 'text-rose-500 animate-pulse' : 'text-foreground']">
                                        {{ timeDisplay }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wider font-bold">Terjawab</p>
                                    <p class="text-base font-black text-primary">
                                        {{ answeredCount }}<span class="text-xs text-muted-foreground font-normal">/{{ questions.length }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Bar -->
                        <div class="sm:hidden space-y-2.5">
                            <div class="flex items-center justify-between">
                                <h1 class="text-xs font-bold text-foreground truncate flex-1 min-w-0">{{ package.title }}</h1>
                                <button
                                    @click="showMobileNav = !showMobileNav"
                                    class="ml-2 px-3 py-1 rounded-xl bg-primary/10 text-primary text-xs font-bold flex items-center gap-1 border border-primary/20"
                                >
                                    <Icon icon="mdi:grid" class="w-3.5 h-3.5" />
                                    No. {{ currentIndex + 1 }}
                                </button>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-muted rounded-full h-2 overflow-hidden">
                                    <div class="h-full bg-primary rounded-full transition-all duration-300" :style="{ width: progressPercent + '%' }"></div>
                                </div>
                                <span class="text-xs font-mono font-black tabular-nums" :class="isWarning ? 'text-rose-500' : 'text-foreground'">{{ timeDisplay }}</span>
                                <span class="text-xs font-bold text-primary">{{ answeredCount }}/{{ questions.length }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Question Card Container -->
                    <div v-for="(q, idx) in questions" :key="idx" v-show="currentIndex === idx" class="anim-fade-in space-y-5">
                        <div class="bg-card rounded-3xl border border-border/80 shadow-xs overflow-hidden">
                            <div class="px-6 py-4 border-b border-border/60 bg-muted/30 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-8 h-8 rounded-xl bg-primary text-primary-foreground flex items-center justify-center text-xs font-extrabold shadow-2xs">
                                        {{ idx + 1 }}
                                    </span>
                                    <span class="text-xs font-bold text-muted-foreground">Butir Soal {{ idx + 1 }} dari {{ questions.length }}</span>
                                </div>
                                <span v-if="q.type === 'isian_singkat'" class="text-[10px] font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full">
                                    Isian Singkat
                                </span>
                                <span v-else class="text-[10px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">
                                    Pilihan Ganda
                                </span>
                            </div>

                            <div class="p-6 sm:p-8">
                                <div class="text-sm sm:text-base leading-relaxed text-foreground font-medium">
                                    <MathRenderer :text="q.question" />
                                </div>

                                <div v-if="q.image" class="mt-5">
                                    <img
                                        :src="q.image.startsWith('/') || q.image.startsWith('http') ? q.image : '/storage/' + q.image"
                                        alt="Gambar soal"
                                        class="max-w-full h-auto rounded-2xl border border-border/80 max-h-80 object-contain shadow-xs"
                                        @error="$event.target.style.display='none'"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Pilihan Ganda Options -->
                        <div v-if="!q.type || q.type === 'pilihan_ganda'" class="space-y-3">
                            <label
                                v-for="(opt, optIdx) in q.options"
                                :key="optIdx"
                                :class="[
                                    'flex items-start gap-3.5 p-4 sm:p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200 select-none',
                                    answers[idx] === opt
                                        ? 'border-primary bg-primary/5 shadow-xs ring-1 ring-primary/20 text-foreground font-semibold'
                                        : 'border-border/80 bg-card hover:border-primary/40 hover:bg-muted/40 text-foreground'
                                ]"
                            >
                                <input
                                    type="radio"
                                    :name="'answers_' + idx"
                                    :value="opt"
                                    v-model="answers[idx]"
                                    class="mt-1 w-4 h-4 text-primary focus:ring-primary/20 cursor-pointer"
                                />
                                <div class="text-xs sm:text-sm leading-relaxed flex-1">
                                    <MathRenderer :text="opt" />
                                </div>
                                <span
                                    v-if="answers[idx] === opt"
                                    class="w-5 h-5 rounded-full bg-primary text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs"
                                >
                                    <Icon icon="mdi:check" class="w-3.5 h-3.5" />
                                </span>
                            </label>
                        </div>

                        <!-- Isian Singkat Input -->
                        <div v-else class="bg-card rounded-2xl border-2 border-border/80 p-5 sm:p-6 space-y-3">
                            <label class="text-xs font-bold text-foreground flex items-center gap-2">
                                <Icon icon="mdi:pencil-outline" class="w-4 h-4 text-primary" />
                                Masukkan Jawaban Singkat:
                            </label>
                            <input
                                type="text"
                                v-model="answers[idx]"
                                placeholder="Ketik jawabanmu di sini..."
                                class="w-full px-4 py-3 rounded-xl border border-border/80 bg-background text-sm text-foreground focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none"
                            />
                            <p v-if="answers[idx]" class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                                <Icon icon="mdi:check-circle" class="w-3.5 h-3.5" />
                                Jawaban terisi
                            </p>
                        </div>

                        <!-- Navigation Bar inside Question -->
                        <div class="flex items-center justify-between pt-4">
                            <Button
                                variant="outline"
                                size="default"
                                @click="prevQuestion"
                                :disabled="idx === 0"
                                class="gap-1.5 font-bold"
                            >
                                <Icon icon="mdi:arrow-left" class="w-4 h-4" />
                                Sebelumnya
                            </Button>

                            <button
                                @click="saveAnswers(false)"
                                class="text-xs text-muted-foreground hover:text-primary font-semibold underline underline-offset-4 transition"
                            >
                                Simpan Manual
                            </button>

                            <Button
                                v-if="idx < questions.length - 1"
                                size="default"
                                @click="nextQuestion"
                                class="gap-1.5 font-bold"
                            >
                                Selanjutnya
                                <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                            </Button>

                            <Button
                                v-else
                                size="default"
                                @click="confirmSubmit"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white gap-1.5 font-bold shadow-sm"
                            >
                                <Icon icon="mdi:check-all" class="w-4 h-4" />
                                Kumpulkan Jawaban
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: Desktop Sidebar Question Navigator -->
                <div class="hidden lg:block w-80 shrink-0">
                    <div class="sticky top-20 space-y-4">
                        <!-- Timer Card -->
                        <div class="bg-card rounded-2xl p-5 text-center border border-border/80 shadow-xs">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground mb-1">Sisa Waktu</p>
                            <p :class="['text-3xl font-black font-mono tabular-nums tracking-tight', isWarning ? 'text-rose-500 animate-pulse' : 'text-foreground']">
                                {{ timeDisplay }}
                            </p>
                            <div class="mt-2 text-[11px] text-muted-foreground font-medium flex items-center justify-center gap-1">
                                <span v-if="saveStatus" class="text-primary animate-pulse">{{ saveStatus }}</span>
                                <span v-else-if="lastSavedAt" class="text-emerald-600 flex items-center gap-1">
                                    <Icon icon="mdi:cloud-check" class="w-3.5 h-3.5" /> Tersimpan aman
                                </span>
                            </div>
                        </div>

                        <!-- Progress Bar Card -->
                        <div class="bg-card rounded-2xl border border-border/80 p-5 shadow-xs">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-bold text-foreground">Progres Pengerjaan</p>
                                <p class="text-xs font-extrabold text-primary">{{ progressPercent }}%</p>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2 overflow-hidden">
                                <div class="h-full bg-primary rounded-full transition-all duration-500" :style="{ width: progressPercent + '%' }"></div>
                            </div>
                            <div class="flex items-center justify-between mt-2 text-[11px] text-muted-foreground font-medium">
                                <span>{{ answeredCount }} Terjawab</span>
                                <span>{{ questions.length - answeredCount }} Tersisa</span>
                            </div>
                        </div>

                        <!-- Question Grid Card -->
                        <div class="bg-card rounded-2xl border border-border/80 p-5 shadow-xs">
                            <p class="text-xs font-bold text-foreground mb-3 uppercase tracking-wider">Navigasi Nomor Soal</p>
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    v-for="(q, idx) in questions"
                                    :key="idx"
                                    @click="goToQuestion(idx)"
                                    :class="[
                                        'aspect-square rounded-xl text-xs font-bold transition-all duration-200 flex items-center justify-center relative select-none',
                                        currentIndex === idx
                                            ? 'bg-primary text-primary-foreground shadow-sm scale-105 ring-2 ring-primary/30'
                                            : answers[idx] !== null && answers[idx] !== undefined && answers[idx] !== ''
                                                ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100'
                                                : 'bg-muted/70 text-muted-foreground hover:bg-muted hover:text-foreground border border-transparent'
                                    ]"
                                >
                                    <span v-if="q.type === 'isian_singkat'" class="absolute top-1 right-1 w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ idx + 1 }}
                                </button>
                            </div>

                            <!-- Legend -->
                            <div class="flex flex-wrap gap-x-3 gap-y-1.5 mt-4 pt-3 border-t border-border/60 text-[10px] text-muted-foreground font-medium">
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-primary"></span> Aktif</span>
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-emerald-100 border border-emerald-300"></span> Terjawab</span>
                                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-muted border border-border/80"></span> Belum</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            @click="confirmSubmit"
                            size="lg"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md active:scale-98"
                        >
                            <Icon icon="mdi:check-all" class="w-5 h-5" />
                            Selesai & Kumpulkan
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Mobile Bottom Sheet Question Grid Modal -->
            <Teleport to="body">
                <Transition name="slide-up">
                    <div v-if="showMobileNav" class="fixed inset-0 z-50 lg:hidden">
                        <div class="absolute inset-0 bg-black/60 backdrop-blur-xs" @click="showMobileNav = false"></div>
                        <div class="absolute bottom-0 left-0 right-0 bg-card rounded-t-3xl shadow-2xl p-5 pb-8 max-h-[75vh] overflow-y-auto anim-slide-up border-t border-border/80">
                            <div class="w-12 h-1 bg-muted rounded-full mx-auto mb-4"></div>
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm font-bold text-foreground">Daftar Nomor Soal</p>
                                <span class="text-xs text-primary font-bold">{{ answeredCount }}/{{ questions.length }} Terjawab</span>
                            </div>
                            <div class="grid grid-cols-4 gap-2.5">
                                <button
                                    v-for="(q, idx) in questions"
                                    :key="idx"
                                    @click="goToQuestion(idx)"
                                    :class="[
                                        'aspect-square rounded-xl text-xs font-bold transition-all flex items-center justify-center relative select-none',
                                        currentIndex === idx
                                            ? 'bg-primary text-primary-foreground shadow-sm ring-2 ring-primary/30'
                                            : answers[idx] !== null && answers[idx] !== undefined && answers[idx] !== ''
                                                ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                                                : 'bg-muted/70 text-muted-foreground border border-transparent'
                                    ]"
                                >
                                    <span v-if="q.type === 'isian_singkat'" class="absolute top-1 right-1 w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ idx + 1 }}
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>

            <!-- Submit Confirmation Dialog -->
            <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-md p-4">
                <div class="bg-card rounded-3xl shadow-2xl p-6 sm:p-8 max-w-sm w-full border border-border/80 anim-fade-in-up text-center">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-4 shadow-xs">
                        <Icon icon="mdi:alert-outline" class="w-7 h-7" />
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-foreground">Kumpulkan Tugas Sekarang?</h3>
                    <p class="text-xs sm:text-sm text-muted-foreground mt-2 leading-relaxed">
                        Kamu baru menjawab <strong class="text-foreground">{{ answeredCount }}</strong> dari <strong class="text-foreground">{{ questions.length }}</strong> butir soal.
                        Soal yang belum dijawab akan dinilai kosong.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <Button variant="outline" size="sm" @click="showConfirm = false" class="flex-1 font-bold">
                            Periksa Lagi
                        </Button>
                        <Button size="sm" @click="submitForm" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                            Ya, Kumpulkan
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.25s ease-out; }
.slide-up-enter-from .absolute.bottom-0, .slide-up-leave-to .absolute.bottom-0 { transform: translateY(100%); }
</style>
