<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
const type = ref('success');
const message = ref('');

function flash() {
    if (page.props.flash?.success) {
        type.value = 'success';
        message.value = page.props.flash.success;
        show.value = true;
    } else if (page.props.flash?.error) {
        type.value = 'error';
        message.value = page.props.flash.error;
        show.value = true;
    } else if (page.props.flash?.info) {
        type.value = 'info';
        message.value = page.props.flash.info;
        show.value = true;
    } else {
        show.value = false;
    }

    if (show.value) {
        setTimeout(() => {
            show.value = false;
        }, 5000);
    }
}

onMounted(flash);
watch(() => [page.props.flash?.success, page.props.flash?.error, page.props.flash?.info], flash);

const icons = {
    success: `<svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>`,
    error: `<svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>`,
    info: `<svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>`,
};

const bgClasses = {
    success: 'bg-green-50 border-green-200 text-green-700',
    error: 'bg-red-50 border-red-200 text-red-700',
    info: 'bg-blue-50 border-blue-200 text-blue-700',
};

const iconBgClasses = {
    success: 'bg-green-100',
    error: 'bg-red-100',
    info: 'bg-blue-100',
};
</script>

<template>
    <Transition name="flash">
        <div v-if="show" :class="['p-4 rounded-lg mb-6 flex items-center gap-3 border', bgClasses[type]]">
            <div :class="['w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0', iconBgClasses[type]]" v-html="icons[type]" />
            <p class="text-sm font-medium flex-1">{{ message }}</p>
            <button @click="show = false" class="text-muted-foreground hover:text-foreground transition p-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </Transition>
</template>

<style scoped>
.flash-enter-active { transition: all 0.3s ease; }
.flash-leave-active { transition: all 0.3s ease; }
.flash-enter-from { opacity: 0; transform: translateY(-8px); }
.flash-leave-to { opacity: 0; transform: translateY(-8px); }
</style>
