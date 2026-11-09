<script setup>
const year = new Date().getFullYear();
</script>

<template>
    <div class="grid lg:grid-cols-12 min-h-screen bg-slate-50 text-foreground antialiased selection:bg-indigo-600 selection:text-white">
        <!-- LEFT: Artistic Brand Panel (5 cols on lg, 6 cols on xl) -->
        <div class="hidden lg:flex lg:col-span-5 xl:col-span-6 brand-panel relative overflow-hidden text-white flex-col justify-between p-10 xl:p-14 select-none">
            <div class="grid-dots absolute inset-0 opacity-40"></div>

            <!-- Ambient Background Glows -->
            <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-indigo-500/25 blur-[120px] pointer-events-none"></div>
            <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-blue-600/20 blur-[120px] pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/3 -translate-y-1/2 w-80 h-80 rounded-full bg-purple-600/15 blur-[100px] pointer-events-none"></div>

            <!-- Top Brand Header -->
            <div class="relative z-10">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 shrink-0 rounded-2xl bg-gradient-to-tr from-indigo-500 via-indigo-600 to-blue-500 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-500/30 border border-white/20">
                        PR
                    </div>
                    <div class="leading-tight">
                        <span class="font-extrabold text-xl tracking-tight block text-white">PR KK</span>
                        <span class="text-[11px] text-indigo-300 tracking-wider uppercase font-extrabold">PR Siswa Kelas Khusus</span>
                    </div>
                </div>
            </div>

            <!-- Middle Motivation Feature Card -->
            <div class="relative z-10 my-auto py-10 max-w-lg">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-bold text-indigo-200 backdrop-blur-md mb-8 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Platform Latihan Mandiri
                </div>
                <h2 class="text-3xl xl:text-5xl font-black leading-[1.18] text-white tracking-tight text-balance mb-6">
                    Belajar Lebih Terarah,<br>
                    <span class="bg-gradient-to-r from-indigo-200 via-sky-200 to-cyan-300 bg-clip-text text-transparent">Raih Prestasi Maksimal.</span>
                </h2>
                <p class="text-slate-300 text-sm xl:text-base leading-relaxed mb-8">
                    Akses ribuan soal terkurasi dengan pembahasan detail, pemantauan statistik pengerjaan, dan papan peringkat interaktif.
                </p>

                <!-- Features list glassmorphism cards -->
                <div class="space-y-3.5 text-sm text-slate-100 font-medium">
                    <div class="flex items-center gap-3.5 bg-white/[0.07] backdrop-blur-md border border-white/10 px-4 py-3 rounded-2xl shadow-sm hover:bg-white/[0.1] transition-colors">
                        <span class="w-8 h-8 rounded-xl bg-indigo-500/25 border border-indigo-400/30 flex items-center justify-center text-indigo-300 font-black text-sm shrink-0">✓</span>
                        <span class="text-slate-200 text-sm">Bank soal berkualitas dengan rumus matematika &amp; IPA presisi</span>
                    </div>
                    <div class="flex items-center gap-3.5 bg-white/[0.07] backdrop-blur-md border border-white/10 px-4 py-3 rounded-2xl shadow-sm hover:bg-white/[0.1] transition-colors">
                        <span class="w-8 h-8 rounded-xl bg-blue-500/25 border border-blue-400/30 flex items-center justify-center text-blue-300 font-black text-sm shrink-0">✓</span>
                        <span class="text-slate-200 text-sm">Sistem anti-kecurangan &amp; pengingat jadwal otomatis</span>
                    </div>
                    <div class="flex items-center gap-3.5 bg-white/[0.07] backdrop-blur-md border border-white/10 px-4 py-3 rounded-2xl shadow-sm hover:bg-white/[0.1] transition-colors">
                        <span class="w-8 h-8 rounded-xl bg-emerald-500/25 border border-emerald-400/30 flex items-center justify-center text-emerald-300 font-black text-sm shrink-0">✓</span>
                        <span class="text-slate-200 text-sm">Papan peringkat interaktif &amp; sertifikat digital</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="relative z-10 text-xs text-slate-400/80 font-semibold">
                &copy; {{ year }} PR KK • Hak cipta dilindungi.
            </div>
        </div>

        <!-- RIGHT: Form Panel -->
        <div class="lg:col-span-7 xl:col-span-6 right-bg relative overflow-y-auto flex items-center justify-center p-6 sm:p-12 lg:p-16">
            <div class="w-full max-w-md xl:max-w-[460px] my-auto">
                <!-- Mobile Brand Header -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-8">
                    <div class="w-11 h-11 shrink-0 rounded-2xl bg-gradient-to-tr from-indigo-600 to-blue-600 flex items-center justify-center text-white text-xs font-black shadow-md shadow-indigo-500/25">
                        PR
                    </div>
                    <div class="leading-tight text-left">
                        <span class="font-extrabold text-slate-900 block text-lg tracking-tight">PR KK</span>
                        <span class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">PR Siswa Kelas Khusus</span>
                    </div>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>