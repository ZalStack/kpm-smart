<script setup>
import { inject,  ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Pagination from '@/Components/shared/Pagination.vue';
const route = inject('route');

const props = defineProps({
    logs: { type: Object, required: true },
    todayCount: { type: Number, default: 0 },
    weekCount: { type: Number, default: 0 },
});

const search = ref('');
const dateFrom = ref('');
const dateTo = ref('');

function applyFilters() {
    router.get(route('admin.login-logs.index'), {
        search: search.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    search.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
}

function formatDateTime(d) {
    if (!d) return '-';
    const dt = new Date(d);
    return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' + dt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <AdminLayout>
        <Head title="Log Login - Admin" />

        <template #header-title>Log Aktivitas Login</template>
        <template #header-sub>Riwayat login pengguna ke platform</template>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Total Login</p><p class="text-2xl font-bold mt-1">{{ logs.total }}</p></div>
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Hari Ini</p><p class="text-2xl font-bold mt-1 text-green-600">{{ todayCount }}</p></div>
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Minggu Ini</p><p class="text-2xl font-bold mt-1 text-blue-600">{{ weekCount }}</p></div>
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">User Unik</p><p class="text-2xl font-bold mt-1">{{ logs.total }}</p></div>
        </div>

        <!-- Toolbar -->
        <div class="bg-card rounded-lg p-4 shadow-sm border mb-6">
            <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-3">
                <Input v-model="search" placeholder="Cari nama, email..." class="flex-1" />
                <Input v-model="dateFrom" type="date" class="w-full sm:w-40" />
                <Input v-model="dateTo" type="date" class="w-full sm:w-40" />
                <div class="flex gap-2">
                    <Button type="submit" size="sm">Filter</Button>
                    <Button type="button" variant="ghost" size="sm" @click="resetFilters">Reset</Button>
                </div>
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-card rounded-lg shadow-sm border overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-sm">
                    <thead>
                        <tr>
                            <th class="text-left px-4 py-3">User</th>
                            <th class="text-left px-4 py-3">Waktu Login</th>
                            <th class="text-left px-4 py-3">IP Address</th>
                            <th class="text-left px-4 py-3">Lokasi</th>
                            <th class="text-left px-4 py-3">Perangkat</th>
                            <th class="text-left px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Tidak ada data login</td>
                        </tr>
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-muted/50 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full overflow-hidden bg-primary/10 flex items-center justify-center flex-shrink-0">
                                        <img v-if="log.user?.profile_photo" :src="'/storage/' + log.user.profile_photo" class="w-full h-full object-cover" />
                                        <span v-else class="text-xs font-bold text-primary">{{ log.user?.name?.charAt(0) || '?' }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium truncate">{{ log.user?.name || 'Unknown' }}</p>
                                        <p class="text-xs text-muted-foreground truncate">{{ log.user?.email || '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ formatDateTime(log.login_at) }}</td>
                            <td class="px-4 py-3 text-sm font-mono">{{ log.ip_address || '-' }}</td>
                            <td class="px-4 py-3 text-sm">{{ log.location || '-' }}</td>
                            <td class="px-4 py-3 text-sm truncate max-w-[150px]" :title="log.user_agent">{{ log.user_agent ? log.user_agent.substring(0, 30) + '...' : '-' }}</td>
                            <td class="px-4 py-3"><Badge variant="success" class="text-[10px]">Berhasil</Badge></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3 mb-6">
            <div v-if="logs.data.length === 0" class="text-center py-12 bg-card rounded-lg shadow-sm border"><p class="text-muted-foreground">Tidak ada data login</p></div>
            <div v-for="log in logs.data" :key="log.id" class="bg-card rounded-lg shadow-sm border p-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <img v-if="log.user?.profile_photo" :src="'/storage/' + log.user.profile_photo" class="w-full h-full object-cover" />
                        <span v-else class="text-sm font-bold text-primary">{{ log.user?.name?.charAt(0) || '?' }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold truncate text-sm">{{ log.user?.name || 'Unknown' }}</p>
                        <p class="text-xs text-muted-foreground">{{ formatDateTime(log.login_at) }}</p>
                    </div>
                    <Badge variant="success" class="text-[10px]">Berhasil</Badge>
                </div>
                <div class="flex items-center gap-3 text-xs text-muted-foreground">
                    <span>🌐 {{ log.ip_address || '-' }}</span>
                    <span>📍 {{ log.location || '-' }}</span>
                </div>
            </div>
        </div>

        <Pagination :links="logs.links" />
    </AdminLayout>
</template>
