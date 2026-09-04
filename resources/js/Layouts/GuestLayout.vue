<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="grid lg:grid-cols-2 min-h-screen">
        <!-- LEFT: Brand panel -->
        <div class="hidden lg:flex brand-panel relative overflow-hidden text-white flex-col justify-between p-12 xl:p-16">
            <div class="grid-dots absolute inset-0 opacity-[0.04]"></div>
            <div class="pattern-overlay absolute inset-0"></div>
            <div class="absolute w-[420px] h-[420px] rounded-full bg-fern opacity-[0.18] -top-28 -right-28 anim-blob blur-[80px]"></div>
            <div class="absolute w-[340px] h-[340px] rounded-full bg-dry-sage opacity-[0.14] -bottom-20 -left-20 anim-blob blur-[72px]" style="animation-delay:-4s"></div>
            <div class="absolute w-[260px] h-[260px] rounded-full bg-pine-teal opacity-[0.13] top-1/2 -left-24 anim-blob blur-[64px]" style="animation-delay:-8s"></div>
            <div class="absolute w-[200px] h-[200px] rounded-full bg-fern opacity-[0.08] bottom-1/3 right-1/4 anim-blob-slow blur-[56px]" style="animation-delay:-6s"></div>

            <div class="relative z-10 fade-up max-w-lg">
                <div class="flex items-center gap-4 mb-14">
                    <div class="w-13 h-13 rounded-[0.9rem] bg-gradient-to-br from-dry-sage to-fern flex items-center justify-center text-white font-bold text-xl shadow-xl shadow-fern/25 ring-pulse">K</div>
                    <div class="leading-tight">
                        <span class="font-bold text-lg tracking-wide block">KPM SMART</span>
                        <span class="text-[0.7rem] text-white/50 tracking-widest uppercase">Platform belajar & bank soal</span>
                    </div>
                </div>

                <h1 class="text-[2.4rem] xl:text-[2.9rem] font-bold leading-[1.15] mb-6 tracking-tight">
                    Setiap login,<br>
                    <span class="text-dry-sage bg-gradient-to-r from-dry-sage to-fern bg-clip-text text-transparent">satu langkah</span><br>
                    lebih siap ujian.
                </h1>
                <p class="text-white/55 text-[0.95rem] leading-relaxed max-w-sm">
                    Ribuan soal terkurasi dengan pembahasan lengkap, siap diakses kapan pun kamu butuh.
                </p>
            </div>

            <div class="relative z-10 text-xs text-white/30 fade-up tracking-wide" style="animation-delay:.15s">
                &copy; {{ new Date().getFullYear() }} KPM SMART. Hak cipta dilindungi.
            </div>
        </div>

        <!-- RIGHT: Form panel -->
        <div class="right-bg relative overflow-hidden flex items-center justify-center p-5 sm:p-10 lg:p-16">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-fern opacity-[0.06] blur-3xl"></div>
            <div class="absolute -bottom-28 -left-24 w-80 h-80 rounded-full bg-dry-sage opacity-[0.07] blur-3xl"></div>
            <div class="absolute top-1/2 right-1/3 w-64 h-64 rounded-full bg-dry-sage opacity-[0.04] blur-3xl anim-blob-slow" style="animation-delay:-3s"></div>

            <div class="relative z-10 w-full max-w-md fade-up" style="animation-delay:.05s">
                <!-- Mobile brand -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-8 relative">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-[0.65rem] bg-gradient-to-br from-dry-sage to-fern flex items-center justify-center text-white font-bold text-base shadow-lg shadow-fern/20">K</div>
                        <div class="leading-tight text-left">
                            <span class="font-bold text-foreground block text-[0.9rem] tracking-tight">KPM SMART</span>
                            <span class="text-[0.65rem] text-muted-foreground tracking-wide">Platform belajar & bank soal</span>
                        </div>
                    </div>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes floatSlow { 0%,100% { transform:translateY(0) } 50% { transform:translateY(-14px) } }
@keyframes blob {
    0%   { transform: translate(0, 0)       scale(1); }
    25%  { transform: translate(18px, -12px) scale(1.04); }
    50%  { transform: translate(-10px, 16px) scale(0.97); }
    75%  { transform: translate(14px, 8px)   scale(1.03); }
    100% { transform: translate(0, 0)        scale(1); }
}
@keyframes blobSlow {
    0%,100% { transform: translate(0,0) scale(1); }
    50%     { transform: translate(12px,-10px) scale(1.05); }
}
@keyframes fadeUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}
@keyframes ringPulse {
    0%,100% { box-shadow:0 0 0 0 rgba(161,203,53,.4); }
    50%     { box-shadow:0 0 0 12px rgba(161,203,53,0); }
}

.pattern-overlay {
    background-image:
        radial-gradient(circle at 1px 1px, rgba(255,255,255,0.06) 1px, transparent 0);
    background-size: 32px 32px;
}

.brand-panel {
    background:
        radial-gradient(95% 65% at 88% 8%, rgba(161,203,53,.35), transparent 58%),
        radial-gradient(70% 55% at 0% 92%, rgba(255,222,78,.15), transparent 60%),
        radial-gradient(60% 50% at 65% 100%, rgba(255,157,77,.12), transparent 62%),
        linear-gradient(150deg, #3D5A14 0%, #5A7A1E 35%, #769826 65%, #A1CB35 100%);
}

.right-bg {
    background:
        radial-gradient(45% 40% at 8% 0%, rgba(161,203,53,.08), transparent 60%),
        radial-gradient(42% 38% at 100% 92%, rgba(255,222,78,.06), transparent 60%),
        linear-gradient(135deg, #fafdf9 0%, #f8fcf5 50%, #f4f9f0 100%);
}

.anim-blob     { animation: blob 14s cubic-bezier(.45,.05,.55,.95) infinite; }
.anim-blob-slow { animation: blobSlow 18s cubic-bezier(.45,.05,.55,.95) infinite; }
.fade-up { opacity:0; animation: fadeUp .8s cubic-bezier(.16,1,.3,1) forwards; }
.ring-pulse { animation: ringPulse 3s ease-in-out infinite; }
</style>
