<script setup>
import { inject } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
const route = inject('route');
const props = defineProps({ announcement: Object });

function formatDate(d){ return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'});}
function priorityLabel(p){ return {low:'Rendah',normal:'Normal',high:'Penting',urgent:'Mendesak'}[p]||p;}
</script>
<template>
<AdminLayout>
<Head :title="announcement.title" />
<template #header-title>Detail Pengumuman</template>
<template #header-sub>{{ formatDate(announcement.created_at) }}</template>
<Link :href="route('admin.announcements.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground mb-5"><Icon icon="mdi:arrow-left" class="w-4 h-4"/> Kembali</Link>
<div class="max-w-3xl mx-auto">
<div class="bg-card border rounded-2xl p-6 md:p-8 shadow-sm">
<div class="flex items-center gap-2 mb-4"><span :class="['text-xs font-bold px-2.5 py-1 rounded-full', announcement.priority==='urgent'?'bg-red-50 text-red-700 ring-1 ring-red-200': announcement.priority==='high'?'bg-amber-50 text-amber-700 ring-1 ring-amber-200':'bg-blue-50 text-blue-700 ring-1 ring-blue-200']">{{ priorityLabel(announcement.priority) }}</span><span class="text-xs text-muted-foreground">{{ formatDate(announcement.created_at) }} • {{ announcement.creator?.name }}</span></div>
<h1 class="text-xl md:text-2xl font-bold leading-tight">{{ announcement.title }}</h1>
<div class="prose prose-sm max-w-none mt-6 whitespace-pre-wrap leading-relaxed text-foreground/90">{{ announcement.content }}</div>
</div>
</div>
</AdminLayout>
</template>
