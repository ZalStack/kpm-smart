<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';

const page = usePage();

const show = ref(false);
const type = ref('success');
const message = ref('');

let timer = null;

/** Menahan timer selama kursor di atas notifikasi, supaya tidak hilang
 *  saat pengguna sedang menyeleksi atau akan menekan tombol di dalamnya. */
const paused = ref(false);
let remaining = 5000;
let startedAt = 0;

const DURATION = 5000;

const meta = {
    success: {
        icon: 'mdi:check-circle-outline',
        wrap: 'bg-green-50 border-green-200 text-green-800',
        iconWrap: 'bg-green-100 text-green-600',
        bar: 'bg-green-500',
        label: 'Berhasil',
    },
    error: {
        icon: 'mdi:alert-circle-outline',
        wrap: 'bg-red-50 border-red-200 text-red-800',
        iconWrap: 'bg-red-100 text-red-600',
        bar: 'bg-red-500',
        label: 'Gagal',
    },
    info: {
        icon: 'mdi:information-outline',
        wrap: 'bg-blue-50 border-blue-200 text-blue-800',
        iconWrap: 'bg-blue-100 text-blue-600',
        bar: 'bg-blue-500',
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
            :class="['relative overflow-hidden rounded-lg border p-3.5 pr-10 mb-5 flex items-start gap-3', meta[type].wrap]"
            role="status"
            aria-live="polite"
            @mouseenter="pauseTimer"
            @mouseleave="resumeTimer"
            @focusin="pauseTimer"
            @focusout="resumeTimer"
        >
            <div :class="['h-8 w-8 shrink-0 rounded-full flex items-center justify-center', meta[type].iconWrap]">
                <Icon :icon="meta[type].icon" class="h-[18px] w-[18px]" aria-hidden="true" />
            </div>

            <div class="min-w-0 flex-1 pt-0.5">
                <p class="text-xs font-semibold uppercase tracking-wide opacity-70">
                    {{ meta[type].label }}
                </p>
                <p class="text-sm font-medium mt-0.5 break-words">{{ message }}</p>
            </div>

            <button
                type="button"
                @click="dismiss"
                class="absolute right-2 top-2.5 rounded-md p-1.5 opacity-60 transition hover:opacity-100 hover:bg-black/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current"
                aria-label="Tutup notifikasi"
            >
                <Icon icon="mdi:close" class="h-4 w-4" aria-hidden="true" />
            </button>

            <!-- Progres durasi, sekaligus petunjuk bahwa notifikasi akan menutup sendiri -->
            <span
                v-if="!paused"
                :class="['absolute bottom-0 left-0 h-0.5', meta[type].bar]"
                :style="{ animation: `flashBar ${DURATION}ms linear forwards` }"
                aria-hidden="true"
            />
        </div>
    </Transition>
</template>

<style scoped>
.flash-enter-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.flash-leave-active { transition: all 0.2s ease-in; }
.flash-enter-from { opacity: 0; transform: translateY(-10px); }
.flash-leave-to { opacity: 0; transform: translateY(-10px); }

@keyframes flashBar {
    from { width: 100%; }
    to { width: 0%; }
}
</style>
