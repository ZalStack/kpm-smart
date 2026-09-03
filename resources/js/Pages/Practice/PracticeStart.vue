<script setup>
import { inject,  ref, computed, onMounted, onBeforeUnmount } from 'vue';
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
});

const currentIndex = ref(0);
const answers = ref({});
const durationSeconds = ref(0);
const showConfirm = ref(false);

// Timer
let timerInterval = null;
const timeDisplay = ref('00:00');
const isWarning = ref(false);

function formatTime(totalSeconds) {
    const m = Math.floor(totalSeconds / 60);
    const s = totalSeconds % 60;
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
    if (timerInterval) clearInterval(timerInterval);
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
    startTimer();
    window.addEventListener('keydown', handleKeydown);
});

onBeforeUnmount(() => {
    if (timerInterval) clearInterval(timerInterval);
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <UserLayout>
        <Head :title="'Mengerjakan - ' + package.title" />

        <!-- Sticky Header -->
        <div class="sticky top-0 z-30 bg-card border-b shadow-sm">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="min-w-0 flex-1">
                    <h1 class="text-sm font-bold truncate">{{ package.title }}</h1>
                    <p class="text-xs text-muted-foreground">{{ questions.length }} Soal</p>
                </div>
                <div class="flex items-center gap-4">
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
            <div class="bg-card rounded-xl border p-4 mb-6">
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
                    <Button v-if="idx < questions.length - 1" size="sm" @click="nextQuestion">
                            Selanjutnya →
                    </Button>
                    <Button v-else size="sm" @click="confirmSubmit" class="bg-fern hover:bg-fern/90">
                        ✅ Selesai & Lihat Hasil
                    </Button>
                </div>
            </div>
        </div>

        <!-- Sticky Submit Bar -->
        <div class="fixed bottom-0 left-0 right-0 z-30 bg-card border-t shadow-lg">
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
