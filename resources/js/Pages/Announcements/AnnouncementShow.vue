<script setup>
import { inject } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';
const route = inject('route');
defineProps({ announcement: Object });
function formatDate(d){ return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'});}
function priorityConfig(p){
  const m={ low:{label:'Rendah',cls:'bg-slate-100 text-slate-700'}, normal:{label:'Informasi',cls:'bg-blue-50 text-blue-700 ring-1 ring-blue-200'}, high:{label:'Penting',cls:'bg-amber-50 text-amber-700 ring-1 ring-amber-200'}, urgent:{label:'Mendesak',cls:'bg-red-50 text-red-700 ring-1 ring-red-200'}};
  return m[p]||m.normal;
}
</script>
<template>
<UserLayout>
<Head :title="announcement.title" />
<template #header-title>Pengumuman</template>
<template #header-sub>Detail pengumuman</template>
<Link :href="route('user.notifications.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-5 group">
  <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform"/> Kembali ke Notifikasi
</Link>
<div class="max-w-3xl mx-auto">
  <div class="bg-card border rounded-2xl overflow-hidden shadow-card">
    <div class="h-1.5 w-full" :class="announcement.priority==='urgent' ? 'bg-red-500' : announcement.priority==='high' ? 'bg-amber-500' : 'bg-primary'"></div>
    <div class="p-6 md:p-8">
      <div class="flex flex-wrap items-center gap-2 mb-4">
        <span :class="['text-[11px] font-bold px-2.5 py-1 rounded-full', priorityConfig(announcement.priority).cls]">{{ priorityConfig(announcement.priority).label }}</span>
        <span class="text-xs text-muted-foreground inline-flex items-center gap-1"><Icon icon="mdi:clock-outline" class="w-3.5 h-3.5"/> {{ formatDate(announcement.created_at) }}</span>
        <span class="text-xs text-muted-foreground">• {{ announcement.creator?.name || 'Admin' }}</span>
      </div>
      <h1 class="text-xl md:text-2xl font-bold leading-tight tracking-tight">{{ announcement.title }}</h1>
      <div class="mt-6 p-4 md:p-5 bg-muted/30 rounded-xl border">
        <p class="text-sm md:text-[15px] leading-relaxed whitespace-pre-wrap text-foreground/90">{{ announcement.content }}</p>
      </div>
      <div class="mt-6 flex gap-2">
        <Link :href="route('user.notifications.index')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-sm font-semibold hover:bg-primary/90 transition"><Icon icon="mdi:bell-outline" class="w-4 h-4"/> Lihat Notifikasi Lain</Link>
        <Link :href="route('user.dashboard')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-card border rounded-xl text-sm font-medium hover:bg-accent transition">Dashboard</Link>
      </div>
    </div>
  </div>
</div>
</UserLayout>
</template>
