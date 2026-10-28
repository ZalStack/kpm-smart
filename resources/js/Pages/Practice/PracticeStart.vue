<script setup>
import { inject, ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
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

const submitApproval = ref(false);
const submitCountdown = ref(3);
let countdownInterval = null;

function submitForm() {
    if (countdownInterval) clearInterval(countdownInterval);
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
    submitApproval.value = false;
    submitCountdown.value = 3;
    showConfirm.value = true;
    if (countdownInterval) clearInterval(countdownInterval);
    countdownInterval = setInterval(() => {
        if (submitCountdown.value > 0) {
            submitCountdown.value--;
        } else {
            clearInterval(countdownInterval);
        }
    }, 1000);
}

function cancelConfirm() {
    showConfirm.value = false;
    submitApproval.value = false;
    if (countdownInterval) clearInterval(countdownInterval);
}

/** Kelas status tombol nomor soal: indikator aktif dipertegas dan dipertajam warnanya. */
function navBtnClass(idx) {
    if (currentIndex.value === idx) {
        return 'bg-blue-600 text-white font-black shadow-md ring-4 ring-blue-400/50 scale-105 border-blue-600';
    }
    if (answers.value[idx] !== null && answers.value[idx] !== undefined && answers.value[idx] !== '') {
        return 'bg-emerald-50 text-emerald-800 border border-emerald-300 font-bold hover:bg-emerald-100';
    }
    return 'bg-muted/70 text-muted-foreground border border-transparent hover:bg-muted hover:text-foreground';
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
    if (countdownInterval) clearInterval(countdownInterval);
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
            <!-- ====== PERSETUJUAN INTEGRITAS (SEBELUM TES) ====== -->
            <div v-if="!testActive" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-md">
                <div class="anim-fade-in-up w-full max-w-md space-y-5 rounded-3xl border border-border/80 bg-card p-6 shadow-2xl sm:p-8">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-primary/20 bg-primary/10 text-primary">
                        <Icon icon="mdi:shield-check-outline" class="h-8 w-8" />
                    </div>
                    <div class="text-center">
                        <h2 class="text-lg font-extrabold tracking-tight text-foreground sm:text-xl">{{ package.title }}</h2>
                        <p class="mt-1 text-sm font-semibold text-muted-foreground">{{ questions.length }} Butir Soal Latihan</p>
                    </div>

                    <div class="rounded-2xl border border-border/60 bg-muted/50 p-4">
                        <p class="mb-2 flex items-center gap-1.5 text-xs font-bold text-foreground">
                            <Icon icon="mdi:alert-decagram" class="h-4 w-4 text-amber-500" />
                            Pernyataan Kejujuran &amp; Integritas
                        </p>
                        <p class="text-sm leading-relaxed text-muted-foreground">
                            Saya berjanji akan mengerjakan latihan tugas ini secara mandiri, jujur tanpa bantuan pihak lain, tidak membuka tab/aplikasi lain, dan tidak menyalin isi soal.
                        </p>
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-border/80 bg-card p-4 transition hover:border-primary/40">
                        <input
                            v-model="integrityChecked"
                            type="checkbox"
                            class="mt-0.5 h-4 w-4 cursor-pointer rounded text-primary focus:ring-primary/20"
                        />
                        <span class="text-sm font-medium leading-snug text-foreground">
                            Saya menyetujui pernyataan di atas dan siap mulai mengerjakan dengan sungguh-sungguh.
                        </span>
                    </label>

                    <button
                        @click="startTest"
                        :disabled="!integrityChecked"
                        class="btn-primary w-full py-3.5"
                    >
                        <Icon icon="mdi:play" class="h-4 w-4" />
                        Mulai Mengerjakan Tugas
                    </button>
                </div>
            </div>

            <!-- ====== PERINGATAN PINDAH TAB ====== -->
            <Transition name="fade">
                <div v-if="showTabWarning" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 p-4 backdrop-blur-md">
                    <div class="anim-fade-in-up w-full max-w-sm space-y-4 rounded-3xl bg-rose-600 p-6 text-center text-white shadow-2xl sm:p-8">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 shadow-inner">
                            <Icon icon="mdi:alert-octagon" class="h-8 w-8" />
                        </div>
                        <h3 class="text-base font-bold">Peringatan Integritas!</h3>
                        <p class="text-sm leading-relaxed text-white/90">{{ tabWarningMessage }}</p>
                    </div>
                </div>
            </Transition>

            <!-- ====== ANTARMUKA TES ====== -->
            <div v-if="testActive" class="flex min-h-[calc(100vh-140px)] flex-col gap-6 lg:flex-row">
                <!-- Area Soal -->
                <div class="flex min-w-0 flex-1 flex-col">
                    <!-- Bilah Status -->
                    <div class="ui-card mb-5 p-4">
                        <!-- Desktop -->
                        <div class="hidden items-center justify-between sm:flex">
                            <div class="min-w-0 flex-1 pr-4">
                                <h1 class="truncate text-sm font-bold text-foreground">{{ package.title }}</h1>
                                <span class="text-xs font-medium text-muted-foreground">Soal ke-{{ currentIndex + 1 }} dari {{ questions.length }}</span>
                                <div class="progress-track mt-2 max-w-xs">
                                    <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
                                </div>
                            </div>

                            <div class="flex items-center gap-6">
                                <div v-if="saveStatus" class="flex items-center gap-1 text-xs font-medium text-primary">
                                    <Icon icon="mdi:loading" class="h-3.5 w-3.5 animate-spin" />
                                    {{ saveStatus }}
                                </div>
                                <div v-else-if="lastSavedAt" class="flex items-center gap-1 text-xs font-medium text-emerald-600">
                                    <Icon icon="mdi:check-circle" class="h-3.5 w-3.5" />
                                    Tersimpan
                                </div>

                                <div class="text-right">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Waktu</p>
                                    <p
                                        class="font-mono text-lg font-black tabular-nums"
                                        :class="isWarning ? 'animate-pulse text-rose-500' : 'text-foreground'"
                                    >
                                        {{ timeDisplay }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground">Terjawab</p>
                                    <p class="text-lg font-black text-primary">
                                        {{ answeredCount }}<span class="text-xs font-normal text-muted-foreground">/{{ questions.length }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile -->
                        <div class="space-y-2.5 sm:hidden">
                            <div class="flex items-center justify-between gap-2">
                                <h1 class="min-w-0 flex-1 truncate text-xs font-bold text-foreground">{{ package.title }}</h1>
                                <button
                                    @click="showMobileNav = !showMobileNav"
                                    class="flex shrink-0 items-center gap-1 rounded-xl border border-primary/20 bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary"
                                >
                                    <Icon icon="mdi:grid" class="h-3.5 w-3.5" />
                                    No. {{ currentIndex + 1 }}
                                </button>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="progress-track h-2 flex-1">
                                    <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
                                </div>
                                <span class="font-mono text-xs font-black tabular-nums" :class="isWarning ? 'text-rose-500' : 'text-foreground'">{{ timeDisplay }}</span>
                                <span class="text-xs font-bold text-primary">{{ answeredCount }}/{{ questions.length }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Soal -->
                    <div v-for="(q, idx) in questions" v-show="currentIndex === idx" :key="idx" class="anim-fade-in space-y-5">
                        <div class="ui-card overflow-hidden">
                            <div class="flex items-center justify-between gap-3 border-b border-border/60 bg-muted/30 px-5 py-4 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary text-xs font-extrabold text-primary-foreground shadow-2xs">
                                        {{ idx + 1 }}
                                    </span>
                                    <span class="text-xs font-bold text-muted-foreground">Butir Soal {{ idx + 1 }} dari {{ questions.length }}</span>
                                </div>
                                <span v-if="q.type === 'isian_singkat'" class="chip bg-amber-100 text-amber-800">Isian Singkat</span>
                                <span v-else class="chip bg-emerald-100 text-emerald-800">Pilihan Ganda</span>
                            </div>

                            <div class="p-5 sm:p-7">
                                <div class="text-sm font-medium leading-relaxed text-foreground sm:text-base">
                                    <MathRenderer :text="q.question" />
                                </div>

                                <div v-if="q.image" class="mt-5">
                                    <img
                                        :src="q.image.startsWith('/') || q.image.startsWith('http') ? q.image : '/storage/' + q.image"
                                        alt="Gambar soal"
                                        class="max-h-80 w-auto max-w-full rounded-2xl border border-border/80 object-contain shadow-sm"
                                        @error="$event.target.style.display='none'"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Pilihan Ganda -->
                        <div v-if="!q.type || q.type === 'pilihan_ganda'" class="space-y-2.5">
                            <label
                                v-for="(opt, optIdx) in q.options"
                                :key="optIdx"
                                :class="[
                                    'flex cursor-pointer select-none items-start gap-3.5 rounded-2xl border-2 p-4 transition-all duration-200 sm:p-5',
                                    answers[idx] === opt
                                        ? 'border-primary bg-primary/5 font-semibold text-foreground shadow-sm ring-1 ring-primary/20'
                                        : 'border-border/80 bg-card hover:border-primary/40 hover:bg-muted/40'
                                ]"
                            >
                                <input
                                    v-model="answers[idx]"
                                    type="radio"
                                    :name="'answers_' + idx"
                                    :value="opt"
                                    class="mt-1 h-4 w-4 cursor-pointer text-primary focus:ring-primary/20"
                                />
                                <div class="flex-1 text-sm leading-relaxed">
                                    <MathRenderer :text="opt" />
                                </div>
                                <span
                                    v-if="answers[idx] === opt"
                                    class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-white shadow-2xs"
                                >
                                    <Icon icon="mdi:check" class="h-3.5 w-3.5" />
                                </span>
                            </label>
                        </div>

                        <!-- Isian Singkat -->
                        <div v-else class="ui-card space-y-3 p-5 sm:p-6">
                            <label class="flex items-center gap-2 text-xs font-bold text-foreground">
                                <Icon icon="mdi:pencil-outline" class="h-4 w-4 text-primary" />
                                Masukkan Jawaban Singkat
                            </label>
                            <input
                                v-model="answers[idx]"
                                type="text"
                                placeholder="Ketik jawabanmu di sini..."
                                class="w-full rounded-xl border border-border/80 bg-background px-4 py-3 text-sm text-foreground outline-none transition-all focus:border-primary focus:ring-2 focus:ring-primary/20"
                            />
                            <p v-if="answers[idx]" class="flex items-center gap-1 text-xs font-semibold text-emerald-600">
                                <Icon icon="mdi:check-circle" class="h-3.5 w-3.5" />
                                Jawaban terisi
                            </p>
                        </div>

                        <!-- Navigasi Soal -->
                        <div class="flex items-center justify-between gap-3 pt-2">
                            <button
                                class="btn-outline px-4 py-2.5 text-xs"
                                :disabled="idx === 0"
                                @click="prevQuestion"
                            >
                                <Icon icon="mdi:arrow-left" class="h-4 w-4" />
                                Sebelumnya
                            </button>

                            <button
                                v-if="idx < questions.length - 1"
                                class="btn-primary px-4 py-2.5 text-xs"
                                @click="nextQuestion"
                            >
                                Selanjutnya
                                <Icon icon="mdi:arrow-right" class="h-4 w-4" />
                            </button>

                            <!-- Di mobile jika nomor terakhir, tombol Selesai & Kumpulkan tunggal -->
                            <button
                                v-else
                                class="btn-primary px-4 py-2.5 text-xs lg:hidden"
                                @click="confirmSubmit"
                            >
                                <Icon icon="mdi:check" class="h-4 w-4" />
                                Selesai &amp; Kumpulkan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Panel Navigasi Soal (Desktop) -->
                <aside class="hidden w-80 shrink-0 lg:block">
                    <div class="sticky top-24 space-y-4">
                        <!-- Timer -->
                        <div class="ui-card p-5 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">Sisa Waktu</p>
                            <p
                                class="mt-1 font-mono text-3xl font-black tabular-nums tracking-tight"
                                :class="isWarning ? 'animate-pulse text-rose-500' : 'text-foreground'"
                            >
                                {{ timeDisplay }}
                            </p>
                            <div class="mt-2 flex items-center justify-center gap-1 text-[11px] font-medium">
                                <span v-if="saveStatus" class="text-primary">{{ saveStatus }}</span>
                                <span v-else-if="lastSavedAt" class="flex items-center gap-1 text-emerald-600">
                                    <Icon icon="mdi:cloud-check" class="h-3.5 w-3.5" /> Tersimpan aman
                                </span>
                            </div>
                        </div>

                        <!-- Progres -->
                        <div class="ui-card p-5">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-xs font-bold text-foreground">Progres Pengerjaan</p>
                                <p class="text-xs font-extrabold text-primary">{{ progressPercent }}%</p>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-[11px] font-medium text-muted-foreground">
                                <span>{{ answeredCount }} Terjawab</span>
                                <span>{{ questions.length - answeredCount }} Tersisa</span>
                            </div>
                        </div>

                        <!-- Grid Nomor -->
                        <div class="ui-card p-5">
                            <p class="mb-3 text-xs font-bold uppercase tracking-wider text-foreground">Navigasi Nomor Soal</p>
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    v-for="(q, idx) in questions"
                                    :key="idx"
                                    class="relative flex aspect-square select-none items-center justify-center rounded-xl border text-xs font-bold transition-all duration-200"
                                    :class="navBtnClass(idx)"
                                    @click="goToQuestion(idx)"
                                >
                                    <span v-if="q.type === 'isian_singkat'" class="absolute right-1 top-1 h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    {{ idx + 1 }}
                                </button>
                            </div>
                        </div>

                        <button @click="confirmSubmit" class="btn-primary w-full py-3.5">
                            <Icon icon="mdi:check" class="h-5 w-5" />
                            Selesai &amp; Kumpulkan
                        </button>
                    </div>
                </aside>
            </div>

            <!-- ====== LEMBAR NOMOR SOAL (MOBILE) ====== -->
            <Teleport to="body">
                <Transition name="slide-up">
                    <div v-if="showMobileNav" class="fixed inset-0 z-50 lg:hidden">
                        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs" @click="showMobileNav = false"></div>
                        <div class="anim-slide-up absolute inset-x-0 bottom-0 max-h-[75vh] overflow-y-auto rounded-t-3xl border-t border-border/80 bg-card p-5 pb-8 shadow-2xl">
                            <div class="mx-auto mb-4 h-1 w-12 rounded-full bg-muted-foreground/30"></div>
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-sm font-bold text-foreground">Daftar Nomor Soal</p>
                                <span class="text-xs font-bold text-primary">{{ answeredCount }}/{{ questions.length }} Terjawab</span>
                            </div>
                            <div class="grid grid-cols-4 gap-2.5">
                                <button
                                    v-for="(q, idx) in questions"
                                    :key="idx"
                                    class="relative flex aspect-square select-none items-center justify-center rounded-xl border text-xs font-bold transition"
                                    :class="navBtnClass(idx)"
                                    @click="goToQuestion(idx)"
                                >
                                    <span v-if="q.type === 'isian_singkat'" class="absolute right-1 top-1 h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                    {{ idx + 1 }}
                                </button>
                            </div>

                            <!-- Ringkasan & aksi kumpul (mobile) -->
                            <div class="mt-5 border-t border-border/60 pt-4">
                                <div class="mb-1.5 flex items-center justify-between text-xs font-semibold">
                                    <span class="text-muted-foreground">Terjawab {{ answeredCount }} dari {{ questions.length }} soal</span>
                                    <span class="text-primary">{{ progressPercent }}%</span>
                                </div>
                                <div class="progress-track">
                                    <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
                                </div>
                                <button class="btn-primary mt-4 w-full py-3" @click="confirmSubmit">
                                    <Icon icon="mdi:check-circle-outline" class="h-4 w-4" />
                                    Selesai &amp; Kumpulkan
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>

            <!-- ====== KONFIRMASI KUMPUL ====== -->
            <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-md">
                <div class="anim-fade-in-up w-full max-w-md rounded-3xl border border-border/80 bg-card p-6 shadow-2xl sm:p-8">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                        <Icon icon="mdi:help-circle-outline" class="h-7 w-7" />
                    </div>
                    <h3 class="text-center text-base font-bold text-foreground sm:text-lg">Selesai &amp; Kumpulkan Tugas?</h3>
                    <p class="mt-2 text-center text-sm leading-relaxed text-muted-foreground">
                        Kamu telah menjawab <strong class="text-foreground">{{ answeredCount }}</strong> dari
                        <strong class="text-foreground">{{ questions.length }}</strong> butir soal.
                        Tugas ini hanya dapat dikerjakan 1 kali.
                    </p>

                    <!-- Checkbox Approval -->
                    <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-2xl border border-border/80 bg-muted/40 p-4 transition hover:border-primary/40">
                        <input
                            v-model="submitApproval"
                            type="checkbox"
                            class="mt-0.5 h-4 w-4 cursor-pointer rounded text-primary focus:ring-primary/20"
                        />
                        <span class="text-xs font-semibold leading-relaxed text-foreground select-none">
                            Saya telah memeriksa jawaban dan yakin untuk mengumpulkan hasil tugas ini.
                        </span>
                    </label>

                    <div class="mt-6 flex gap-3">
                        <button class="btn-outline flex-1 py-3 text-xs" @click="cancelConfirm">Periksa Lagi</button>
                        <button
                            class="btn-primary flex-1 py-3 text-xs"
                            :disabled="submitCountdown > 0 || !submitApproval"
                            @click="submitForm"
                        >
                            <span v-if="submitCountdown > 0">Tunggu ({{ submitCountdown }}d)</span>
                            <span v-else>Ya, Kumpulkan</span>
                        </button>
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
