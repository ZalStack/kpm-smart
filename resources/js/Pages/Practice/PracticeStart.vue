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

// Feature 1: Integrity
const integrityChecked = ref(false);

// Feature 3: Auto-save
const autoSaveInterval = ref(null);
const lastSavedAt = ref(null);
const saveStatus = ref('');

// Feature 4: Secure mode
const tabViolationCount = ref(0);
const maxTabViolations = 5;
const showTabWarning = ref(false);
const testActive = ref(false);
const tabWarningMessage = ref('');
let visibilityHandler = null;
let blurHandler = null;
let contextMenuHandler = null;
let keydownHandler = null;

// Timer
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

// Feature 1: Start test after integrity check
function startTest() {
    if (!integrityChecked.value) return;
    testActive.value = true;
    startTimer();
    startAutoSave();
    enableSecureMode();
}

// Feature 3: Auto-save
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

// Feature 4: Secure mode
function enableSecureMode() {
    // Anti tab switch
    visibilityHandler = () => {
        if (document.hidden && testActive.value) {
            tabViolationCount.value++;
            const remaining = maxTabViolations - tabViolationCount.value;

            if (remaining <= 0) {
                tabWarningMessage.value = 'Anda telah berpindah tab terlalu banyak. Tes akan diselesaikan otomatis.';
                showTabWarning.value = true;
                setTimeout(() => {
                    submitForm();
                }, 3000);
            } else {
                tabWarningMessage.value = `Peringatan! Anda berpishah dari halaman tes. Sisa peringatan: ${remaining}`;
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
                setTimeout(() => {
                    submitForm();
                }, 3000);
            } else {
                tabWarningMessage.value = `Peringatan! Jangan tinggalkan halaman tes. Sisa peringatan: ${remaining}`;
                showTabWarning.value = true;
                setTimeout(() => { showTabWarning.value = false; }, 4000);
            }
        }
    };

    // Anti right-click / context menu
    contextMenuHandler = (e) => {
        if (testActive.value) e.preventDefault();
    };

    // Anti Ctrl+P (print), Ctrl+S (save), Ctrl+U (view source), F12
    keydownHandler = (e) => {
        if (!testActive.value) return;
        if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && e.key === 'I') || (e.ctrlKey && e.shiftKey && e.key === 'J')) {
            e.preventDefault();
        }
        if (e.ctrlKey && (e.key === 'p' || e.key === 's' || e.key === 'u')) {
            e.preventDefault();
        }
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
    }
}

function nextQuestion() {
    if (currentIndex.value < props.questions.length - 1) {
        currentIndex.value++;
    }
}

function prevQuestion() {
    if (currentIndex.value > 0) {
        currentIndex.value--;
    }
}

function handleKeydown(e) {
    if (!testActive.value) return;
    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
        e.preventDefault();
        prevQuestion();
    } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
        e.preventDefault();
        nextQuestion();
    }
}

