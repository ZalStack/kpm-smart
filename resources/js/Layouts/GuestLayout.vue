<script setup>
const year = new Date().getFullYear();
</script>

<template>
    <div class="grid lg:grid-cols-12 min-h-screen bg-background text-foreground antialiased selection:bg-primary selection:text-primary-foreground">
        <!-- LEFT: Artistic Brand Panel (5 cols on lg, 6 cols on xl) -->
        <div class="hidden lg:flex lg:col-span-5 xl:col-span-6 brand-panel relative overflow-hidden text-foreground flex-col justify-between p-10 xl:p-14 select-none">
            <div class="grid-dots absolute inset-0 opacity-100"></div>

            <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-brand-indigo/20 blur-[120px] pointer-events-none"></div>
            <div class="absolute -bottom-40 -left-40 w-[80px] h-[80px] rounded-full bg-brand-blue/20 blur-[80px] pointer-events-none"></div>
            <div class="absolute top-1/3 left-1/4 w-72 h-72 rounded-full bg-brand-cyan/20 blur-[100px] pointer-events-none"></div>

            <!-- Top Brand Header -->
            <div class="relative z-10">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 shrink-0 rounded-3xl bg-gradient-to-r from-brand-indigo to-brand-blue flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-300/40">
                        PR
                    </div>
                    <div class="leading-tight">
                        <span class="font-extrabold text-lg tracking-tight block text-foreground">PR KK</span>
                        <span class="text-[11px] text-primary/80 tracking-wider uppercase font-extrabold">PR Siswa Kelas Khusus</span>
                    </div>
                </div>
            </div>

            <!-- Middle Motivation Feature Card -->
            <div class="relative z-10 my-auto py-10 max-w-md">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-indigo/10 border border-brand-indigo/20 text-xs font-extrabold text-brand-indigo mb-8">
                    <span class="w-2 h-2 rounded-full bg-brand-indigo animate-pulse"></span>
                    Platform Latihan Mandiri
                </div>
                <h2 class="text-3xl xl:text-5xl font-extrabold leading-tight text-foreground tracking-tight text-balance mb-6">
                    Belajar Lebih Terarah,<br>
                    <span class="gradient-text">Raih Prestasi Maksimal.</span>
                </h2>
                <p class="text-muted-foreground text-sm leading-relaxed mb-8">
                    Akses ribuan soal terkurasi dengan pembahasan detail, pemantauan statistik pengerjaan, dan papan peringkat interaktif.
                </p>

                <!-- Features list pills -->
                <div class="space-y-3 text-sm text-foreground font-medium">
                    <div class="flex items-center gap-3 bg-card border border-border px-4 py-3 rounded-2xl shadow-sm">
                        <span class="w-6 h-6 rounded-lg bg-brand-indigo/10 flex items-center justify-center text-brand-indigo font-bold">✓</span>
                        <span>Bank soal berkualitas dengan rumus matematika &amp; IPA presisi</span>
                    </div>
                    <div class="flex items-center gap-3 bg-card border border-border px-4 py-3 rounded-2xl shadow-sm">
                        <span class="w-6 h-6 rounded-lg bg-brand-blue/10 flex items-center justify-center text-brand-blue font-bold">✓</span>
                        <span>Sistem anti-kecurangan &amp; pengingat jadwal otomatis</span>
                    </div>
                    <div class="flex items-center gap-3 bg-card border border-border px-4 py-3 rounded-2xl shadow-sm">
                        <span class="w-6 h-6 rounded-lg bg-brand-emerald/10 flex items-center justify-center text-brand-emerald font-bold">✓</span>
                        <span>Papan peringkat &amp; sertifikat digital</span>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="relative z-10 text-xs text-muted-foreground/60 font-extrabold">
                &copy; {{ year }} PR KK. Hak cipta dilindungi.
            </div>
        </div>

        <!-- RIGHT: Form Panel -->
        <div class="lg:col-span-7 xl:col-span-6 right-bg relative overflow-y-auto flex items-center justify-center p-6 sm:p-12 lg:p-16">
            <div class="w-full max-w-md my-auto">
                <!-- Mobile Brand Header -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-10">
                    <div class="w-10 h-10 shrink-0 rounded-3xl bg-gradient-to-r from-brand-indigo to-brand-blue flex items-center justify-center text-white text-xs font-black shadow-md shadow-blue-600/25">
                        PR
                    </div>
                    <div class="leading-tight text-left">
                        <span class="font-extrabold text-foreground block text-base tracking-tight">PR KK</span>
                        <span class="text-[11px] text-muted-foreground font-extrabold">PR Siswa Kelas Khusus</span>
                    </div>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>