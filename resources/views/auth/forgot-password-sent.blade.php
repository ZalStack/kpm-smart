<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cek Email Kamu - KPM Belajar Online</title>
    <meta name="description" content="Tautan pemulihan akun KPM Belajar Online telah dikirim ke email kamu.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root{
            --navy:#0E1447;
            --navy-deep:#090C2E;
            --blue:#27438D;
            --cyan:#00A2E9;
            --gold:#FCC626;
            --green:#009A4B;
            --ink:#1B1E34;
            --muted:#64748B;
            --line:#E5EAF4;
            --ring: rgba(0,162,233,.16);
        }
        *{ font-family:'Poppins', sans-serif; }
        .font-display{ font-family:'Sora', sans-serif; }
        html, body{ height:100%; }
        body{ background:#F6F8FC; }

        @media (prefers-reduced-motion: no-preference){
            @keyframes floatSlow{ 0%,100%{ transform:translateY(0) } 50%{ transform:translateY(-14px) } }
            @keyframes blob{ 0%,100%{ transform:translate(0,0) scale(1) } 33%{ transform:translate(20px,-14px) scale(1.06) } 66%{ transform:translate(-16px,12px) scale(.96) } }
            @keyframes fadeUp{ from{ opacity:0; transform:translateY(16px) } to{ opacity:1; transform:translateY(0) } }
            @keyframes ringPulse{ 0%,100%{ box-shadow:0 0 0 0 rgba(252,198,38,.4) } 50%{ box-shadow:0 0 0 10px rgba(252,198,38,0) } }
            @keyframes sendFly{ 0%{ transform:translate(-4px,2px); opacity:0 } 30%{ opacity:1 } 70%{ transform:translate(6px,-3px); opacity:1 } 100%{ transform:translate(14px,-8px); opacity:0 } }
            @keyframes popIn{ 0%{ transform:scale(.6); opacity:0 } 70%{ transform:scale(1.06) } 100%{ transform:scale(1); opacity:1 } }
            .anim-float{ animation: floatSlow 6s ease-in-out infinite; }
            .anim-blob{ animation: blob 12s ease-in-out infinite; }
            .fade-up{ opacity:0; animation: fadeUp .7s cubic-bezier(.16,1,.3,1) forwards; }
            .ring-pulse{ animation: ringPulse 2.6s ease-in-out infinite; }
            .icon-pop{ animation: popIn .55s cubic-bezier(.16,1,.3,1) .15s both; }
            .send-fly{ animation: sendFly 2.2s ease-in-out infinite; transform-origin:center; }
        }
        @media (prefers-reduced-motion: reduce){ .fade-up,.icon-pop{ opacity:1; animation:none; } }

        .brand-panel{
            background:
                radial-gradient(95% 65% at 88% 8%, rgba(0,162,233,.34), transparent 58%),
                radial-gradient(70% 55% at 0% 92%, rgba(252,198,38,.16), transparent 60%),
                radial-gradient(60% 50% at 65% 100%, rgba(0,154,75,.14), transparent 62%),
                linear-gradient(150deg, var(--navy-deep) 0%, var(--navy) 46%, #1B2E74 100%);
        }
        .grid-dots{ background-image: radial-gradient(rgba(255,255,255,.13) 1px, transparent 1px); background-size: 22px 22px; }
        .text-gold-grad{ background:linear-gradient(92deg,#FCC626 0%,#FFD877 60%,#FFB020 100%); -webkit-background-clip:text; background-clip:text; color:transparent; }

        .right-bg{
            background:
                radial-gradient(45% 40% at 8% 0%, rgba(0,162,233,.07), transparent 60%),
                radial-gradient(42% 38% at 100% 92%, rgba(252,198,38,.08), transparent 60%),
                #F6F8FC;
        }

        .auth-card{
            background:rgba(255,255,255,.9);
            -webkit-backdrop-filter:blur(14px);
            backdrop-filter:blur(14px);
            border:1px solid rgba(229,234,244,.95);
            box-shadow:0 24px 60px -24px rgba(14,20,71,.18);
        }

        .btn-primary{
            position:relative; overflow:hidden;
            background: linear-gradient(135deg, var(--blue), var(--navy));
            box-shadow:0 10px 24px -10px rgba(14,20,71,.55);
            transition: transform .2s ease, box-shadow .25s ease;
        }
        .btn-primary:hover{ transform: translateY(-2px); box-shadow: 0 16px 32px -12px rgba(0,162,233,.55); }
        .btn-primary:active{ transform: translateY(0); }

        .btn-ghost{
            border:1.5px solid var(--line);
            background:#fff;
            transition: all .2s ease;
        }
        .btn-ghost:hover{ border-color:var(--cyan); color:var(--blue); transform: translateY(-2px); box-shadow:0 10px 22px -14px rgba(0,162,233,.5); }

        /* Step indicator */
        .step-dot{ width:.5rem; height:.5rem; border-radius:999px; background:var(--line); transition: all .25s ease; }
        .step-dot.active{ width:1.5rem; background: var(--cyan); }
        .step-dot.done{ background: var(--green); }

        .email-chip{
            background:#EDF4FF;
            border:1px solid #D7E5FB;
            color:var(--blue);
        }
    </style>
</head>
<body class="min-h-screen">

    <div class="grid lg:grid-cols-2 min-h-screen">

        <!-- LEFT: Brand panel (full-bleed) -->
        <div class="hidden lg:flex brand-panel relative overflow-hidden text-white flex-col justify-between p-12 xl:p-16">
            <div class="grid-dots absolute inset-0 opacity-70"></div>
            <div class="absolute w-96 h-96 rounded-full bg-[var(--cyan)] opacity-20 -top-24 -right-24 anim-blob blur-3xl"></div>
            <div class="absolute w-80 h-80 rounded-full bg-[var(--gold)] opacity-15 -bottom-16 -left-16 anim-blob blur-3xl" style="animation-delay:-4s"></div>
            <div class="absolute w-56 h-56 rounded-full bg-[var(--green)] opacity-15 top-1/2 -left-20 anim-blob blur-3xl" style="animation-delay:-8s"></div>

            <div class="relative z-10 fade-up max-w-lg">
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[var(--gold)] to-[#f5a623] flex items-center justify-center text-[var(--navy-deep)] font-display font-bold text-xl shadow-lg ring-pulse">K</div>
                    <div class="leading-tight">
                        <span class="font-display font-bold text-lg block">KPM Belajar Online</span>
                        <span class="text-xs text-white/60">Platform belajar & bank soal</span>
                    </div>
                </div>

                <h1 class="font-display text-[2.4rem] xl:text-[2.9rem] font-bold leading-[1.15] mb-5">
                    Satu langkah<br>
                    <span class="text-gold-grad">lagi</span> menuju<br>
                    akunmu kembali.
                </h1>
                <p class="text-white/65 text-base leading-relaxed max-w-sm">
                    Buka email dari kami, klik tautan pemulihan, lalu buat kata sandi baru kamu.
                </p>
            </div>

            <!-- Signature element: floating mail card -->
            <div class="relative z-10 h-44 hidden xl:block" aria-hidden="true">
                <div class="anim-float absolute left-2 top-4 w-64 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4">
                    <div class="flex items-center gap-2.5 mb-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[var(--cyan)]/25 flex items-center justify-center">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="#7FDBFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <p class="text-[11px] text-white/50">Keamanan Akun</p>
                    </div>
                    <p class="text-sm font-medium">Tautan unik · sekali pakai · aman.</p>
                </div>
                <div class="anim-float absolute left-24 top-16 w-56 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4" style="animation-delay:-3s">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] text-white/50">Masa Berlaku Tautan</p>
                        <span class="text-[10px] font-semibold bg-[var(--green)]/70 rounded-full px-2 py-0.5">30 menit</span>
                    </div>
                    <p class="text-sm font-medium mt-2">Setelah itu, minta tautan baru.</p>
                </div>
            </div>

            <div class="relative z-10 text-xs text-white/40 fade-up" style="animation-delay:.15s">
                © {{ date('Y') }} KPM Belajar Online. Hak cipta dilindungi.
            </div>
        </div>

        <!-- RIGHT: Content panel (full-bleed) -->
        <div class="right-bg relative overflow-hidden flex items-center justify-center p-5 sm:p-10 lg:p-16">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-[var(--cyan)] opacity-[.08] blur-3xl"></div>
            <div class="absolute -bottom-28 -left-24 w-80 h-80 rounded-full bg-[var(--gold)] opacity-[.09] blur-3xl"></div>

            <div class="relative z-10 w-full max-w-md fade-up" style="animation-delay:.05s">

                <!-- Mobile brand -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-6 relative">
                    <a href="{{ route('login') }}" aria-label="Kembali ke halaman masuk"
                       class="absolute left-0 top-1/2 -translate-y-1/2 w-10 h-10 rounded-xl bg-white border border-border text-[var(--navy)] flex items-center justify-center shadow-sm hover:bg-muted transition text-lg">←</a>
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[var(--gold)] to-[#f5a623] flex items-center justify-center text-[var(--navy-deep)] font-display font-bold text-lg">K</div>
                        <div class="leading-tight text-left">
                            <span class="font-display font-bold text-[var(--navy)] block">KPM Belajar Online</span>
                            <span class="text-[11px] text-muted-foreground">Platform belajar & bank soal</span>
                        </div>
                    </div>
                </div>

                <!-- Auth card -->
                <div class="auth-card rounded-[1.75rem] p-5 sm:p-9 text-center">

                    <!-- Step indicator -->
                    <div class="hidden sm:flex items-center justify-center gap-2 mb-6" aria-hidden="true">
                        <span class="step-dot done"></span>
                        <span class="step-dot active"></span>
                        <span class="text-xs text-[var(--muted)] ml-2">Langkah 2 dari 2 · Cek Email</span>
                    </div>

                    <!-- Ilustrasi ikon email -->
                    <div class="mx-auto mb-6 icon-pop w-20 h-20 rounded-3xl bg-gradient-to-br from-[rgba(0,162,233,.12)] to-[rgba(39,67,141,.10)] border border-[#D7E5FB] flex items-center justify-center relative overflow-visible">
                        <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="var(--blue)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6c0-1.1.9-2 2-2Z"/>
                            <path d="m22 6-10 7L2 6"/>
                        </svg>
                        <span class="send-fly absolute right-1 top-1 w-2.5 h-2.5 rounded-full bg-[var(--green)]"></span>
                    </div>

                    <h1 class="font-display text-xl sm:text-[1.8rem] font-bold text-[var(--navy)]">Cek Email Kamu</h1>

                    <p class="text-[var(--muted)] text-sm mt-2 leading-relaxed">
                        Jika email tersebut terdaftar, tautan pemulihan sudah kami kirimkan ke
                    </p>
                    <p class="email-chip inline-block mt-2 px-4 py-1.5 rounded-full text-sm font-semibold break-all max-w-full">{{ $email }}</p>

                    <div class="mt-5 mx-auto max-w-sm text-left bg-gold-400/10 border border-gold-400/20 border-l-4 border-l-gold-400 rounded-xl px-4 py-3">
                        <p class="text-[13px] text-gold-600 leading-relaxed">
                            <strong>&#9200; Berlaku 30 menit.</strong> Tautan hanya bisa dipakai satu kali.
                            Tidak menemukan email? Periksa folder <em>spam</em> atau <em>promosi</em>.
                        </p>
                    </div>

                    <div class="mt-7 space-y-3">
                        <a href="{{ route('login') }}"
                           class="btn-primary btn-shine w-full py-3.5 text-[15px] font-semibold text-white rounded-xl flex items-center justify-center gap-2.5">
                            Kembali ke Halaman Masuk
                        </a>
                        <a href="{{ route('password.request') }}"
                           class="btn-ghost w-full py-3.5 text-[15px] font-semibold text-[var(--navy)] rounded-xl flex items-center justify-center gap-2.5">
                            Email tidak masuk? Kirim ulang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
