<script setup>
import { inject,  ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
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
        <div class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 sm:p-6 mb-6 sm:mb-8 anim-fade-in-up overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="text-lg font-bold text-muted-foreground">#{{ ticket.id }}</span>
                    <Badge :variant="ticket.status === 'pending' ? 'warning' : ticket.status === 'answered' ? 'success' : 'secondary'" class="gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold">
                        <span class="w-2 h-2 rounded-full" :class="ticket.status === 'pending' ? 'bg-amber-500' : ticket.status === 'answered' ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                        {{ ticket.status === 'pending' ? 'Menunggu' : ticket.status === 'answered' ? 'Dijawab' : 'Ditutup' }}
                    </Badge>
                </div>
                <div class="flex gap-2">
                    <Button variant="destructive" size="sm" class="gap-1.5 rounded-xl font-semibold hover:shadow-md transition-all" @click="deleteTicket"><Icon icon="mdi:delete-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Hapus</Button>
                </div>
            </div>
            <div class="flex items-center gap-4 text-xs text-muted-foreground">
                <span class="inline-flex items-center gap-1.5"><Icon icon="mdi:calendar-outline" class="w-4 h-4 inline-block align-middle" /> Dibuat: {{ formatDate(ticket.created_at) }}</span>
                <span v-if="ticket.answered_at" class="inline-flex items-center gap-1.5"><Icon icon="mdi:check-circle" class="w-4 h-4 inline-block align-middle" /> Dijawab: {{ formatDate(ticket.answered_at) }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- User Info -->
                <div class="bg-muted/50 rounded-2xl p-4 sm:p-5 anim-fade-in-up anim-delay-1">
                    <p class="text-xs text-muted-foreground mb-2 font-medium uppercase tracking-wider">Pengirim</p>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-sm font-bold text-primary flex-shrink-0 ring-2 ring-background shadow-sm">{{ (ticket.name || 'A').charAt(0) }}</div>
                        <div>
                            <p class="text-sm font-semibold">{{ ticket.name || 'Anonim' }}</p>
                            <p class="text-xs text-muted-foreground">{{ ticket.email || '-' }}</p>
                        </div>
                        <Badge v-if="ticket.user" variant="outline" class="ml-2 text-[10px] rounded-lg">Member</Badge>
                        <Badge v-else variant="secondary" class="ml-2 text-[10px] rounded-lg">Pengunjung</Badge>
                    </div>
                </div>

                <!-- Question -->
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 sm:p-6 anim-fade-in-up anim-delay-2">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center"><Icon icon="mdi:help-circle-outline" class="w-4 h-4 text-primary" /></div>
                        <h3 class="font-bold">Pertanyaan</h3>
                    </div>
                    <p class="text-sm leading-relaxed whitespace-pre-wrap text-muted-foreground">{{ ticket.question }}</p>
                </div>

                <!-- Answer -->
                <div v-if="ticket.answer" class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 sm:p-6 border-l-4 border-l-emerald-500 anim-fade-in-up anim-delay-2">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center"><Icon icon="mdi:check-circle" class="w-4 h-4 text-emerald-600" /></div>
                        <h3 class="font-bold text-emerald-700">Jawaban Admin</h3>
                    </div>
                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ ticket.answer }}</p>
                    <p class="text-xs text-muted-foreground mt-3">Dijawab pada {{ formatDate(ticket.answered_at) }}</p>
                </div>

                <!-- Answer Form (if not answered yet) -->
                <div v-if="!ticket.answer" class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 sm:p-6 anim-fade-in-up anim-delay-2">
                    <h3 class="font-bold mb-3 inline-flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center"><Icon icon="mdi:chat-outline" class="w-4 h-4 text-primary" /></div>
                        Tulis Jawaban
                    </h3>
                    <form @submit.prevent="submitAnswer">
                        <Textarea v-model="answerForm.answer" :rows="6" placeholder="Tuliskan jawaban Anda..." class="mb-3 rounded-xl" />
                        <p v-if="answerForm.errors.answer" class="text-xs text-destructive mb-2">{{ answerForm.errors.answer }}</p>
                        <div class="flex justify-end">
                            <Button type="submit" :disabled="answerForm.processing" class="gap-2 px-6 py-2.5 rounded-xl font-semibold shadow-sm hover:shadow-lg hover:shadow-primary/20 transition-all duration-300 active:scale-[0.97]">{{ answerForm.processing ? 'Mengirim...' : '<Icon icon="mdi:send" class="w-4 h-4" /> Kirim Jawaban' }}</Button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <!-- Status Update -->
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 anim-fade-in-up anim-delay-3">
                    <h3 class="font-bold text-sm mb-4 inline-flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center"><Icon icon="mdi:chart-bar" class="w-4 h-4 text-primary" /></div>
                        Status Tiket
                    </h3>
                    <div class="space-y-2">
                        <button v-for="s in ['pending', 'answered', 'closed']" :key="s" @click="updateStatus(s)"
                                :class="['w-full text-left px-3.5 py-2.5 rounded-xl text-sm transition-all duration-200 font-medium', ticket.status === s ? 'bg-primary/10 text-primary ring-1 ring-primary/20 shadow-sm' : 'hover:bg-muted text-muted-foreground hover:shadow-sm']">
                            <template v-if="s === 'pending'"><Icon icon="mdi:clock-outline" class="w-4 h-4 inline-block align-middle mr-1.5" /> Menunggu</template>
                            <template v-else-if="s === 'answered'"><Icon icon="mdi:check-circle" class="w-4 h-4 inline-block align-middle mr-1.5" /> Dijawab</template>
                            <template v-else><Icon icon="mdi:lock-outline" class="w-4 h-4 inline-block align-middle mr-1.5" /> Ditutup</template>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