const answeredCount = computed(() => Object.keys(answers.value).filter(k => answers.value[k] !== null && answers.value[k] !== undefined).length);

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
    // Load saved answers
    if (props.savedAnswers && Object.keys(props.savedAnswers).length > 0) {
        answers.value = { ...props.savedAnswers };
    }
    // Restore question index
    if (props.savedCurrentIndex !== null && props.savedCurrentIndex !== undefined) {
        currentIndex.value = props.savedCurrentIndex;
    }
    // Restore duration from previous session
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

        <!-- Secure Mode: CSS anti-select -->
        <div :class="{ 'select-none': testActive }">

        <!-- Integrity Check Overlay (Feature 1) -->
        <div v-if="!integrityChecked" class="fixed inset-0 z-50 flex items-center justify-center bg-background/95 backdrop-blur-sm">
            <div class="bg-card rounded-2xl shadow-2xl p-8 max-w-md mx-4 border text-center space-y-5">
                <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold">Pernyataan Integritas</h2>
                    <p class="text-sm text-muted-foreground mt-2 leading-relaxed">
                        Saya menyatakan bahwa saya akan mengerjakan tes ini dengan jujur tanpa bantuan pihak lain, tidak menyalin soal, dan tidak membuka tab/aplikasi lain selama pengerjaan.
                    </p>
                </div>
                <label class="flex items-start gap-3 cursor-pointer bg-muted/50 rounded-lg p-3 text-left">
                    <input type="checkbox" v-model="integrityChecked"
                           class="mt-0.5 w-5 h-5 rounded border-2 text-primary focus:ring-primary/20" />
                    <span class="text-sm font-medium leading-snug">
                        Saya menyetujui pernyataan integritas di atas dan akan mengerjakan dengan jujur
                    </span>
                </label>
                <Button @click="startTest" :disabled="!integrityChecked"
                        :class="['w-full', !integrityChecked ? 'opacity-50 cursor-not-allowed' : '']">
                    Mulai Mengerjakan
                </Button>
            </div>
        </div>

        <!-- Tab Warning Overlay (Feature 4) -->
        <Transition name="fade">
            <div v-if="showTabWarning" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
                <div class="bg-red-600 text-white rounded-2xl shadow-2xl p-8 max-w-sm mx-4 text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium">{{ tabWarningMessage }}</p>
                </div>
            </div>
        </Transition>

        <!-- Sticky Header (only shown after test starts) -->
        <div v-if="testActive" class="sticky top-0 z-30 bg-card border-b shadow-sm">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="min-w-0 flex-1">
                    <h1 class="text-sm font-bold truncate">{{ package.title }}</h1>
                    <p class="text-xs text-muted-foreground">{{ questions.length }} Soal</p>
                </div>
                <div class="flex items-center gap-4">
                    <div v-if="saveStatus" class="text-xs text-muted-foreground animate-pulse">{{ saveStatus }}</div>
                    <div v-if="lastSavedAt && !saveStatus" class="text-[10px] text-muted-foreground">✓ Tersimpan</div>
                    <div class="text-center">
                        <p class="text-xs text-muted-foreground">Waktu</p>
                        <p :class="['text-lg font-bold font-mono', isWarning ? 'text-red-500 animate-pulse' : 'text-foreground']">{{ timeDisplay }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-muted-foreground">Terjawab</p>
                        <p class="text-lg font-bold text-fern">{{ answeredCount }}/{{ questions.length }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Question Navigation Grid -->
            <div v-if="testActive" class="bg-card rounded-xl border p-4 mb-6">
                <p class="text-xs font-semibold text-muted-foreground mb-2">Navigasi Soal</p>
                <div class="flex flex-wrap gap-2">
                    <button v-for="(q, idx) in questions" :key="idx"
                            @click="goToQuestion(idx)"
                            :class="[
                                'w-9 h-9 rounded-lg text-sm font-medium transition-all',
                                currentIndex === idx ? 'bg-primary text-primary-foreground shadow-md scale-110' :
                                answers[idx] !== null && answers[idx] !== undefined ? 'bg-fern/20 text-fern border border-fern/30' :
                                'bg-muted text-muted-foreground hover:bg-muted/80'
                            ]">
                        {{ idx + 1 }}
                    </button>
                </div>
                <div class="flex items-center gap-4 mt-2 text-[10px] text-muted-foreground">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-primary"></span> Aktif</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-fern/20 border border-fern/30"></span> Terjawab</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-muted border"></span> Belum</span>
                </div>
            </div>

            <!-- Questions -->
            <div v-for="(q, idx) in questions" :key="idx" v-show="currentIndex === idx" class="bg-card rounded-xl border p-6">
                <div class="flex items-start gap-3 mb-4">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-bold">{{ idx + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm leading-relaxed" v-html="q.question"></div>
                        <div v-if="q.image" class="mt-3">
                            <img :src="q.image" alt="Gambar soal" class="max-w-full h-auto rounded-lg border max-h-64 object-contain" @error="$event.target.style.display='none'" />
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="space-y-2 ml-11">
                    <label v-for="(opt, optIdx) in q.options" :key="optIdx"
                           :class="[
                               'flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-all',
                               answers[idx] === opt ? 'border-primary bg-primary/5 ring-1 ring-primary/20' : 'border-border hover:border-primary/30 hover:bg-muted/50'
                           ]">
                        <input type="radio" :name="'answers_' + idx" :value="opt" v-model="answers[idx]"
                               class="mt-0.5 w-4 h-4 text-primary" />
                        <span class="text-sm leading-relaxed">{{ opt }}</span>
                    </label>
                </div>

                <!-- Navigation -->
                <div class="flex items-center justify-between mt-6 ml-11">
                    <Button variant="ghost" size="sm" @click="prevQuestion" :disabled="idx === 0" :class="idx === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                        ← Sebelumnya
                    </Button>
                    <div class="flex items-center gap-3">
                        <button @click="saveAnswers(false)" class="text-xs text-muted-foreground hover:text-foreground transition underline">
                            Simpan Jawaban
                        </button>
                        <Button v-if="idx < questions.length - 1" size="sm" @click="nextQuestion">
                                Selanjutnya →
                        </Button>
                        <Button v-else size="sm" @click="confirmSubmit" class="bg-fern hover:bg-fern/90">
                            ✅ Selesai & Lihat Hasil
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky Submit Bar -->
        <div v-if="testActive" class="fixed bottom-0 left-0 right-0 z-30 bg-card border-t shadow-lg">
            <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
                <div class="text-sm">
                    <span class="text-muted-foreground">Terjawab: </span>
                    <span class="font-bold text-fern">{{ answeredCount }}/{{ questions.length }}</span>
                    <span v-if="answeredCount < questions.length" class="text-xs text-yellow-600 ml-2">⚠️ Masih ada yang belum dijawab</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-mono font-bold">{{ timeDisplay }}</span>
                    <Button @click="confirmSubmit" class="bg-fern hover:bg-fern/90">✅ Selesai</Button>
                </div>
            </div>
        </div>

        </div>

        <!-- Confirm Dialog -->
        <div v-if="showConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="showConfirm = false">
            <div class="bg-card rounded-xl shadow-2xl p-6 max-w-sm mx-4 border">
                <h3 class="text-lg font-bold mb-2">Konfirmasi Selesai</h3>
                <p class="text-sm text-muted-foreground mb-4">
                    Anda baru menjawab <strong>{{ answeredCount }}</strong> dari <strong>{{ questions.length }}</strong> soal.
                    Soal yang belum dijawab akan dianggap kosong. Yakin ingin selesai?
                </p>
                <div class="flex gap-3 justify-end">
                    <Button variant="ghost" size="sm" @click="showConfirm = false">Kembali</Button>
                    <Button size="sm" @click="submitForm" class="bg-fern hover:bg-fern/90">Ya, Selesai</Button>
                </div>
            </div>
        </div>
    </UserLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
