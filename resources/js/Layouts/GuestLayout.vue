<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="grid lg:grid-cols-2 min-h-screen">
        <!-- LEFT: Brand panel -->
        <div class="hidden lg:flex brand-panel relative overflow-hidden text-white flex-col justify-between p-12 xl:p-16">
            <div class="grid-dots absolute inset-0 opacity-70"></div>
            <div class="absolute w-96 h-96 rounded-full bg-fern opacity-20 -top-24 -right-24 anim-blob blur-3xl"></div>
            <div class="absolute w-80 h-80 rounded-full bg-dry-sage opacity-15 -bottom-16 -left-16 anim-blob blur-3xl" style="animation-delay:-4s"></div>
            <div class="absolute w-56 h-56 rounded-full bg-pine-teal opacity-15 top-1/2 -left-20 anim-blob blur-3xl" style="animation-delay:-8s"></div>

            <div class="relative z-10 fade-up max-w-lg">
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-dry-sage to-fern flex items-center justify-center text-white font-bold text-xl shadow-lg ring-pulse">K</div>
                    <div class="leading-tight">
                        <span class="font-bold text-lg block">KPM Belajar Online</span>
                        <span class="text-xs text-white/60">Platform belajar & bank soal</span>
                    </div>
                </div>

                <h1 class="text-[2.4rem] xl:text-[2.9rem] font-bold leading-[1.15] mb-5">
                    Setiap login,<br>
                    <span class="text-dry-sage">satu langkah</span><br>
                    lebih siap ujian.
                </h1>
                <p class="text-white/65 text-base leading-relaxed max-w-sm">
                    Ribuan soal terkurasi dengan pembahasan lengkap, siap diakses kapan pun kamu butuh.
                </p>
            </div>

            <div class="relative z-10 text-xs text-white/40 fade-up" style="animation-delay:.15s">
                &copy; {{ new Date().getFullYear() }} KPM Belajar Online. Hak cipta dilindungi.
            </div>
        </div>

        <!-- RIGHT: Form panel -->
        <div class="right-bg relative overflow-hidden flex items-center justify-center p-5 sm:p-10 lg:p-16">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-fern opacity-[.08] blur-3xl"></div>
            <div class="absolute -bottom-28 -left-24 w-80 h-80 rounded-full bg-dry-sage opacity-[.09] blur-3xl"></div>

            <div class="relative z-10 w-full max-w-md fade-up" style="animation-delay:.05s">
                <!-- Mobile brand -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-6 relative">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-md bg-gradient-to-br from-dry-sage to-fern flex items-center justify-center text-white font-bold text-lg">K</div>
                        <div class="leading-tight text-left">
                            <span class="font-bold text-foreground block">KPM Belajar Online</span>
                            <span class="text-[11px] text-muted-foreground">Platform belajar & bank soal</span>
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
@keyframes blob { 0%,100% { transform:translate(0,0) scale(1) } 33% { transform:translate(20px,-14px) scale(1.06) } 66% { transform:translate(-16px,12px) scale(.96) } }
@keyframes fadeUp { from { opacity:0; transform:translateY(16px) } to { opacity:1; transform:translateY(0) } }
@keyframes ringPulse { 0%,100% { box-shadow:0 0 0 0 rgba(88,129,87,.4) } 50% { box-shadow:0 0 0 10px rgba(88,129,87,0) } }

.anim-blob { animation: blob 12s ease-in-out infinite; }
.fade-up { opacity:0; animation: fadeUp .7s cubic-bezier(.16,1,.3,1) forwards; }
.ring-pulse { animation: ringPulse 2.6s ease-in-out infinite; }
</style>
