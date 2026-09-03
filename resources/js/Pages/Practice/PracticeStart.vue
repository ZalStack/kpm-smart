<script setup>
import { inject, ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
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
        const resp = await fetch(route('practice.save-answers', props.session.id), {
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
                tabWarningMessage.value = 'Anda telah berpindah tab terlalu banyak. Tes akan diselesaikan otomatis.';
                showTabWarning.value = true;
                setTimeout(() => submitForm(), 3000);
            } else {
                tabWarningMessage.value = `Peringatan! Anda berpindah dari halaman tes. Sisa peringatan: ${remaining}`;
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
                tabWarningMessage.value = 'Anda telah berpindah tab terlalu banyak. Tes akan diselesaikan otomatis.';
                showTabWarning.value = true;
                setTimeout(() => submitForm(), 3000);
            } else {
                tabWarningMessage.value = `Peringatan! Jangan tinggalkan halaman tes. Sisa peringatan: ${remaining}`;
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

const answeredCount = computed(() => Object.keys(answers.value).filter(k => answers.value[k] !== null && answers.value[k] !== undefined).length);
const progressPercent = computed(() => props.questions.length > 0 ? Math.round((answeredCount.value / props.questions.length) * 100) : 0);

function submitForm() {
    testActive.value = false;
    if (timerInterval) clearInterval(timerInterval);
    if (autoSaveInterval.value) clearInterval(autoSaveInterval.value);
    disableSecureMode();
    router.post(route('practice.submit', props.session.id), {
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

        <!-- Integrity Check -->
        <div v-if="!testActive" class="fixed inset-0 z-50 flex items-center justify-center bg-background/95 backdrop-blur-sm p-4">
            <div class="bg-card rounded-3xl shadow-2xl p-6 sm:p-8 max-w-md w-full border space-y-5 anim-fade-in-up">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <div class="text-center">
                    <h2 class="text-lg font-bold">{{ package.title }}</h2>
                    <p class="text-sm text-muted-foreground mt-1">{{ questions.length }} Soal</p>
                </div>
                <div class="bg-muted/40 rounded-xl p-4">
                    <p class="text-xs font-semibold text-foreground mb-2 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                        Pernyataan Integritas
                    </p>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Saya menyatakan akan mengerjakan tes ini dengan jujur tanpa bantuan pihak lain, tidak menyalin soal, dan tidak membuka tab/aplikasi lain selama pengerjaan.
                    </p>
                </div>
                <label class="flex items-start gap-3 cursor-pointer bg-muted/30 rounded-xl p-3 text-left border hover:border-primary/30 transition">
                    <input type="checkbox" v-model="integrityChecked" class="mt-0.5 w-4 h-4 rounded border-2 text-primary focus:ring-primary/20" />
                    <span class="text-xs font-medium leading-snug">Saya menyetujui pernyataan di atas dan akan mengerjakan dengan jujur</span>
                </label>
                <Button @click="startTest" :disabled="!integrityChecked" class="w-full h-11 rounded-xl font-semibold">
                    Mulai Mengerjakan
                </Button>
            </div>
        </div>

        <!-- Tab Warning -->
        <Transition name="fade">
            <div v-if="showTabWarning" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                <div class="bg-red-600 text-white rounded-3xl shadow-2xl p-6 sm:p-8 max-w-sm w-full text-center space-y-4 anim-fade-in-up">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium">{{ tabWarningMessage }}</p>
                </div>
            </div>
        </Transition>

        <!-- Main Layout: Desktop sidebar + Mobile bottom nav -->
        <div v-if="testActive" class="min-h-screen flex flex-col lg:flex-row">

            <!-- LEFT: Question Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Sticky Header -->
                <div class="sticky top-0 z-30 bg-card/95 backdrop-blur-md border-b shadow-sm">
                    <div class="px-4 py-3">
                        <!-- Desktop Header -->
                        <div class="hidden sm:flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                <h1 class="text-sm font-bold truncate">{{ package.title }}</h1>
                                <p class="text-xs text-muted-foreground">{{ questions.length }} Soal</p>
                            </div>
                            <div class="flex items-center gap-5">
                                <div v-if="saveStatus" class="text-xs text-muted-foreground animate-pulse">{{ saveStatus }}</div>
                                <div v-else-if="lastSavedAt" class="text-[10px] text-emerald-600 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    Tersimpan
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wider">Waktu</p>
                                    <p :class="['text-lg font-bold font-mono tabular-nums', isWarning ? 'text-red-500 animate-pulse' : 'text-foreground']">{{ timeDisplay }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] text-muted-foreground uppercase tracking-wider">Terjawab</p>
                                    <p class="text-lg font-bold text-primary">{{ answeredCount }}<span class="text-sm text-muted-foreground font-normal">/{{ questions.length }}</span></p>
                                </div>
                            </div>
                        </div>
                        <!-- Mobile Header -->
                        <div class="sm:hidden">
                            <div class="flex items-center justify-between mb-2">
                                <h1 class="text-sm font-bold truncate flex-1 min-w-0">{{ package.title }}</h1>
                                <button @click="showMobileNav = !showMobileNav" class="ml-3 px-3 py-1.5 rounded-lg bg-primary/10 text-primary text-xs font-semibold flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                                    Soal {{ currentIndex + 1 }}
                                </button>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-muted rounded-full h-2 overflow-hidden">
                                    <div class="h-full bg-primary rounded-full transition-all duration-300" :style="{ width: progressPercent + '%' }"></div>
                                </div>
                                <span class="text-xs font-mono font-bold tabular-nums" :class="isWarning ? 'text-red-500' : 'text-foreground'">{{ timeDisplay }}</span>
                                <span class="text-xs font-semibold text-primary">{{ answeredCount }}/{{ questions.length }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Question Content -->
                <div class="flex-1 px-4 py-6 pb-24 lg:pb-6">
                    <div v-for="(q, idx) in questions" :key="idx" v-show="currentIndex === idx" class="anim-fade-in-up">
                        <!-- Question Card -->
                        <div class="bg-card rounded-2xl border shadow-sm overflow-hidden">
                            <div class="px-5 py-4 border-b bg-gradient-to-r from-primary/5 to-transparent">
                                <div class="flex items-center gap-3">
                                    <span class="w-9 h-9 rounded-xl bg-primary text-primary-foreground flex items-center justify-center text-sm font-bold shadow-sm">{{ idx + 1 }}</span>
                                    <span class="text-xs text-muted-foreground font-medium">Soal {{ idx + 1 }} dari {{ questions.length }}</span>
                                </div>
                            </div>
                            <div class="p-5 sm:p-6">
                                <div class="text-sm sm:text-[15px] leading-relaxed" v-html="q.question"></div>
                                <div v-if="q.image" class="mt-4">
                                    <img :src="q.image" alt="Gambar soal" class="max-w-full h-auto rounded-xl border max-h-72 object-contain" @error="$event.target.style.display='none'" />
                                </div>
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="mt-4 space-y-2.5">
                            <label v-for="(opt, optIdx) in q.options" :key="optIdx"
                                   :class="[
                                       'flex items-start gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all duration-200',
                                       answers[idx] === opt
                                           ? 'border-primary bg-primary/5 shadow-sm ring-1 ring-primary/10'
                                           : 'border-border hover:border-primary/30 hover:bg-muted/30'
                                   ]">
                                <input type="radio" :name="'answers_' + idx" :value="opt" v-model="answers[idx]" class="mt-0.5 w-4 h-4 text-primary focus:ring-primary/20" />
                                <span class="text-sm leading-relaxed flex-1">{{ opt }}</span>
                                <span v-if="answers[idx] === opt" class="w-5 h-5 rounded-full bg-primary text-primary-foreground flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                </span>
                            </label>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex items-center justify-between mt-6">
                            <Button variant="ghost" size="sm" @click="prevQuestion" :disabled="idx === 0"
                                    :class="['gap-1.5', idx === 0 ? 'opacity-40 cursor-not-allowed' : '']">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                                Sebelumnya
                            </Button>
                            <button @click="saveAnswers(false)" class="text-xs text-muted-foreground hover:text-foreground transition underline underline-offset-2">
                                Simpan
                            </button>
                            <Button v-if="idx < questions.length - 1" size="sm" @click="nextQuestion" class="gap-1.5">
                                Selanjutnya
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </Button>
                            <Button v-else size="sm" @click="confirmSubmit" class="bg-fern hover:bg-fern/90 gap-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Selesai
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Bottom Bar -->
                <div class="lg:hidden fixed bottom-0 left-0 right-0 z-30 bg-card/95 backdrop-blur-md border-t shadow-lg safe-area-bottom">
                    <div class="px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-muted-foreground">Dijawab:</span>
                            <span class="text-sm font-bold text-primary">{{ answeredCount }}/{{ questions.length }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-mono font-bold tabular-nums" :class="isWarning ? 'text-red-500' : 'text-foreground'">{{ timeDisplay }}</span>
                            <Button @click="confirmSubmit" size="sm" class="bg-fern hover:bg-fern/90 rounded-xl px-5">
                                Selesai
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SIDEBAR: Question Grid (Desktop) -->
            <div class="hidden lg:block w-80 border-l bg-card/50 flex-shrink-0">
                <div class="sticky top-0 h-screen overflow-y-auto p-5 space-y-5">
                    <!-- Timer Card -->
                    <div class="bg-gradient-to-br from-primary/10 to-primary/5 rounded-2xl p-5 text-center border border-primary/10">
                        <p class="text-[10px] text-muted-foreground uppercase tracking-widest mb-1">Sisa Waktu</p>
                        <p :class="['text-3xl font-bold font-mono tabular-nums', isWarning ? 'text-red-500 animate-pulse' : 'text-foreground']">
                            {{ timeDisplay }}
                        </p>
                        <div class="mt-3 flex items-center justify-center gap-2 text-[10px] text-muted-foreground">
                            <div v-if="saveStatus" class="animate-pulse">{{ saveStatus }}</div>
                            <div v-else-if="lastSavedAt" class="flex items-center gap-1 text-emerald-600">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Tersimpan
                            </div>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div class="bg-card rounded-2xl border p-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-semibold text-muted-foreground">Progress</p>
                            <p class="text-xs font-bold text-primary">{{ progressPercent }}%</p>
                        </div>
                        <div class="w-full bg-muted rounded-full h-2.5 overflow-hidden">
                            <div class="h-full bg-primary rounded-full transition-all duration-500" :style="{ width: progressPercent + '%' }"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2 text-[10px] text-muted-foreground">
                            <span>{{ answeredCount }} terjawab</span>
                            <span>{{ questions.length - answeredCount }} tersisa</span>
                        </div>
                    </div>

                    <!-- Question Grid -->
                    <div class="bg-card rounded-2xl border p-4">
                        <p class="text-xs font-semibold text-muted-foreground mb-3">Nomor Soal</p>
                        <div class="grid grid-cols-4 gap-2">
                            <button v-for="(q, idx) in questions" :key="idx"
                                    @click="goToQuestion(idx)"
                                    :class="[
                                        'aspect-square rounded-xl text-sm font-semibold transition-all duration-200 flex items-center justify-center',
                                        currentIndex === idx
                                            ? 'bg-primary text-primary-foreground shadow-md scale-105 ring-2 ring-primary/30'
                                            : answers[idx] !== null && answers[idx] !== undefined
                                                ? 'bg-fern/15 text-fern border border-fern/25 hover:bg-fern/25'
                                                : 'bg-muted text-muted-foreground hover:bg-muted/80 hover:text-foreground border border-transparent'
                                    ]">
                                {{ idx + 1 }}
                            </button>
                        </div>
                        <!-- Legend -->
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 text-[10px] text-muted-foreground">
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-primary"></span> Aktif
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-fern/15 border border-fern/25"></span> Terjawab
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded bg-muted border"></span> Belum
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button @click="confirmSubmit"
                            class="w-full py-3 rounded-xl bg-fern text-white font-semibold text-sm hover:bg-fern/90 transition-all duration-200 shadow-sm hover:shadow-md active:scale-[0.98] flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Selesai & Lihat Hasil
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Question Grid Modal -->
        <Teleport to="body">
            <Transition name="slide-up">
                <div v-if="showMobileNav" class="fixed inset-0 z-50 lg:hidden">
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showMobileNav = false"></div>
                    <div class="absolute bottom-0 left-0 right-0 bg-card rounded-t-3xl shadow-2xl p-5 pb-8 max-h-[70vh] overflow-y-auto anim-slide-up">
                        <div class="w-10 h-1 bg-muted rounded-full mx-auto mb-4"></div>
                        <p class="text-sm font-semibold mb-3">Nomor Soal</p>
                        <div class="grid grid-cols-4 gap-2.5">
                            <button v-for="(q, idx) in questions" :key="idx"
                                    @click="goToQuestion(idx)"
                                    :class="[
                                        'aspect-square rounded-xl text-sm font-semibold transition-all flex items-center justify-center',
                                        currentIndex === idx
                                            ? 'bg-primary text-primary-foreground shadow-md ring-2 ring-primary/30'
                                            : answers[idx] !== null && answers[idx] !== undefined
                                                ? 'bg-fern/15 text-fern border border-fern/25'
                                                : 'bg-muted text-muted-foreground border border-transparent'
                                    ]">
                                {{ idx + 1 }}
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3 text-[10px] text-muted-foreground">
                            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-primary"></span> Aktif</span>
                            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-fern/15 border border-fern/25"></span> Terjawab</span>
                            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-muted border"></span> Belum</span>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        </div>

        <!-- Confirm Dialog -->
        <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-card rounded-3xl shadow-2xl p-6 max-w-sm w-full border anim-fade-in-up">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                </div>
                <h3 class="text-base font-bold text-center">Konfirmasi Selesai</h3>
                <p class="text-sm text-muted-foreground text-center mt-2 leading-relaxed">
                    Anda baru menjawab <strong class="text-foreground">{{ answeredCount }}</strong> dari <strong class="text-foreground">{{ questions.length }}</strong> soal.
                    Soal yang belum dijawab akan dianggap kosong.
                </p>
                <div class="flex gap-3 mt-6">
                    <Button variant="ghost" size="sm" @click="showConfirm = false" class="flex-1">Kembali</Button>
                    <Button size="sm" @click="submitForm" class="flex-1 bg-fern hover:bg-fern/90">Ya, Selesai</Button>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.3s ease; }
.slide-up-enter-from .absolute.bottom-0, .slide-up-leave-to .absolute.bottom-0 { transform: translateY(100%); }
.slide-up-enter-from { opacity: 1; }
.slide-up-leave-to { opacity: 0; }
</style>
