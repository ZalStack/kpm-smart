<script setup>
import { ref } from 'vue';
import Dialog from '@/Components/ui/dialog/Dialog.vue';
import DialogHeader from '@/Components/ui/dialog/DialogHeader.vue';
import DialogTitle from '@/Components/ui/dialog/DialogTitle.vue';
import DialogFooter from '@/Components/ui/dialog/DialogFooter.vue';
import Button from '@/Components/ui/button/Button.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Konfirmasi' },
    message: { type: String, default: 'Apakah Anda yakin?' },
    confirmText: { type: String, default: 'Ya' },
    cancelText: { type: String, default: 'Batal' },
    variant: { type: String, default: 'destructive' },
});

const emit = defineEmits(['update:open', 'confirm']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogHeader>
            <DialogTitle>{{ title }}</DialogTitle>
            <p class="text-sm text-muted-foreground">{{ message }}</p>
        </DialogHeader>
        <DialogFooter>
            <Button variant="outline" @click="emit('update:open', false)">{{ cancelText }}</Button>
            <Button :variant="variant" @click="emit('confirm'); emit('update:open', false)">{{ confirmText }}</Button>
        </DialogFooter>
    </Dialog>
</template>
