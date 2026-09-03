<script setup>
import { inject,  ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Select from '@/Components/ui/select/Select.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
const route = inject('route');

const props = defineProps({
    ticket: { type: Object, required: true },
});

const answerForm = useForm({ answer: '' });
const statusForm = useForm({ status: props.ticket.status });

function submitAnswer() {
    answerForm.post(route('admin.support.answer', props.ticket.id));
}

function updateStatus(statusVal) {
    if (!confirm('Yakin ingin mengubah status tiket ini?')) return;
    statusForm.status = statusVal;
    statusForm.put(route('admin.support.update-status', props.ticket.id));
}

function deleteTicket() {
    if (!confirm('Yakin ingin menghapus tiket ini?')) return;
    router.delete(route('admin.support.delete', props.ticket.id));
}

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <AdminLayout>
        <Head :title="'Tiket #' + ticket.id + ' - Admin'" />

        <template #header-title>Detail Tiket Dukungan</template>
        <template #header-sub>Tiket #{{ ticket.id }}</template>

        <Link :href="route('admin.support.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-6">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar Tiket
        </Link>

        <!-- Header Card -->
        <div class="bg-card rounded-xl border shadow-sm p-5 mb-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold text-muted-foreground">#{{ ticket.id }}</span>
                    <Badge :variant="ticket.status === 'pending' ? 'warning' : ticket.status === 'answered' ? 'success' : 'secondary'">
                        {{ ticket.status === 'pending' ? 'Menunggu' : ticket.status === 'answered' ? 'Dijawab' : 'Ditutup' }}
                    </Badge>
                </div>
                <div class="flex gap-2">
                    <Button variant="destructive" size="sm" @click="deleteTicket">🗑️ Hapus</Button>
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs text-muted-foreground">
                <span>📅 Dibuat: {{ formatDate(ticket.created_at) }}</span>
                <span v-if="ticket.answered_at">✅ Dijawab: {{ formatDate(ticket.answered_at) }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- User Info -->
                <div class="bg-muted/50 rounded-xl p-4">
                    <p class="text-xs text-muted-foreground mb-1">Pengirim</p>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-xs font-bold text-primary">{{ (ticket.name || 'A').charAt(0) }}</div>
                        <div>
                            <p class="text-sm font-medium">{{ ticket.name || 'Anonim' }}</p>
                            <p class="text-xs text-muted-foreground">{{ ticket.email || '-' }}</p>
                        </div>
                        <Badge v-if="ticket.user" variant="outline" class="ml-2 text-[10px]">Member</Badge>
                        <Badge v-else variant="secondary" class="ml-2 text-[10px]">Pengunjung</Badge>
                    </div>
                </div>

                <!-- Question -->
                <div class="bg-card rounded-xl border shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-lg">❓</span>
                        <h3 class="font-semibold">Pertanyaan</h3>
                    </div>
                    <p class="text-sm leading-relaxed whitespace-pre-wrap text-muted-foreground">{{ ticket.question }}</p>
                </div>

                <!-- Answer -->
                <div v-if="ticket.answer" class="bg-card rounded-xl border shadow-sm p-5 border-l-4 border-l-green-500">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-lg">✅</span>
                        <h3 class="font-semibold text-green-700">Jawaban Admin</h3>
                    </div>
                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ ticket.answer }}</p>
                    <p class="text-xs text-muted-foreground mt-3">Dijawab pada {{ formatDate(ticket.answered_at) }}</p>
                </div>

                <!-- Answer Form (if not answered yet) -->
                <div v-if="!ticket.answer" class="bg-card rounded-xl border shadow-sm p-5">
                    <h3 class="font-semibold mb-3">💬 Tulis Jawaban</h3>
                    <form @submit.prevent="submitAnswer">
                        <Textarea v-model="answerForm.answer" :rows="6" placeholder="Tuliskan jawaban Anda..." class="mb-3" />
                        <p v-if="answerForm.errors.answer" class="text-xs text-destructive mb-2">{{ answerForm.errors.answer }}</p>
                        <div class="flex justify-end">
                            <Button type="submit" :disabled="answerForm.processing">{{ answerForm.processing ? 'Mengirim...' : '📤 Kirim Jawaban' }}</Button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Status Update -->
                <div class="bg-card rounded-xl border shadow-sm p-5">
                    <h3 class="font-semibold text-sm mb-3">📊 Status Tiket</h3>
                    <div class="space-y-2">
                        <button v-for="s in ['pending', 'answered', 'closed']" :key="s" @click="updateStatus(s)"
                                :class="['w-full text-left px-3 py-2 rounded-lg text-sm transition', ticket.status === s ? 'bg-primary/10 text-primary font-medium' : 'hover:bg-muted text-muted-foreground']">
                            {{ s === 'pending' ? '⏳ Menunggu' : s === 'answered' ? '✅ Dijawab' : '🔒 Ditutup' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
