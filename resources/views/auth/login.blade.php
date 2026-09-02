<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - KPM Belajar Online</title>
    <meta name="description" content="Masuk ke akun KPM Belajar Online untuk mengakses ribuan soal dan pembahasan.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *{ font-family:'Poppins', sans-serif; }
        .font-display{ font-family:'Sora', sans-serif; }
        html, body{ height:100%; }
        body{ background:hsl(var(--background)); }

        @media (prefers-reduced-motion: no-preference){
            @keyframes floatSlow{ 0%,100%{ transform:translateY(0) } 50%{ transform:translateY(-14px) } }
            @keyframes blob{ 0%,100%{ transform:translate(0,0) scale(1) } 33%{ transform:translate(20px,-14px) scale(1.06) } 66%{ transform:translate(-16px,12px) scale(.96) } }
            @keyframes fadeUp{ from{ opacity:0; transform:translateY(16px) } to{ opacity:1; transform:translateY(0) } }
            @keyframes cardCycle{
                0%,30%{ opacity:1; transform: translateY(0) rotate(var(--rot,0deg)); }
                35%,100%{ opacity:0; transform: translateY(-12px) rotate(var(--rot,0deg)); }
            }
            @keyframes ringPulse{ 0%,100%{ box-shadow:0 0 0 0 rgba(252,198,38,.4) } 50%{ box-shadow:0 0 0 10px rgba(252,198,38,0) } }
            .anim-float{ animation: floatSlow 6s ease-in-out infinite; }
            .anim-blob{ animation: blob 12s ease-in-out infinite; }
            .fade-up{ opacity:0; animation: fadeUp .7s cubic-bezier(.16,1,.3,1) forwards; }
            .quiz-card{ animation: cardCycle 9s ease-in-out infinite; }
            .ring-pulse{ animation: ringPulse 2.6s ease-in-out infinite; }
        }
        @media (prefers-reduced-motion: reduce){ .fade-up{ opacity:1; } }

        /* Checkbox Toggle Switch */
        .switch {
            position: relative;
            width: 2.75rem;
            height: 1.5rem;
            background: hsl(var(--input));
            border-radius: 9999px;
            transition: background 0.3s ease;
            flex-shrink: 0;
        }
        .switch::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 1.25rem;
            height: 1.25rem;
            background: hsl(var(--background));
            border-radius: 9999px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }
        .peer:checked ~ .switch {
            background: hsl(var(--primary));
        }
        .peer:checked ~ .switch::after {
            transform: translateX(1.25rem);
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
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-gold-400 to-gold-500 flex items-center justify-center text-foreground font-display font-extrabold text-xl shadow-lg ring-pulse">K</div>
                    <div class="leading-tight">
                        <span class="font-display font-bold text-lg block">KPM Belajar Online</span>
                        <span class="text-xs text-white/60">Platform belajar & bank soal</span>
                    </div>
                </div>

                <h1 class="font-display text-[2.4rem] xl:text-[2.9rem] font-bold leading-[1.15] mb-5">
                    Setiap login,<br>
                    <span class="text-gold-grad">satu langkah</span><br>
                    lebih siap ujian.
                </h1>
                <p class="text-white/65 text-base leading-relaxed max-w-sm">
                    Ribuan soal terkurasi dengan pembahasan lengkap, siap diakses kapan pun kamu butuh.
                </p>
            </div>

            <!-- Signature element: floating quiz card stack -->
            <div class="relative z-10 h-44 hidden xl:block" aria-hidden="true">
                <div class="quiz-card absolute left-0 top-6 w-72 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 anim-float" style="--rot:-6deg; animation-delay:0s">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[11px] text-white/50">Soal · Matematika</p>
                        <span class="text-[10px] font-semibold bg-white/15 rounded-full px-2 py-0.5">Mudah</span>
                    </div>
                    <p class="text-sm font-medium mb-2.5">Nilai dari 2x + 5 = 13 adalah…</p>
                    <div class="space-y-1.5">
                        <div class="text-xs bg-white/10 rounded-lg px-2.5 py-1.5">A. 3</div>
                        <div class="text-xs bg-[var(--green)]/80 rounded-lg px-2.5 py-1.5 flex items-center justify-between">B. 4 <span>✓</span></div>
                    </div>
                </div>
                <div class="quiz-card absolute left-12 top-2 w-72 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 anim-float" style="--rot:4deg; animation-delay:-3s">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[11px] text-white/50">Soal · Bahasa Indonesia</p>
                        <span class="text-[10px] font-semibold bg-[var(--cyan)]/40 rounded-full px-2 py-0.5">Bahasa</span>
                    </div>
                    <p class="text-sm font-medium mb-2.5">Sinonim kata "cermat" adalah…</p>
                    <div class="space-y-1.5">
                        <div class="text-xs bg-white/10 rounded-lg px-2.5 py-1.5">A. Ceroboh</div>
                        <div class="text-xs bg-[var(--green)]/80 rounded-lg px-2.5 py-1.5 flex items-center justify-between">B. Teliti <span>✓</span></div>
                    </div>
                </div>
            </div>

            <div class="relative z-10 text-xs text-white/40 fade-up" style="animation-delay:.15s">
                © {{ date('Y') }} KPM Belajar Online. Hak cipta dilindungi.
            </div>
        </div>

        <!-- RIGHT: Form panel (full-bleed) -->
        <div class="right-bg relative overflow-hidden flex items-center justify-center p-5 sm:p-10 lg:p-16">
            <div class="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-[var(--cyan)] opacity-[.08] blur-3xl"></div>
            <div class="absolute -bottom-28 -left-24 w-80 h-80 rounded-full bg-[var(--gold)] opacity-[.09] blur-3xl"></div>

            <div class="relative z-10 w-full max-w-md fade-up" style="animation-delay:.05s">

                <!-- Mobile brand -->
                <div class="lg:hidden flex items-center justify-center gap-3 mb-6 relative">
                    <a href="{{ url('/') }}" aria-label="Kembali ke beranda"
                       class="absolute left-0 top-1/2 -translate-y-1/2 w-10 h-10 rounded-md bg-background border border-border text-foreground flex items-center justify-center shadow-sm hover:bg-muted transition text-lg">←</a>
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-md bg-gradient-to-br from-gold-400 to-gold-500 flex items-center justify-center text-foreground font-display font-extrabold text-lg">K</div>
                        <div class="leading-tight text-left">
                            <span class="font-display font-bold text-foreground block">KPM Belajar Online</span>
                            <span class="text-[11px] text-muted-foreground">Platform belajar & bank soal</span>
                        </div>
                    </div>
                </div>

                <!-- Auth card -->
                <div class="auth-card rounded-[1.75rem] p-5 sm:p-9">
                    <div class="mb-7">
                        <h1 class="font-display text-xl sm:text-[1.8rem] font-bold text-foreground">Selamat datang kembali</h1>
                        <p class="text-muted-foreground text-sm mt-1.5">Masuk untuk melanjutkan proses belajarmu.</p>
                    </div>

                    @if($errors->any())
                        <div class="bg-destructive/10 border border-destructive/20 border-l-4 border-l-destructive text-destructive px-4 py-3 rounded-md mb-6 text-sm" role="alert">
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4.5 h-4.5 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                <ul class="space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('login') }}" method="POST" class="space-y-5" id="loginForm" novalidate>
                        @csrf

                    <div class="field {{ $errors->has('email') ? 'has-error' : '' }}">
                        <span class="icon-wrap">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6c0-1.1.9-2 2-2Z"/><path d="m22 6-10 7L2 6"/></svg>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus placeholder=" " autocomplete="email" autocapitalize="none" spellcheck="false" inputmode="email">
                        <label for="email">Alamat Email</label>
                        <p class="field-hint text-xs text-destructive mt-1">@error('email'){{ $message }}@enderror</p>
                    </div>

                    <div class="field {{ $errors->has('password') ? 'has-error' : '' }}">
                        <span class="icon-wrap">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" name="password" id="password" required placeholder=" " autocomplete="current-password" class="pr-12">
                        <label for="password">Kata Sandi</label>
                            <button type="button" id="togglePassword" aria-label="Tampilkan kata sandi" aria-pressed="false"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition p-1">
                                <svg id="eyeOpen" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg id="eyeClosed" class="hidden" viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.6 21.6 0 0 1 5.06-6.06M9.9 4.24A10.6 10.6 0 0 1 12 4c7 0 11 8 11 8a21.5 21.5 0 0 1-2.61 3.94M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m1 1 22 22"/></svg>
                            </button>
                            <p class="field-hint text-xs text-destructive mt-1">@error('password'){{ $message }}@enderror</p>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                <input type="checkbox" name="remember" class="hidden peer">
                                <span class="switch"></span>
                                <span class="text-sm text-muted-foreground">Ingat saya</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="text-sm text-primary hover:text-primary/80 font-semibold transition">Lupa kata sandi?</a>
                        </div>

                        <button type="submit" id="submitBtn"
                                class="btn-auth w-full py-3.5 text-[15px] font-semibold text-primary-foreground rounded-md flex items-center justify-center gap-2.5">
                            <span id="btnLabel">Masuk</span>
                        </button>
                    </form>

                    <div class="mt-8 relative text-center">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-border"></div></div>
                        <span class="relative bg-background px-4 text-xs text-muted-foreground uppercase tracking-wide">atau</span>
                    </div>

                    <p class="mt-6 text-center text-sm text-muted-foreground">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="text-primary hover:text-primary/80 font-bold transition">Daftar sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function(){
            const toggleBtn = document.getElementById('togglePassword');
            const pwd = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpen');
            const eyeClosed = document.getElementById('eyeClosed');

            toggleBtn.addEventListener('click', function(){
                const isPassword = pwd.getAttribute('type') === 'password';
                pwd.setAttribute('type', isPassword ? 'text' : 'password');
                toggleBtn.setAttribute('aria-pressed', String(isPassword));
                toggleBtn.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                eyeOpen.classList.toggle('hidden', isPassword);
                eyeClosed.classList.toggle('hidden', !isPassword);
            });

            const form = document.getElementById('loginForm');
            const btn = document.getElementById('submitBtn');
            const label = document.getElementById('btnLabel');
            form.addEventListener('submit', function(e){
                if(!form.checkValidity()){ return; }
                btn.setAttribute('disabled', 'true');
                label.textContent = 'Memproses…';
                const spinner = document.createElement('span');
                spinner.className = 'spinner';
                btn.appendChild(spinner);
            });
        })();
    </script>
</body>
</html>
