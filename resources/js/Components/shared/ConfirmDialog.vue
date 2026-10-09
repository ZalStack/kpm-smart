<script setup>
import Dialog from '@/Components/ui/dialog/Dialog.vue';
import DialogHeader from '@/Components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/Components/ui/dialog/DialogTitle.vue';
import DialogFooter from '@/Components/ui/dialog/DialogFooter.vue';
import Button from '@/Components/ui/button/Button.vue';
import { Icon } from '@iconify/vue/offline';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Konfirmasi Tindakan' },
    message: { type: String, default: 'Apakah Anda yakin ingin melanjutkan tindakan ini?' },
    confirmText: { type: String, default: 'Ya, Lanjutkan' },
    cancelText: { type: String, default: 'Batal' },
    variant: { type: String, default: 'destructive' },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['update:open', 'confirm']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <div class="flex items-start gap-4">
            <div
                :class="[
                    'w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 shadow-xs',
                    variant === 'destructive' ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-primary/10 text-primary border border-primary/20'
                ]"
            >
                <Icon :icon="variant === 'destructive' ? 'mdi:alert-circle-outline' : 'mdi:help-circle-outline'" class="w-6 h-6" />
            </div>
            <div class="min-w-0 flex-1">
                <DialogHeader class="p-0 text-left">
                    <DialogTitle class="text-base sm:text-lg font-bold text-foreground">{{ title }}</DialogTitle>
                    <p class="text-xs sm:text-sm text-muted-foreground mt-1.5 leading-relaxed">{{ message }}</p>
                </DialogHeader>
            </div>
        </div>
        <DialogFooter class="mt-4 sm:mt-6 flex gap-2.5 sm:justify-end">
            <Button variant="outline" size="sm" :disabled="loading" @click="emit('update:open', false)">
                {{ cancelText }}
            </Button>
            <Button :variant="variant" size="sm" :loading="loading" @click="emit('confirm')">
                {{ confirmText }}
            </Button>
        </DialogFooter>
    </Dialog>
</template>
