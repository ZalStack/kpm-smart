<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';

const page = usePage();

const show = ref(false);
const type = ref('success');
const message = ref('');

let timer = null;
const paused = ref(false);
let remaining = 5000;
let startedAt = 0;

const DURATION = 5000;

const meta = {
    success: {
        icon: 'mdi:check-circle',
        wrap: 'bg-emerald-50/95 border-emerald-200/80 text-emerald-900 shadow-emerald-500/10',
        iconWrap: 'bg-emerald-500 text-white shadow-sm shadow-emerald-500/30',
        bar: 'bg-emerald-500',
        label: 'Berhasil',
    },
    error: {
        icon: 'mdi:alert-circle',
        wrap: 'bg-rose-50/95 border-rose-200/80 text-rose-900 shadow-rose-500/10',
        iconWrap: 'bg-rose-500 text-white shadow-sm shadow-rose-500/30',
        bar: 'bg-rose-500',
        label: 'Terjadi Kesalahan',
    },
    info: {
        icon: 'mdi:information',
        wrap: 'bg-sky-50/95 border-sky-200/80 text-sky-900 shadow-sky-500/10',
        iconWrap: 'bg-sky-500 text-white shadow-sm shadow-sky-500/30',
        bar: 'bg-sky-500',
        label: 'Informasi',
    },
};

function startTimer() {
    if (paused.value || !show.value) return;
    startedAt = Date.now();
    timer = setTimeout(dismiss, remaining);
}

function pauseTimer() {
    if (!timer) return;
    clearTimeout(timer);
    timer = null;
    remaining -= Date.now() - startedAt;
}

function resumeTimer() {
    paused.value = false;
    startTimer();
}

function dismiss() {
    if (timer) clearTimeout(timer);
    timer = null;
    show.value = false;
}

function flash() {
    const { success, error, info } = page.props.flash || {};

    if (success) {
        type.value = 'success';
        message.value = success;
    } else if (error) {
        type.value = 'error';
        message.value = error;
    } else if (info) {
        type.value = 'info';
        message.value = info;
    } else {
        dismiss();
        return;
    }

    paused.value = false;
    remaining = DURATION;
    show.value = true;

    if (timer) clearTimeout(timer);
    timer = setTimeout(dismiss, remaining);
}

onMounted(flash);
onUnmounted(() => timer && clearTimeout(timer));
watch(() => [page.props.flash?.success, page.props.flash?.error, page.props.flash?.info], flash);
</script>

<template>
    <Transition name="flash">
        <div
            v-if="show"
            :class="['relative overflow-hidden rounded-2xl border p-4 pr-11 mb-6 flex items-start gap-3.5 shadow-lg backdrop-blur-sm transition-all duration-300', meta[type].wrap]"
            role="status"
            aria-live="polite"
            @mouseenter="pauseTimer"
            @mouseleave="resumeTimer"
            @focusin="pauseTimer"
            @focusout="resumeTimer"
        >
            <div :class="['h-9 w-9 shrink-0 rounded-xl flex items-center justify-center', meta[type].iconWrap]">
                <Icon :icon="meta[type].icon" class="h-5 w-5" aria-hidden="true" />
            </div>

            <div class="min-w-0 flex-1 pt-0.5">
                <p class="text-[11px] font-bold uppercase tracking-wider opacity-80">
                    {{ meta[type].label }}
                </p>
                <p class="text-sm font-medium mt-0.5 break-words leading-relaxed">{{ message }}</p>
            </div>

            <button
                type="button"
                @click="dismiss"
                class="absolute right-2.5 top-3 rounded-lg p-1.5 opacity-60 transition-all hover:opacity-100 hover:bg-black/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current"
                aria-label="Tutup notifikasi"
            >
                <Icon icon="mdi:close" class="h-4 w-4" aria-hidden="true" />
            </button>

            <span
                v-if="!paused"
                :class="['absolute bottom-0 left-0 h-1 rounded-r-full', meta[type].bar]"
                :style="{ animation: `flashBar ${DURATION}ms linear forwards` }"
                aria-hidden="true"
            />
        </div>
    </Transition>
</template>

<style scoped>
.flash-enter-active { transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
.flash-leave-active { transition: all 0.25s ease-in; }
.flash-enter-from { opacity: 0; transform: translateY(-12px) scale(0.98); }
.flash-leave-to { opacity: 0; transform: translateY(-12px) scale(0.98); }

@keyframes flashBar {
    from { width: 100%; }
    to { width: 0%; }
}
</style>
